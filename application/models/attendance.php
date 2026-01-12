<?php
defined('BASEPATH') or exit('No direct script access allowed');

class attendance extends MY_Model
{
    public $table = 'attendance';
    public $primary_key = 'attendance_id';
    public $protected = ['attendance_id'];
    public $fillable = ['status', 'schedule_id', 'student_id', 'ip_address', 'date', 'reason'];

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
                JOIN classes c ON cs.class_id = c.class_id 
                JOIN semester_master sem ON cs.semester_id = sem.trans_no
                WHERE student_id = $id
                AND a.status = 'present'
                AND sem.is_active = 1
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
            JOIN semester_master sem ON cs.semester_id = sem.trans_no
            WHERE cs.section = ? AND status = 'present' AND DATE(a.date) BETWEEN ? AND ?
            AND sem.is_active = 1
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
            JOIN semester_master sem ON cs.semester_id = sem.trans_no
            WHERE cs.section = ? AND student_id = ? AND (status = 'present' OR status = 'excuse')
            AND DATE(a.date) BETWEEN ? AND ?
            AND sem.is_active = 1
        ",
            [$section, $student_id, $start_date, $end_date]
        );

        return (int)$query->row()->present_days ?? 0;
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
            JOIN class_schedule cs ON a.schedule_id = cs.schedule_id
            JOIN semester_master sem ON cs.semester_id = sem.trans_no
            WHERE student_id = ? AND status = 'present' 
            AND date(a.date) BETWEEN ? AND ?
            AND sem.is_active = 1
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
                'warning',
                'You have been absent for 6 or more sessions. Please process your re-admission accourdingly'
            );
        }

        return $absent_days;
    }

    public function get_student_absences($student_id, $section, $start_date, $end_date)
    {
        $query = $this->db->query(
            "
            SELECT *
            FROM attendance a
            JOIN class_schedule cs ON a.schedule_id = cs.schedule_id
            JOIN semester_master sem ON cs.semester_id = sem.trans_no
            WHERE cs.section = ? AND a.student_id = ? AND a.status = 'absent' AND a.reason IS NULL
            AND DATE(a.date) BETWEEN ? AND ?
            AND sem.is_active = 1
        ",
            [$section, $student_id, $start_date, $end_date]
        );

        return $query->result_array();
    }

    public function get_attendance_by_section($section_id, $start_date)
    {
        $sql = "
            SELECT 
                s.trans_no AS student_id, 
                s.lastname, 
                s.firstname, 
                sec.section, 
                SUM(CASE WHEN a.status = 'present' THEN 1 ELSE 0 END) AS present,
                SUM(CASE WHEN a.status = 'absent' THEN 1 ELSE 0 END) AS absents,
                GROUP_CONCAT(DISTINCT DATE(a.date) ORDER BY a.date ASC SEPARATOR ', ') AS absence_dates
            FROM 
                attendance a
            JOIN 
                student_master s ON a.student_id = s.trans_no
            JOIN 
                class_student sec ON s.trans_no = sec.student_id
            JOIN 
                class_schedule cs ON sec.student_id = cs.student_id
            JOIN 
                semester_master sem ON cs.semester_id = sem.trans_no
                WHERE 
                sec.section = ? 
            AND 
                a.date >= ?
            AND 
                a.status = 'absent'
            AND 
                sem.is_active = 1
            GROUP BY 
                s.trans_no, s.lastname, s.firstname, sec.section
            ORDER BY 
                absents DESC;
        ";

        $query = $this->db->query($sql, [$section_id, $start_date]);
        return $query->result_array();
    }

    public function get_present_students($section_id, $date)
    {
        $sql = "
            SELECT 
                s.trans_no, 
                s.firstname, 
                s.lastname 
            FROM attendance a
            JOIN student_master s ON a.student_id = s.trans_no
            JOIN class_schedule cs ON a.schedule_id = cs.schedule_id
            JOIN semester_master sem ON cs.semester_id = sem.trans_no
            WHERE a.schedule_id = ? AND a.status = 'present' 
            AND DATE(a.date) = ?
            AND sem.is_active = 1
        ";

        $query = $this->db->query($sql, [$section_id, $date]);

        return $query->result_array();
    }

    public function add_reason($attendance_id, $data)
    {
        $this->db->where('attendance_id', $attendance_id);
        return $this->db->update($this->table, $data);
    }

    public function get_double_entry($date, $schedule_id)
    {
        $sql = "SELECT a.student_id, sm.lastname, sm.firstname, a.date, a.status, a.ip_address
                FROM attendance a
                JOIN student_master sm
                ON a.student_id = sm.trans_no
                JOIN class_schedule cs ON a.schedule_id = cs.schedule_id
                JOIN semester_master sem ON cs.semester_id = sem.trans_no
                WHERE ip_address IN (
                    SELECT ip_address
                    FROM attendance
                    WHERE DATE(date) = ?  -- Same date filter
                    AND schedule_id = ?
                    GROUP BY ip_address
                    HAVING COUNT(*) > 1
                )
                AND DATE(a.date) = ?  -- Apply date filter to main query too
                AND a.schedule_id = ?
                AND sem.is_active = 1
                ORDER BY a.ip_address, date;
                ";

        $query = $this->db->query($sql, [$date, $schedule_id, $date, $schedule_id]);

        return $query->result_array();
    }

    public function get_student_status($schedule_id, $date, $status)
    {
        $sql = "
            SELECT 
                  a.student_id,
                s.firstname, 
                s.lastname,
                a.status,
                a.date
            FROM 
                attendance a
            JOIN 
                student_master s 
            ON 
                a.student_id = s.trans_no
            WHERE 
                a.schedule_id = ? 
            AND 
                a.status = ?
            AND 
                DATE(a.date) = ?
        ";

        $query = $this->db->query($sql, [$schedule_id, $status, $date]);

        return $query->result_array();
    }
}
