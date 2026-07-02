<?php
defined('BASEPATH') or exit('No direct script access allowed');

// Widget D — Brainstorm & Voting Board (root/docs/paperless-midterm-plan.md #4,
// #10-adjacent). Unlike every other widget, this one is a single shared board
// per assessment (section-wide), not a per-student submission — built on the
// generic Live_state_model with group_id = NULL, per the plan doc's guidance,
// instead of a new table. A classworks row is still created per participating
// student (no score) so admin submission lists show who took part.
class BrainstormController extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        if (!isset($_SESSION['online'])) {
            redirect('login');
        }
        $this->load->model(['Widgets_model', 'Live_state_model']);
    }

    private function _resolve($assessment_id)
    {
        $assessment = $this->assessments->as_array()->get($assessment_id);
        if (!$assessment) show_404();

        if (empty($assessment['widget_id'])) return null;
        $widget = $this->Widgets_model->get($assessment['widget_id']);
        if (!$widget || $widget['widget_key'] !== 'brainstorm') return null;

        return $assessment;
    }

    public function board($assessment_id)
    {
        $assessment = $this->_resolve($assessment_id);
        if (!$assessment) {
            redirect('assessment/' . $assessment_id);
            return;
        }

        $config = json_decode($assessment['given'] ?? '', true) ?: [];
        $state = $this->Live_state_model->get_or_create($assessment_id, null);

        $this->load->view('brainstorm_board', [
            'assessment' => $assessment,
            'config'     => $config,
            'state'      => $state,
            'student_id' => $this->session->student_id,
        ]);
    }

    // AJAX: polled every 2s
    public function state($assessment_id)
    {
        $assessment = $this->_resolve($assessment_id);
        if (!$assessment) {
            $this->_json(['ok' => false], 400);
            return;
        }

        $state = $this->Live_state_model->get_or_create($assessment_id, null);
        $board = json_decode($state['content'] ?? '', true) ?: ['notes' => []];

        $this->_json(['ok' => true, 'notes' => $board['notes'] ?? []]);
    }

    // AJAX: add a sticky note
    public function add_note($assessment_id)
    {
        $assessment = $this->_resolve($assessment_id);
        if (!$assessment) {
            $this->_json(['ok' => false], 400);
            return;
        }

        $text = trim((string) $this->input->post('text'));
        if ($text === '') {
            $this->_json(['ok' => false, 'msg' => 'Note text is required'], 400);
            return;
        }

        $state = $this->Live_state_model->get_or_create($assessment_id, null);
        $board = json_decode($state['content'] ?? '', true) ?: ['notes' => [], 'next_id' => 1];
        $next_id = $board['next_id'] ?? (count($board['notes'] ?? []) + 1);

        $board['notes'][] = [
            'id'     => $next_id,
            'text'   => mb_substr($text, 0, 280),
            'author' => trim($this->session->firstname . ' ' . mb_substr($this->session->lastname, 0, 1) . '.'),
            'votes'  => [],
        ];
        $board['next_id'] = $next_id + 1;

        $this->Live_state_model->save_content($state['state_id'], json_encode($board), $this->session->student_id);
        $this->_mark_participated($assessment_id);

        $this->_json(['ok' => true]);
    }

    // AJAX: toggle the current student's vote on a note
    public function toggle_vote($assessment_id)
    {
        $assessment = $this->_resolve($assessment_id);
        if (!$assessment) {
            $this->_json(['ok' => false], 400);
            return;
        }

        $note_id = (int) $this->input->post('note_id');
        $config = json_decode($assessment['given'] ?? '', true) ?: [];
        $max_votes = (int) ($config['max_votes_per_student'] ?? 3);
        $student_id = (string) $this->session->student_id;

        $state = $this->Live_state_model->get_or_create($assessment_id, null);
        $board = json_decode($state['content'] ?? '', true) ?: ['notes' => []];

        $total_votes_used = 0;
        foreach ($board['notes'] ?? [] as $n) {
            if (in_array($student_id, $n['votes'] ?? [], true)) $total_votes_used++;
        }

        $found = false;
        foreach ($board['notes'] as &$note) {
            if ((int) $note['id'] !== $note_id) continue;
            $found = true;
            $votes = $note['votes'] ?? [];
            $already_voted = in_array($student_id, $votes, true);

            if ($already_voted) {
                $note['votes'] = array_values(array_diff($votes, [$student_id]));
            } elseif ($total_votes_used < $max_votes) {
                $votes[] = $student_id;
                $note['votes'] = $votes;
            } else {
                $this->_json(['ok' => false, 'msg' => 'No votes remaining'], 400);
                return;
            }
        }
        unset($note);

        if (!$found) {
            $this->_json(['ok' => false, 'msg' => 'Note not found'], 404);
            return;
        }

        $this->Live_state_model->save_content($state['state_id'], json_encode($board), $this->session->student_id);
        $this->_mark_participated($assessment_id);

        $this->_json(['ok' => true]);
    }

    // Ensures a lightweight classworks row exists so admin submission lists
    // show participation — no score, no code (the real content lives on the
    // shared board, not per-student).
    private function _mark_participated($assessment_id)
    {
        $existing = $this->classworks->where([
            'student_id'    => $this->session->student_id,
            'assessment_id' => $assessment_id,
        ])->get();

        if (!$existing) {
            $this->classworks->insert([
                'student_id'    => $this->session->student_id,
                'assessment_id' => $assessment_id,
                'status'        => 'submitted',
                'submitted_at'  => date('Y-m-d H:i:s'),
                'created_at'    => date('Y-m-d H:i:s'),
            ]);
        }
    }

    private function _json($data, $status = 200)
    {
        http_response_code($status);
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }
}
