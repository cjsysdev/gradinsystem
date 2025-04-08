<?php
defined('BASEPATH') or exit('No direct script access allowed');

class classworks extends MY_Model
{
    public $table = 'classworks';
    public $primary_key = 'classwork_id';
    public $protected = array('classwork_id');
    public $timestamps = FALSE;

    public function __construct()
    {
        $this->timestamps = FALSE;
        $this->has_many['student'] =  array(
            'foreign_model' => 'student_master',
            'foreign_table' => 'student_master',
            'foreign_key' => 'trans_no',
            'local_key' => 'student_id'
        );
        $this->has_many['assessments'] =  array(
            'foreign_model' => 'assessments',
            'foreign_table' => 'assessments',
            'foreign_key' => 'assessment_id',
            'local_key' => 'assessment_id'
        );
        parent::__construct();
    }

    public function get_all_submissions($assessment_id)
    {
        $sql = "SELECT c.classwork_id, s.trans_no, s.firstname, s.lastname, c.code, c.file_upload, c.created_at 
                FROM gradingsystem.classworks c 
                JOIN student_master s ON s.trans_no = c.student_id 
                WHERE assessment_id = ? AND c.score IS NULL ";

        $query = $this->db->query($sql, [$assessment_id]);

        if ($query === false) {
            $error = $this->db->error();
            log_message('error', 'Database error: ' . $error['message']);
            return []; // Return an empty array or handle the error as needed
        }

        return $query->result_array();
    }

    public function add_score($classwork_id, $score)
    {
        return $this->db->set(['score' => $score])
            ->where('classwork_id', $classwork_id)
            ->from('classworks')
            ->update();
    }

    public function update_score($classwork_id, $student_id, $score)
    {
        $this->db->where('classwork_id', $classwork_id);
        $this->db->where('student_id', $student_id);
        $this->db->update('classworks', ['score' => $score]);
    }

    public function getActivitiesGrade($term, $iotype, $student_id)
    {
        $query = $this->db->query("
                SELECT 
                    ROUND(SUM(c.score), 2) AS total_score,
                    ROUND(SUM(a.max_score), 2) AS total_max_score,
                    ROUND((SUM(c.score) / SUM(a.max_score)) * 100, 2) AS percentage,
                    ROUND(
                        CASE 
                            WHEN (SUM(c.score) / SUM(a.max_score)) * 100 <= 50 THEN 
                                5.0 - (2.0 / 50) * ((SUM(c.score) / SUM(a.max_score)) * 100)
                            WHEN (SUM(c.score) / SUM(a.max_score)) * 100 > 50 THEN 
                                3.0 - (2.0 / 50) * (((SUM(c.score) / SUM(a.max_score)) * 100) - 50)
                        END, 
                        2
                    ) AS grade_point
                FROM 
                    classworks c
                JOIN 
                    assessments a ON c.assessment_id = a.assessment_id
                WHERE 
                    a.term = '$term'
                    AND a.iotype_id = $iotype
                    AND c.student_id = $student_id
                GROUP BY 
                    c.student_id
            ");

        $result = $query->row_array();

        if ($result) {
            return $result;
        } else {
            return null; // or handle the case when no data is found
        }
    }

    public function getGradesByIotype($term, $student_id)
    {
        $query = $this->db->query("
            SELECT 
                a.iotype_id,
                i.type AS iotype_name,
                i.percentage AS iotype_percentage,
                ROUND(SUM(c.score), 2) AS total_score,
                ROUND(SUM(a.max_score), 2) AS total_max_score,
                ROUND((SUM(c.score) / SUM(a.max_score)) * 100, 2) AS percentage,
                ROUND(
                    CASE 
                        WHEN (SUM(c.score) / SUM(a.max_score)) * 100 <= 50 THEN 
                            5.0 - (2.0 / 50) * ((SUM(c.score) / SUM(a.max_score)) * 100)
                        WHEN (SUM(c.score) / SUM(a.max_score)) * 100 > 50 THEN 
                            3.0 - (2.0 / 50) * (((SUM(c.score) / SUM(a.max_score)) * 100) - 50)
                    END, 
                    2
                ) AS grade_point,
                ROUND((SUM(c.score) / SUM(a.max_score)) * i.percentage, 2) AS weighted_grade -- Weighted grade
            FROM 
                classworks c
            JOIN 
                assessments a ON c.assessment_id = a.assessment_id
            JOIN 
                io_type i ON a.iotype_id = i.iotype_id
            WHERE 
                a.term = '$term'
                AND c.student_id = $student_id
            GROUP BY 
                a.iotype_id
        ");

        $result = $query->result_array(); // Return all results grouped by iotype_id

        if ($result) {
            return $result;
        } else {
            return null; // or handle the case when no data is found
        }
    }

    public function getGradesBySection($term, $section)
    {
        $query = $this->db->query("
            SELECT 
                cs.section,
                class.class_code,
                class.class_name,
                c.student_id,
                sm.firstname,
                sm.lastname,
                a.iotype_id,
                i.type AS iotype_name,
                i.percentage AS iotype_percentage,
                ROUND(SUM(c.score), 2) AS total_score,
                ROUND(SUM(a.max_score), 2) AS total_max_score,
                ROUND((SUM(c.score) / SUM(a.max_score)) * 100, 2) AS percentage,
                ROUND(
                    CASE 
                        WHEN (SUM(c.score) / SUM(a.max_score)) * 100 <= 50 THEN 
                            5.0 - (2.0 / 50) * ((SUM(c.score) / SUM(a.max_score)) * 100)
                        WHEN (SUM(c.score) / SUM(a.max_score)) * 100 > 50 THEN 
                            3.0 - (2.0 / 50) * (((SUM(c.score) / SUM(a.max_score)) * 100) - 50)
                    END, 
                    2
                ) AS grade_point,
                ROUND((SUM(c.score) / SUM(a.max_score)) * i.percentage, 2) AS weighted_grade
            FROM 
                classworks c
            JOIN 
                assessments a ON c.assessment_id = a.assessment_id
            JOIN 
                io_type i ON a.iotype_id = i.iotype_id
            JOIN 
                class_student cs ON c.student_id = cs.student_id
            JOIN 
                student_master sm ON cs.student_id = sm.trans_no
            JOIN 
                class_schedule sched ON a.schedule_id = sched.schedule_id
            JOIN 
                classes class ON class.class_id = sched.class_id
            WHERE 
                a.term = ?
                AND cs.section = ?
            GROUP BY 
                cs.section, c.student_id, a.iotype_id
            ORDER BY 
                cs.section, sm.lastname, sm.firstname;
        ", [$term, $section]);

        return $query->result_array(); // Return the result as an array of rows
    }

    public function get_submissions_by_student($student_id)
    {
        $sql = "
            SELECT 
                c.*, 
                a.title, 
                s.firstname, 
                s.lastname 
            FROM 
                classworks c
            JOIN 
                assessments a 
            ON 
                c.assessment_id = a.assessment_id
            JOIN 
                student_master s 
            ON 
                c.student_id = s.trans_no
            WHERE 
                c.student_id = ?
            ORDER BY 
                c.created_at DESC
        ";

        $query = $this->db->query($sql, [$student_id]);

        return $query->result_array();
    }
}
