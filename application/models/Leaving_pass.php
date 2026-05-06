<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Leaving_pass extends MY_Model
{
    public $table = 'leaving_pass_requests';
    public $primary_key = 'pass_id';
    public $protected = ['pass_id'];
    public $fillable = ['student_id', 'schedule_id', 'pass_date', 'reason', 'status', 'admin_notes'];

    public function __construct()
    {
        $this->timestamps = true;
        parent::__construct();
    }

    public function get_student_passes($student_id)
    {
        $sql = "
            SELECT r.*, c.class_code, c.class_name, cs.section, cs.day, cs.time_start
            FROM leaving_pass_requests r
            JOIN class_schedule cs ON r.schedule_id = cs.schedule_id
            JOIN classes c ON cs.class_id = c.class_id
            WHERE r.student_id = ?
            ORDER BY r.pass_date DESC, r.created_at DESC
        ";
        return $this->db->query($sql, [$student_id])->result_array();
    }

    public function count_passes($status = null)
    {
        $this->db->from('leaving_pass_requests r');
        if ($status) {
            $this->db->where('r.status', $status);
        }
        return $this->db->count_all_results();
    }

    public function get_all_passes($status = null, $limit = null, $offset = 0)
    {
        $sql = "
            SELECT r.*, sm.lastname, sm.firstname, sm.student_no,
                   c.class_code, c.class_name, cs.section, cs.day, cs.time_start
            FROM leaving_pass_requests r
            JOIN student_master sm ON r.student_id = sm.trans_no
            JOIN class_schedule cs ON r.schedule_id = cs.schedule_id
            JOIN classes c ON cs.class_id = c.class_id
        ";
        $params = [];
        if ($status) {
            $sql .= " WHERE r.status = ?";
            $params[] = $status;
        }
        $sql .= " ORDER BY r.pass_date ASC, r.created_at DESC";
        if ($limit !== null) {
            $sql .= " LIMIT ? OFFSET ?";
            $params[] = (int)$limit;
            $params[] = (int)$offset;
        }
        return $this->db->query($sql, $params)->result_array();
    }

    public function has_duplicate($student_id, $schedule_id, $pass_date)
    {
        return $this->db
            ->where(['student_id' => $student_id, 'schedule_id' => $schedule_id, 'pass_date' => $pass_date])
            ->where_in('status', ['pending', 'approved'])
            ->count_all_results('leaving_pass_requests') > 0;
    }
}
