<?php
defined('BASEPATH') or exit('No direct script access allowed');

class QuizController extends CI_Controller
{

    public function __construct()
    {
        parent::__construct();
        $this->load->model(['accounts', 'assessments', 'student_master', 'classworks', 'class_schedule', 'attendance', 'class_student']);
        $this->load->helper(['url']);
        $this->load->library(['session', 'upload']);
        $this->is_offline = !isset($_SESSION['online']);
    }

    public function index($assessment_id)
    {
        // Fetch the JSON file path for the given assessment_id
        $this->load->database();
        $query = $this->db->get_where('assessment_files', ['assessment_id' => $assessment_id]);
        $fileRecord = $query->row();

        if (!$fileRecord) {
            show_error('JSON file for this assessment is not configured.', 404);
            return;
        }

        $jsonFilePath = $fileRecord->json_file_path;

        // Check if the student has already taken the quiz
        $value = $this->classworks->where(
            [
                'student_id' => $this->session->student_id,
                'assessment_id' => $assessment_id
            ]
        )->get();

        if ($value) redirect('attendance');

        if ($this->is_offline) redirect();

        // Load and shuffle questions from the JSON file
        if (!$this->session->userdata('shuffled_questions')) {
            if (!file_exists($jsonFilePath)) {
                show_error('The JSON file does not exist.', 404);
                return;
            }

            $json = file_get_contents($jsonFilePath);
            $allQuestions = json_decode($json, true);

            shuffle($allQuestions);
            $questions = array_slice($allQuestions, 0, 10);

            foreach ($questions as &$question) {
                shuffle($question['choices']);
            }
            unset($question);

            $this->session->set_userdata('shuffled_questions', $questions);
        } else {
            $questions = $this->session->userdata('shuffled_questions');
        }

        $data['questions'] = $questions;
        $this->load->view('quiz_view', $data);
    }

    public function submit($assessment_id)
    {
        $questions = $this->session->userdata('shuffled_questions');
        $userAnswers = $this->input->post('answers');

        $score = 0;
        $results = [];

        foreach ($questions as $index => $question) {
            $userAnswer = isset($userAnswers[$index]) ? $userAnswers[$index] : 'No answer';
            $isCorrect = ($userAnswer === $question['answer']);

            if ($isCorrect) {
                $score++;
            }

            $results[] = [
                'question' => $question['question'],
                'user_answer' => $userAnswer,
                'correct_answer' => $question['answer'],
                'is_correct' => $isCorrect
            ];
        }

        $data['score'] = $score;
        $data['total'] = count($questions);
        $data['results'] = $results;

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
                'score' => $score,
                'code' => json_encode($data['results'], JSON_PRETTY_PRINT)
            ]);
        }

        $this->load->view('quiz_result', $data);
    }

    public function check_session()
    {
        header('Content-Type: application/json');
        echo json_encode(['logged_in' => isset($_SESSION['online'])]);
    }
}
