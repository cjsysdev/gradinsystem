<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Admin dashboard, attendance, and project-log browsing.
 *
 * The other admin screens live in AdminSubmissionController,
 * AdminAssessmentController, AdminStudentController and AdminContentController;
 * all five share Admin_Controller (application/core/MY_Controller.php) for the
 * session gate and the topic/widget helper seams. Old AdminController/* URLs
 * still resolve to whichever controller now owns them — see the legacy routes
 * in application/config/routes.php.
 */
class AdminController extends Admin_Controller
{
    // Read-only browse of students' project progress logs, optionally filtered
    // by course and/or section. Also carries the group-designation panel: for
    // every course, which grouping set(s) (if any) govern its project log.
    public function project_logs()
    {
        $this->load->model(['Project_log_model', 'classes']);

        $class_id = $this->input->get('class_id') ?: null;
        $section  = $this->input->get('section') ?: null;

        $all_courses = $this->classes->as_array()->order_by('class_code')->get_all();

        $designations = [];
        foreach ($all_courses as $course) {
            $designations[$course['class_id']] = [
                'course'         => $course,
                'available_sets' => $this->Project_log_model->get_available_sets_for_class($course['class_id']),
                'set_ids'        => $this->Project_log_model->get_set_ids_for_class($course['class_id']),
            ];
        }

        $data['courses']      = $this->Project_log_model->get_logged_courses();
        $data['sections']     = $this->class_schedule->get_sections();
        $data['class_id']     = $class_id;
        $data['section']      = $section;
        $data['logs']         = $this->Project_log_model->get_all_for_admin($class_id, $section);
        $data['designations'] = $designations;

        $this->load->view('admin/project_logs', $data);
    }

    // Admin write: designate which grouping set(s) govern a course's project
    // log (or clear the designation to fall back to per-student logging).
    public function save_project_log_groupings()
    {
        $this->load->model('Project_log_model');

        $class_id = (int) $this->input->post('class_id');
        $set_ids  = (array) $this->input->post('set_id');

        if (empty($class_id)) {
            $this->session->set_flashdata('error', 'Course is required.');
            redirect('admin/project_logs');
            return;
        }

        $this->Project_log_model->set_class_groupings($class_id, $set_ids);
        $this->session->set_flashdata('success', 'Project log groupings updated.');
        redirect('admin/project_logs');
    }

    public function dashboard()
    {
        $today = date('Y-m-d');
        $requested_date = $this->input->get('date');
        $requested_schedule_id = $this->input->get('schedule_id');
        $is_filtering = ($requested_date !== null && $requested_date !== '') || !empty($requested_schedule_id);

        $data['schedules'] = $this->class_schedule->get_all_active();

        // Discussion mode is a global toggle, independent of whether a class
        // happens to be in session — fetch it up front for both branches.
        $query = $this->db->get_where('global_settings', [
            'setting_key' => 'discussion_mode',
        ]);
        $data['discussion_mode'] = $query->row()->setting_value === '1';

        if (!$is_filtering) {
            // Default view: whatever class is live right now, exactly as before.
            $class = $this->class_schedule->class_today(date('D'));

            $data['selected_date'] = $today;
            $data['selected_schedule_id'] = '';

            if (!$class) {
                $data['attendance'] = [];
                $data['lates'] = [];
                $data['absents'] = [];
                $data['chronic_absentees'] = $this->attendance->get_chronic_absentees(null, $today, 3);
                $this->load->view('admin/dashboard', $data);
                return;
            }

            $data['attendance'] = $this->attendance->get_double_entry($today, $class['schedule_id']);
            $data['lates'] = $this->attendance->get_student_status($class['schedule_id'], $today, 'late');
            $data['absents'] = $this->attendance->get_student_status($class['schedule_id'], $today, 'absent');
            $data['chronic_absentees'] = $this->attendance->get_chronic_absentees($class['schedule_id'], $today, 3);

            $this->load->view('admin/dashboard', $data);
            return;
        }

        // Browsing another date (and/or a specific section) — no "currently
        // in session" gate; leaving the section blank spans every active one.
        $selected_date = ($requested_date && DateTime::createFromFormat('Y-m-d', $requested_date))
            ? $requested_date
            : $today;
        $selected_schedule_id = $requested_schedule_id ?: null;

        $data['selected_date'] = $selected_date;
        $data['selected_schedule_id'] = $selected_schedule_id ?? '';

        $data['attendance'] = $this->attendance->get_double_entry($selected_date, $selected_schedule_id);
        $data['lates'] = $this->attendance->get_student_status($selected_schedule_id, $selected_date, 'late');
        $data['absents'] = $this->attendance->get_student_status($selected_schedule_id, $selected_date, 'absent');
        $data['chronic_absentees'] = $this->attendance->get_chronic_absentees($selected_schedule_id, $selected_date, 3);

        $this->load->view('admin/dashboard', $data);
    }

    // AJAX — inline-edit an attendance row's status from the dashboard
    public function update_attendance_status()
    {
        header('Content-Type: application/json');

        $attendance_id = $this->input->post('attendance_id');
        $status        = $this->input->post('status');
        $allowed       = ['present', 'absent', 'late', 'excuse', 'others'];

        if (!$attendance_id || !in_array($status, $allowed, true)) {
            echo json_encode(['success' => false]);
            return;
        }

        $result = $this->attendance->set_status($attendance_id, $status);
        echo json_encode(['success' => (bool) $result]);
    }

    // Toggle discussion mode
    public function toggle_discussion_mode()
    {
        // Load the database library
        $this->load->database();

        // Get the current mode from the database
        $query = $this->db->get_where('global_settings', [
            'setting_key' => 'discussion_mode',
        ]);
        $current_mode = $query->row()->setting_value ?? '0';

        // Toggle the mode
        $new_mode = $current_mode === '1' ? '0' : '1';

        // Update the database
        $this->db->where('setting_key', 'discussion_mode');
        $this->db->update('global_settings', ['setting_value' => $new_mode]);

        // Redirect back to the dashboard
        redirect('dashboard');
    }

    public function view_attendance()
    {
        $active_semester = $this->db->where('is_active', 1)->get('semester_master')->row_array();
        $default_start_date = ($active_semester['class_started'] ?? null) ?: date('Y-m-d');

        $section_id = $this->input->get('section_id');
        $start_date = $this->input->get('start_date') ?: $default_start_date;

        // Fetch all sections for the dropdown
        $data['sections'] = $this->class_schedule->get_sections();

        // Fetch attendance data once a section is picked (start date always
        // has a value — defaults to the active semester's class_started).
        if ($section_id) {
            $data['attendance'] = $this->attendance->get_attendance_by_section($section_id, $start_date);
            $data['selected_section_id'] = $section_id;
        } else {
            $data['attendance'] = [];
            $data['selected_section_id'] = null;
        }
        $data['start_date'] = $start_date;

        $this->load->view('admin/view_attendance', $data);
    }

    // Every attendance record for one student, across every class/schedule
    // they're enrolled in for the active semester — editable inline, same
    // pattern as the dashboard's status dropdown.
    public function student_attendance($student_id = null)
    {
        if (!$student_id) {
            redirect('view_attendance');
            return;
        }

        $student = $this->student_master->get_student_info($student_id);
        if (!$student) {
            $this->session->set_flashdata('error', 'Student not found.');
            redirect('view_attendance');
            return;
        }

        $active_semester = $this->db->where('is_active', 1)->get('semester_master')->row_array();

        $data['student']         = $student;
        $data['active_semester'] = $active_semester;
        $data['records']         = $this->attendance->get_student_attendance_full($student_id);

        $this->load->view('admin/student_attendance', $data);
    }
}
