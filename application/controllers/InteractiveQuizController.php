<?php
defined('BASEPATH') or exit('No direct script access allowed');

class InteractiveQuizController extends CI_Controller
{
    private $json_path;

    public function __construct()
    {
        parent::__construct();
        $this->load->model(['classworks', 'assessments', 'Iq_attempts']);
        $this->load->helper(['url']);
        $this->load->library(['session']);
        $this->json_path = FCPATH . 'assets/json/';
    }

    // Display an interactive quiz by topic name.
    // Optionally linked to an assessment_id for grade recording.
    public function load($topic, $assessment_id = null)
    {
        if (!preg_match('/^[a-z0-9_]+$/', $topic)) {
            show_error('Invalid topic name.', 400);
            return;
        }

        $json_file = $this->json_path . $topic . '.json';

        if (!file_exists($json_file)) {
            show_error('Topic not found: ' . htmlspecialchars($topic, ENT_QUOTES), 404);
            return;
        }

        $raw        = file_get_contents($json_file);
        $topic_data = json_decode($raw, true);

        if (!$topic_data || !isset($topic_data['sections'])) {
            show_error('Invalid or malformed topic data.', 500);
            return;
        }

        $sections        = $topic_data['sections'];
        $total_questions = 0;
        foreach ($sections as $section) {
            $total_questions += count($section['questions'] ?? []);
        }

        $data = [
            'topic'           => $topic,
            'title'           => $topic_data['title'] ?? ucwords(str_replace('_', ' ', $topic)),
            'description'     => $topic_data['description'] ?? '',
            'sections'        => $sections,
            'total_questions' => $total_questions,
            'assessment_id'   => $assessment_id ? (int) $assessment_id : null,
        ];

        $this->load->view('interactive_quiz_view', $data);
    }

    // List all available topics found in assets/json/
    public function list_topics()
    {
        $files  = glob($this->json_path . '*.json') ?: [];
        $topics = [];

        foreach ($files as $file) {
            $base = basename($file, '.json');
            $raw  = file_get_contents($file);
            $meta = json_decode($raw, true);

            $topics[] = [
                'topic'       => $base,
                'title'       => $meta['title'] ?? ucwords(str_replace('_', ' ', $base)),
                'description' => $meta['description'] ?? '',
                'sections'    => count($meta['sections'] ?? []),
                'questions'   => array_sum(array_map(function ($s) {
                    return count($s['questions'] ?? []);
                }, $meta['sections'] ?? [])),
                'url'         => site_url('interactive_quiz/load/' . $base),
            ];
        }

        $this->load->view('interactive_quiz_topics_view', ['topics' => $topics]);
    }

    // JSON API endpoint — returns the raw topic data
    public function get_data($topic)
    {
        header('Content-Type: application/json');

        if (!preg_match('/^[a-z0-9_]+$/', $topic)) {
            http_response_code(400);
            echo json_encode(['error' => 'Invalid topic name']);
            return;
        }

        $json_file = $this->json_path . $topic . '.json';

        if (!file_exists($json_file)) {
            http_response_code(404);
            echo json_encode(['error' => 'Topic not found']);
            return;
        }

        echo file_get_contents($json_file);
    }

    // AJAX — record one question attempt for analytics
    public function record_attempt()
    {
        header('Content-Type: application/json');

        $topic          = $this->input->post('topic');
        $section_index  = (int) $this->input->post('section_index');
        $section_title  = $this->input->post('section_title');
        $question_index = (int) $this->input->post('question_index');
        $question_text  = $this->input->post('question_text');
        $is_correct     = $this->input->post('is_correct') === '1' ? 1 : 0;
        $student_id     = $this->session->student_id ?? 'guest';

        if (!$topic || !preg_match('/^[a-z0-9_]+$/', $topic)) {
            echo json_encode(['success' => false]);
            return;
        }

        $this->load->database();
        $this->Iq_attempts->ensure_table();

        $this->Iq_attempts->record([
            'student_id'     => $student_id,
            'topic'          => $topic,
            'section_index'  => $section_index,
            'section_title'  => mb_substr((string) $section_title, 0, 255),
            'question_index' => $question_index,
            'question_text'  => mb_substr((string) $question_text, 0, 1000),
            'is_correct'     => $is_correct,
        ]);

        echo json_encode(['success' => true]);
    }

    // Admin — per-section analytics for a given topic
    public function analytics($topic = null)
    {
        $this->load->database();
        $this->Iq_attempts->ensure_table();

        // Build topic list from JSON files + DB data
        $files           = glob($this->json_path . '*.json') ?: [];
        $available_topics = [];
        foreach ($files as $f) {
            $base = basename($f, '.json');
            $meta = json_decode(file_get_contents($f), true);
            $available_topics[$base] = $meta['title'] ?? ucwords(str_replace('_', ' ', $base));
        }

        // Default to first topic if none specified
        if (!$topic || !array_key_exists($topic, $available_topics)) {
            $topic = array_key_first($available_topics);
        }

        if (!$topic) {
            $this->load->view('interactive_quiz_analytics_view', [
                'available_topics' => [],
                'topic'            => null,
                'topic_title'      => '',
                'summary'          => ['total' => 0, 'students' => 0, 'accuracy' => 0],
                'sections'         => [],
                'missed'           => [],
            ]);
            return;
        }

        $data = [
            'available_topics' => $available_topics,
            'topic'            => $topic,
            'topic_title'      => $available_topics[$topic],
            'summary'          => $this->Iq_attempts->topic_summary($topic),
            'sections'         => $this->Iq_attempts->section_stats($topic),
            'missed'           => $this->Iq_attempts->missed_questions($topic, 10),
        ];

        $this->load->view('interactive_quiz_analytics_view', $data);
    }

    // Save the student's score to classworks (requires valid assessment_id)
    public function save_result()
    {
        $assessment_id = (int) $this->input->post('assessment_id');
        $score         = (int) $this->input->post('score');
        $student_id    = $this->session->student_id;

        if (!$assessment_id || !$student_id) {
            $this->session->set_flashdata('warning', 'Unable to save score — missing data.');
            redirect('attendance');
            return;
        }

        $existing = $this->classworks->where([
            'student_id'    => $student_id,
            'assessment_id' => $assessment_id,
        ])->get();

        if (!$existing) {
            $this->classworks->insert([
                'student_id'    => $student_id,
                'assessment_id' => $assessment_id,
                'score'         => $score,
            ]);
            $this->session->set_flashdata('success', 'Score saved successfully!');
        } else {
            $this->session->set_flashdata('info', 'Score already recorded.');
        }

        redirect('attendance');
    }
}
