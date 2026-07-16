<?php
defined('BASEPATH') or exit('No direct script access allowed');

class AuthenticationController extends CI_Controller
{
    public function login()
    {
        $post = $this->input->post();
        $user = $this->accounts->with_student()->get(['username' => $post['username']]);

        $authenticated = false;
        if ($user) {
            $stored = (string) $user->password;
            $info = password_get_info($stored);
            if (!empty($info['algo'])) {
                // Stored value is already a bcrypt hash — normal verification.
                $authenticated = password_verify($post['password'], $stored);
            } else {
                // Legacy plaintext row: timing-safe compare, then upgrade the
                // stored value to a hash in place so it migrates on first login.
                if (hash_equals($stored, (string) $post['password'])) {
                    $authenticated = true;
                    $this->accounts->update(
                        ['password' => password_hash($post['password'], PASSWORD_DEFAULT)],
                        $user->account_id
                    );
                }
            }
        }

        if ($user && $authenticated) {
            $active_semester = $this->db->where('is_active', 1)->get('semester_master')->row_array();

            $enrollment = null;
            if ($active_semester) {
                $enrollment = $this->class_student->get([
                    'student_id'  => $user->student_id,
                    'semester_id' => $active_semester['trans_no'],
                ]);
            }

            $session_data = [
                'account_id'   => $user->account_id,
                'student_id'   => $user->student_id,
                'student_no'   => $user->student->student_no,
                'lastname'     => $user->student->lastname,
                'firstname'    => $user->student->firstname,
                'course'       => $user->student->course,
                'current_year' => $user->student->current_year,
                'section'      => $enrollment ? $enrollment->section : null,
                'role'         => $user->role,
                'username'     => $user->username,
                'profile_pic'  => $user->profile_pic,
                'online'       => true,
                'exam_term'    => false,
                'exam_review'  => false,
                'must_change_password' => (int) ($user->must_change_password ?? 0),
            ];

            $this->session->set_userdata($session_data);

            // Student was reset to a temporary password by the admin — force a change first.
            if ($user->role !== 'admin' && !empty($session_data['must_change_password'])) {
                redirect('update_account_form');
            }

            if ($user->role === 'admin') {
                redirect('dashboard');
            } else {
                redirect($enrollment ? 'attendance' : 'student/add_section');
            }
        } else {
            $this->session->set_flashdata('error', 'Login Error');
            redirect();
        }
    }

    public function logout()
    {
        $this->session->sess_destroy();
        redirect();
    }

    // Public: shows the "Forgot Password" request form. Locked-out students
    // land here from the login page. install() lazily creates the storage.
    public function forgot_password()
    {
        $this->password_reset_request->install();
        $this->load->view('forgot_password');
    }

    // Public: records a pending reset request. The student identifies themselves
    // by student number + last name (two fields to reduce abuse). The request
    // becomes an admin notification; no password is exposed here.
    public function submit_forgot_password()
    {
        $this->password_reset_request->install();

        $student_no = trim($this->input->post('student_no'));
        $lastname   = trim($this->input->post('lastname'));

        if ($student_no === '' || $lastname === '') {
            $this->session->set_flashdata('error', 'Please enter your student number and last name.');
            redirect('forgot_password');
            return;
        }

        $student = $this->student_master
            ->where(['student_no' => $student_no, 'lastname' => $lastname])
            ->get();

        if (!$student) {
            $this->session->set_flashdata('error', 'No student found with that student number and last name. Please check with your instructor.');
            redirect('forgot_password');
            return;
        }

        $account = $this->accounts->get(['student_id' => $student->trans_no]);
        if (!$account) {
            $this->session->set_flashdata('error', 'No account is linked to that student. Please see your instructor.');
            redirect('forgot_password');
            return;
        }

        if ($this->password_reset_request->has_pending($student->trans_no)) {
            $this->session->set_flashdata('success', 'You already have a pending request. Please see your instructor to get your temporary password.');
            redirect('forgot_password');
            return;
        }

        $this->password_reset_request->insert([
            'student_id' => $student->trans_no,
            'student_no' => $student_no,
            'status'     => 'pending',
        ]);

        $this->session->set_flashdata('success', 'Your request was sent to your instructor/admin. Please see them to get your temporary password.');
        redirect('forgot_password');
    }

    // Restores the admin session stashed by AdminController::login_as_student()
    // before it was overwritten with the impersonated student's session.
    public function return_to_admin()
    {
        $impersonator = $this->session->userdata('impersonator');
        if (!$impersonator) {
            redirect('login');
            return;
        }

        $admin = $this->accounts->as_array()->get(['account_id' => $impersonator['account_id']]);
        if (!$admin) {
            $this->session->sess_destroy();
            redirect('login');
            return;
        }

        $this->session->unset_userdata('impersonator');
        $this->session->set_userdata([
            'account_id'   => $admin['account_id'],
            'student_id'   => $admin['student_id'],
            'role'         => $admin['role'],
            'username'     => $admin['username'],
            'profile_pic'  => $admin['profile_pic'],
            'online'       => true,
        ]);

        redirect('dashboard');
    }
}
