<?php
defined('BASEPATH') or exit('No direct script access allowed');

class PollController extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('Polls');
        $this->load->helper(['url', 'form']);
        $this->load->library('session');
    }

    // ── Install ──────────────────────────────────────────────────────────────

    // One-time setup: creates DB tables. Admin only.
    public function install()
    {
        if (!$this->session->teacher) {
            redirect('login');
        }
        $this->Polls->install();
        $this->session->set_flashdata('success', 'Poll tables created successfully.');
        redirect('poll/dashboard');
    }

    // ── Teacher: Dashboard ───────────────────────────────────────────────────

    public function dashboard()
    {
        if (!$this->session->teacher) {
            redirect('login');
        }
        $data['polls'] = $this->Polls->get_all_polls();
        $this->load->view('polls/teacher_dashboard', $data);
    }

    // ── Teacher: Create poll ─────────────────────────────────────────────────

    public function create()
    {
        if (!$this->session->teacher) {
            redirect('login');
        }

        if ($this->input->method() === 'post') {
            $title     = trim($this->input->post('title'));
            $questions = $this->input->post('questions') ?: [];

            if ($title === '') {
                $this->session->set_flashdata('error', 'Poll title is required.');
                redirect('poll/create');
                return;
            }

            $result  = $this->Polls->create_poll($title, $this->session->username ?? 'teacher');
            $poll_id = $result['poll_id'];

            foreach ($questions as $qi => $q) {
                $q_text = trim($q['text'] ?? '');
                if ($q_text === '') continue;

                $question_id = $this->Polls->add_question($poll_id, $q_text, $qi);

                foreach (($q['options'] ?? []) as $oi => $opt) {
                    $opt_text = trim($opt);
                    if ($opt_text !== '') {
                        $this->Polls->add_option($question_id, $opt_text, $oi);
                    }
                }
            }

            $this->session->set_flashdata('success', 'Poll created! PIN: ' . $result['pin']);
            redirect('poll/present/' . $poll_id);
            return;
        }

        $this->load->view('polls/teacher_create');
    }

    // ── Teacher: Present / control ───────────────────────────────────────────

    public function present($poll_id)
    {
        if (!$this->session->teacher) {
            redirect('login');
        }
        $poll = $this->Polls->get_poll($poll_id);
        if (!$poll) show_404();

        $questions = $this->Polls->get_questions($poll_id);
        foreach ($questions as &$q) {
            $q['options'] = $this->Polls->get_options($q['question_id']);
        }
        unset($q);

        $data = [
            'poll'      => $poll,
            'questions' => $questions,
        ];
        $this->load->view('polls/teacher_present', $data);
    }

    // AJAX: activate a question (teacher presses "Launch")
    public function activate_question($question_id)
    {
        if (!$this->session->teacher) {
            $this->_json(['ok' => false, 'msg' => 'Unauthorized'], 403);
            return;
        }
        $question = $this->Polls->get_question($question_id);
        if (!$question) {
            $this->_json(['ok' => false, 'msg' => 'Not found'], 404);
            return;
        }
        $this->Polls->set_active_question($question['poll_id'], $question_id);
        $this->_json(['ok' => true]);
    }

    // AJAX: toggle show_results for current question
    public function toggle_results($question_id)
    {
        if (!$this->session->teacher) {
            $this->_json(['ok' => false, 'msg' => 'Unauthorized'], 403);
            return;
        }
        $show = $this->Polls->toggle_show_results($question_id);
        $this->_json(['ok' => true, 'show_results' => (bool) $show]);
    }

    // AJAX: close the poll
    public function close_poll($poll_id)
    {
        if (!$this->session->teacher) {
            $this->_json(['ok' => false, 'msg' => 'Unauthorized'], 403);
            return;
        }
        $this->Polls->set_status($poll_id, 'closed');
        $this->Polls->clear_active_question($poll_id);
        $this->_json(['ok' => true]);
    }

    // AJAX: delete a poll
    public function delete_poll($poll_id)
    {
        if (!$this->session->teacher) {
            $this->_json(['ok' => false, 'msg' => 'Unauthorized'], 403);
            return;
        }
        $this->Polls->delete_poll($poll_id);
        $this->_json(['ok' => true]);
    }

    // AJAX: live results for teacher display (polls every 2 s)
    public function results($question_id)
    {
        if (!$this->session->teacher) {
            $this->_json(['ok' => false], 403);
            return;
        }
        $question = $this->Polls->get_question($question_id);
        if (!$question) {
            $this->_json(['ok' => false], 404);
            return;
        }
        $this->_json([
            'ok'           => true,
            'results'      => $this->Polls->get_results($question_id),
            'total'        => $this->Polls->get_total_responses($question_id),
            'show_results' => (bool) $question['show_results'],
        ]);
    }

    // AJAX: returns the first active poll (for attendance view banner)
    public function active_poll()
    {
        $poll = $this->db->where('status', 'active')->limit(1)->get('polls')->row_array();
        if ($poll) {
            $this->_json(['ok' => true, 'poll' => ['pin' => $poll['pin'], 'title' => $poll['title']]]);
        } else {
            $this->_json(['ok' => true, 'poll' => null]);
        }
    }

    // ── Student: Join (fallback PIN entry — kept for direct URL access) ──────

    public function join($pin = null)
    {
        if (!$this->session->student_id) {
            redirect('login');
        }

        if ($pin) {
            $poll = $this->Polls->get_poll_by_pin($pin);
            if ($poll && $poll['status'] === 'active') {
                redirect('poll/answer/' . $pin);
                return;
            }
            $this->session->set_flashdata('error', 'Poll not found or not active.');
        }

        redirect('attendance');
    }

    // Student answer view
    public function answer($pin)
    {
        if (!$this->session->student_id) {
            redirect('login');
        }
        $poll = $this->Polls->get_poll_by_pin(strtoupper($pin));
        if (!$poll || $poll['status'] !== 'active') {
            $this->session->set_flashdata('error', 'Poll is not active.');
            redirect('poll/join');
            return;
        }

        $this->load->view('polls/student_answer', ['poll' => $poll]);
    }

    // AJAX: student checks current state (active question, whether they answered, results if shown)
    public function student_state($pin)
    {
        if (!$this->session->student_id) {
            $this->_json(['ok' => false, 'msg' => 'Not logged in'], 401);
            return;
        }
        $poll = $this->Polls->get_poll_by_pin(strtoupper($pin));
        if (!$poll) {
            $this->_json(['ok' => false, 'msg' => 'Poll not found'], 404);
            return;
        }

        if ($poll['status'] === 'closed') {
            $this->_json(['ok' => true, 'status' => 'closed']);
            return;
        }

        if (!$poll['active_question_id']) {
            $this->_json(['ok' => true, 'status' => 'waiting']);
            return;
        }

        $qid      = (int) $poll['active_question_id'];
        $sid      = $this->session->student_id;
        $question = $this->Polls->get_question($qid);
        $options  = $this->Polls->get_options($qid);
        $answered = $this->Polls->has_answered($qid, $sid);

        $payload = [
            'ok'          => true,
            'status'      => 'active',
            'question_id' => $qid,
            'question'    => $question['question_text'],
            'options'     => $options,
            'answered'    => $answered,
        ];

        if ($answered && $question['show_results']) {
            $payload['results'] = $this->Polls->get_results($qid);
            $payload['total']   = $this->Polls->get_total_responses($qid);
        }

        $this->_json($payload);
    }

    // AJAX: student submits an answer
    public function submit_answer()
    {
        if (!$this->session->student_id) {
            $this->_json(['ok' => false, 'msg' => 'Not logged in'], 401);
            return;
        }

        $question_id = (int) $this->input->post('question_id');
        $option_id   = (int) $this->input->post('option_id');
        $student_id  = $this->session->student_id;

        if (!$question_id || !$option_id) {
            $this->_json(['ok' => false, 'msg' => 'Missing fields'], 400);
            return;
        }

        // Verify option belongs to question
        $option = $this->db->get_where('poll_options', [
            'option_id'   => $option_id,
            'question_id' => $question_id,
        ])->row_array();

        if (!$option) {
            $this->_json(['ok' => false, 'msg' => 'Invalid option'], 400);
            return;
        }

        $saved = $this->Polls->submit_response($question_id, $option_id, $student_id);
        $this->_json(['ok' => true, 'saved' => $saved]);
    }

    // ── Helpers ──────────────────────────────────────────────────────────────

    private function _json($data, $status = 200)
    {
        http_response_code($status);
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }
}
