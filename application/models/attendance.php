<?php
defined('BASEPATH') or exit('No direct script access allowed');

class attendance extends MY_Model
{
    public $table = 'attendance';
    public $primary_key = 'attendance_id';
    public $protected = ['attendance_id'];
    public $fillable = ['status'];

    public function __construct()
    {
        $this->timestamps = true;
        $this->has_many['student'] = [
            'foreign_model' => 'student_master',
            'foreign_table' => 'student_master',
            'foreign_key' => 'trans_no',
            'local_key' => 'student_id',
        ];
        $this->has_many['class_schedule'] = [
            'foreign_model' => 'class_schedule',
            'foreign_table' => 'class_schedule',
            'foreign_key' => 'schedule_id',
            'local_key' => 'schedule_id',
        ];
        parent::__construct();
    }

    public function insert_data($data)
    {
        return $this->db->insert('attendance', $data); // Insert data into the 'attendance' table
    }

    public function get_student_attendance($id)
    {
        $query = $this->db
            ->query("SELECT c.class_code, c.class_name, cs.type, sm.lastname, sm.firstname, a.date 
                FROM attendance a
                JOIN student_master sm ON a.student_id = sm.trans_no
                JOIN class_schedule cs ON a.schedule_id = cs.schedule_id
                JOIN classes c ON cs.class_id = c.class_id WHERE student_id = $id
                AND a.status = 'present'
                order by date desc");

        return $query->result_array();
    }

    public function start_class($schedule_id, $section, $date)
    {
        $check_duplicate_query = $this->db->query(
            " SELECT * FROM attendance WHERE schedule_id = $schedule_id AND date like '$date%' "
        );

        if ($check_duplicate_query->row() === null) {
            $sql = "
            INSERT INTO attendance (schedule_id, student_id, status)
                    SELECT 
                        ?, 
                        student_id, 
                        'absent'
                    FROM class_student
                    WHERE section = ?;
            ";

            $query = $this->db->query($sql, [$schedule_id, $section]);

            if ($query === false) {
                $error = $this->db->error();
            }

            return false;
        }

        return true;
    }

    public function update_status($status, $ip_address, $student_id, $date)
    {
        return $this->db
            ->set([
                'status' => $status,
                'ip_address' => $ip_address,
                'date' => $date . ' ' . date('H:i:s'),
            ])
            ->where([
                'student_id' => $student_id,
                'date(date)' => $date,
                'status' => 'absent',
            ])
            ->from('attendance')
            ->update();
    }

    public function getMaxAttendanceDays($section, $start_date, $end_date)
    {
        $query = $this->db->query(
            "
            SELECT student_id, COUNT(*) as present_days
            FROM attendance a
            JOIN class_schedule cs ON a.schedule_id = cs.schedule_id 
            WHERE cs.section = ? AND status = 'present' AND DATE(a.date) BETWEEN ? AND ?
            GROUP BY student_id
            ORDER BY present_days DESC
            LIMIT 1;
            ",
            [$section, $start_date, $end_date]
        );

        $result = $query->row();
        return $result ? $result->present_days : 0;
    }

    public function get_present_days($student_id, $section, $start_date, $end_date)
    {
        $query = $this->db->query(
            "
            SELECT COUNT(*) as present_days
            FROM attendance a
            JOIN class_schedule cs ON a.schedule_id = cs.schedule_id 
            WHERE cs.section = ? AND student_id = ? AND status = 'present' AND DATE(a.date) BETWEEN ? AND ?
        ",
            [$section, $student_id, $start_date, $end_date]
        );

        return $query->row()->present_days ?? 0;
    }

    public function checkStudentAbsences(
        $student_id,
        $section,
        $start_date,
        $end_date
    ) {
        // Count the student's present days
        $query = $this->db->query(
            "
            SELECT COUNT(*) as present_days
            FROM attendance a
            WHERE student_id = ? AND status = 'present' AND date(a.date) BETWEEN ? AND ?
        ",
            [$student_id, $start_date, $end_date]
        );

        $student_present_days = $query->row()->present_days;

        // Get the maximum attendance days for the section
        $max_present_days = $this->getMaxAttendanceDays(
            $section,
            $start_date,
            $end_date
        );

        // Calculate absences
        $absent_days = (int)$max_present_days - (int)$student_present_days;

        // Check if the student has been absent for 6 or more days
        if ($absent_days >= 6) {
            $this->session->set_flashdata(
                'error',
                'You have been absent for 6 or more days.'
            );
        }

        return $absent_days;
    }
}
