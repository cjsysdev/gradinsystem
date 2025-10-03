<?php
defined('BASEPATH') or exit('No direct script access allowed');

class ClassworkController extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->is_offline = !isset($_SESSION['online']);
    }

    public function classwork()
    {
        if ($this->is_offline) redirect();
        $student_id = $this->session->student_id;
        $student = $this->class_student->where('student_id', $student_id)->get();

        if (!$student) {
            $this->session->set_flashdata('error', 'Student section not found');
            redirect('attendance');
        }

        $missing = $this->assessments->get_students_assessments(
            $student_id,
            $student->section
        );

        $submitted = $this->assessments->get_submitted_assessments(
            $student_id
        );

        $data = [
            'assessments' => $missing,
            'submitted' => $submitted
        ];

        $this->load->view('classwork', $data);
    }

    public function submit_classwork()
    {
        $post = $this->input->post();
        $value = $this->classworks->where(
            [
                'student_id' => $this->session->student_id,
                'assessment_id' => $post['assessment_id']
            ]
        )->get();

        if (!$value) {
            $this->classworks->insert($post);
            $this->session->set_flashdata('success', 'Classwork submitted successfully');
        } else {
            $this->session->set_flashdata('warning', 'You have already submitted this classwork!');
        }

        redirect('classwork');
    }

    public function add_score()
    {
        $classwork_id = $this->input->post('classwork_id');
        $student_id = $this->input->post('student_id');
        $score = $this->input->post('score');
        $assessment_id = $this->input->post('assessment_id');

        // Validate inputs
        if (!is_numeric($score) || $score < 0) {
            $this->session->set_flashdata('error', 'Invalid score.');
            redirect('all_submissions');
        }

        // Update the score in the database
        $this->classworks->update_score($classwork_id, $student_id, $score);

        $this->session->set_flashdata('success', 'Score updated successfully!');
        redirect("all_submissions/$assessment_id");
    }

    public function add_rand_score($classwork_id, $type, $assessment_id)
    {
        switch ($type) {
            case 5:
                $score = randomizeNumber(5.0, 6.9);
                break;
            case 6:
                $score = randomizeNumber(6.0, 6.9);
                break;
            case 7:
                $score = randomizeNumber(7.0, 7.9);
                break;
            case 8:
                $score = randomizeNumber(8.0, 8.9);
                break;
            case 9:
                $score = randomizeNumber(9.0, 9.9);
                break;
            case 10:
                $score = 10;
                break;
            default:
                $score = null;
                break;
        }

        $this->classworks->add_score($classwork_id, $score);
        redirect("all_submissions/$assessment_id");
    }

    public function student_submission($classwork_id)
    {
        $submission = $this->classworks->with_assessments()->as_array()->get($classwork_id);

        $data = [
            'classwork' => $submission
        ];

        $this->load->view('student_submission', $data);
    }

    public function unsubmit_work()
    {
        // Get the JSON input
        $input = json_decode(file_get_contents('php://input'), true);

        // Validate the input
        if (!isset($input['classwork_id'])) {
            echo json_encode(['success' => false, 'message' => 'Invalid request.']);
            return;
        }

        $classwork_id = $input['classwork_id'];

        $this->db->where('classwork_id', $classwork_id);
        $deleted = $this->db->delete('classworks');

        if ($deleted) {
            echo json_encode(['success' => true, 'message' => 'Classwork unsubmitted successfully.']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to unsubmit classwork.']);
        }
    }

    public function error_submission()
    {
        $this->load->view('output_upload');
    }
}
