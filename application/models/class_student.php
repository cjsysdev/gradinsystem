<?php
defined('BASEPATH') or exit('No direct script access allowed');

class class_student extends MY_Model
{
    public $table = 'class_student';
    public $primary_key = 'id';
    public $protected = array('id');

    public function __construct()
    {
        $this->timestamps = TRUE;
        $this->has_one['student_master'] =  array(
            'foreign_model' => 'student_master',
            'foreign_table' => 'student_master',
            'foreign_key' => 'trans_no',
            'local_key' => 'trans_no'
        );
        parent::__construct();
    }

    public function get_uncleared_students_by_section($section)
    {
        return $this->db
            ->join('student_master', 'class_student.student_id = student_master.trans_no')
            ->join('semester_master', 'class_student.semester_id = semester_master.trans_no')
            ->where('class_student.section', $section)
            ->where('semester_master.is_active', 1)
            ->where('is_cleared IS NULL', null, false)
            ->order_by('student_master.lastname')
            ->get($this->table)
            ->result_array();
    }

    public function clear_student($id)
    {
        return $this->db
            ->where('id', $id)
            ->update($this->table, ['is_cleared' => 1]);
    }

    public function get_sections_with_uncleared_counts()
    {
        $sql = "
            SELECT cs.section, COUNT(*) AS uncleared_count
            FROM class_student cs
            JOIN semester_master sm ON cs.semester_id = sm.trans_no
            WHERE sm.is_active = 1 AND cs.is_cleared IS NULL
            GROUP BY cs.section
            ORDER BY cs.section
        ";
        $query = $this->db->query($sql);
        return $query ? $query->result_array() : [];
    }

    public function add_section($id, $section, $semester_id = null)
    {
        $semester_id = $semester_id ?: $this->_active_semester_id();
        return $this->db
            ->where('student_id', $id)
            ->where('semester_id', $semester_id)
            ->update($this->table, ['section' => $section]);
    }

    public function update_class($id, $class, $semester_id = null)
    {
        $semester_id = $semester_id ?: $this->_active_semester_id();
        return $this->db
            ->where('student_id', $id)
            ->where('semester_id', $semester_id)
            ->update($this->table, ['class_id' => $class]);
    }

    public function re_enroll($student_id, $class_id, $section, $semester_id, $schedule_id = null)
    {
        $exists = $this->db
            ->where('student_id', $student_id)
            ->where('semester_id', $semester_id)
            ->count_all_results($this->table);

        if ($exists) {
            return $this->db
                ->where('student_id', $student_id)
                ->where('semester_id', $semester_id)
                ->update($this->table, [
                    'class_id'    => $class_id,
                    'schedule_id' => $schedule_id,
                    'section'     => $section,
                    'status'      => 'enrolled',
                ]);
        }

        return $this->db->insert($this->table, [
            'student_id'  => $student_id,
            'class_id'    => $class_id,
            'schedule_id' => $schedule_id,
            'section'     => $section,
            'semester_id' => $semester_id,
            'status'      => 'enrolled',
            'is_cleared'  => 0,
        ]);
    }

    public function is_enrolled_in_schedule($student_id, $schedule_id)
    {
        return $this->db
            ->where('student_id', $student_id)
            ->where('schedule_id', $schedule_id)
            ->where('status', 'enrolled')
            ->count_all_results($this->table) > 0;
    }

    private function _active_semester_id()
    {
        $row = $this->db->select('trans_no')->where('is_active', 1)->get('semester_master')->row();
        return $row ? $row->trans_no : null;
    }

    public function get_students_with_names_by_section($section)
    {
        $sql = "
                SELECT class_student.student_id, student_master.firstname, student_master.lastname
                FROM class_student
                LEFT JOIN student_master ON class_student.student_id = student_master.student_id
                JOIN semester_master ON class_student.semester_id = semester_master.trans_no
                WHERE class_student.section = ? AND semester_master.is_active = 1
                ";

        $query = $this->db->query($sql, [$section]);
        if ($query && $query->num_rows() > 0) {
            return $query->result_array();
        } else {
            return [];
        }
    }

    public function get_class_student_info($student_id)
    {
        $sql = "
            SELECT
                cs.id,
                cs.student_id,
                cs.class_id,
                cs.section,
                cs.is_cleared,
                cs.status,
                sm.semcode,
                sm.description AS semester_description,
                sm.semyear
            FROM class_student cs
            INNER JOIN semester_master sm
                ON cs.semester_id = sm.trans_no
            WHERE sm.is_active = 1 AND cs.student_id = ?
        ";

        $query = $this->db->query($sql, [$student_id]);
        return $query ? $query->row_array() : [];
    }

    public function get_students_with_profile_by_section($section)
    {
        $sql = "
            SELECT
                cs.student_id,
                sm.firstname,
                sm.lastname,
                a.profile_pic
            FROM class_student cs
            JOIN student_master sm ON cs.student_id = sm.trans_no
            LEFT JOIN accounts a ON a.student_id = cs.student_id
            JOIN semester_master sem ON cs.semester_id = sem.trans_no
            WHERE cs.section = ? AND sem.is_active = 1
            ORDER BY sm.lastname, sm.firstname
        ";

        $query = $this->db->query($sql, [$section]);
        return $query ? $query->result_array() : [];
    }

}
