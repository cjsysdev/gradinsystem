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
}
