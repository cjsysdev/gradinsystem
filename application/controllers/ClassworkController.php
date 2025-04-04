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

    public function all_submissions($assessment_id)
    {
        $submissions = $this->classworks->get_all_submissions($assessment_id);

        $data = ["submissions" => $submissions];

        $this->load->view('all_submission', $data);
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

    public function add_rand_score($classwork_id, $type)
    {
        switch ($type) {
            case 1:
                $score = randomizeNumber(5.0, 7.4);
                break;
            case 2:
                $score = randomizeNumber(7.5, 7.9);
                break;
            case 3:
                $score = randomizeNumber(8.0, 8.9);
                break;
            case 4:
                $score = randomizeNumber(9.0, 9.4);
                break;
            case 5:
                $score = randomizeNumber(9.5, 10.0);
                break;
            default:
                $score = null;
                break;
        }

        $this->classworks->add_score($classwork_id, $score);
        redirect('all_submissions');
    }

    public function student_submission($classwork_id)
    {
        $submission = $this->classworks->with_assessments()->as_array()->get($classwork_id);

        $data = [
            'classwork' => $submission
        ];

        $this->load->view('student_submission', $data);
    }
}
