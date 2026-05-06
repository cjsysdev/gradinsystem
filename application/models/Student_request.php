<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Student_request extends MY_Model
{
    public $table = 'student_requests';
    public $primary_key = 'request_id';
    public $protected = ['request_id'];
    public $fillable = ['type', 'student_id', 'schedule_id', 'request_date', 'reason', 'status', 'admin_notes'];

    public function __construct()
    {
        $this->timestamps = true;
        parent::__construct();
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
                   c.class_code, c.class_name, cs.section, cs.day, cs.time_start
            FROM student_requests r
            JOIN student_master sm ON r.student_id = sm.trans_no
            JOIN class_schedule cs ON r.schedule_id = cs.schedule_id
            JOIN classes c ON cs.class_id = c.class_id
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
