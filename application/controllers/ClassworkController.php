<?php
defined('BASEPATH') or exit('No direct script access allowed');

class ClassworkController extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        if (!isset($_SESSION['online'])) {
            redirect('login');
        }
    }

    public function classwork()
    {
        $student_id = $this->session->student_id;
        $student = $this->class_student->get_class_student_info($student_id);

        if (!$student) {
            $this->session->set_flashdata('error', 'Student section not found');
            redirect('attendance');
        }

        $missing = $this->assessments->get_students_assessments(
            $student_id,
            $student['section']
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

    /**
     * Grade a submission. Admin only — the class constructor checks that a
     * session exists, which every logged-in student also has.
     */
    public function add_score()
    {
        $this->_require_admin();

        $classwork_id  = $this->input->post('classwork_id');
        $student_id    = $this->input->post('student_id');
        $score         = $this->input->post('score');
        $assessment_id = $this->input->post('assessment_id');

        $error = null;
        if (!$this->classworks->update_score($classwork_id, $student_id, $score, $error)) {
            $this->session->set_flashdata('error', $error ?: 'Invalid score.');
            redirect("all_submissions/$assessment_id");
        }

        // set_score() clamps rather than rejects, so a capped write still
        // succeeds — surface that instead of reporting a clean save.
        $this->session->set_flashdata(
            $error ? 'warning' : 'success',
            $error ?: 'Score updated successfully!'
        );
        redirect("all_submissions/$assessment_id");
    }

    // add_rand_score() was removed. It was routed as
    // GET /add_rand_score/{classwork_id}/{score}/{assessment_id}, had no
    // role check, no validation and no cap, so any logged-in student could
    // write an arbitrary score to their own submission. It had no callers.
    // Randomised scoring lives in AdminController::add_rand_score_incremental(),
    // which is admin-gated and clamps to max_score.

    private function _require_admin()
    {
        if ($this->session->userdata('role') !== 'admin') {
            show_error('You are not authorised to grade submissions.', 403);
        }
    }

    public function student_submission($classwork_id)
    {
        $submission = $this->classworks->with_assessments()->as_array()->get($classwork_id);

        $widget = null;
        if (!empty($submission['assessments'][0]->widget_id)) {
            $this->load->model('Widgets_model');
            $widget = $this->Widgets_model->get($submission['assessments'][0]->widget_id);
        }

        $data = [
            'classwork' => $submission,
            'widget' => $widget,
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
