<?php
defined('BASEPATH') or exit('No direct script access allowed');

class class_student extends MY_Model
{
    public $table = 'class_student';
    public $primary_key = 'id';
    public $protected = array('id');

    public function __construct()
    {
        $this->timestamps = TRUE;
        $this->has_one['student_master'] =  array(
            'foreign_model' => 'student_master',
            'foreign_table' => 'student_master',
            'foreign_key' => 'trans_no',
            'local_key' => 'trans_no'
        );
        parent::__construct();
    }

    public function get_uncleared_students_by_section($section)
    {
        return $this->db
            ->join('student_master', 'class_student.student_id = student_master.trans_no')
            ->where('section', $section)
            ->where('is_cleared IS NULL', null, false)
            ->order_by('student_master.lastname')
            ->get($this->table)
            ->result_array();
    }

    public function clear_student($id)
    {
        return $this->db
            ->where('id', $id)
            ->update($this->table, ['is_cleared' => 1]);
    }

    public function add_section($id, $section)
    {
        return $this->db
            ->where('student_id', $id)
            ->update($this->table, ['section' => $section]);
    }

    public function update_class($id, $class)
    {
        return $this->db
            ->where('student_id', $id)
            ->update($this->table, ['class_id' => $class]);
    }

    public function get_students_with_names_by_section($section)
    {
        $sql = "
                SELECT class_student.student_id, student_master.firstname, student_master.lastname
                FROM class_student
                LEFT JOIN student_master ON class_student.student_id = student_master.student_id
                WHERE class_student.section = ?
                ";

        $query = $this->db->query($sql, [$section]);
        if ($query && $query->num_rows() > 0) {
            return $query->result_array();
        } else {
            return [];
        }
    }
}
