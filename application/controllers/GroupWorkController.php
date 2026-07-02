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
        $this->load->model(['Grouping_model', 'Group_member_model', 'Live_state_model']);
    }

    // Resolves the assessment + the current student's group for it.
    // Returns null if the assessment isn't a valid group assessment at all;
    // returns ['assessment' => ..., 'group' => null] if valid but the
    // student isn't assigned to any group in the set.
    private function _resolve($assessment_id)
    {
        $assessment = $this->assessments->as_array()->get($assessment_id);
        if (!$assessment) show_404();

        $set_id = $this->Grouping_model->get_set_for_assessment($assessment_id);
        if (empty($assessment['is_groupings']) || !$set_id) {
            return null;
        }

        $group = $this->Grouping_model->get_student_group($this->session->student_id, $set_id);

        return ['assessment' => $assessment, 'group' => $group];
    }

    public function workspace($assessment_id)
    {
        $resolved = $this->_resolve($assessment_id);
        if (!$resolved) {
            $this->session->set_flashdata('error', 'This assessment is not set up for group submission.');
            redirect('assessment/' . $assessment_id);
            return;
        }

        if (!$resolved['group']) {
            $this->load->view('group_workspace_unassigned', ['assessment' => $resolved['assessment']]);
            return;
        }

        $group = $resolved['group'];
        $state = $this->Live_state_model->get_or_create($assessment_id, $group['group_id']);
        $members = $this->Group_member_model->get_members_by_group($group['group_id']);
        $ready_map = $this->Live_state_model->get_ready_map($state['state_id']);

        $this->load->view('group_workspace', [
            'assessment' => $resolved['assessment'],
            'group'      => $group,
            'state'      => $state,
            'members'    => $members,
            'ready_map'  => $ready_map,
            'student_id' => $this->session->student_id,
        ]);
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
        $this->_json(['ok' => true]);
    }

    // AJAX: polled every 2s by the workspace view
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

        $this->_json([
            'ok'             => true,
            'content'        => $state['content'],
            'last_edited_by' => $state['last_edited_by'],
            'updated_at'     => $state['updated_at'],
            'members'        => $member_payload,
        ]);
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
    // group member, all referencing the same file — classworks itself keeps
    // its normal per-student insert/update shape untouched.
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

        $section = $this->class_student->get(['student_id' => $this->session->student_id])->section;
        $safe_group_name = preg_replace('/[^A-Za-z0-9_-]/', '', $group['group_name']);
        $filename = $section . '-group' . $group['group_id'] . '-' . $safe_group_name . '-' . time() . '.txt';
        $upload_path = './uploads/classworks/';
        if (!is_dir($upload_path)) {
            mkdir($upload_path, 0777, true);
        }
        file_put_contents($upload_path . $filename, (string) $state['content']);

        foreach ($members as $member) {
            $submission_data = [
                'student_id'    => $member['student_id'],
                'assessment_id' => $assessment_id,
                'status'        => 'submitted',
                'submitted_at'  => date('Y-m-d H:i:s'),
                'created_at'    => date('Y-m-d H:i:s'),
                'file_upload'   => $filename,
                'code'          => null,
            ];

            $existing = $this->classworks->where([
                'student_id'    => $member['student_id'],
                'assessment_id' => $assessment_id,
            ])->get();

            if (!$existing) {
                $this->classworks->insert($submission_data);
            } else {
                // MY_Model::update() takes (data, where) — NOT (where, data).
                $this->classworks->update($submission_data, $existing->classwork_id);
            }
        }

        $this->session->set_flashdata('success', 'Submitted for the whole group!');
        redirect('classwork');
    }

    private function _json($data, $status = 200)
    {
        http_response_code($status);
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }
}
