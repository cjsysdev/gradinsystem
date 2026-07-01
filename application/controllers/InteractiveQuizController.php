<?php
defined('BASEPATH') or exit('No direct script access allowed');

class InteractiveQuizController extends CI_Controller
{
    private $json_path;

    public function __construct()
    {
        parent::__construct();
        $this->load->model(['classworks', 'assessments', 'Iq_attempts', 'discussions', 'class_student']);
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

        $sections = $topic_data['sections'];

        // Shuffle questions within each section (and choices within each question)
        // when the topic has "shuffle": true. Original indices are preserved in
        // _orig_qi so analytics still record the canonical question position.
        if (!empty($topic_data['shuffle'])) {
            foreach ($sections as &$section) {
                if (!empty($section['questions'])) {
                    foreach ($section['questions'] as $oi => &$q) {
                        $q['_orig_qi'] = $oi;
                        shuffle($q['choices']);
                    }
                    unset($q);
                    shuffle($section['questions']);
                }
            }
            unset($section);
        }

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

    // List all topics — static and interactive, both from the discussions table.
    // Schema requires: discussions.type ENUM('static','interactive')
    // For interactive rows, discussions.link holds the JSON topic slug (e.g. '105_mysqli').
    public function list_topics()
    {
        $enrollment       = $this->class_student->get(['student_id' => $_SESSION['student_id']]);
        $student_class_id = $enrollment ? (int) $enrollment->class_id : null;

        $all = $student_class_id
            ? $this->discussions->as_array()->order_by('created_at', 'desc')->get_all(['class_id' => $student_class_id]) ?: []
            : [];

        $static_topics      = [];
        $interactive_topics = [];

        foreach ($all as $d) {
            $type = $d['type'] ?? 'static';
            $link = $d['link'] ?? '';

            if ($type === 'interactive') {
                $interactive_topics[] = [
                    'title'       => $d['title']       ?? '',
                    'description' => $d['description'] ?? '',
                    'url'         => $link ? site_url("interactive_quiz/discussion/{$link}") : '#',
                    'format'      => 'discussion',
                ];
            } else {
                $static_topics[] = [
                    'title'       => $d['title']       ?? '',
                    'description' => $d['description'] ?? '',
                    'link'        => $link ? site_url($link) : '',
                ];
            }
        }

        $this->load->view('interactive_quiz_topics_view', [
            'static_topics'      => $static_topics,
            'interactive_topics' => $interactive_topics,
        ]);
    }

    // Admin — list topics and handle uploads/deletes
    public function manage_topics()
    {
        $this->load->database();

        // All interactive rows from discussions table, grouped by slug
        $discussion_rows = $this->db->where('type', 'interactive')->get('discussions')->result_array();
        $linked = [];
        foreach ($discussion_rows as $d) {
            if (!empty($d['link'])) {
                $linked[$d['link']][] = $d;
            }
        }

        // Classes for label display
        $classes = $this->db->order_by('class_id')->get('classes')->result_array();
        $class_map = [];
        foreach ($classes as $c) {
            $class_map[$c['class_id']] = $c['class_code'] . ' — ' . $c['class_name'];
        }

        // Annotate each JSON topic with its linked discussion records
        $topics = $this->_build_topic_list();
        $topic_slugs = [];
        foreach ($topics as &$t) {
            $t['discussions'] = $linked[$t['slug']] ?? [];
            $topic_slugs[] = $t['slug'];
        }
        unset($t);

        // Orphaned: discussion record points to a slug with no JSON file
        $orphaned = [];
        foreach ($discussion_rows as $d) {
            if (!empty($d['link']) && !in_array($d['link'], $topic_slugs, true)) {
                $orphaned[] = $d;
            }
        }

        $this->load->view('interactive_quiz_manage_topics_view', [
            'topics'    => $topics,
            'class_map' => $class_map,
            'orphaned'  => $orphaned,
        ]);
    }

    // Admin — handle JSON topic file upload (POST)
    public function upload_topic()
    {
        if ($this->input->method() !== 'post') {
            redirect('interactive_quiz/manage_topics');
            return;
        }

        $file = $_FILES['topic_json'] ?? null;

        // Basic file presence / upload error check
        if (!$file || $file['error'] !== UPLOAD_ERR_OK || empty($file['tmp_name'])) {
            $msg = $file ? $this->_upload_error_message($file['error']) : 'No file received.';
            $this->session->set_flashdata('error', $msg);
            redirect('interactive_quiz/manage_topics');
            return;
        }

        // Size cap: 5 MB
        if ($file['size'] > 5 * 1024 * 1024) {
            $this->session->set_flashdata('error', 'File too large. Maximum size is 5 MB.');
            redirect('interactive_quiz/manage_topics');
            return;
        }

        // Must be a JSON file
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if ($ext !== 'json') {
            $this->session->set_flashdata('error', 'Only .json files are accepted.');
            redirect('interactive_quiz/manage_topics');
            return;
        }

        // Parse and validate JSON structure
        $raw  = file_get_contents($file['tmp_name']);
        $data = json_decode($raw, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            $this->session->set_flashdata('error', 'Invalid JSON: ' . json_last_error_msg());
            redirect('interactive_quiz/manage_topics');
            return;
        }

        $validation_error = $this->_validate_topic_structure($data);
        if ($validation_error) {
            $this->session->set_flashdata('error', $validation_error);
            redirect('interactive_quiz/manage_topics');
            return;
        }

        // Determine slug: use topic field if present, else sanitize the filename
        $slug = '';
        if (!empty($data['topic'])) {
            $slug = $data['topic'];
        } else {
            $slug = strtolower(pathinfo($file['name'], PATHINFO_FILENAME));
            $slug = preg_replace('/[^a-z0-9]+/', '_', $slug);
            $slug = trim($slug, '_');
        }

        if (!preg_match('/^[a-z0-9_]{1,100}$/', $slug)) {
            $this->session->set_flashdata('error',
                'Could not derive a valid slug from the file. ' .
                'Add a "topic" field (lowercase letters, digits, underscores) to your JSON.');
            redirect('interactive_quiz/manage_topics');
            return;
        }

        // Check assets/json/ is writable
        if (!is_writable($this->json_path)) {
            $this->session->set_flashdata('error', 'Upload directory is not writable. Contact your administrator.');
            redirect('interactive_quiz/manage_topics');
            return;
        }

        $dest      = $this->json_path . $slug . '.json';
        $overwrite = file_exists($dest);

        // Ensure the topic field is stored in the file
        $data['topic'] = $slug;
        $pretty        = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

        if (file_put_contents($dest, $pretty) === false) {
            $this->session->set_flashdata('error', 'Failed to save file. Check directory permissions.');
            redirect('interactive_quiz/manage_topics');
            return;
        }

        $verb = $overwrite ? 'updated' : 'uploaded';
        $this->session->set_flashdata('success',
            "Topic <strong>" . htmlspecialchars($data['title']) . "</strong> " .
            "(<code>{$slug}</code>) {$verb} successfully.");
        redirect('interactive_quiz/manage_topics');
    }

    // Admin — delete a JSON topic file (POST)
    public function delete_topic($topic)
    {
        if ($this->input->method() !== 'post') {
            redirect('interactive_quiz/manage_topics');
            return;
        }

        if (!preg_match('/^[a-z0-9_]+$/', $topic)) {
            $this->session->set_flashdata('error', 'Invalid topic name.');
            redirect('interactive_quiz/manage_topics');
            return;
        }

        $file = $this->json_path . $topic . '.json';

        if (!file_exists($file)) {
            $this->session->set_flashdata('error', "Topic <code>{$topic}</code> not found.");
            redirect('interactive_quiz/manage_topics');
            return;
        }

        if (!unlink($file)) {
            $this->session->set_flashdata('error', 'Could not delete the file. Check directory permissions.');
            redirect('interactive_quiz/manage_topics');
            return;
        }

        $this->session->set_flashdata('success', "Topic <code>{$topic}</code> deleted.");
        redirect('interactive_quiz/manage_topics');
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
        $chosen_option  = $this->input->post('chosen_option');
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
            'chosen_option'  => $chosen_option !== null && $chosen_option !== ''
                                    ? mb_substr((string) $chosen_option, 0, 500)
                                    : null,
        ]);

        echo json_encode(['success' => true]);
    }

    // Teacher view — shows per-section answer distribution for a discussion topic
    public function discussion_results($topic)
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

        $topic_data = json_decode(file_get_contents($json_file), true);

        if (!$topic_data || !isset($topic_data['sections'])) {
            show_error('Invalid or malformed topic data.', 500);
            return;
        }

        $validation_error = $this->_validate_discussion_structure($topic_data);
        if ($validation_error) {
            show_error($validation_error, 422);
            return;
        }

        $this->load->database();
        $this->Iq_attempts->ensure_table();

        $this->load->view('interactive_quiz_discussion_results_view', [
            'topic'      => $topic,
            'topic_data' => $topic_data,
            'stats'      => $this->Iq_attempts->choice_distribution_by_topic($topic),
        ]);
    }

    // AJAX — returns JSON choice distribution for all sections of a topic (teacher refresh)
    public function get_choice_stats($topic)
    {
        header('Content-Type: application/json');

        if (!preg_match('/^[a-z0-9_]+$/', $topic)) {
            http_response_code(400);
            echo json_encode(['error' => 'Invalid topic name']);
            return;
        }

        $this->load->database();
        $this->Iq_attempts->ensure_table();

        echo json_encode([
            'topic'   => $topic,
            'sections' => $this->Iq_attempts->choice_distribution_by_topic($topic),
        ]);
    }

    // Admin — per-section analytics for a given topic
    public function analytics($topic = null)
    {
        $this->load->database();
        $this->Iq_attempts->ensure_table();

        $files            = glob($this->json_path . '*.json') ?: [];
        $available_topics = [];
        foreach ($files as $f) {
            $base                    = basename($f, '.json');
            $meta                    = json_decode(file_get_contents($f), true);
            $available_topics[$base] = $meta['title'] ?? ucwords(str_replace('_', ' ', $base));
        }

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

        $this->load->view('interactive_quiz_analytics_view', [
            'available_topics' => $available_topics,
            'topic'            => $topic,
            'topic_title'      => $available_topics[$topic],
            'summary'          => $this->Iq_attempts->topic_summary($topic),
            'sections'         => $this->Iq_attempts->section_stats($topic),
            'missed'           => $this->Iq_attempts->missed_questions($topic, 10),
        ]);
    }

    // Display an interactive discussion by topic name (single-quiz-per-section format).
    // JSON schema: sections[].quiz = { question, options[], correct (index), code? }
    public function discussion($topic, $assessment_id = null)
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

        $topic_data = json_decode(file_get_contents($json_file), true);

        if (!$topic_data || !isset($topic_data['sections'])) {
            show_error('Invalid or malformed topic data.', 500);
            return;
        }

        $validation_error = $this->_validate_discussion_structure($topic_data);
        if ($validation_error) {
            show_error($validation_error, 422);
            return;
        }

        $this->load->view('discussions/_interactive_quiz_template', [
            'topic_data'    => $topic_data,
            'assessment_id' => $assessment_id ? (int) $assessment_id : null,
        ]);
    }

    // Save the student's score to classworks (requires valid assessment_id).
    // Supports both AJAX (returns JSON) and full-page form posts (redirects).
    public function save_result()
    {
        $assessment_id = (int) $this->input->post('assessment_id');
        $score         = (int) $this->input->post('score');
        $student_id    = $this->session->student_id;
        $is_ajax       = $this->input->is_ajax_request();

        if (!$assessment_id || !$student_id) {
            if ($is_ajax) {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => 'Missing data']);
                return;
            }
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
            $message = 'Score saved successfully!';
        } else {
            $message = 'Score already recorded.';
        }

        if ($is_ajax) {
            header('Content-Type: application/json');
            echo json_encode(['success' => true, 'message' => $message]);
            return;
        }

        $this->session->set_flashdata($existing ? 'info' : 'success', $message);
        redirect('attendance');
    }

    // Admin — question editor page for a topic (GET)
    public function edit_topic($topic)
    {
        if (!preg_match('/^[a-z0-9_]+$/', $topic)) {
            show_error('Invalid topic name.', 400);
            return;
        }
        $file = $this->json_path . $topic . '.json';
        if (!file_exists($file)) {
            show_error('Topic not found.', 404);
            return;
        }
        $this->load->view('interactive_quiz_editor_view', [
            'topic'      => $topic,
            'topic_data' => json_decode(file_get_contents($file), true),
        ]);
    }

    // AJAX — add or update a question in a section (POST, JSON body)
    public function save_question($topic)
    {
        header('Content-Type: application/json');
        if ($this->input->method() !== 'post') {
            echo json_encode(['success' => false, 'error' => 'POST required']);
            return;
        }
        if (!preg_match('/^[a-z0-9_]+$/', $topic)) {
            echo json_encode(['success' => false, 'error' => 'Invalid topic']);
            return;
        }

        $payload = json_decode(file_get_contents('php://input'), true);
        if (!$payload) {
            echo json_encode(['success' => false, 'error' => 'Invalid JSON payload']);
            return;
        }

        $section_index  = isset($payload['section_index']) ? (int)$payload['section_index'] : -1;
        $question_index = (isset($payload['question_index']) && $payload['question_index'] !== null)
                          ? (int)$payload['question_index'] : null;
        $question_text  = trim($payload['question'] ?? '');
        $choices        = array_values(array_filter(array_map('trim', $payload['choices'] ?? [])));
        $answer         = trim($payload['answer'] ?? '');
        $explanation    = trim($payload['explanation'] ?? '');

        if (empty($question_text)) {
            echo json_encode(['success' => false, 'error' => 'Question text is required.']);
            return;
        }
        if (count($choices) < 2) {
            echo json_encode(['success' => false, 'error' => 'At least 2 choices are required.']);
            return;
        }
        if (!in_array($answer, $choices, true)) {
            echo json_encode(['success' => false, 'error' => 'Answer must exactly match one of the choices.']);
            return;
        }

        $file = $this->json_path . $topic . '.json';
        if (!file_exists($file)) {
            echo json_encode(['success' => false, 'error' => 'Topic not found.']);
            return;
        }

        $topic_data = json_decode(file_get_contents($file), true);
        if (!isset($topic_data['sections'][$section_index])) {
            echo json_encode(['success' => false, 'error' => 'Section not found.']);
            return;
        }

        $q = ['question' => $question_text, 'choices' => $choices, 'answer' => $answer];
        if ($explanation !== '') {
            $q['explanation'] = $explanation;
        }

        if (!isset($topic_data['sections'][$section_index]['questions'])) {
            $topic_data['sections'][$section_index]['questions'] = [];
        }
        $qs = &$topic_data['sections'][$section_index]['questions'];

        if ($question_index !== null && isset($qs[$question_index])) {
            $qs[$question_index] = $q;
            $saved_index = $question_index;
        } else {
            $qs[] = $q;
            $saved_index = count($qs) - 1;
        }

        $pretty = json_encode($topic_data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        if (file_put_contents($file, $pretty) === false) {
            echo json_encode(['success' => false, 'error' => 'Failed to write file.']);
            return;
        }

        echo json_encode(['success' => true, 'question_index' => $saved_index, 'question' => $q]);
    }

    // AJAX — delete a question from a section (POST, JSON body)
    public function delete_question($topic)
    {
        header('Content-Type: application/json');
        if ($this->input->method() !== 'post') {
            echo json_encode(['success' => false, 'error' => 'POST required']);
            return;
        }
        if (!preg_match('/^[a-z0-9_]+$/', $topic)) {
            echo json_encode(['success' => false, 'error' => 'Invalid topic']);
            return;
        }

        $payload        = json_decode(file_get_contents('php://input'), true);
        $section_index  = isset($payload['section_index'])  ? (int)$payload['section_index']  : -1;
        $question_index = isset($payload['question_index']) ? (int)$payload['question_index'] : -1;

        $file = $this->json_path . $topic . '.json';
        if (!file_exists($file)) {
            echo json_encode(['success' => false, 'error' => 'Topic not found.']);
            return;
        }

        $topic_data = json_decode(file_get_contents($file), true);
        if (!isset($topic_data['sections'][$section_index]['questions'][$question_index])) {
            echo json_encode(['success' => false, 'error' => 'Question not found.']);
            return;
        }

        array_splice($topic_data['sections'][$section_index]['questions'], $question_index, 1);

        $pretty = json_encode($topic_data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        if (file_put_contents($file, $pretty) === false) {
            echo json_encode(['success' => false, 'error' => 'Failed to write file.']);
            return;
        }

        echo json_encode(['success' => true]);
    }

    // AJAX — update topic-level settings: shuffle flag (POST, JSON body)
    public function save_topic_settings($topic)
    {
        header('Content-Type: application/json');
        if ($this->input->method() !== 'post') {
            echo json_encode(['success' => false, 'error' => 'POST required']);
            return;
        }
        if (!preg_match('/^[a-z0-9_]+$/', $topic)) {
            echo json_encode(['success' => false, 'error' => 'Invalid topic']);
            return;
        }

        $payload = json_decode(file_get_contents('php://input'), true);
        $file    = $this->json_path . $topic . '.json';

        if (!file_exists($file)) {
            echo json_encode(['success' => false, 'error' => 'Topic not found.']);
            return;
        }

        $topic_data = json_decode(file_get_contents($file), true);

        if (array_key_exists('shuffle', $payload)) {
            $topic_data['shuffle'] = (bool)$payload['shuffle'];
        }

        $pretty = json_encode($topic_data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        if (file_put_contents($file, $pretty) === false) {
            echo json_encode(['success' => false, 'error' => 'Failed to write file.']);
            return;
        }

        echo json_encode(['success' => true]);
    }

    // ── Private helpers ──────────────────────────────────────────────────────

    private function _build_topic_list()
    {
        $files  = glob($this->json_path . '*.json') ?: [];
        $topics = [];

        foreach ($files as $file) {
            $base     = basename($file, '.json');
            $raw      = file_get_contents($file);
            $meta     = json_decode($raw, true);
            $sections = $meta['sections'] ?? [];

            // Detect format: sections with a 'quiz' key → discussion template;
            // sections with 'questions' array → multi-question quiz.
            $format = 'discussion';
            foreach ($sections as $s) {
                if (isset($s['questions'])) { $format = 'quiz'; break; }
            }

            $topics[] = [
                'slug'      => $base,
                'title'     => $meta['title']       ?? ucwords(str_replace('_', ' ', $base)),
                'desc'      => $meta['description'] ?? '',
                'format'    => $format,
                'sections'  => count($sections),
                'questions' => array_sum(array_map(function ($s) {
                    return count($s['questions'] ?? []);
                }, $sections)),
                'size'      => filesize($file),
                'modified'  => filemtime($file),
                'shuffle'   => !empty($meta['shuffle']),
            ];
        }

        return $topics;
    }

    private function _validate_discussion_structure(array $data): string
    {
        if (empty($data['title']) || !is_string($data['title'])) {
            return 'JSON must have a non-empty "title" string field.';
        }
        if (empty($data['sections']) || !is_array($data['sections'])) {
            return 'JSON must have a non-empty "sections" array.';
        }
        foreach ($data['sections'] as $i => $section) {
            $n = $i + 1;
            if (empty($section['title'])) {
                return "Section {$n} is missing a \"title\" field.";
            }
            if (!isset($section['lesson'])) {
                return "Section {$n} is missing a \"lesson\" field.";
            }
            if (!isset($section['quiz']) || $section['quiz'] === null) {
                continue;
            }
            if (!is_array($section['quiz']) || empty($section['quiz'])) {
                return "Section {$n} has an invalid \"quiz\" value; use null or omit it when there is no quiz.";
            }
            $q = $section['quiz'];
            if (empty($q['question'])) {
                return "Section {$n} quiz is missing a \"question\" field.";
            }
            if (empty($q['options']) || !is_array($q['options']) || count($q['options']) < 2) {
                return "Section {$n} quiz must have at least 2 \"options\".";
            }
            if (!isset($q['correct']) || !is_int($q['correct']) || $q['correct'] < 0 || $q['correct'] >= count($q['options'])) {
                return "Section {$n} quiz \"correct\" must be a valid option index.";
            }
        }
        return '';
    }

    private function _validate_topic_structure(array $data): string
    {
        if (empty($data['title']) || !is_string($data['title'])) {
            return 'JSON must have a non-empty "title" string field.';
        }
        if (empty($data['sections']) || !is_array($data['sections'])) {
            return 'JSON must have a non-empty "sections" array.';
        }
        foreach ($data['sections'] as $i => $section) {
            $n = $i + 1;
            if (empty($section['title'])) {
                return "Section {$n} is missing a \"title\" field.";
            }
            if (!isset($section['lesson'])) {
                return "Section {$n} is missing a \"lesson\" field.";
            }
            foreach ($section['questions'] ?? [] as $qi => $q) {
                $qn = $qi + 1;
                if (empty($q['question'])) {
                    return "Section {$n}, question {$qn} is missing a \"question\" field.";
                }
                if (empty($q['choices']) || !is_array($q['choices']) || count($q['choices']) < 2) {
                    return "Section {$n}, question {$qn} must have at least 2 choices.";
                }
                if (empty($q['answer'])) {
                    return "Section {$n}, question {$qn} is missing an \"answer\" field.";
                }
                if (!in_array($q['answer'], $q['choices'], true)) {
                    return "Section {$n}, question {$qn}: \"answer\" must match one of the choices exactly.";
                }
            }
        }
        return '';
    }

    private function _upload_error_message(int $code): string
    {
        $messages = [
            UPLOAD_ERR_INI_SIZE   => 'File exceeds the server upload size limit.',
            UPLOAD_ERR_FORM_SIZE  => 'File exceeds the form size limit.',
            UPLOAD_ERR_PARTIAL    => 'File was only partially uploaded.',
            UPLOAD_ERR_NO_FILE    => 'No file was uploaded.',
            UPLOAD_ERR_NO_TMP_DIR => 'Missing temporary folder.',
            UPLOAD_ERR_CANT_WRITE => 'Failed to write file to disk.',
        ];
        return $messages[$code] ?? 'Unknown upload error (code ' . $code . ').';
    }
}
