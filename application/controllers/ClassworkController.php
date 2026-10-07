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

        // Closed / past-due items stay visible; the student asks the
        // instructor to reopen them instead of answering. See
        // submission_lock_helper.php.
        $this->student_request->install();
        $requests = $this->student_request->late_requests_for_student($student_id);

        // Only open work or work that is past due is listed. A closed
        // assessment that is not yet due is unreleased, so it stays hidden.
        $missing = array_values(array_filter($missing, function ($m) {
            return submission_status_open($m['status'] ?? null) || submission_past_due($m);
        }));

        foreach ($missing as &$m) {
            $m['locked']  = submission_locked($m);
            $m['grant']   = $m['locked'] ? $this->student_request->active_late_grant($student_id, $m['assessment_id']) : null;
            $m['request'] = $requests[$m['assessment_id']] ?? null;
            $m['closed']  = !submission_status_open($m['status'] ?? null);
        }
        unset($m);

        foreach ($submitted as &$sub) {
            $sub['was_late'] = $this->student_request->had_approved_late($student_id, $sub['assessment_id']);
        }
        unset($sub);

        $data = [
            'assessments' => $missing,
            'submitted' => $submitted
        ];

        $this->load->view('classwork', $data);
    }

    /**
     * Student asks the instructor to reopen a closed / past-due classwork.
     * Lands in admin/student_requests as type 'late_submission'.
     */
    public function request_access()
    {
        $student_id    = $this->session->student_id;
        $assessment_id = (int) $this->input->post('assessment_id');
        $reason        = trim((string) $this->input->post('reason'));

        $this->student_request->install();

        $student = $this->class_student->get_class_student_info($student_id);
        $row     = $student ? $this->assessments->as_array()->get($assessment_id) : null;

        // Must be a real assessment in this student's own section...
        $this->load->model('class_schedule');
        $sched = $row ? $this->db->get_where('class_schedule', ['schedule_id' => $row['schedule_id']])->row_array() : null;
        if (!$row || !$sched || $sched['section'] !== $student['section']) {
            $this->session->set_flashdata('error', 'Classwork not found.');
            redirect('classwork');
            return;
        }

        // ...in the active semester, genuinely locked, and not yet submitted.
        $this->load->model('Semester_model');
        if (!$this->Semester_model->is_active($this->Semester_model->assessment_semester_id($assessment_id))) {
            $this->session->set_flashdata('error', 'That classwork belongs to a past semester and is read-only.');
            redirect('classwork');
            return;
        }
        if (!submission_locked($row)) {
            $this->session->set_flashdata('warning', 'That classwork is still open — you can answer it now.');
            redirect('classwork');
            return;
        }
        if ($this->classworks->where(['student_id' => $student_id, 'assessment_id' => $assessment_id])->get()) {
            $this->session->set_flashdata('warning', 'You have already submitted this classwork.');
            redirect('classwork');
            return;
        }
        if ($this->student_request->active_late_grant($student_id, $assessment_id)) {
            redirect('classwork');
            return;
        }
        if ($this->student_request->has_open_late_request($student_id, $assessment_id)) {
            $this->session->set_flashdata('warning', 'You already have a pending request for this classwork.');
            redirect('classwork');
            return;
        }
        if ($reason === '') {
            $this->session->set_flashdata('error', 'Please tell your instructor why you missed it.');
            redirect('classwork');
            return;
        }

        $this->db->insert('student_requests', [
            'type'          => 'late_submission',
            'student_id'    => $student_id,
            'schedule_id'   => $row['schedule_id'],
            'assessment_id' => $assessment_id,
            'request_date'  => date('Y-m-d'),
            'reason'        => mb_substr($reason, 0, 500),
            'status'        => 'pending',
            'created_at'    => date('Y-m-d H:i:s'),
        ]);

        $this->session->set_flashdata('success', 'Request sent. Your instructor will review it.');
        redirect('classwork');
    }

    public function submit_classwork()
    {
        $post = $this->input->post();

        // Only the active semester accepts submissions; archived ones are read-only.
        $this->load->model('Semester_model');
        if (!$this->Semester_model->is_active($this->Semester_model->assessment_semester_id($post['assessment_id'] ?? 0))) {
            $this->session->set_flashdata('error', 'That classwork belongs to a past semester and is read-only.');
            redirect('classwork');
            return;
        }

        // Same gate as AssessmentController::submit_classwork() — this is the
        // second POST path into classworks. See clearance_helper.php.
        if (clearance_gate($post['assessment_id'] ?? null, 'classwork')) return;
        if (submission_gate($post['assessment_id'] ?? null, 'classwork')) return;

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
        if (!$submission) {
            show_404();
        }

        // Students may open only their own submissions, and only for a
        // semester that is active or released. Admins may open any.
        if ($this->session->role !== 'admin') {
            $this->load->model('Semester_model');
            $sem_id = $this->Semester_model->assessment_semester_id($submission['assessment_id']);
            if ((int) $submission['student_id'] !== (int) $this->session->student_id
                || !$this->Semester_model->is_released($sem_id)) {
                show_404();
            }
        }

        $widget = null;
        $timer = null;
        if (!empty($submission['assessments'][0]->widget_id)) {
            $this->load->model('Widgets_model');
            $widget = $this->Widgets_model->get($submission['assessments'][0]->widget_id);

            // Code Snippet timed batches — computed for the SUBMISSION'S OWN
            // student (not the viewing session), so an admin opening this page
            // sees the same badge the student would. Widgets_model::
            // code_snippet_timer() returns null for an untimed assessment.
            if ($widget && $widget['widget_key'] === 'code_snippet' && !empty($submission['assessments'][0]->timer_config)) {
                $timer = $this->Widgets_model->code_snippet_timer(
                    $submission['assessments'][0]->timer_config,
                    $submission['student_id']
                );
            }
        }

        $data = [
            'classwork' => $submission,
            'widget' => $widget,
            'widget_timer' => $timer,
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

        $cw = $this->classworks->as_array()->get($classwork_id);
        $this->load->model('Semester_model');
        if ($cw && !$this->Semester_model->is_active($this->Semester_model->assessment_semester_id($cw['assessment_id']))) {
            echo json_encode(['success' => false, 'message' => 'Past-semester work is read-only.']);
            return;
        }

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
