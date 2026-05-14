<?php
defined('BASEPATH') or exit('No direct script access allowed');

class student_master extends MY_Model
{

    public function __construct()
    {
        $this->timestamps = FALSE;
        $this->has_one['accounts'] = array(
            'foreign_model' => 'accounts',
            'foreign_table' => 'accounts',
            'foreign_key' => 'student_id',
            'local_key' => 'trans_no'
        );
        parent::__construct();
        $this->load->driver('cache', ['adapter' => 'memcached', 'backup' => 'file']);
    }

    public function get_student_classworks($student_id)
    {
        $sql = "SELECT student_id, asm.assessment_id, score, cw.created_at, iotype_id,
                title, max_score, section, is_active
                FROM classworks cw
                JOIN assessments asm ON asm.assessment_id = cw.assessment_id
                JOIN class_schedule cs ON cs.schedule_id = asm.schedule_id
                JOIN semester_master sem ON sem.trans_no = cs.semester_id
                WHERE student_id = $student_id AND sem.is_active = 1";

        return $this->db->query($sql)->result_array();
    }

    // Fetch absences with dates and reasons
    public function get_absences($student_id)
    {
        $sql = "SELECT c.class_name AS course, a.date, a.reason, a.status
                FROM attendance a
                JOIN class_schedule cs ON a.schedule_id = cs.schedule_id
                JOIN semester_master sem ON cs.semester_id = sem.trans_no
                JOIN classes c ON cs.class_id = c.class_id
                WHERE a.student_id = $student_id
                AND sem.is_active = 1
                AND (a.status = 'absent' OR a.reason IS NOT NULL)
                ORDER BY DATE(a.date) DESC";

        return $this->db->query($sql)->result_array();
    }

    public $table = 'student_master';
    public $primary_key = 'trans_no';
    public $protected = array('trans_no');


    public function search_by_name($search)
    {
        $this->db->like('firstname', $search);
        $this->db->or_like('lastname', $search);
        return $this->db->get('student_master')->result_array();
    }

    // Fetch student info for performance sheet
    public function get_student_info($student_id)
    {
        $this->db->select('*');
        $this->db->where('trans_no', $student_id);

        $student = $this->db->get('student_master')->row_array();

        if ($student) {
            $student['name'] = $student['firstname'] . ' ' . $student['lastname'];
        }

        return $student;
    }

    public function get_attendance_summary($student_id)
    {
        $cache_key = 'att_summary_' . $student_id;
        $cached = $this->cache->get($cache_key);
        if ($cached !== FALSE) {
            return $cached;
        }

        $sql = "
            SELECT
                SUM(a.status = 'present') AS present_count,
                SUM(a.status = 'absent')  AS absent_count,
                SUM(a.status = 'late')    AS late_count,
                SUM(a.status = 'excuse')  AS excuse_count
            FROM attendance a
            JOIN class_schedule cs  ON a.schedule_id = cs.schedule_id
            JOIN semester_master sem ON cs.semester_id = sem.trans_no AND sem.is_active = 1
            WHERE a.student_id = ?
        ";
        $row = $this->db->query($sql, [$student_id])->row_array();
        $result = $row ?: ['present_count' => 0, 'absent_count' => 0, 'late_count' => 0, 'excuse_count' => 0];
        $this->cache->save($cache_key, $result, 300);
        return $result;
    }

    // Fetch current course for student
    public function get_current_course($student_id)
    {
        $sql = "SELECT c.class_name AS course
                FROM class_student cs
                JOIN classes c ON cs.class_id = c.class_id
                WHERE cs.student_id = 'student_id'
                ORDER BY cs.student_id DESC
                LIMIT 1";
        $result = $this->db->query($sql)->row_array();
        return $result ? $result['course'] : '';
    }
}
