<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Violation extends MY_Model
{
    public $table = 'student_violations';
    public $primary_key = 'violation_id';
    public $protected = ['violation_id'];

    public function get_by_student($student_id)
    {
        return $this->db
            ->order_by('date_of_violation', 'DESC')
            ->order_by('created_at', 'DESC')
            ->get_where('student_violations', ['student_id' => $student_id])
            ->result_array();
    }

    public function get_by_date_range($student_id, $start_date, $end_date)
    {
        return $this->db
            ->where('student_id', $student_id)
            ->where('date_of_violation >=', $start_date)
            ->where('date_of_violation <=', $end_date)
            ->order_by('date_of_violation', 'DESC')
            ->get('student_violations')
            ->result_array();
    }

    public function get_all_violations($filter = [])
    {
        $query = $this->db;

        if (!empty($filter['student_id'])) {
            $query->where('student_id', $filter['student_id']);
        }

        if (!empty($filter['status'])) {
            $query->where('status', $filter['status']);
        }

        if (!empty($filter['severity'])) {
            $query->where('severity', $filter['severity']);
        }

        if (!empty($filter['start_date'])) {
            $query->where('date_of_violation >=', $filter['start_date']);
        }

        if (!empty($filter['end_date'])) {
            $query->where('date_of_violation <=', $filter['end_date']);
        }

        return $query
            ->order_by('date_of_violation', 'DESC')
            ->order_by('created_at', 'DESC')
            ->get('student_violations')
            ->result_array();
    }

    public function get_student_violation_count($student_id, $severity = null)
    {
        $query = $this->db->where('student_id', $student_id);

        if ($severity) {
            $query->where('severity', $severity);
        }

        return $query->count_all_results('student_violations');
    }

    public function get_violation_types()
    {
        return $this->db
            ->where('active', 1)
            ->order_by('type_name', 'ASC')
            ->get('violation_types')
            ->result_array();
    }

    public function add_violation($student_id, $violation_type, $description, $severity, $date_of_violation, $reported_by, $notes = null)
    {
        $data = [
            'student_id' => $student_id,
            'violation_type' => $violation_type,
            'description' => $description,
            'severity' => $severity,
            'date_of_violation' => $date_of_violation,
            'reported_by' => $reported_by,
            'notes' => $notes,
            'status' => 'pending',
            'created_at' => date('Y-m-d H:i:s')
        ];

        return $this->db->insert('student_violations', $data);
    }

    public function update_violation_status($violation_id, $status, $notes = null)
    {
        $data = ['status' => $status];
        if ($notes) {
            $data['notes'] = $notes;
        }
        return $this->db->where('violation_id', $violation_id)->update('student_violations', $data);
    }

    public function get_violation_summary_by_student($student_id)
    {
        return $this->db
            ->select('severity, COUNT(*) as count')
            ->where('student_id', $student_id)
            ->group_by('severity')
            ->get('student_violations')
            ->result_array();
    }
}
