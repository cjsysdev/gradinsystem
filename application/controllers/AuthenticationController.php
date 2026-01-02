<?php
defined('BASEPATH') or exit('No direct script access allowed');

class AuthenticationController extends CI_Controller
{
    public function login()
    {
        $post = $this->input->post();
        $user = $this->accounts->with_student()->get(['username' => $post['username']]);
        $section = $this->class_student->get(['student_id' => $user->student_id]);

        if ($user && $user->password == $post['password']) {
            $session_data = [
                'account_id' => $user->account_id,
                'student_id' => $user->student_id,
                'student_no' => $user->student->student_no,
                'lastname' => $user->student->lastname,
                'firstname' => $user->student->firstname,
                'course' => $user->student->course,
                'current_year' => $user->student->current_year,
                'section' => $section->section,
                'role' => $user->role,
                'online' => true,
                'exam_term' => true,
                'exam_review' => false
            ];

            $this->session->set_userdata($session_data);
            redirect('attendance');
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
