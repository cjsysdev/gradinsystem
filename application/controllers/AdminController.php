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
        $class = $this->class_schedule->class_today(date('D'));

        // Get the current discussion mode from the database
        $query = $this->db->get_where('global_settings', [
            'setting_key' => 'discussion_mode',
        ]);
        $data['discussion_mode'] = $query->row()->setting_value === '1';

        $data['attendance'] = $this->attendance->get_double_entry(date('Y-m-d'), $class['schedule_id']);

        $data['lates'] = $this->attendance->get_student_status($class['schedule_id'], date('Y-m-d'), 'late');

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

        $day = date('D');
        $class = $this->class_schedule->class_today($day);

        $term = 'midterm';

        $data['assessments'] = (isset($class['schedule_id'])) ?
            $this->assessments->where(['schedule_id' => $class["schedule_id"]])->order_by('schedule_id', 'asc')
            ->with_class_schedule()->as_array()->get_all() :
            $this->assessments->order_by('schedule_id', 'asc')
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

        // Fetch all classworks (submitted and missing) for the student
        $this->load->model('classworks');
        $this->load->model('assessments');
        $submitted_classworks = $this->classworks->get_submissions_by_student($student_id);
        $all_assessments = $this->assessments->get_all_assessments();

        // Merge submitted classworks with missing ones
        $classworks = [];
        foreach ($all_assessments as $assessment) {
            $found = false;
            foreach ($submitted_classworks as $submission) {
                if ($submission['assessment_id'] == $assessment['assessment_id']) {
                    $classworks[] = $submission;
                    $found = true;
                    break;
                }
            }
            if (!$found) {
                $classworks[] = [
                    'assessment_id' => $assessment['assessment_id'],
                    'title' => $assessment['title'],
                    'classwork_id' => null,
                    'score' => null,
                    'created_at' => null,
                    'status' => 'missing',
                ];
            }
        }

        $data['classworks'] = $classworks;

        // Load the view
        $this->load->view('admin/student_submissions', $data);
    }

    public function student_submissions()
    {
        $student_id = $this->input->get('student_id');
        $data['students'] = $this->student_master->get_all(); // Already correct

        if ($student_id) {
            // Fetch submissions for the selected student
            $data['submissions'] = $this->classworks->get_submissions_by_student($student_id);
        } else {
            $data['submissions'] = [];
        }

        // Load the view
        $this->load->view('admin/student_submissions', $data);
    }

    public function search_students()
    {
        $search = $this->input->get('search');
        $students = [];
        if ($search) {
            $students = $this->student_master->search_by_name($search); // Should return array
        }
        header('Content-Type: application/json');
        echo json_encode($students);
    }

    public function view_attendance()
    {
        $section_id = $this->input->get('section_id');
        $start_date = $this->input->get('start_date');

        // Fetch all sections for the dropdown
        $data['sections'] = $this->class_schedule->get_sections();

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

        $this->load->view('admin/view_attendance', $data);
    }

    public function active_participation($assessment_id = null)
    {
        $section_id = $this->input->get('section_id');
        $date = $this->input->get('date') ?? date('Y-m-d');

        // Fetch all sections for the dropdown
        $this->db->distinct();
        $this->db->select('section');
        $data['sections'] = $this->db->get('class_schedule')->result_array();

        // Fetch present students if section and date are provided
        if ($section_id) {
            $this->load->model('attendance');
            $data['students'] = $this->attendance->get_present_students($section_id, $date);
            $data['selected_section_id'] = $section_id;
            $data['date'] = $date;
        } else {
            $data['students'] = [];
            $data['selected_section_id'] = null;
            $data['date'] = $date;
        }

        // Pass the assessment ID for scoring
        $data['assessment_id'] = $assessment_id;

        // Load the view
        $this->load->view('admin/active_participation', $data);
    }

    public function check_new_submissions_by_assessment($assessment_id)
    {
        // Fetch the latest submissions for the assessment
        $submissions = $this->classworks->get_all_submissions($assessment_id);

        // Return the data as JSON
        echo json_encode($submissions);
    }

    public function uncleared_students($section)
    {
        $this->load->model('class_student');
        $data['students'] = $this->class_student->get_uncleared_students_by_section($section);
        $data['section'] = $section;

        // var_dump($data);
        $this->load->view('admin/uncleared_students', $data);
    }

    public function clear_student($id, $section)
    {
        $this->load->model('class_student');
        $this->class_student->clear_student($id);
        redirect('AdminController/uncleared_students/' . $section);
    }

    public function increment_randomized_count($classwork_id)
    {
        $this->classworks->set('randomized_count', 'randomized_count+1', FALSE)
            ->where('classwork_id', $classwork_id)
            ->update('classwork');
        echo json_encode(['success' => true]);
    }

    public function add_score($classwork_id, $score)
    {
        $result = $this->classworks->add_score($classwork_id, $score);
        echo json_encode(['success' => $result]);
    }
}
