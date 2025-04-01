<?php
defined('BASEPATH') or exit('No direct script access allowed');

class StudentController extends CI_Controller
{
    public function student_info()
    {
        $this->load->view('student_info');
    }

    public function get_id()
    {
        $input = $this->input->post();
        $student = $this->student_master->where([
            'lastname' => $input['lastname'],
            'firstname' => $input['firstname']
        ])->get();

        if (!$student) {
            $this->session->set_flashdata('error', 'Student not found');
            redirect('find_id');
        }

        $this->load->view('student_details', $student);
    }

    public function find_id()
    {
        $this->load->view('find_id');
    }
}