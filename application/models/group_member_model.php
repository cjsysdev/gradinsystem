<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Group_member_model extends CI_Model
{
    protected $table = 'group_members';

    public function add_members_batch($rows)
    {
        return $this->db->insert_batch($this->table, $rows);
    }

    public function get_members_by_group($group_id)
    {
        return $this->db->select('gm.*, sm.firstname, sm.lastname, sm.trans_no')
            ->from($this->table . ' gm')
            ->join('student_master sm', 'gm.student_id = sm.trans_no', 'left')
            ->where('gm.group_id', $group_id)
            ->order_by('sm.lastname, sm.firstname')
            ->get()->result_array();
    }

    // Every student_id already placed in some group within $set_id — used to
    // work out who's still ungrouped (e.g. a late arrival the auto-assign
    // skipped) so the admin can manually place them.
    public function get_grouped_student_ids($set_id)
    {
        $rows = $this->db->select('gm.student_id')
            ->from($this->table . ' gm')
            ->join('groupings g', 'gm.group_id = g.group_id')
            ->where('g.set_id', $set_id)
            ->get()->result_array();
        return array_column($rows, 'student_id');
    }

    public function add_member($group_id, $student_id)
    {
        $exists = $this->db->where(['group_id' => $group_id, 'student_id' => $student_id])
            ->count_all_results($this->table);
        if ($exists) return false;
        return $this->db->insert($this->table, ['group_id' => $group_id, 'student_id' => $student_id]);
    }

    public function remove_member($group_id, $student_id)
    {
        return $this->db->where(['group_id' => $group_id, 'student_id' => $student_id])->delete($this->table);
    }

    public function move_member($from_group_id, $to_group_id, $student_id)
    {
        if ((int) $from_group_id === (int) $to_group_id) return;
        $this->remove_member($from_group_id, $student_id);
        $this->add_member($to_group_id, $student_id);
    }
}
