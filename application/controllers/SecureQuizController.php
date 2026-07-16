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
        $this->load->library(['session']);
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

        // Load and shuffle questions from the widget config
        // Session key is scoped per assessment_id so a stale question set
        // from a previously-taken quiz isn't reused for a different one.
        $session_key = 'shuffled_questions_' . $assessment_id;
        if (!$this->session->userdata($session_key)) {
            $allQuestions = $config['questions'] ?? [];
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
        $data['submit_url'] = site_url('secure_quiz/submit/' . $assessment_id);
        $this->load->view('secure_quiz_view', $data);
    }

    public function submit($assessment_id)
    {
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
            $this->classworks->insert([
                'student_id' => $this->session->student_id,
                'assessment_id' => $assessment_id,
                'score' => $graded['score'],
                'code' => json_encode($graded['results'], JSON_PRETTY_PRINT)
            ]);
        }

        $data['score'] = $graded['score'];
        $data['total'] = count($questions);
        $data['results'] = $graded['results'];
        $data['assessment_id'] = $assessment_id;
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
        $questions = $config['questions'] ?? [];
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
        $this->load->view('quiz_result', $data);
    }
}
