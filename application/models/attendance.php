<?php
defined('BASEPATH') or exit('No direct script access allowed');

class attendance extends MY_Model
{
    public $table = 'attendance';
    public $primary_key = 'attendance';
    public $protected = array('attendance_id');

    public function __construct()
    {
        $this->timestamps = TRUE;
        $this->has_many['student'] =  array(
            'foreign_model' => 'student_master',
            'foreign_table' => 'student_master',
            'foreign_key' => 'trans_no',
            'local_key' => 'student_id'
        );
        $this->has_many['class_schedule'] =  array(
            'foreign_model' => 'class_schedule',
            'foreign_table' => 'class_schedule',
            'foreign_key' => 'schedule_id',
            'local_key' => 'schedule_id'
        );
        parent::__construct();
    }

    public function insert_data($data)
    {
        return $this->db->insert('attendance', $data); // Insert data into the 'attendance' table
    }

    public function get_student_attendance($id)
    {
        $query = $this->db->query("SELECT c.class_code, c.class_name, cs.type, sm.lastname, sm.firstname, a.date 
                FROM attendance a
                JOIN student_master sm ON a.student_id = sm.trans_no
                JOIN class_schedule cs ON a.schedule_id = cs.schedule_id
                JOIN classes c ON cs.class_id = c.class_id WHERE student_id = $id");

        return $query->result_array();
    }
}
