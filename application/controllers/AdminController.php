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
        // Load the database library
        $this->load->database();

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
        $data['assessments'] = $this->assessments->as_array()->get_all();

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
}
