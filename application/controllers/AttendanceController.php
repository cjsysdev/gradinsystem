<?php
defined('BASEPATH') or exit('No direct script access allowed');

class AttendanceController extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->is_offline = !isset($_SESSION['online']);
    }

    public function attendance_main()
    {
        $section = $this->class_student->get(['student_id' => $this->session->student_id]);

        if ($section->section == null && $this->session->role != 'admin') {
            $this->session->set_flashdata('error', 'Please add your section first.');
            redirect('student/add_section');
        }

        if ($this->is_offline) {
            redirect();
        }
        date_default_timezone_set('Asia/Manila');

        $day = date('D');
        $date = date('Y-m-d');
        $start_date = '2025-08-01'; // Example start date

        $class = $this->class_schedule->class_today($day);
        $student_id = $this->session->student_id;

        $account = $this->class_student->get(['student_id' => $student_id]);
        $admin_id = 14;

        if ($student_id == $admin_id) {
            redirect('dashboard');
        }

        if (
            $this->shouldDenyAttendance(
                $class,
                $student_id,
                $admin_id,
                $account
            )
        ) {
            $this->session->set_flashdata('error', 'No available class');
        } else {
            $this->handleStudentAttendance($class, $student_id, $date);
        }

        $attendance_record = $this->attendance->get_student_attendance(
            $student_id
        );

        $absences = $this->attendance->checkStudentAbsences(
            $student_id,
            $account->section,
            $start_date,
            $date
        );

        $data = [
            'class' => $class,
            'record' => $attendance_record,
            'events' => json_encode($attendance_record),
            'absences' => $absences,
            'present' => $this->attendance->get_present_days(
                $student_id,
                $account->section,
                $start_date,
                $date
            ),
            'show_red_overlay' => $absences >= 50,
        ];

        $this->load->view('attendance_view', $data);
    }

    private function shouldDenyAttendance(
        $class,
        $student_id,
        $admin_id,
        $account
    ) {
        return !$class ||
            $student_id == $admin_id ||
            (isset($account->section) &&
                $account->section != $class['section']);
    }

    private function handleStudentAttendance($class, $student_id, $date)
    {
        $this->attendance->start_class(
            $class['schedule_id'],
            $class['section'],
            $date
        );

        $check_student = $this->attendance
            ->where([
                'student_id' => $student_id,
                'schedule_id' => $class['schedule_id'],
                'date(date)' => $date,
            ])
            ->get();

        if (
            isset($check_student->status) &&
            $check_student->status === 'absent'
        ) {
            $client_ip = $this->input->ip_address();
            $this->attendance->update_status(
                'present',
                $client_ip,
                $student_id,
                $date
            );
        } else if (!$check_student) {
            $this->attendance->insert_data([
                'schedule_id' => $class['schedule_id'],
                'student_id' => $student_id,
                'status' => 'present',
                'ip_address' => $this->input->ip_address(),
                'date' => $date . ' ' . date('H:i:s'),
            ]);
        }
    }
}
