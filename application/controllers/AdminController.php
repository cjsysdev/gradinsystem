<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Admin dashboard, attendance (daily roll + the Section Monitoring sheet), and
 * project-log browsing.
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
        $this->load->library('pagination');
        $this->load->helper('pagination');

        $class_id = $this->input->get('class_id') ?: null;
        $section  = $this->input->get('section') ?: null;

        // The team list is course-scoped, so a group_id belonging to another
        // course (left over from a course switch, or hand-typed) is dropped
        // rather than silently filtering everything away. 'none' — individual,
        // team-less entries — is always valid.
        $teams    = $this->Project_log_model->get_teams_for_class($class_id);
        $group_id = $class_id ? ($this->input->get('group_id') ?: null) : null;
        if ($group_id !== null && $group_id !== 'none'
            && !in_array((int) $group_id, array_map('intval', array_column($teams, 'group_id')), true)) {
            $group_id = null;
        }

        $per_page = 25;
        $offset   = (int) $this->input->get('per_page');

        $all_courses = $this->classes->as_array()->order_by('class_code')->get_all();

        $designations = [];
        foreach ($all_courses as $course) {
            $designations[$course['class_id']] = [
                'course'         => $course,
                'available_sets' => $this->Project_log_model->get_available_sets_for_class($course['class_id']),
                'set_ids'        => $this->Project_log_model->get_set_ids_for_class($course['class_id']),
            ];
        }

        $total = $this->Project_log_model->count_all_for_admin($class_id, $section, $group_id);
        $this->pagination->initialize(
            bs_pagination_config(base_url('admin/project_logs'), $total, $per_page)
        );

        $data['courses']      = $this->Project_log_model->get_logged_courses();
        $data['sections']     = $this->class_schedule->get_sections();
        $data['teams']        = $teams;
        $data['class_id']     = $class_id;
        $data['section']      = $section;
        $data['group_id']     = $group_id;
        $data['logs']         = $this->Project_log_model->get_all_for_admin($class_id, $section, $group_id, $per_page, $offset);
        $data['designations'] = $designations;
        $data['pagination']   = $this->pagination->create_links();
        $data['total']        = $total;
        $data['per_page']     = $per_page;
        $data['offset']       = $offset;

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

    // Section Monitoring — one row per enrolled student on a schedule, with
    // their term grades and their attendance tallies, either half toggleable.
    // Replaces the old view_attendance sheet, which filtered on the section
    // string and so joined class_student.section to class_schedule.section,
    // ignoring semester and enrolment status and double-counting anyone
    // enrolled in both a LEC and a LAB of the same section.
    public function section_monitoring()
    {
        $this->load->model('Grade_calculator');

        $f = $this->_monitoring_filters();

        // Guard here, not inside _monitoring_rows(): schedule_id 0 would
        // otherwise run three full grade passes over an empty roster.
        $data['rows'] = $f['schedule_id']
            ? $this->_monitoring_rows($f['schedule_id'], $f['grade_mode'])
            : [];

        $data['schedules']       = $this->class_schedule->get_all_active();
        $data['schedule_id']     = $f['schedule_id'];
        $data['show_grades']     = $f['show_grades'];
        $data['show_attendance'] = $f['show_attendance'];
        $data['grade_mode']      = $f['grade_mode'];
        $data['columns']         = $this->_monitoring_columns($f['show_grades'], $f['show_attendance'], $f['grade_mode']);

        // Built here rather than reassembled in the view, so the Export link
        // can never disagree with the filters the table was rendered from.
        $data['export_query'] = [
            'schedule_id'     => $f['schedule_id'],
            'filters_applied' => 1,
            'show_grades'     => $f['show_grades'] ? 1 : 0,
            'show_attendance' => $f['show_attendance'] ? 1 : 0,
            'grade_mode'      => $f['grade_mode'],
        ];

        $this->load->view('admin/section_monitoring', $data);
    }

    // The same rows and the same columns as the screen, as .xlsx — it reads
    // the same GET params, so the download always matches what's on display.
    public function export_section_monitoring()
    {
        $this->load->model('Grade_calculator');

        $f = $this->_monitoring_filters();

        if (!$f['schedule_id']) {
            $this->session->set_flashdata('error', 'Pick a section to export.');
            redirect('view_attendance');
            return;
        }

        $rows = $this->_monitoring_rows($f['schedule_id'], $f['grade_mode']);
        if (empty($rows)) {
            $this->session->set_flashdata('error', 'No enrolled students on that section.');
            redirect('view_attendance?schedule_id=' . $f['schedule_id']);
            return;
        }

        $columns = $this->_monitoring_columns($f['show_grades'], $f['show_attendance'], $f['grade_mode']);

        $sched = $this->db->query("
            SELECT sched.section, sched.type, cl.class_code
            FROM class_schedule sched
            JOIN classes cl ON cl.class_id = sched.class_id
            WHERE sched.schedule_id = ?
        ", [$f['schedule_id']])->row_array() ?: ['section' => '', 'type' => '', 'class_code' => ''];

        $this->load->library('xlsx_writer');

        $this->xlsx_writer
            ->set_sheet_name(trim($sched['section'] . ' ' . $sched['class_code']))
            ->set_columns(array_column($columns, 'width'))
            ->add_row(array_column($columns, 'label'), TRUE);

        // Cells go in raw — Xlsx_writer::esc() escapes them itself, and
        // pre-escaping here would write literal &amp; into the spreadsheet.
        foreach ($rows as $row) {
            $cells = [];
            foreach ($columns as $c) {
                $cells[] = $row[$c['key']];
            }
            $this->xlsx_writer->add_row($cells);
        }

        $safe = preg_replace(
            '/[^A-Za-z0-9_-]/',
            '_',
            $sched['section'] . '_' . $sched['class_code'] . '_' . $sched['type']
        );
        // The filename says which grades are inside, so a provisional export
        // sitting in a downloads folder cannot be mistaken for an official one.
        $tag = ($f['grade_mode'] === Grade_calculator::MODE_CURRENT && $f['show_grades']) ? '_current' : '';
        $this->xlsx_writer->download('section_monitoring_' . $safe . $tag . '_' . date('Y-m-d') . '.xlsx');
    }

    /**
     * The Section Monitoring filter state, read identically by the screen and
     * the export so the two can never disagree.
     *
     * Both checkboxes default to on, and an unticked checkbox sends no key at
     * all — so "never submitted" and "deliberately unticked" look the same in
     * the query string. The form's hidden filters_applied=1 marker separates
     * them; without it, unticking a box would silently re-tick on reload.
     *
     * grade_mode needs no such marker — a <select> always submits a value — and
     * anything other than an explicit 'current' falls back to the official INC
     * rendering, so a mangled query string can never turn a submission sheet
     * into provisional numbers by accident.
     */
    protected function _monitoring_filters()
    {
        $submitted = $this->input->get('filters_applied') !== NULL;

        return [
            'schedule_id'     => (int) $this->input->get('schedule_id'),
            'show_grades'     => $submitted ? ($this->input->get('show_grades') === '1') : TRUE,
            'show_attendance' => $submitted ? ($this->input->get('show_attendance') === '1') : TRUE,
            'grade_mode'      => $this->input->get('grade_mode') === Grade_calculator::MODE_CURRENT
                ? Grade_calculator::MODE_CURRENT
                : Grade_calculator::MODE_INC,
        ];
    }

    /**
     * The Section Monitoring row set for one schedule, in roster order
     * (lastname, firstname — Grade_calculator::roster() already sorts it, and
     * for_schedule() preserves that insertion order, so no usort is needed).
     *
     * Three for_schedule() passes is deliberate: for_schedule_final() covers
     * midterm + final + overall but never touches 'tentative-final', and the
     * alternative — one bespoke multi-term query — would be a fourth copy of
     * the weighting rules, which is exactly what Grade_calculator exists to
     * prevent. $with_attendance is FALSE on all of them: it saves three
     * redundant queries and keeps the grading-derived `late` out of the data
     * entirely, so the raw ENUM counts can't be confused with it.
     */
    protected function _monitoring_rows($schedule_id, $grade_mode = Grade_calculator::MODE_INC)
    {
        $gc = $this->Grade_calculator;

        $finals    = $gc->for_schedule_final($schedule_id, FALSE);
        $tentative = $gc->for_schedule($schedule_id, 'tentative-final', FALSE);
        $counts    = $this->attendance->status_counts_for_schedule($schedule_id);

        $rows = [];
        $n    = 0;
        foreach ($finals['students'] as $sid => $s) {
            // Same roster on all three passes, so this fallback should never
            // fire; it carries grade_point because display_grade_point() reads
            // that key once the status check lets it through, and no
            // 'provisional' key so MODE_CURRENT falls through to 'INC' rather
            // than inventing a standing for a student we have no data for.
            $t = isset($tentative['students'][$sid]['term'])
                ? $tentative['students'][$sid]['term']
                : ['status' => 'inc', 'grade_point' => NULL];

            $rows[] = $this->_monitoring_row(
                ++$n,
                $s,
                $t,
                isset($counts[$sid]) ? $counts[$sid] : NULL,
                $grade_mode
            );
        }

        return $rows;
    }

    /**
     * One Section Monitoring row. Always carries every field — which of them
     * get rendered is _monitoring_columns()' job — and returns values raw, so
     * each consumer escapes for its own medium.
     *
     * $s comes from for_schedule_final() and carries 'midterm', 'final' and
     * 'overall'; $tentative is the 'tentative-final' term block. The Final
     * Grade column is `overall` (the midterm/final blend), not the 'final'
     * term on its own.
     *
     * Under MODE_CURRENT an incomplete grade renders as its provisional figure
     * instead of 'INC'. Each such cell also gets a `<key>_provisional` flag so
     * the screen can mark which numbers are not the official grade — the .xlsx
     * ignores those keys and takes the mode from the column headings instead.
     * `is_inc` keeps meaning "the OFFICIAL overall grade is INC" in both modes,
     * so the mode never changes what that flag reports.
     */
    protected function _monitoring_row($n, array $s, array $tentative, $counts, $grade_mode = Grade_calculator::MODE_INC)
    {
        $gc     = $this->Grade_calculator;
        $counts = $counts ?: ['present' => 0, 'absent' => 0, 'late' => 0, 'excuse' => 0];

        return [
            'n'                     => $n,
            'student_id'            => $s['student_id'],
            'lastname'              => $s['lastname'],
            'firstname'             => $s['firstname'],
            'midterm'               => $gc->display_grade_point($s['midterm'], 2, $grade_mode),
            'tentative'             => $gc->display_grade_point($tentative, 2, $grade_mode),
            'overall'               => $gc->display_grade_point($s['overall'], 2, $grade_mode),
            'midterm_provisional'   => $gc->is_provisional($s['midterm'], $grade_mode),
            'tentative_provisional' => $gc->is_provisional($tentative, $grade_mode),
            'overall_provisional'   => $gc->is_provisional($s['overall'], $grade_mode),
            'is_inc'                => $s['overall']['status'] !== 'ok',
            'present'               => $counts['present'],
            'absent'                => $counts['absent'],
            'late'                  => $counts['late'],
            'excuse'                => $counts['excuse'],
        ];
    }

    /**
     * The ordered column spec the HTML table and the .xlsx both render from,
     * so adding or reordering a column cannot desync the two outputs. Header
     * text, the _monitoring_row() key and the spreadsheet width live together.
     *
     * The per-row View/Edit link is deliberately absent — it has no
     * spreadsheet counterpart, so the view appends that column itself.
     *
     * The grade headings carry the display mode, which is the only way an
     * exported .xlsx can say whether its numbers are official or provisional —
     * a bare spreadsheet has no legend and outlives the query string that
     * produced it.
     */
    protected function _monitoring_columns($show_grades, $show_attendance, $grade_mode = Grade_calculator::MODE_INC)
    {
        $cols = [
            ['key' => 'n',         'label' => '#',         'width' => 5],
            ['key' => 'lastname',  'label' => 'Lastname',  'width' => 22],
            ['key' => 'firstname', 'label' => 'Firstname', 'width' => 22],
        ];

        if ($show_grades) {
            $suffix = ($grade_mode === Grade_calculator::MODE_CURRENT) ? ' (current)' : '';
            $grow   = ($grade_mode === Grade_calculator::MODE_CURRENT) ? 10 : 0;

            $cols[] = ['key' => 'midterm',   'label' => 'Midterm' . $suffix,         'width' => 12 + $grow];
            $cols[] = ['key' => 'tentative', 'label' => 'Tentative Final' . $suffix, 'width' => 16 + $grow];
            $cols[] = ['key' => 'overall',   'label' => 'Final Grade' . $suffix,     'width' => 13 + $grow];
        }

        if ($show_attendance) {
            $cols[] = ['key' => 'present', 'label' => 'Present', 'width' => 10];
            $cols[] = ['key' => 'absent',  'label' => 'Absent',  'width' => 10];
            $cols[] = ['key' => 'late',    'label' => 'Late',    'width' => 10];
            $cols[] = ['key' => 'excuse',  'label' => 'Excused', 'width' => 10];
        }

        return $cols;
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
