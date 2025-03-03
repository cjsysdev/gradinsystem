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

    public function get_students_assessments($student_id, $section)
    {
        $sql = "
            SELECT 
                a.assessment_id,
                a.title,
                a.description,
                a.max_score,
                a.created_at,
                a.due,
                cs.section
            FROM 
                assessments a
            LEFT JOIN 
                classworks c 
                ON a.assessment_id = c.assessment_id 
                AND c.student_id = ?
            JOIN 
                class_schedule cs
                ON a.schedule_id = cs.schedule_id
            WHERE 
                c.classwork_id IS NULL AND cs.section = ?
        ";

        $query = $this->db->query($sql, [$student_id, $section]);

        if ($query === false) {
            $error = $this->db->error();
            log_message('error', 'Database error: ' . $error['message']);
            return []; // Return an empty array or handle the error as needed
        }

        return $query->result_array();
    }

    public function get_submmited_assessments($student_id)
    {
        $sql = "
            SELECT 
                a.assessment_id,
                a.title,
                a.description,
                a.max_score,
                a.created_at,
                a.due
            FROM 
                assessments a
			JOIN 
                classworks c 
                ON a.assessment_id = c.assessment_id 
			WHERE 
				c.student_id = ?;";

        $query = $this->db->query($sql, [$student_id]);

        if ($query === false) {
            $error = $this->db->error();
            log_message('error', 'Database error: ' . $error['message']);
            return []; // Return an empty array or handle the error as needed
        }

        return $query->result_array();
    }
}
