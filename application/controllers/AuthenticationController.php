<?php
defined('BASEPATH') or exit('No direct script access allowed');

class AuthenticationController extends CI_Controller
{
    public function login()
    {
        $post = $this->input->post();
        $user = $this->accounts->with_student()->get(['username' => $post['username']]);

        if ($user && $user->password == $post['password']) {
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
}
