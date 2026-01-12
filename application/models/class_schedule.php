<?php
defined('BASEPATH') or exit('No direct script access allowed');

class class_schedule extends MY_Model
{
    public $table = 'class_schedule';
    public $primary_key = 'schedule_id';
    public $protected = array('schedule_id');

    public function __construct()
    {
        $this->timestamps = TRUE;
        $this->has_one['classes'] =  array(
            'foreign_model' => 'classes',
            'foreign_table' => 'classes',
            'foreign_key' => 'class_id',
            'local_key' => 'class_id'
        );
        parent::__construct();
    }

    public function class_today($day)
    {
        $query = $this->db->query("SELECT * FROM class_schedule 
        JOIN classes ON class_schedule.class_id = classes.class_id 
        JOIN semester_master sm ON class_schedule.semester_id = sm.trans_no
        WHERE day LIKE '%$day%' 
        AND CURTIME() BETWEEN time_start 
        AND time_end
        AND sm.is_active = 1");

        return $query->row_array() ?? [];
    }

    public function get_sections()
    {
        $sql = "SELECT distinct(section) FROM class_schedule
        JOIN semester_master sm ON class_schedule.semester_id = sm.trans_no
        WHERE sm.is_active = 1";
        $query = $this->db->query($sql);

        return $query->result_array();
    }
}
