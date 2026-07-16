<?php
defined('BASEPATH') or exit('No direct script access allowed');

class GroupWorkController extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        if (!isset($_SESSION['online'])) {
            redirect('login');
        }
        $this->load->model(['Grouping_model', 'Group_member_model', 'Live_state_model', 'Widgets_model']);
    }

    // Resolves the assessment + grouping set + the current student's group
    // for it. Returns null if the assessment isn't a valid group assessment
    // at all; returns ['assessment' => ..., 'set' => ..., 'group' => null]
    // if valid but the student isn't assigned to any group in the set yet.
    private function _resolve($assessment_id)
    {
        $assessment = $this->assessments->as_array()->get($assessment_id);
        if (!$assessment) show_404();

        $set_id = $this->Grouping_model->get_set_for_assessment($assessment_id);
        if (empty($assessment['is_groupings']) || !$set_id) {
            return null;
        }

        $set = $this->Grouping_model->get_set($set_id);
        $group = $this->Grouping_model->get_student_group($this->session->student_id, $set_id);

        return ['assessment' => $assessment, 'set' => $set, 'group' => $group];
    }

    // True if the current student is marked present/late today in the
    // set's section — same rule Groupings::store() enforces for the
    // admin auto-assign shuffle, applied here to self-select create/join.
    private function _is_present_today($section_id)
    {
        date_default_timezone_set('Asia/Manila');
        $today = date('Y-m-d');
        $present_ids = $this->attendance->get_present_student_ids_by_section($section_id, $today);
        return in_array($this->session->student_id, $present_ids);
    }

    public function workspace($assessment_id)
    {
        $resolved = $this->_resolve($assessment_id);
        if (!$resolved) {
            $this->session->set_flashdata('error', 'This assessment is not set up for group submission.');
            redirect('assessment/' . $assessment_id);
            return;
        }

        $set = $resolved['set'];
        $group = $resolved['group'];

        if (!empty($set['self_select'])) {
            // Stay on the picker/"waiting for teammates" screen until the
            // group reaches its target size — that's the only "lock" trigger
            // this feature needs (see Grouping_model::count_members()).
            $is_full = $group && $this->Grouping_model->count_members($group['group_id']) >= $set['min_members'];
            if (!$is_full) {
                if ($group) {
                    $group['members'] = $this->Group_member_model->get_members_by_group($group['group_id']);
                }
                $this->load->view('group_self_select', [
                    'assessment'  => $resolved['assessment'],
                    'set'         => $set,
                    'my_group'    => $group,
                    'is_present'  => $this->_is_present_today($set['section_id']),
                    'open_groups' => $group ? [] : array_values(array_filter(
                        $this->Grouping_model->get_groups_with_members($set['set_id']),
                        function ($g) use ($set) { return count($g['members']) < $set['min_members']; }
                    )),
                ]);
                return;
            }
        } elseif (!$group) {
            $this->load->view('group_workspace_unassigned', ['assessment' => $resolved['assessment']]);
            return;
        }
        $state = $this->Live_state_model->get_or_create($assessment_id, $group['group_id']);
        $members = $this->Group_member_model->get_members_by_group($group['group_id']);
        $ready_map = $this->Live_state_model->get_ready_map($state['state_id']);

        $widget = null;
        if (!empty($resolved['assessment']['widget_id'])) {
            $widget = $this->Widgets_model->get($resolved['assessment']['widget_id']);
        }

        $is_iq = $widget && $widget['widget_key'] === 'iq_discussion';

        // Any member's "Submit for Group" fans a classworks row out to every
        // member, so once one submits the whole group is done. Send teammates
        // still sitting on the workspace straight to their classwork list —
        // they see it's submitted instead of re-editing a draft that no longer
        // counts. The interactive-quiz path has its own "already submitted →
        // show score" screen, so leave that one to _render_group_iq().
        if (!$is_iq && $this->_already_submitted($assessment_id)) {
            $this->session->set_flashdata('success', 'Your group has already submitted this assessment.');
            redirect('classwork');
            return;
        }

        // Interactive Discussion/Quiz group play: the whole group runs one
        // shared/synced copy of the full-screen quiz (lockstep via the same
        // Live_state_model blob), not the generic shared-draft workspace.
        if ($is_iq) {
            $this->_render_group_iq($resolved['assessment'], $group, $state, $members);
            return;
        }

        $this->load->view('group_workspace', [
            'assessment'    => $resolved['assessment'],
            'group'         => $group,
            'state'         => $state,
            'members'       => $members,
            'ready_map'     => $ready_map,
            'student_id'    => $this->session->student_id,
            'widget'        => $widget,
            'widget_config' => $widget ? (json_decode($resolved['assessment']['given'] ?? '', true) ?: []) : [],
        ]);
    }

    // Student-facing self-select actions — only valid when the resolved
    // grouping set has self_select on. All three redirect back to
    // workspace(), which naturally shows the picker, the still-forming
    // group, or (once min_members is reached) the real group_workspace —
    // there's no separate "lock" step to trigger.
    public function create_group($assessment_id)
    {
        $resolved = $this->_resolve($assessment_id);
        if (!$resolved || empty($resolved['set']['self_select'])) {
            show_404();
            return;
        }
        if ($resolved['group']) {
            $this->session->set_flashdata('error', 'You already belong to a group for this assessment.');
            redirect('GroupWorkController/workspace/' . $assessment_id);
            return;
        }
        if (!$this->_is_present_today($resolved['set']['section_id'])) {
            $this->session->set_flashdata('error', 'You must be marked present today to form a group.');
            redirect('GroupWorkController/workspace/' . $assessment_id);
            return;
        }

        $group_name = trim($this->input->post('group_name'));
        if ($group_name === '') {
            $this->session->set_flashdata('error', 'Please name your group.');
            redirect('GroupWorkController/workspace/' . $assessment_id);
            return;
        }

        $this->Grouping_model->create_group_with_member($resolved['set']['set_id'], $group_name, $this->session->student_id);
        redirect('GroupWorkController/workspace/' . $assessment_id);
    }

    public function join_group($assessment_id, $group_id)
    {
        $resolved = $this->_resolve($assessment_id);
        if (!$resolved || empty($resolved['set']['self_select'])) {
            show_404();
            return;
        }
        if ($resolved['group']) {
            $this->session->set_flashdata('error', 'You already belong to a group for this assessment.');
            redirect('GroupWorkController/workspace/' . $assessment_id);
            return;
        }
        if (!$this->_is_present_today($resolved['set']['section_id'])) {
            $this->session->set_flashdata('error', 'You must be marked present today to join a group.');
            redirect('GroupWorkController/workspace/' . $assessment_id);
            return;
        }

        $group = $this->Grouping_model->get($group_id);
        if (!$group || (int) $group['set_id'] !== (int) $resolved['set']['set_id']) {
            show_404();
            return;
        }
        if ($this->Grouping_model->count_members($group_id) >= $resolved['set']['min_members']) {
            $this->session->set_flashdata('error', 'That group is already full.');
            redirect('GroupWorkController/workspace/' . $assessment_id);
            return;
        }

        $this->Grouping_model->join_group($group_id, $this->session->student_id);
        redirect('GroupWorkController/workspace/' . $assessment_id);
    }

    public function leave_group($assessment_id)
    {
        $resolved = $this->_resolve($assessment_id);
        if (!$resolved || empty($resolved['set']['self_select']) || !$resolved['group']) {
            redirect('GroupWorkController/workspace/' . $assessment_id);
            return;
        }
        if ($this->Grouping_model->count_members($resolved['group']['group_id']) >= $resolved['set']['min_members']) {
            $this->session->set_flashdata('error', 'This group is already full and locked in — you can no longer leave it.');
            redirect('GroupWorkController/workspace/' . $assessment_id);
            return;
        }

        $this->Grouping_model->leave_group($resolved['group']['group_id'], $this->session->student_id);
        redirect('GroupWorkController/workspace/' . $assessment_id);
    }

    // AJAX: autosave the shared draft
    public function save_draft($assessment_id)
    {
        $resolved = $this->_resolve($assessment_id);
        if (!$resolved || !$resolved['group']) {
            $this->_json(['ok' => false], 400);
            return;
        }

        $state = $this->Live_state_model->get_or_create($assessment_id, $resolved['group']['group_id']);
        $this->Live_state_model->save_content($state['state_id'], $this->input->post('content'), $this->session->student_id);
        // Return the fresh version stamp so the saver can advance its own
        // poll marker and not have the server echo this content straight back.
        $fresh = $this->Live_state_model->get_state($assessment_id, $resolved['group']['group_id']);
        $this->_json(['ok' => true, 'updated_at' => $fresh['updated_at']]);
    }

    // AJAX: polled every 2s by the workspace view.
    //
    // Conditional payload: if the caller passes ?since=<updated_at> and it still
    // matches the row, the (potentially large) shared content blob is omitted —
    // only the small members/ready payload ships. Callers that don't send
    // ?since (e.g. the interactive-quiz template) always get the full content,
    // so this stays backward-compatible.
    public function state($assessment_id)
    {
        $resolved = $this->_resolve($assessment_id);
        if (!$resolved || !$resolved['group']) {
            $this->_json(['ok' => false], 400);
            return;
        }

        $group = $resolved['group'];
        $state = $this->Live_state_model->get_or_create($assessment_id, $group['group_id']);
        $members = $this->Group_member_model->get_members_by_group($group['group_id']);
        $ready_map = $this->Live_state_model->get_ready_map($state['state_id']);

        $member_payload = array_map(function ($m) use ($ready_map) {
            return [
                'student_id' => $m['student_id'],
                'name'       => trim($m['firstname'] . ' ' . $m['lastname']),
                'ready'      => !empty($ready_map[$m['student_id']]),
            ];
        }, $members);

        // Ready flags live in a separate table and don't bump this row's
        // updated_at, so members/ready are always sent — only the big content
        // blob is gated on the version the client last applied.
        $since   = $this->input->get('since');
        $changed = ($since === null || $since !== $state['updated_at']);

        $payload = [
            'ok'              => true,
            'updated_at'      => $state['updated_at'],
            'content_changed' => $changed,
            'members'         => $member_payload,
            // Lets a teammate's still-open workspace notice a submission that
            // happened elsewhere and bounce itself to the classwork list.
            'submitted'       => $this->_already_submitted($assessment_id),
        ];
        if ($changed) {
            $payload['content']        = $state['content'];
            $payload['last_edited_by'] = $state['last_edited_by'];
        }
        $this->_json($payload);
    }

    // AJAX: toggle the current student's own ready flag
    public function toggle_ready($assessment_id)
    {
        $resolved = $this->_resolve($assessment_id);
        if (!$resolved || !$resolved['group']) {
            $this->_json(['ok' => false], 400);
            return;
        }

        $state = $this->Live_state_model->get_or_create($assessment_id, $resolved['group']['group_id']);
        $this->Live_state_model->set_ready($state['state_id'], $this->session->student_id, (bool) $this->input->post('ready'));
        $this->_json(['ok' => true]);
    }

    // Fans out the shared draft into a per-student classworks row for every
    // group member (same shared file for plain text, same JSON in the code
    // column for widget submissions) — classworks itself keeps its normal
    // per-student insert/update shape untouched.
    public function submit_group($assessment_id)
    {
        $resolved = $this->_resolve($assessment_id);
        if (!$resolved || !$resolved['group']) {
            $this->session->set_flashdata('error', 'Unable to submit — group not found.');
            redirect('classwork');
            return;
        }

        $group = $resolved['group'];
        $state = $this->Live_state_model->get_or_create($assessment_id, $group['group_id']);
        $members = $this->Group_member_model->get_members_by_group($group['group_id']);

        if (empty($members)) {
            $this->session->set_flashdata('error', 'No members found in this group.');
            redirect('GroupWorkController/workspace/' . $assessment_id);
            return;
        }

        // The submitter's own screen is the source of truth — the debounced
        // autosave may not have landed yet (see prepareGroupSubmit() in
        // group_workspace.php). Fall back to the last saved state only if the
        // client didn't send anything (JS disabled/degraded).
        $posted = $this->input->post('content');
        $content = ($posted !== null && $posted !== '') ? $posted : $state['content'];

        $is_widget = !empty($resolved['assessment']['widget_id']);

        $widget = $is_widget ? $this->Widgets_model->get($resolved['assessment']['widget_id']) : null;
        $is_quiz = $widget && $widget['widget_key'] === 'quiz';

        // Refuse to fan out a blank submission over every member's row —
        // getWidgetState() always returns non-empty JSON shells like
        // {"rows":[]} or {"answers":{"0":"","1":""}}, so a plain emptiness
        // check on the raw string would never catch an untouched widget.
        $decoded = $is_widget ? (json_decode($content ?? '', true) ?: []) : $content;
        if (!$this->_has_content($decoded)) {
            $this->session->set_flashdata('error', 'Nothing has been filled in yet — there is nothing to submit.');
            redirect('GroupWorkController/workspace/' . $assessment_id);
            return;
        }

        // Persist what's actually being submitted so the stored draft matches.
        $this->Live_state_model->save_content($state['state_id'], $content, $this->session->student_id);

        $filename = null;
        $quiz_score = null;
        $quiz_results_json = null;

        if ($is_quiz) {
            // Auto-graded, shared across the whole group — never trust a
            // client-computed score, grade server-side from the config.
            $config = json_decode($resolved['assessment']['given'] ?? '', true) ?: [];
            $answers = json_decode($content ?? '', true)['answers'] ?? [];
            $graded = $this->Widgets_model->grade_quiz($config, $answers);
            $quiz_score = $graded['score'];
            $quiz_results_json = json_encode($graded['results']);
        } elseif (!$is_widget) {
            // Plain text/code drafts are written to one shared file — matches
            // how AssessmentController::submit_classwork() stores individual
            // text submissions.
            $section = $this->class_student->get(['student_id' => $this->session->student_id])->section;
            $safe_group_name = preg_replace('/[^A-Za-z0-9_-]/', '', $group['group_name']);
            $filename = $section . '-group' . $group['group_id'] . '-' . $safe_group_name . '-' . time() . '.txt';
            $upload_path = './uploads/classworks/';
            if (!is_dir($upload_path)) {
                mkdir($upload_path, 0777, true);
            }
            file_put_contents($upload_path . $filename, (string) $content);
        }

        $my_classwork_id = null;

        foreach ($members as $member) {
            $submission_data = [
                'student_id'    => $member['student_id'],
                'assessment_id' => $assessment_id,
                'status'        => 'submitted',
                'submitted_at'  => date('Y-m-d H:i:s'),
                'created_at'    => date('Y-m-d H:i:s'),
                // Widget submissions are structured JSON — kept in the code
                // column (like the solo widget path) instead of a shared file.
                'file_upload'   => $is_widget ? null : $filename,
                'code'          => $is_quiz ? $quiz_results_json : ($is_widget ? $content : null),
            ];
            if ($is_quiz) {
                $submission_data['score'] = $quiz_score;
            }

            $existing = $this->classworks->where([
                'student_id'    => $member['student_id'],
                'assessment_id' => $assessment_id,
            ])->get();

            if (!$existing) {
                $classwork_id = $this->classworks->insert($submission_data);
            } else {
                // MY_Model::update() takes (data, where) — NOT (where, data).
                $this->classworks->update($submission_data, $existing->classwork_id);
                $classwork_id = $existing->classwork_id;
            }

            if ((string) $member['student_id'] === (string) $this->session->student_id) {
                $my_classwork_id = $classwork_id;
            }
        }

        $this->session->set_flashdata('success', 'Submitted for the whole group!');

        if ($is_quiz && $my_classwork_id) {
            redirect('student_submission/' . $my_classwork_id);
            return;
        }

        redirect('classwork');
    }

    // True once this student has a submitted classworks row for the assessment —
    // which, for a group assessment, means some member already turned it in for
    // everyone (submit_group / submit_group_iq fan the row out to all members).
    private function _already_submitted($assessment_id)
    {
        $row = $this->classworks->where([
            'student_id'    => $this->session->student_id,
            'assessment_id' => $assessment_id,
        ])->get();
        return $row && $row->status === 'submitted';
    }

    // Recursively checks a decoded widget payload (or a plain string) for any
    // non-blank value. trim((string) 0) !== '' is true, so numeric 0 ratings
    // (decision_matrix, case_dossier) correctly count as content.
    private function _has_content($decoded)
    {
        if (is_array($decoded)) {
            foreach ($decoded as $v) {
                if ($this->_has_content($v)) return true;
            }
            return false;
        }
        return trim((string) $decoded) !== '';
    }

    // Renders the interactive quiz template in group mode. Group forming,
    // membership and the live-state row are already resolved by workspace();
    // this just loads the topic JSON and hands the template the sync context.
    private function _render_group_iq($assessment, $group, $state, $members)
    {
        $config = json_decode($assessment['given'] ?? '', true) ?: [];
        $topic  = $config['topic'] ?? '';
        $file   = $topic ? $this->_resolve_topic_file($topic) : false;

        if (!$file) {
            show_error('Interactive topic not found for this assessment.', 404);
            return;
        }

        $topic_data = json_decode(file_get_contents($file), true);
        if (!$topic_data || !isset($topic_data['sections'])) {
            show_error('Invalid or malformed topic data.', 500);
            return;
        }

        // If the group already submitted, this student has a recorded row —
        // show their score up front and treat replays as practice (same
        // first-try-only contract as the solo InteractiveQuizController flow).
        $already_submitted = false;
        $previous_score    = null;
        $previous_answers  = [];

        $existing = $this->classworks->where([
            'student_id'    => $this->session->student_id,
            'assessment_id' => $assessment['assessment_id'],
        ])->get();

        if ($existing) {
            $already_submitted = true;
            $previous_score    = $existing->score;
            $previous_answers  = json_decode($existing->code ?? '', true) ?: [];
        }

        $this->load->view('discussions/_interactive_quiz_template', [
            'topic_data'        => $topic_data,
            'assessment_id'     => (int) $assessment['assessment_id'],
            'already_submitted' => $already_submitted,
            'previous_score'    => $previous_score,
            'previous_answers'  => $previous_answers,
            // Group-mode context (consumed only when group_mode is set):
            'group_mode'        => true,
            'group'             => $group,
            'group_members'     => $members,
            'state_content'     => $state['content'],
            'student_id'        => $this->session->student_id,
        ]);
    }

    // AJAX: grade a group's interactive-quiz play server-side from the topic
    // JSON (never trusting a client score) and fan one result out to every
    // member's classworks row. Mirrors submit_group()'s fan-out + the solo
    // save_result()'s first-completion-only recording.
    public function submit_group_iq($assessment_id)
    {
        $resolved = $this->_resolve($assessment_id);
        if (!$resolved || !$resolved['group']) {
            $this->_json(['success' => false, 'message' => 'Group not found'], 400);
            return;
        }

        $group      = $resolved['group'];
        $assessment = $resolved['assessment'];

        $config = json_decode($assessment['given'] ?? '', true) ?: [];
        $topic  = $config['topic'] ?? '';
        $file   = $topic ? $this->_resolve_topic_file($topic) : false;
        if (!$file) {
            $this->_json(['success' => false, 'message' => 'Topic not found'], 404);
            return;
        }

        $topic_data = json_decode(file_get_contents($file), true);
        $sections   = $topic_data['sections'] ?? [];

        // Authoritative answers come from the shared live-state blob, not the
        // POST body — the same blob every member has been syncing into.
        $state  = $this->Live_state_model->get_or_create($assessment_id, $group['group_id']);
        $blob   = json_decode($state['content'] ?? '', true) ?: [];
        $picked = $blob['sections'] ?? [];

        $score   = 0;
        $total   = 0;
        $results = [];

        foreach ($sections as $i => $section) {
            $quiz = $section['quiz'] ?? null;
            if (!is_array($quiz) || empty($quiz['question']) || empty($quiz['options'])) {
                continue; // lesson-only section — not graded
            }
            $total++;

            $sel            = isset($picked[$i]['selected']) ? (int) $picked[$i]['selected'] : -1;
            $correct_idx    = (int) ($quiz['correct'] ?? -1);
            $chosen         = ($sel >= 0 && isset($quiz['options'][$sel])) ? $quiz['options'][$sel] : '';
            $correct_answer = isset($quiz['options'][$correct_idx]) ? $quiz['options'][$correct_idx] : '';
            $is_correct     = ($sel >= 0 && $sel === $correct_idx);

            if ($is_correct) {
                $score++;
            }

            $results[] = [
                'section'        => $i,
                'section_title'  => $section['title'] ?? '',
                'question'       => $quiz['question'],
                'chosen'         => $chosen,
                'correct_answer' => $correct_answer,
                'is_correct'     => $is_correct,
            ];
        }

        // First-completion-only: if this student already has a row, the group
        // already submitted — report the recorded score, don't overwrite.
        $mine = $this->classworks->where([
            'student_id'    => $this->session->student_id,
            'assessment_id' => $assessment_id,
        ])->get();

        if ($mine) {
            $this->_json([
                'success'  => true,
                'recorded' => false,
                'score'    => (int) $mine->score,
                'total'    => $total,
                'message'  => 'Score already recorded.',
            ]);
            return;
        }

        $members      = $this->Group_member_model->get_members_by_group($group['group_id']);
        $results_json = json_encode($results);
        $now          = date('Y-m-d H:i:s');

        foreach ($members as $member) {
            $data = [
                'student_id'    => $member['student_id'],
                'assessment_id' => $assessment_id,
                'status'        => 'submitted',
                'submitted_at'  => $now,
                'created_at'    => $now,
                'score'         => $score,
                'code'          => $results_json,
            ];

            $existing = $this->classworks->where([
                'student_id'    => $member['student_id'],
                'assessment_id' => $assessment_id,
            ])->get();

            if (!$existing) {
                $this->classworks->insert($data);
            } else {
                $this->classworks->update($data, $existing->classwork_id);
            }
        }

        $this->_json([
            'success'  => true,
            'recorded' => true,
            'score'    => $score,
            'total'    => $total,
        ]);
    }

    // Topic JSON files live in assets/json/ or one class-code folder down —
    // same resolution InteractiveQuizController uses.
    private function _resolve_topic_file($topic)
    {
        if (!preg_match('/^[a-z0-9_]+$/', $topic)) {
            return false;
        }
        $base   = FCPATH . 'assets/json/';
        $direct = $base . $topic . '.json';
        if (file_exists($direct)) {
            return $direct;
        }
        foreach (glob($base . '*/' . $topic . '.json') ?: [] as $match) {
            return $match;
        }
        return false;
    }

    private function _json($data, $status = 200)
    {
        http_response_code($status);
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }
}
