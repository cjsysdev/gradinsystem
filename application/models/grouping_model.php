<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Grouping_model extends CI_Model
{
    protected $table = 'groupings';

    public function create_group($data)
    {
        $this->db->insert($this->table, $data);
        return $this->db->insert_id();
    }

    public function get_groups_by_section($section_id)
    {
        return $this->db->where('section_id', $section_id)->order_by('group_id')->get($this->table)->result_array();
    }

    public function get($group_id)
    {
        return $this->db->where('group_id', $group_id)->get($this->table)->row_array();
    }
}
