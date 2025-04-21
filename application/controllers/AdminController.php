<?php
defined('BASEPATH') or exit('No direct script access allowed');

class AdminController extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
    }

    public function dashboard()
    {
        // Get the current discussion mode from the database
        $query = $this->db->get_where('global_settings', [
            'setting_key' => 'discussion_mode',
        ]);
        $data['discussion_mode'] = $query->row()->setting_value === '1';

        $this->load->view('admin/dashboard', $data);
    }

    // Toggle discussion mode
    public function toggle_discussion_mode()
    {
        // Load the database library
        $this->load->database();

        // Get the current mode from the database
        $query = $this->db->get_where('global_settings', [
            'setting_key' => 'discussion_mode',
        ]);
        $current_mode = $query->row()->setting_value ?? '0';

        // Toggle the mode
        $new_mode = $current_mode === '1' ? '0' : '1';

        // Update the database
        $this->db->where('setting_key', 'discussion_mode');
        $this->db->update('global_settings', ['setting_value' => $new_mode]);

        // Redirect back to the dashboard
        redirect('dashboard');
    }

    public function all_submissions($assessment_id = null)
    {
        // Fetch all assessments for the dropdown
        $data['assessments'] = $this->assessments->order_by('created_at', 'DESC')->order_by('iotype_id', 'DESC')
            ->with_class_schedule()->as_array()->get_all();

        // Fetch submissions for the selected assessment
        if ($assessment_id) {
            $data['submissions'] = $this->classworks->get_all_submissions(
                $assessment_id
            );
            $data['selected_assessment_id'] = $assessment_id;
        } else {
            $data['submissions'] = [];
            $data['selected_assessment_id'] = null;
        }
        // Load the view
        $this->load->view('admin/all_submission', $data);
    }

    public function manage_json_files()
    {
        $this->load->database();

        if ($this->input->post()) {
            $assessment_id = $this->input->post('assessment_id');
            $json_file_path = $this->input->post('json_file_path');

            $this->db->replace('assessment_files', [
                'assessment_id' => $assessment_id,
                'json_file_path' => $json_file_path
            ]);

            $this->session->set_flashdata('success', 'JSON file path updated successfully.');
            redirect('AdminController/manage_json_files');
        }

        $data['assessments'] = $this->db->get('assessments')->result_array();
        $data['json_files'] = $this->db->get('assessment_files')->result_array();

        $this->load->view('manage_json_files', $data);
    }

    public function view_student_submissions($student_id = null)
    {
        // Check if a student ID is provided
        if (!$student_id) {
            $this->session->set_flashdata('error', 'No student selected.');
            redirect('AdminController/dashboard');
        }

        // Fetch student details
        $data['student'] = $this->accounts->as_array()->get(['student_id' => $student_id]);

        if (!$data['student']) {
            $this->session->set_flashdata('error', 'Student not found.');
            redirect('AdminController/dashboard');
        }

        // Fetch all submitted classworks for the student
        $data['submissions'] = $this->classworks->get_submissions_by_student($student_id);

        // Load the view
        $this->load->view('admin/student_submissions', $data);
    }

    public function student_submissions()
    {
        $search = $this->input->get('search');

        if ($search) {
            // Search for the student in the student_master table
            $this->db->like('firstname', $search);
            $this->db->or_like('lastname', $search);
            $student = $this->db->get('student_master')->row_array();

            if ($student) {
                // Fetch submissions for the found student
                $data['submissions'] = $this->classworks->get_submissions_by_student($student['trans_no']);
            } else {
                $data['submissions'] = [];
                $this->session->set_flashdata('error', 'No student found with the given name.');
            }
        } else {
            $data['submissions'] = [];
        }

        // Load the view
        $this->load->view('admin/student_submissions', $data);
    }

    public function view_attendance()
    {
        $section_id = $this->input->get('section_id');
        $start_date = $this->input->get('start_date');

        // Fetch all sections for the dropdown
        $data['sections'] = $this->accounts->as_array()->get_all();

        // Fetch attendance data if section and date are provided
        if ($section_id && $start_date) {
            $data['attendance'] = $this->attendance->get_attendance_by_section($section_id, $start_date);
            $data['selected_section_id'] = $section_id;
            $data['start_date'] = $start_date;
        } else {
            $data['attendance'] = [];
            $data['selected_section_id'] = null;
            $data['start_date'] = null;
        }

        // Load the view
        $this->load->view('admin/view_attendance', $data);
    }
}
