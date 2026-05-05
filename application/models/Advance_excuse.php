<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Advance_excuse extends MY_Model
{
    public $table = 'advance_excuse_requests';
    public $primary_key = 'request_id';
    public $protected = ['request_id'];
    public $fillable = ['student_id', 'schedule_id', 'absence_date', 'reason', 'status', 'admin_notes'];

    public function __construct()
    {
        $this->timestamps = true;
        parent::__construct();
    }

    public function get_student_requests($student_id)
    {
        $sql = "
            SELECT r.*, c.class_code, c.class_name, cs.section, cs.day, cs.time_start
            FROM advance_excuse_requests r
            JOIN class_schedule cs ON r.schedule_id = cs.schedule_id
            JOIN classes c ON cs.class_id = c.class_id
            WHERE r.student_id = ?
            ORDER BY r.absence_date DESC, r.created_at DESC
        ";
        return $this->db->query($sql, [$student_id])->result_array();
    }

    public function count_requests($status = null)
    {
        $this->db->from('advance_excuse_requests r');
        if ($status) {
            $this->db->where('r.status', $status);
        }
        return $this->db->count_all_results();
    }

    public function get_all_requests($status = null, $limit = null, $offset = 0)
    {
        $sql = "
            SELECT r.*, sm.lastname, sm.firstname, sm.student_no,
                   c.class_code, c.class_name, cs.section, cs.day, cs.time_start
            FROM advance_excuse_requests r
            JOIN student_master sm ON r.student_id = sm.trans_no
            JOIN class_schedule cs ON r.schedule_id = cs.schedule_id
            JOIN classes c ON cs.class_id = c.class_id
        ";
        $params = [];
        if ($status) {
            $sql .= " WHERE r.status = ?";
            $params[] = $status;
        }
        $sql .= " ORDER BY r.absence_date ASC, r.created_at DESC";
        if ($limit !== null) {
            $sql .= " LIMIT ? OFFSET ?";
            $params[] = (int)$limit;
            $params[] = (int)$offset;
        }
        return $this->db->query($sql, $params)->result_array();
    }

    public function has_duplicate($student_id, $schedule_id, $absence_date)
    {
        return $this->db
            ->where(['student_id' => $student_id, 'schedule_id' => $schedule_id, 'absence_date' => $absence_date])
            ->where_in('status', ['pending', 'approved'])
            ->count_all_results('advance_excuse_requests') > 0;
    }
}
