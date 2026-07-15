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
            JOIN
                semester_master sem
                ON cs.semester_id = sem.trans_no
                AND sem.is_active = 1
            WHERE
                c.classwork_id IS NULL AND cs.section = ? AND a.status = 1
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
            SELECT *, a.description
            FROM classworks c 
            JOIN assessments a ON c.assessment_id = a.assessment_id 
            JOIN io_type iot ON a.iotype_id = iot.iotype_id
            JOIN class_schedule cs ON a.schedule_id = cs.schedule_id
            JOIN semester_master sem ON cs.semester_id = sem.trans_no 
            AND sem.is_active = 1
            WHERE student_id = ? ORDER BY c.created_at DESC";

        $query = $this->db->query($sql, [$student_id]);

        if ($query === false) {
            $error = $this->db->error();
            log_message('error', 'Database error: ' . $error['message']);
            return []; // Return an empty array or handle the error as needed
        }

        return $query->result_array();
    }

    public function get_all_for_admin($schedule_id = null, $limit = null, $offset = 0)
    {
        $base = "
            SELECT
                a.*,
                cs.section,
                cs.type AS schedule_type,
                iot.type AS iotype,
                iot.percentage,
                cl.class_name,
                cl.class_code,
                ag.set_id AS grouping_set_id,
                COUNT(cw.classwork_id) AS submission_count,
                SUM(CASE WHEN cw.classwork_id IS NOT NULL AND cw.score IS NULL THEN 1 ELSE 0 END) AS unscored_count,
                (SELECT COUNT(*) FROM class_student cst WHERE cst.schedule_id = a.schedule_id AND cst.status = 'enrolled') AS enrolled_count,
                COUNT(DISTINCT cw.student_id) AS submitted_student_count
            FROM assessments a
            JOIN class_schedule cs ON a.schedule_id = cs.schedule_id
            JOIN io_type iot ON a.iotype_id = iot.iotype_id
            JOIN classes cl ON cs.class_id = cl.class_id
            JOIN semester_master sem ON cs.semester_id = sem.trans_no AND sem.is_active = 1
            LEFT JOIN classworks cw ON cw.assessment_id = a.assessment_id
            LEFT JOIN assessment_groupings ag ON ag.assessment_id = a.assessment_id
        ";

        $params = [];
        if ($schedule_id) {
            $sql = $base . " WHERE a.schedule_id = ? GROUP BY a.assessment_id ORDER BY a.assessment_id DESC, cs.section, a.term, a.created_at DESC";
            $params[] = (int) $schedule_id;
        } else {
            $sql = $base . " GROUP BY a.assessment_id ORDER BY a.assessment_id DESC, cs.section, a.term, a.created_at DESC";
        }

        if ($limit !== null) {
            $sql .= " LIMIT ? OFFSET ?";
            $params[] = (int) $limit;
            $params[] = (int) $offset;
        }

        $query = $this->db->query($sql, $params);

        return $query ? $query->result_array() : [];
    }

    // Total assessments matching the same filter/joins as get_all_for_admin(),
    // for the manage_assessments pager — no aggregate/LEFT JOINs needed since
    // we're only counting assessment rows, not classwork submissions.
    public function count_all_for_admin($schedule_id = null)
    {
        $sql = "
            SELECT COUNT(*) AS c
            FROM assessments a
            JOIN class_schedule cs ON a.schedule_id = cs.schedule_id
            JOIN semester_master sem ON cs.semester_id = sem.trans_no AND sem.is_active = 1
        ";

        $params = [];
        if ($schedule_id) {
            $sql .= " WHERE a.schedule_id = ?";
            $params[] = (int) $schedule_id;
        }

        $query = $this->db->query($sql, $params);
        return $query ? (int) $query->row_array()['c'] : 0;
    }

    // Every assessment_id matching the same filter as get_all_for_admin(), for
    // the manage_assessments "Open All"/"Close All" buttons to act on every
    // filtered assessment, not just the ones on the current page.
    public function get_all_ids_for_admin($schedule_id = null)
    {
        $sql = "
            SELECT a.assessment_id
            FROM assessments a
            JOIN class_schedule cs ON a.schedule_id = cs.schedule_id
            JOIN semester_master sem ON cs.semester_id = sem.trans_no AND sem.is_active = 1
        ";

        $params = [];
        if ($schedule_id) {
            $sql .= " WHERE a.schedule_id = ?";
            $params[] = (int) $schedule_id;
        }

        $query = $this->db->query($sql, $params);
        return $query ? array_column($query->result_array(), 'assessment_id') : [];
    }

    public function get_for_schedule($schedule_id = null)
    {
        if ($schedule_id) {
            $sql = "
                SELECT 
                    a.*,
                    cs.*,
                    iot.type AS iotype
                FROM 
                    assessments a
                JOIN 
                    class_schedule cs ON a.schedule_id = cs.schedule_id
                JOIN
                    io_type iot ON a.iotype_id = iot.iotype_id
                JOIN
                    semester_master sem ON cs.semester_id = sem.trans_no AND sem.is_active = 1
                WHERE 
                    a.schedule_id = ?
                ORDER BY 
                    a.created_at DESC
            ";
            $query = $this->db->query($sql, [$schedule_id]);
        } else {
            $sql = "
                SELECT 
                    a.*,
                    cs.*,
                    iot.type AS iotype
                FROM 
                    assessments a
                JOIN 
                    class_schedule cs ON a.schedule_id = cs.schedule_id
                JOIN
                    io_type iot ON a.iotype_id = iot.iotype_id
                JOIN
                    semester_master sem ON cs.semester_id = sem.trans_no AND sem.is_active = 1
                WHERE
                    sem.is_active = 1
                ORDER BY 
                    cs.section, a.created_at DESC
            ";
            $query = $this->db->query($sql);
        }

        if ($query === false) {
            $error = $this->db->error();
            log_message('error', 'Database error: ' . $error['message']);
            return [];
        }

        return $query->result_array();
    }
}
