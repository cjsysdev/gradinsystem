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
        $this->has_many['classworks'] =  array(
            'foreign_model' => 'classworks',
            'foreign_table' => 'classworks',
            'foreign_key' => 'assessment_id',
            'local_key' => 'assessment_id'
        );
        parent::__construct();
    }

    public function get_students_assessments($student_id, $section)
    {
        $sql = "
            SELECT 
                a.assessment_id,
                a.iotype_id,
                a.title,
                a.description,
                a.max_score,
                a.created_at,
                a.due,
                iot.type,
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
            JOIN
                io_type iot
                ON iot.iotype_id = a.iotype_id
            WHERE 
                c.classwork_id IS NULL AND cs.section = ?
            ORDER BY 
                a.created_at DESC
        ";

        $query = $this->db->query($sql, [$student_id, $section]);

        if ($query === false) {
            $error = $this->db->error();
            log_message('error', 'Database error: ' . $error['message']);
            return []; // Return an empty array or handle the error as needed
        }

        return $query->result_array();
    }

    public function get_submitted_assessments($student_id)
    {
        $sql = "
            SELECT * FROM classworks c 
            JOIN assessments a ON c.assessment_id = a.assessment_id 
            JOIN io_type iot ON a.iotype_id = iot.iotype_id
            WHERE student_id = ? ORDER BY c.created_at DESC";

        $query = $this->db->query($sql, [$student_id]);

        if ($query === false) {
            $error = $this->db->error();
            log_message('error', 'Database error: ' . $error['message']);
            return []; // Return an empty array or handle the error as needed
        }

        return $query->result_array();
    }
}
