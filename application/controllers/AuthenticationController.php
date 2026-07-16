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
            ];

            $this->session->set_userdata($session_data);
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
