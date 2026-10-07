<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Student_request extends MY_Model
{
    public $table = 'student_requests';
    public $primary_key = 'request_id';
    public $protected = ['request_id'];
    public $fillable = ['type', 'student_id', 'schedule_id', 'request_date', 'reason', 'status', 'admin_notes', 'assessment_id', 'granted_until'];

    public function __construct()
    {
        $this->timestamps = true;
        parent::__construct();
    }

    // Idempotent, ADD COLUMN only. Late-submission requests reuse this table
    // (type 'late_submission'); assessment_id is a SECTION id, like
    // classworks.assessment_id.
    public function install()
    {
        if (!$this->db->table_exists('student_requests')) {
            return;
        }
        if (!$this->db->field_exists('assessment_id', 'student_requests')) {
            $this->db->query("ALTER TABLE `student_requests` ADD `assessment_id` INT NULL");
        }
        if (!$this->db->field_exists('granted_until', 'student_requests')) {
            $this->db->query("ALTER TABLE `student_requests` ADD `granted_until` DATETIME NULL");
        }
        $idx = $this->db->query("SHOW INDEX FROM `student_requests` WHERE Key_name = 'idx_assessment'")->num_rows();
        if (!$idx) {
            $this->db->query("ALTER TABLE `student_requests` ADD KEY `idx_assessment` (`assessment_id`, `student_id`)");
        }
    }

    private function ready()
    {
        return $this->db->table_exists('student_requests')
            && $this->db->field_exists('assessment_id', 'student_requests');
    }

    // Latest late_submission request per assessment for one student, keyed by assessment_id.
    public function late_requests_for_student($student_id)
    {
        if (!$this->ready()) return [];
        $rows = $this->db
            ->where(['student_id' => $student_id, 'type' => 'late_submission'])
            ->order_by('request_id', 'ASC')
            ->get('student_requests')->result_array();
        $map = [];
        foreach ($rows as $r) {
            $map[$r['assessment_id']] = $r; // later rows overwrite earlier
        }
        return $map;
    }

    // Approved request whose reopen window is still running, or null.
    public function active_late_grant($student_id, $assessment_id)
    {
        if (!$this->ready()) return null;
        $row = $this->db
            ->where(['student_id' => $student_id, 'assessment_id' => (int) $assessment_id,
                     'type' => 'late_submission', 'status' => 'approved'])
            ->where('granted_until >', date('Y-m-d H:i:s'))
            ->order_by('granted_until', 'DESC')->limit(1)
            ->get('student_requests')->row_array();
        return $row ?: null;
    }

    public function has_open_late_request($student_id, $assessment_id)
    {
        if (!$this->ready()) return false;
        return $this->db
            ->where(['student_id' => $student_id, 'assessment_id' => (int) $assessment_id,
                     'type' => 'late_submission', 'status' => 'pending'])
            ->count_all_results('student_requests') > 0;
    }

    public function had_approved_late($student_id, $assessment_id)
    {
        if (!$this->ready()) return false;
        return $this->db
            ->where(['student_id' => $student_id, 'assessment_id' => (int) $assessment_id,
                     'type' => 'late_submission', 'status' => 'approved'])
            ->count_all_results('student_requests') > 0;
    }

    // student_id => true for every student with an approved late request on this assessment.
    public function approved_late_students($assessment_id)
    {
        if (!$this->ready()) return [];
        $rows = $this->db->select('student_id')
            ->where(['assessment_id' => (int) $assessment_id, 'type' => 'late_submission', 'status' => 'approved'])
            ->get('student_requests')->result_array();
        return array_fill_keys(array_column($rows, 'student_id'), true);
    }

    public function get_student_requests($student_id, $type = null)
    {
        $sql = "
            SELECT r.*, c.class_code, c.class_name, cs.section, cs.day, cs.time_start
            FROM student_requests r
            JOIN class_schedule cs ON r.schedule_id = cs.schedule_id
            JOIN classes c ON cs.class_id = c.class_id
            WHERE r.student_id = ?
        ";
        $params = [$student_id];
        if ($type) {
            $sql .= " AND r.type = ?";
            $params[] = $type;
        }
        $sql .= " ORDER BY r.request_date DESC, r.created_at DESC";
        return $this->db->query($sql, $params)->result_array();
    }

    // Pending requests of every type (absence, pass, late_submission) —
    // drives the admin nav badge.
    public function count_pending()
    {
        if (!$this->db->table_exists('student_requests')) return 0;
        return $this->db->where('status', 'pending')->count_all_results('student_requests');
    }

    public function count_requests($status = null, $type = null)
    {
        $this->db->from('student_requests r');
        if ($status) $this->db->where('r.status', $status);
        if ($type)   $this->db->where('r.type', $type);
        return $this->db->count_all_results();
    }

    public function get_all_requests($status = null, $type = null, $limit = null, $offset = 0)
    {
        $sql = "
            SELECT r.*, sm.lastname, sm.firstname, sm.student_no,
                   c.class_code, c.class_name, cs.section, cs.day, cs.time_start,
                   af.title AS assessment_title, af.due AS assessment_due
            FROM student_requests r
            JOIN student_master sm ON r.student_id = sm.trans_no
            JOIN class_schedule cs ON r.schedule_id = cs.schedule_id
            JOIN classes c ON cs.class_id = c.class_id
            LEFT JOIN assessment_full af ON af.assessment_id = r.assessment_id
        ";
        $params  = [];
        $wheres  = [];
        if ($status) { $wheres[] = "r.status = ?"; $params[] = $status; }
        if ($type)   { $wheres[] = "r.type = ?";   $params[] = $type;   }
        if ($wheres) $sql .= " WHERE " . implode(' AND ', $wheres);
        $sql .= " ORDER BY r.request_date ASC, r.created_at DESC";
        if ($limit !== null) {
            $sql .= " LIMIT ? OFFSET ?";
            $params[] = (int)$limit;
            $params[] = (int)$offset;
        }
        return $this->db->query($sql, $params)->result_array();
    }

    public function has_duplicate($student_id, $schedule_id, $request_date, $type)
    {
        return $this->db
            ->where(['student_id' => $student_id, 'schedule_id' => $schedule_id,
                     'request_date' => $request_date, 'type' => $type])
            ->where_in('status', ['pending', 'approved'])
            ->count_all_results('student_requests') > 0;
    }
}
