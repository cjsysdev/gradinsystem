<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Quiz extends CI_Controller
{

    public function __construct()
    {
        parent::__construct();
        $this->load->model(['accounts', 'assessments', 'student_master', 'classworks', 'class_schedule', 'attendance', 'class_student']);
        $this->load->helper(['url']);
        $this->load->library(['session', 'upload']);
        $this->is_offline = !isset($_SESSION['online']);
    }

    public function index()
    {
        // Load the JSON file
        $json = file_get_contents('uploads/103.json');
        $allQuestions = json_decode($json, true);

        // Shuffle the questions randomly
        shuffle($allQuestions);

        // Limit the number of questions to 15
        $questions = array_slice($allQuestions, 0, 15);

        // Store shuffled and limited questions in session to retain order for submission
        $this->session->set_userdata('shuffled_questions', $questions);

        // Pass the shuffled and limited questions to the view
        $data['questions'] = $questions;

        // Load the quiz view
        $this->load->view('quiz_view', $data);
    }

    public function submit()
    {
        // Retrieve shuffled and limited questions from session
        $questions = $this->session->userdata('shuffled_questions');

        // Get user answers
        $userAnswers = $this->input->post('answers');

        // Calculate the score
        $score = 0;
        $results = [];

        foreach ($questions as $index => $question) {
            $userAnswer = isset($userAnswers[$index]) ? $userAnswers[$index] : 'No answer';
            $isCorrect = ($userAnswer === $question['answer']);

            if ($isCorrect) {
                $score++;
            }

            // Store results for each question
            $results[] = [
                'question' => $question['question'],
                'user_answer' => $userAnswer,
                'correct_answer' => $question['answer'],
                'is_correct' => $isCorrect
            ];
        }

        // Pass the score, results, and total questions to the result view
        $data['score'] = $score;
        $data['total'] = count($questions);
        $data['results'] = $results;

        // Load the result view
        $this->load->view('quiz_result', $data);
    }
}
