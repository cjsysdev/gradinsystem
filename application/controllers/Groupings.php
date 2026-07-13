<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Groupings extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        if ($this->session->userdata('role') !== 'admin') {
            redirect('login');
        }
        $this->load->model('Grouping_model');
        $this->load->model('Group_member_model');
        $this->load->model('attendance');
    }

    // One-time (idempotent) schema setup/upgrade — run once as admin.
    public function install()
    {
        $this->Grouping_model->install();
        $this->session->set_flashdata('success', 'Grouping tables ready.');
        redirect('Groupings');
    }

    public function index()
    {
        $data['sections'] = $this->class_schedule->get_sections();
        $this->load->view('groupings/index', $data);
    }

    // List every grouping set (e.g. "Lab Groups", "Project Teams") for a section
    public function sets($section)
    {
        $data['section'] = $section;
        $data['sets'] = $this->Grouping_model->get_sets_by_section($section);
        $this->load->view('groupings/sets', $data);
    }

    public function create($section = null)
    {
        $data['sections'] = $this->class_schedule->get_sections();
        $data['preselected_section'] = $section;
        $this->load->view('groupings/create', $data);
    }

    public function store()
    {
        $section = $this->input->post('section');
        $set_name = trim($this->input->post('set_name'));
        $min_members = max(1, (int) $this->input->post('min_members'));
        $desired_groups = (int) $this->input->post('desired_groups'); // optional
        $group_name_prefix = $this->input->post('group_name') ?: 'Group';
        $self_select = (bool) $this->input->post('self_select');

        if (!$section) {
            $this->session->set_flashdata('error', 'Please select a section.');
            redirect('Groupings/create');
            return;
        }
        if ($set_name === '') {
            $this->session->set_flashdata('error', 'Please name this grouping set.');
            redirect('Groupings/create/' . $section);
            return;
        }

        // Self-select: students form/join their own groups (up to
        // min_members, the target group size) from GroupWorkController — no
        // pre-assignment here, just the set itself.
        if ($self_select) {
            $set_id = $this->Grouping_model->create_set($section, $set_name, $min_members, true);
            $this->session->set_flashdata('success', 'Grouping set "' . $set_name . '" created — students will form their own groups (target size: ' . $min_members . ').');
            redirect('Groupings/view_set/' . $set_id);
            return;
        }

        // get students in section, scoped to the active semester — section
        // codes (e.g. "3B") get reused every term, so without this a section
        // pulls in students enrolled under that label in past semesters too.
        $students = $this->db->select('sm.trans_no, sm.firstname, sm.lastname')
            ->from('class_student cs')
            ->join('student_master sm', 'cs.student_id = sm.trans_no', 'left')
            ->join('semester_master sem', 'cs.semester_id = sem.trans_no')
            ->where('cs.section', $section)
            ->where('sem.is_active', 1)
            ->get()->result_array();

        if (count($students) === 0) {
            $this->session->set_flashdata('error', 'No students found in selected section.');
            redirect('Groupings/create/' . $section);
            return;
        }

        // only group students marked present (or late) today — absent/excused
        // students aren't in class to take part in the grouped activity.
        date_default_timezone_set('Asia/Manila');
        $today = date('Y-m-d');
        $present_ids = $this->attendance->get_present_student_ids_by_section($section, $today);
        $students = array_values(array_filter($students, function ($s) use ($present_ids) {
            return in_array($s['trans_no'], $present_ids);
        }));

        $total = count($students);
        if ($total === 0) {
            $this->session->set_flashdata('error', 'No present students found in selected section for today.');
            redirect('Groupings/create/' . $section);
            return;
        }

        // determine number of groups
        if ($desired_groups > 0) {
            $groups = $desired_groups;
            if (ceil($total / $groups) < $min_members) {
                // desired group count is too high to satisfy min_members; shrink it
                $groups = max(1, (int) floor($total / $min_members));
            }
        } else {
            $groups = max(1, (int) floor($total / $min_members));
        }

        $set_id = $this->Grouping_model->create_set($section, $set_name, $min_members);

        $groupIds = [];
        for ($g = 1; $g <= $groups; $g++) {
            $groupIds[] = $this->Grouping_model->create_group([
                'set_id'     => $set_id,
                'group_name' => $group_name_prefix . ' ' . $g,
            ]);
        }

        // shuffle students, then distribute round-robin to keep group sizes balanced
        shuffle($students);
        $memberRows = [];
        $gIndex = 0;
        foreach ($students as $s) {
            $memberRows[] = [
                'group_id'   => $groupIds[$gIndex],
                'student_id' => $s['trans_no'],
            ];
            $gIndex = ($gIndex + 1) % count($groupIds);
        }

        if (!empty($memberRows)) {
            $this->Group_member_model->add_members_batch($memberRows);
        }

        $this->session->set_flashdata('success', 'Grouping set "' . $set_name . '" created with ' . count($groupIds) . ' groups.');
        redirect('Groupings/view_set/' . $set_id);
    }

    public function view_set($set_id)
    {
        $set = $this->Grouping_model->get_set($set_id);
        if (!$set) show_404();

        $data['set'] = $set;
        $data['groups'] = $this->Grouping_model->get_groups_by_set($set_id);
        foreach ($data['groups'] as &$g) {
            $g['members'] = $this->Group_member_model->get_members_by_group($g['group_id']);
        }
        unset($g);
        $this->load->view('groupings/view_set', $data);
    }

    public function delete_set($set_id)
    {
        $set = $this->Grouping_model->get_set($set_id);
        if (!$set) show_404();

        $this->Grouping_model->delete_set($set_id);
        $this->session->set_flashdata('success', 'Grouping set deleted.');
        redirect('Groupings/sets/' . $set['section_id']);
    }
}
