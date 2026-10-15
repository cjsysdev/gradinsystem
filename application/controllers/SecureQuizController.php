<?php
defined('BASEPATH') or exit('No direct script access allowed');

// Widget-driven counterpart to QuizController — same fullscreen/timer/
// tab-switch-lockdown UI (quiz_view.php/quiz_result.php), but the question
// bank comes from assessments.given (the "Timed/Secure Quiz" widget's JSON
// config, same {question, choices, answer} shape as widgets/quiz.php) instead
// of a json_file_path file on disk. Reached via AssessmentController::
// assessment_view_code()'s redirect for widget_key 'secure_quiz'. Grading is
// delegated to Widgets_model::grade_quiz() so both quiz widgets score
// identically.
class SecureQuizController extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model(['accounts', 'assessments', 'student_master', 'classworks', 'class_schedule', 'attendance', 'class_student', 'Widgets_model']);
        $this->load->helper(['url']);
        $this->load->library(['session', 'Quiz_backup']);
        $this->is_offline = !isset($_SESSION['online']);
    }

    public function index($assessment_id)
    {
        if ($this->is_offline) redirect();

        $assessment = $this->assessments->get($assessment_id);
        if (!$assessment) {
            show_error('Assessment not found.', 404);
            return;
        }

        // Reachable directly by URL, not only through AssessmentController's
        // redirect — so the clearance gate has to be here too.
        if (clearance_gate($assessment)) return;
        if (submission_gate($assessment)) return;

        $config = json_decode($assessment->given ?? '', true) ?: [];
        $query_max_items = $assessment->max_score;
        $data['max_items'] = $query_max_items;

        // Check if the student has already taken the quiz
        $value = $this->classworks->where(
            [
                'student_id' => $this->session->student_id,
                'assessment_id' => $assessment_id
            ]
        )->get();

        if ($value) redirect('attendance');

        // Backup-upload window open (instructor's network-failure fallback):
        // show the upload page instead of starting an attempt. "Continue the
        // quiz" is offered only to a student whose drawn set is still in their
        // session — i.e. who had actually started — so the window can't be
        // used to skip the quiz from a fresh login. ?take=1 is that link.
        $session_key = 'shuffled_questions_' . $assessment_id;
        if ($this->quiz_backup->window_open($assessment_id) && !$this->input->get('take')) {
            $this->_render_backup($assessment, null);
            return;
        }

        // Load and shuffle questions from the widget config
        // Session key is scoped per assessment_id so a stale question set
        // from a previously-taken quiz isn't reused for a different one.
        if (!$this->session->userdata($session_key)) {
            $allQuestions = $this->Widgets_model->quiz_questions($config);
            shuffle($allQuestions);
            $questions = array_slice($allQuestions, 0, (int)$query_max_items ?: 10);

            foreach ($questions as &$question) {
                if (!empty($question['choices'])) {
                    shuffle($question['choices']);
                }
            }
            unset($question);

            $this->session->set_userdata($session_key, $questions);
        } else {
            $questions = $this->session->userdata($session_key);
        }

        $data['questions'] = $questions;
        $data['assessment_id'] = (int) $assessment_id;
        $data['submit_url'] = site_url('secure_quiz/submit/' . $assessment_id);
        $this->load->view('secure_quiz_view', $data);
    }

    // Network-failure fallback: the student uploads the encrypted backup file
    // secure_quiz_view.php downloaded at submit, and it becomes their
    // submission — only while the instructor has this section's backup window
    // open (AdminSubmissionController::secure_quiz_backup_window()).
    //
    // The file is never trusted for a score: Quiz_backup reads only question
    // text + the student's answers, matches them to the student's drawn set
    // (still in the session, since the lost POST never cleared it) or else to
    // the bank, and Widgets_model::grade_quiz() grades it like a live submit.
    // submission_gate is skipped on purpose — the open window is the
    // instructor's due-date override.
    public function backup($assessment_id)
    {
        if ($this->is_offline) redirect();

        $assessment = $this->assessments->get($assessment_id);
        if (!$assessment) {
            show_error('Assessment not found.', 404);
            return;
        }
        if (clearance_gate($assessment)) return;

        $error = $this->_backup_eligibility($assessment);
        if ($error !== null) {
            $this->session->set_flashdata('warning', $error);
            redirect('classwork');
            return;
        }

        if ($this->input->method() !== 'post') {
            $this->_render_backup($assessment, null);
            return;
        }

        $file = $_FILES['backup_file'] ?? null;
        if (!$file || ($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK || !is_uploaded_file($file['tmp_name'])) {
            $this->_render_backup($assessment, 'Please choose your backup file.');
            return;
        }
        if ($file['size'] > Quiz_backup::MAX_BYTES) {
            $this->_render_backup($assessment, 'That file is too large to be a quiz backup.');
            return;
        }

        $student_id = (int) $this->session->student_id;
        $plain = $this->quiz_backup->decrypt(file_get_contents($file['tmp_name']));
        $payload = $plain === null ? null : $this->quiz_backup->parse($plain);
        if ($payload === null) {
            $this->_render_backup($assessment, 'That file could not be read. Upload the quiz_submission_….json file your phone downloaded when you submitted.');
            return;
        }

        // Whose / which quiz. v2 files say so inside; legacy files only carry
        // the student id in the download name (a re-download may add " (1)").
        if ($payload['student_id'] !== null && $payload['student_id'] !== $student_id) {
            $this->_render_backup($assessment, 'This backup file belongs to another student.');
            return;
        }
        if ($payload['assessment_id'] !== null && $payload['assessment_id'] !== (int) $assessment_id) {
            $this->_render_backup($assessment, 'This backup file is for a different quiz.');
            return;
        }
        if ($payload['student_id'] === null
            && preg_match('/quiz_submission_(\d+)/', (string) $file['name'], $m)
            && (int) $m[1] !== $student_id) {
            $this->_render_backup($assessment, 'This backup file belongs to another student.');
            return;
        }

        $session_key = 'shuffled_questions_' . $assessment_id;
        $drawn = $this->session->userdata($session_key);
        $pool = is_array($drawn) && $drawn
            ? $drawn
            : $this->Widgets_model->quiz_questions(json_decode($assessment->given ?? '', true) ?: []);

        $rebuilt = $this->quiz_backup->rebuild($payload['items'], $pool, (int) $assessment->max_score);
        if (is_string($rebuilt)) {
            $this->_render_backup($assessment, $rebuilt);
            return;
        }
        list($questions, $answers) = $rebuilt;

        $graded = $this->Widgets_model->grade_quiz(['questions' => $questions], $answers);

        // Re-check right before the write: a double-tapped upload must not
        // create two rows.
        if ($this->classworks->where(['student_id' => $student_id, 'assessment_id' => $assessment_id])->get()) {
            redirect('attendance');
            return;
        }

        $row = [
            'student_id'    => $student_id,
            'assessment_id' => $assessment_id,
            'score'         => $this->classworks->clamp_score_for_assessment($assessment_id, $graded['score']),
            'code'          => json_encode($graded['results'], JSON_PRETTY_PRINT),
            // Badge on admin/all_submission.php: recovered, not live-submitted.
            'status'        => 'backup_upload',
        ];
        if ($payload['switch_count'] !== null && $this->classworks->has_switch_count()) {
            $row['switch_count'] = min(65535, max(0, $payload['switch_count']));
        }
        $this->classworks->insert($row);
        $this->session->unset_userdata($session_key);

        $data['score'] = $graded['score'];
        $data['total'] = count($questions);
        $data['results'] = $graded['results'];
        $data['assessment_id'] = $assessment_id;
        $data['show_review'] = true;
        $this->load->view('quiz_result', $data);
    }

    // NULL when this student may upload a backup for this section now, else
    // the reason they can't.
    private function _backup_eligibility($assessment)
    {
        $assessment_id = (int) $assessment->assessment_id;
        $student_id = (int) $this->session->student_id;

        if (!$this->quiz_backup->window_open($assessment_id)) {
            return 'Backup upload is not open for this quiz. Ask your instructor to open it.';
        }

        $widget = !empty($assessment->widget_id) ? $this->Widgets_model->get($assessment->widget_id) : null;
        if (!$widget || $widget['widget_key'] !== 'secure_quiz') {
            return 'This classwork does not accept quiz backup files.';
        }

        $this->load->model('Semester_model');
        if (!$this->Semester_model->is_active($this->Semester_model->assessment_semester_id($assessment_id))) {
            return 'This quiz belongs to a past semester.';
        }

        $enrolled = $this->db->where([
            'schedule_id' => $assessment->schedule_id,
            'student_id'  => $student_id,
            'status'      => 'enrolled',
        ])->count_all_results('class_student') > 0;
        if (!$enrolled) {
            return 'You are not enrolled in this section.';
        }

        if ($this->classworks->where(['student_id' => $student_id, 'assessment_id' => $assessment_id])->get()) {
            return 'You already have a submission for this quiz.';
        }

        return null;
    }

    private function _render_backup($assessment, $error)
    {
        $assessment_id = (int) $assessment->assessment_id;
        $this->load->view('secure_quiz_backup', [
            'assessment' => $assessment,
            'error'      => $error,
            'until'      => $this->quiz_backup->window_until($assessment_id),
            'upload_url' => site_url('secure_quiz/backup/' . $assessment_id),
            // Only a student who had already drawn their questions gets to
            // carry on with the live attempt from here.
            'can_continue' => (bool) $this->session->userdata('shuffled_questions_' . $assessment_id),
            'continue_url' => site_url('secure_quiz/' . $assessment_id) . '?take=1',
        ]);
    }

    public function submit($assessment_id)
    {
        // An uncleared student must not be able to POST an attempt either.
        if (clearance_gate($assessment_id)) return;
        if (submission_gate($assessment_id)) return;

        $session_key = 'shuffled_questions_' . $assessment_id;
        $questions = $this->session->userdata($session_key) ?: [];
        $userAnswers = $this->input->post('answers') ?: [];

        $graded = $this->Widgets_model->grade_quiz(['questions' => $questions], $userAnswers);
        $this->session->unset_userdata($session_key);

        $value = $this->classworks->where(
            [
                'student_id' => $this->session->student_id,
                'assessment_id' => $assessment_id
            ]
        )->get();

        if (!$value) {
            // Defensive: grade_quiz() bounds the score by question count, but
            // clamp anyway in case an admin edited max_score to be smaller.
            $row = [
                'student_id' => $this->session->student_id,
                'assessment_id' => $assessment_id,
                'score' => $this->classworks->clamp_score_for_assessment($assessment_id, $graded['score']),
                'code' => json_encode($graded['results'], JSON_PRETTY_PRINT)
            ];

            // Tab-switch count. secure_quiz_view.php counts window blurs during
            // the attempt and posts the tally as `blur_count`; until now nothing
            // read it. It is a client-reported number and trivially forgeable, so
            // it is a soft proctoring signal for the instructor to eyeball, never
            // an input to the score. Clamped into the SMALLINT UNSIGNED column;
            // skipped entirely if an admin hasn't run WidgetsController/install
            // yet, so a missing column can never cost a student their submission.
            if ($this->classworks->has_switch_count()) {
                $row['switch_count'] = min(65535, max(0, (int) $this->input->post('blur_count')));
            }

            $this->classworks->insert($row);
        }

        $data['score'] = $graded['score'];
        $data['total'] = count($questions);
        $data['results'] = $graded['results'];
        $data['assessment_id'] = $assessment_id;
        // quiz_result.php then renders the missed items only — never the full
        // question/answer list (same rule as the readonly widget review in
        // widgets/secure_quiz.php).
        $data['show_review'] = true;
        $this->load->view('quiz_result', $data);
    }

    // Lets an admin take the quiz from the "Widget Config (JSON)" preview in
    // manage_assessments — before the assessment even has an assessment_id
    // (or is saved at all), and without ever touching classworks. Config
    // comes straight from the posted textarea contents, not the DB.
    public function test()
    {
        if ($this->session->userdata('role') !== 'admin') {
            show_404();
            return;
        }

        $config = json_decode($this->input->post('config') ?? '', true) ?: [];
        $questions = $this->Widgets_model->quiz_questions($config);
        shuffle($questions);

        foreach ($questions as &$question) {
            if (!empty($question['choices'])) {
                shuffle($question['choices']);
            }
        }
        unset($question);

        $this->session->set_userdata('test_shuffled_questions', $questions);

        $data['questions'] = $questions;
        $data['max_items'] = count($questions) ?: 1;
        $data['test_mode'] = true;
        $data['submit_url'] = site_url('secure_quiz/submit_test');
        $this->load->view('secure_quiz_view', $data);
    }

    public function submit_test()
    {
        if ($this->session->userdata('role') !== 'admin') {
            show_404();
            return;
        }

        $questions = $this->session->userdata('test_shuffled_questions') ?: [];
        $userAnswers = $this->input->post('answers') ?: [];

        $graded = $this->Widgets_model->grade_quiz(['questions' => $questions], $userAnswers);

        $data['score'] = $graded['score'];
        $data['total'] = count($questions);
        $data['results'] = $graded['results'];
        $data['assessment_id'] = null;
        $data['test_mode'] = true;
        $data['show_review'] = true;
        $this->load->view('quiz_result', $data);
    }
}
