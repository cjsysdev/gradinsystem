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
            if ($this->is_offline) {
                redirect();
            }
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

        $absences_dates = $this->attendance->get_student_absences(
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
            'absences_dates' => $absences_dates,
            'present' => $this->attendance->get_present_days(
                $student_id,
                $account->section,
                $start_date,
                $date
            ),
            'show_red_overlay' => $absences >= 100,
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

    public function attendance_visualizer()
    {
        // Get all available class schedules
        $class_schedules = $this->class_schedule->as_array()->get_all();

        // If no schedule is selected, show selection form
        if (!$this->input->post('schedule_id')) {
            $data = [
                'class_schedules' => $class_schedules,
                'class' => null,
                'record' => []
            ];
            return $this->load->view('attendance_visualizer', $data);
        }

        // Get selected schedule
        $schedule_id = $this->input->post('schedule_id');
        $class = $this->class_schedule->get(['schedule_id' => $schedule_id]);
        $date = date('Y-m-d');

        // Get all students in the section
        $students = $this->class_student->get_students_with_names_by_section($class['section']);

        // Get today's attendance for the schedule
        $attendance = $this->attendance->where([
            'schedule_id' => $class['schedule_id'],
            'date(date)' => $date
        ])->as_array()->get_all();

        // Map attendance status to students
        $attendance_map = [];
        foreach ($attendance as $att) {
            $attendance_map[$att['student_id']] = $att['status'];
        }

        // Prepare record for view
        $record = [];
        foreach ($students as $student) {
            $record[] = [
                'student_id' => $student['student_id'],
                // 'lastname' => $student['lastname'],
                // 'firstname' => $student['firstname'],
                'status' => $attendance_map[$student['student_id']] ?? 'absent'
            ];
        }

        $data = [
            'class_schedules' => $class_schedules,
            'class' => $class,
            'record' => $record
        ];

        $this->load->view('attendance_visualizer', $data);
    }

    public function add_reason()
    {
        $attendance_id = $this->input->post('attendance_id');
        $reason = $this->input->post('reason');

        if ($attendance_id && $reason) {
            $this->attendance->add_reason($attendance_id, ["reason" => $reason]);
            $this->session->set_flashdata('success', 'Reason added successfully.');
        } else {
            $this->session->set_flashdata('error', 'Failed to add reason.');
        }
        redirect('attendance');
    }
}
