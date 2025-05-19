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
}
