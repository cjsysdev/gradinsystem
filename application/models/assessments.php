<?php
defined('BASEPATH') or exit('No direct script access allowed');

class assessments extends MY_Model
{
    public $table = 'assessments';
    public $primary_key = 'assessment_id';
    public $protected = array('assessment_id');
    public $timestamps = TRUE;

    public function __construct()
    {
        $this->timestamps = TRUE;
        $this->has_one['type'] =  array(
            'foreign_model' => 'io_type',
            'foreign_table' => 'io_type',
            'foreign_key' => 'iotype_id',
            'local_key' => 'iotype_id'
        );
        $this->has_one['class_schedule'] =  array(
            'foreign_model' => 'class_schedule',
            'foreign_table' => 'class_schedule',
            'foreign_key' => 'schedule_id',
            'local_key' => 'schedule_id'
        );
        parent::__construct();
    }

    public function get_students_assessments()
    {
        $query = $this->db->query('SELECT * FROM gradingsystem.classworks c 
JOIN assessments a ON a.assessment_id = c.assessment_id WHERE ');
        return $query->result_array();
    }
}
