<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Groupings extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('Grouping_model');
        $this->load->model('Group_member_model');
        // existing student/section models assumed:
        $this->load->model('class_student');
        $this->load->model('student_master');
    }

    public function index()
    {
        $data['sections'] = $this->db->select('section')->distinct()->get('class_schedule')->result_array();
        $this->load->view('groupings/list', $data);
    }

    public function create()
    {
        // show form
        $data['sections'] = $this->db->select('section')->distinct()->get('class_schedule')->result_array();
        $this->load->view('groupings/create', $data);
    }

    public function store()
    {
        $section = $this->input->post('section');
        $min_members = max(1, (int)$this->input->post('min_members'));
        $desired_groups = (int)$this->input->post('desired_groups'); // optional
        $group_name_prefix = $this->input->post('group_name') ?: 'Group';

        // get students in section
        $students = $this->db->select('sm.trans_no, sm.firstname, sm.lastname')
            ->from('class_student cs')
            ->join('student_master sm', 'cs.student_id = sm.trans_no', 'left')
            ->where('cs.section', $section)
            ->get()->result_array();

        $total = count($students);
        if ($total === 0) {
            $this->session->set_flashdata('error', 'No students found in selected section.');
            redirect('Groupings/create');
            return;
        }

        // determine number of groups
        if ($desired_groups > 0) {
            $groups = $desired_groups;
            // ensure min_members satisfied
            if (ceil($total / $groups) < $min_members) {
                // increase min_members or reduce groups: increase groups until ok
                $groups = max(1, floor($total / $min_members));
            }
        } else {
            // compute groups from min_members
            $groups = max(1, floor($total / $min_members));
            if ($groups === 0) $groups = 1;
        }

        // ensure groups at least 1
        $groups = max(1, $groups);

        // shuffle students for random grouping
        shuffle($students);

        // distribute students as evenly as possible
        $perGroupBase = intdiv($total, $groups);
        $remainder = $total % $groups;

        $groupRows = [];
        $memberRows = [];
        $idx = 0;
        for ($g = 1; $g <= $groups; $g++) {
            $size = $perGroupBase + ($remainder > 0 ? 1 : 0);
            if ($remainder > 0) $remainder--;
            $groupRows[] = [
                'group_name' => $group_name_prefix . ' ' . $g,
                'section_id' => $section,
                'min_members' => $min_members,
            ];
        }

        // insert groups and then members
        foreach ($groupRows as $gr) {
            $group_id = $this->Grouping_model->create_group($gr);
            $currentSize = ($perGroupBase + ($remainder > 0 ? 1 : 0)); // not used now; we'll assign sequentially
            // assign members one by one
        }

        // Alternate approach: create groups then assign sequentially
        // Recreate groups to get IDs
        // Delete last inserted groups for this run (simple approach: create, get ids from last N rows by created_at)
        // Simpler: create then fetch last inserted groups for this section ordered by group_id desc limit $groups
        // Instead, we will insert groups & members in one pass:
        // Reset and do single-pass insertion:

        // Clear any groups we just created for section (if you prefer atomic behavior you may wrap in transaction)
        $this->db->where('section_id', $section)->where('group_name LIKE', $group_name_prefix . '%')->delete('groupings');

        // create groups fresh and store ids
        $groupIds = [];
        for ($g = 1; $g <= $groups; $g++) {
            $gid = $this->Grouping_model->create_group([
                'group_name' => $group_name_prefix . ' ' . $g,
                'section_id' => $section,
                'min_members' => $min_members
            ]);
            $groupIds[] = $gid;
        }

        // distribute students round-robin to keep balanced
        $memberRows = [];
        $gIndex = 0;
        foreach ($students as $s) {
            $memberRows[] = [
                'group_id' => $groupIds[$gIndex],
                'student_id' => $s['trans_no']
            ];
            $gIndex++;
            if ($gIndex >= count($groupIds)) $gIndex = 0;
        }

        // insert members
        if (!empty($memberRows)) {
            $this->Group_member_model->add_members_batch($memberRows);
        }

        $this->session->set_flashdata('success', 'Groups created: ' . count($groupIds));
        redirect('Groupings/list/' . $section);
    }

    public function list($section = null)
    {
        $data['groups'] = $this->Grouping_model->get_groups_by_section($section);
        foreach ($data['groups'] as &$g) {
            $g['members'] = $this->Group_member_model->get_members_by_group($g['group_id']);
        }
        $data['section'] = $section;
        $this->load->view('groupings/list', $data);
    }
}
