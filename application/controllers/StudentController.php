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

    // New method to update username and password
    public function update_account()
    {
        $input = $this->input->post();

        // Check if the logged-in student is updating their own account
        if ($this->session->student_id != $input['student_id']) {
            $this->session->set_flashdata('error', 'You are not authorized to update this account.');
            redirect('update_account_form');
        }

        // Validate input
        if (empty($input['username']) || empty($input['password']) || empty($input['student_id'])) {
            $this->session->set_flashdata('error', 'All fields are required.');
            redirect('update_account_form');
        }

        // Hash the password
        $hashed_password = $input['password'];

        // Update the account in the database
        $this->db->where('student_id', $input['student_id']);
        $this->db->update('accounts', [
            'username' => $input['username'],
            'password' => $hashed_password
        ]);

        if ($this->db->affected_rows() > 0) {
            $this->session->set_flashdata('success', 'Account updated successfully.');
        } else {
            $this->session->set_flashdata('error', 'Failed to update account. Please try again.');
        }

        redirect('update_account_form');
    }

    public function update_account_form()
    {
        $this->load->view('update_account_form');
    }

    // Method to get the current discussion mode
    public function get_discussion_mode()
    {
        // Load the database library
        $this->load->database();

        // Query the database for the discussion mode
        $query = $this->db->get_where('global_settings', [
            'setting_key' => 'discussion_mode',
        ]);

        // Check if the setting exists and return the value
        if ($query->num_rows() > 0) {
            $discussion_mode = $query->row()->setting_value === '1';
            echo json_encode(['discussion_mode' => $discussion_mode]);
        } else {
            echo json_encode(['error' => 'Discussion mode setting not found.']);
        }
    }

    public function add_section()
    {
        date_default_timezone_set('Asia/Manila');

        $day = date('D');

        $class = $this->class_schedule->class_today($day);

        if (!$class) {
            $this->session->set_flashdata('error', 'No class found for today.');
            // redirect('attendance');
        }

        $data = [
            'class' => $class ?? [],
        ];

        // This method can be used to redirect students to a section addition page
        $this->load->view('add_section', $data);
    }

    public function section()
    {
        $post = $this->input->post();
        $id = $this->session->student_id;

        if (empty($post['section'])) {
            $this->session->set_flashdata('error', 'Section cannot be empty.');
            redirect('student/add_section');
        } else {
            // Add the section to the class_student model
            $this->class_student->add_section($id, $post['section']);
            $this->class_student->update_class($id, $post['class_id']);
            // Set a success message
            $this->session->set_flashdata('success', 'Section added successfully.');
            redirect('attendance');
        }
    }
}
