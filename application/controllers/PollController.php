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

    public function install()
    {
        if ($this->session->role != 'admin') {
            redirect('login');
        }
        $this->Polls->install();
        $this->session->set_flashdata('success', 'Poll tables ready.');
        redirect('poll/dashboard');
    }

    // ── Teacher: Dashboard ───────────────────────────────────────────────────

    public function dashboard()
    {
        if ($this->session->role != 'admin') {
            redirect('login');
        }
        $data['polls'] = $this->Polls->get_all_polls();
        $this->load->view('polls/teacher_dashboard', $data);
    }

    // ── Teacher: Create poll ─────────────────────────────────────────────────

    public function create()
    {
        if ($this->session->role != 'admin') {
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

                $q_type      = ($q['type'] ?? 'multiple_choice') === 'open_ended' ? 'open_ended' : 'multiple_choice';
                $question_id = $this->Polls->add_question($poll_id, $q_text, $q_type, $qi);

                if (!$question_id) {
                    $this->session->set_flashdata('error', 'Failed to add question: ' . $q_text);
                    redirect('poll/create');
                    return;
                }

                if ($q_type === 'multiple_choice') {
                    foreach (($q['options'] ?? []) as $oi => $opt) {
                        $opt_text = trim($opt);
                        if ($opt_text !== '') {
                            $option_id = $this->Polls->add_option($question_id, $opt_text, $oi);
                            if (!$option_id) {
                                $this->session->set_flashdata('error', 'Failed to add option: ' . $opt_text);
                                redirect('poll/create');
                                return;
                            }
                        }
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
        if ($this->session->role != 'admin') {
            redirect('login');
        }
        $poll = $this->Polls->get_poll($poll_id);
        if (!$poll) show_404();

        $questions = $this->Polls->get_questions($poll_id);
        foreach ($questions as &$q) {
            $q['options'] = ($q['question_type'] === 'multiple_choice')
                ? $this->Polls->get_options($q['question_id'])
                : [];
        }
        unset($q);

        $this->load->view('polls/teacher_present', [
            'poll'      => $poll,
            'questions' => $questions,
        ]);
    }

    // AJAX: activate a question
    public function activate_question($question_id)
    {
        if ($this->session->role != 'admin') {
            $this->_json(['ok' => false, 'msg' => 'Unauthorized'], 403);
            return;
        }
        $question = $this->Polls->get_question($question_id);
        if (!$question) {
            $this->_json(['ok' => false], 404);
            return;
        }
        $this->Polls->set_active_question($question['poll_id'], $question_id);
        $this->_json(['ok' => true]);
    }

    // AJAX: toggle show_results
    public function toggle_results($question_id)
    {
        if (!$this->session->teacher) {
            $this->_json(['ok' => false, 'msg' => 'Unauthorized'], 403);
            return;
        }
        $show = $this->Polls->toggle_show_results($question_id);
        $this->_json(['ok' => true, 'show_results' => (bool) $show]);
    }

    // AJAX: close poll
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

    // AJAX: delete poll
    public function delete_poll($poll_id)
    {
        if (!$this->session->teacher) {
            $this->_json(['ok' => false, 'msg' => 'Unauthorized'], 403);
            return;
        }
        $this->Polls->delete_poll($poll_id);
        $this->_json(['ok' => true]);
    }

    // AJAX: live results for teacher (polls every 2 s)
    public function results($question_id)
    {
        if ($this->session->role != 'admin') {
            $this->_json(['ok' => false], 403);
            return;
        }
        $question = $this->Polls->get_question($question_id);
        if (!$question) {
            $this->_json(['ok' => false], 404);
            return;
        }

        $is_oe = $question['question_type'] === 'open_ended';

        $this->_json([
            'ok'            => true,
            'question_type' => $question['question_type'],
            'results'       => $is_oe
                ? $this->Polls->get_oe_results($question_id)
                : $this->Polls->get_mc_results($question_id),
            'total'         => $this->Polls->get_total_responses($question_id),
            'show_results'  => (bool) $question['show_results'],
        ]);
    }

    // AJAX: returns active poll for attendance banner
    public function active_poll()
    {
        if (!$this->session->role && !$this->session->student_id) {
            $this->_json(['ok' => false], 403);
            return;
        }
        $poll = $this->db->where('status', 'active')->limit(1)->get('polls')->row_array();
        $this->_json([
            'ok'   => true,
            'poll' => $poll ? ['pin' => $poll['pin'], 'title' => $poll['title']] : null,
        ]);
    }

    // ── Student: Join (redirect to attendance if no active poll) ─────────────

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
            redirect('attendance');
            return;
        }
        $this->load->view('polls/student_answer', ['poll' => $poll]);
    }

    // AJAX: student checks current state
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
        $answered = $this->Polls->has_answered($qid, $sid);
        $is_oe    = $question['question_type'] === 'open_ended';

        $payload = [
            'ok'            => true,
            'status'        => 'active',
            'question_id'   => $qid,
            'question'      => $question['question_text'],
            'question_type' => $question['question_type'],
            'options'       => $is_oe ? [] : $this->Polls->get_options($qid),
            'answered'      => $answered,
        ];

        if ($answered && $question['show_results']) {
            $payload['results'] = $is_oe
                ? $this->Polls->get_oe_results($qid)
                : $this->Polls->get_mc_results($qid);
            $payload['total'] = $this->Polls->get_total_responses($qid);
        }

        $this->_json($payload);
    }

    // AJAX: student submits answer (multiple-choice or open-ended)
    public function submit_answer()
    {
        if (!$this->session->student_id) {
            $this->_json(['ok' => false, 'msg' => 'Not logged in'], 401);
            return;
        }

        $question_id   = (int) $this->input->post('question_id');
        $option_id     = $this->input->post('option_id')   ?: null;
        $response_text = trim($this->input->post('response_text') ?? '');
        $student_id    = $this->session->student_id;

        if (!$question_id) {
            $this->_json(['ok' => false, 'msg' => 'Missing question_id'], 400);
            return;
        }

        $question = $this->Polls->get_question($question_id);
        if (!$question) {
            $this->_json(['ok' => false, 'msg' => 'Question not found'], 404);
            return;
        }

        if ($question['question_type'] === 'multiple_choice') {
            $option_id = (int) $option_id;
            if (!$option_id) {
                $this->_json(['ok' => false, 'msg' => 'Missing option_id'], 400);
                return;
            }
            // Verify option belongs to question
            $opt = $this->db->get_where('poll_options', [
                'option_id'   => $option_id,
                'question_id' => $question_id,
            ])->row_array();
            if (!$opt) {
                $this->_json(['ok' => false, 'msg' => 'Invalid option'], 400);
                return;
            }
            $saved = $this->Polls->submit_response($question_id, $student_id, $option_id, null);
        } else {
            // open_ended
            if ($response_text === '') {
                $this->_json(['ok' => false, 'msg' => 'Response cannot be empty'], 400);
                return;
            }
            $response_text = mb_substr($response_text, 0, 100);
            $saved = $this->Polls->submit_response($question_id, $student_id, null, $response_text);
        }

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
