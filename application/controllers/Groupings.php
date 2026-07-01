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
        $data['sections'] = $this->db->select('section')->distinct()->get('class_schedule')->result_array();
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
        $data['sections'] = $this->db->select('section')->distinct()->get('class_schedule')->result_array();
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

        // get students in section
        $students = $this->db->select('sm.trans_no, sm.firstname, sm.lastname')
            ->from('class_student cs')
            ->join('student_master sm', 'cs.student_id = sm.trans_no', 'left')
            ->where('cs.section', $section)
            ->get()->result_array();

        $total = count($students);
        if ($total === 0) {
            $this->session->set_flashdata('error', 'No students found in selected section.');
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
