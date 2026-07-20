<?php
defined('BASEPATH') or exit('No direct script access allowed');

class AdminController extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        if ($this->session->userdata('role') !== 'admin') {
            redirect('login');
        }
    }

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

    public function all_submissions($assessment_id = null)
    {
        // Fetch all assessments for the dropdown

        $day = date('D');
        $class = $this->class_schedule->class_today($day);

        $data['assessments'] = $this->assessments->get_for_schedule($class['schedule_id']);

        // Fetch submissions for the selected assessment
        $data['widget'] = null;
        $data['widget_config'] = [];

        if ($assessment_id) {
            $data['submissions'] = $this->classworks->get_all_submissions(
                $assessment_id
            );
            $data['missing_students'] = $this->classworks->get_missing_submissions($assessment_id);
            $data['selected_assessment_id'] = $assessment_id;

            $assessment = $this->assessments->as_array()->get($assessment_id);
            if (!empty($assessment['widget_id'])) {
                $this->load->model('Widgets_model');
                $data['widget'] = $this->Widgets_model->get($assessment['widget_id']);
                $data['widget_config'] = json_decode($assessment['given'] ?? '', true) ?: [];
            }
        } else {
            $data['submissions'] = [];
            $data['missing_students'] = [];
            $data['selected_assessment_id'] = null;
        }

        $this->load->view('admin/all_submission', $data);
    }

    public function manage_json_files()
    {
        $this->load->database();

        if ($this->input->post()) {
            $assessment_id = $this->input->post('assessment_id');
            $json_file_path = $this->input->post('json_file_path');

            $this->db->replace('assessment_files', [
                'assessment_id' => $assessment_id,
                'json_file_path' => $json_file_path
            ]);

            $this->session->set_flashdata('success', 'JSON file path updated successfully.');
            redirect('AdminController/manage_json_files');
        }

        $data['assessments'] = $this->db->get('assessment_full')->result_array();
        $data['json_files'] = $this->db->get('assessment_files')->result_array();

        $this->load->view('manage_json_files', $data);
    }

    public function view_student_submissions($student_id = null)
    {
        // Check if a student ID is provided
        if (!$student_id) {
            $this->session->set_flashdata('error', 'No student selected.');
            redirect('AdminController/dashboard');
        }

        // Fetch student details
        $data['student'] = $this->accounts->as_array()->get(['student_id' => $student_id]);

        if (!$data['student']) {
            $this->session->set_flashdata('error', 'Student not found.');
            redirect('AdminController/dashboard');
        }

        // Fetch all classworks (submitted and missing) for the student
        $this->load->model('classworks');
        $this->load->model('assessments');
        $submitted_classworks = $this->classworks->get_submissions_by_student($student_id);
        $all_assessments = $this->assessments->get_all_assessments();

        // Merge submitted classworks with missing ones
        $classworks = [];
        foreach ($all_assessments as $assessment) {
            $found = false;
            foreach ($submitted_classworks as $submission) {
                if ($submission['assessment_id'] == $assessment['assessment_id']) {
                    $classworks[] = $submission;
                    $found = true;
                    break;
                }
            }
            if (!$found) {
                $classworks[] = [
                    'assessment_id' => $assessment['assessment_id'],
                    'title' => $assessment['title'],
                    'classwork_id' => null,
                    'score' => null,
                    'created_at' => null,
                    'status' => 'missing',
                ];
            }
        }

        $data['classworks'] = $classworks;

        // Load the view
        $this->load->view('admin/student_submissions', $data);
    }

    public function student_submissions()
    {
        $student_id = $this->input->get('student_id');
        $data['students'] = $this->student_master->get_all(); // Already correct

        if ($student_id) {
            // Fetch submissions for the selected student
            $data['submissions'] = $this->classworks->get_submissions_by_student($student_id);
        } else {
            $data['submissions'] = [];
        }

        // Load the view
        $this->load->view('admin/student_submissions', $data);
    }

    public function emergency_contacts()
    {
        $this->load->library('pagination');

        $student_id          = $this->input->get('student_id');
        $data['student']     = null;
        $data['contacts']    = [];
        $data['pagination']  = '';
        $data['total']       = 0;
        $data['per_page']    = 20;
        $data['offset']      = 0;
        $data['selected_section'] = '';

        if ($student_id) {
            $data['student'] = $this->student_master->get_student_info($student_id);
            if ($data['student']) {
                $data['contacts'] = $this->emergency_contact->get_by_student($student_id);
                $data['total']    = count($data['contacts']);
            } else {
                $this->session->set_flashdata('error', 'Student not found.');
            }
        } else {
            $section  = trim((string) $this->input->get('section'));
            $per_page = 20;
            $offset   = (int)($this->input->get('per_page') ?? 0);
            $total    = $this->emergency_contact->count_all_contacts($section ?: null);

            $config = [
                'base_url'             => base_url('admin/emergency_contacts'),
                'total_rows'           => $total,
                'per_page'             => $per_page,
                'page_query_string'    => TRUE,
                'query_string_segment' => 'per_page',
                'reuse_query_string'   => TRUE,
                'use_page_numbers'     => FALSE,
                'full_tag_open'        => '<ul class="pagination pagination-sm mb-0">',
                'full_tag_close'       => '</ul>',
                'first_link'           => '&laquo;',
                'first_tag_open'       => '<li class="page-item">',
                'first_tag_close'      => '</li>',
                'last_link'            => '&raquo;',
                'last_tag_open'        => '<li class="page-item">',
                'last_tag_close'       => '</li>',
                'next_link'            => '&rsaquo;',
                'next_tag_open'        => '<li class="page-item">',
                'next_tag_close'       => '</li>',
                'prev_link'            => '&lsaquo;',
                'prev_tag_open'        => '<li class="page-item">',
                'prev_tag_close'       => '</li>',
                'num_tag_open'         => '<li class="page-item">',
                'num_tag_close'        => '</li>',
                'cur_tag_open'         => '<li class="page-item active"><a class="page-link" href="#">',
                'cur_tag_close'        => '</a></li>',
                'attributes'           => ['class' => 'page-link'],
                'num_links'            => 4,
            ];
            $this->pagination->initialize($config);

            $data['contacts']         = $this->emergency_contact->get_all_paged($per_page, $offset, $section ?: null);
            $data['pagination']       = $this->pagination->create_links();
            $data['total']            = $total;
            $data['per_page']         = $per_page;
            $data['offset']           = $offset;
            $data['selected_section'] = $section;
        }

        $data['sections'] = $this->emergency_contact->get_exportable_sections();

        $this->load->view('admin/emergency_contacts', $data);
    }

    // Downloads one section's roster as .xlsx in the fixed column order the
    // school's emergency-contact form expects.
    public function export_emergency_contacts()
    {
        $section = trim((string) $this->input->get('section'));

        if ($section === '') {
            $this->session->set_flashdata('error', 'Pick a section to export.');
            redirect('admin/emergency_contacts');
            return;
        }

        $students = $this->emergency_contact->get_by_section($section);

        if (empty($students)) {
            $this->session->set_flashdata('error', 'No students enrolled in section ' . $section . '.');
            redirect('admin/emergency_contacts');
            return;
        }

        $this->load->library('xlsx_writer');

        $this->xlsx_writer
            ->set_sheet_name($section)
            ->set_columns([18, 18, 8, 18, 30, 22, 22])
            ->add_row([
                'Lastname',
                'Firstname',
                'Middle Initial',
                'Contact Number',
                'Name of Parent/Guardian',
                'Relationship with the Student',
                'Contact Number of Parent / Guardian',
            ], TRUE);

        foreach ($students as $s) {
            $this->xlsx_writer->add_row([
                $s['lastname'],
                $s['firstname'],
                $this->middle_initial($s['middlename']),
                $s['student_contact'],
                $s['guardian_name'],
                $s['guardian_relationship'],
                $s['guardian_contact'],
            ]);
        }

        $safe_section = preg_replace('/[^A-Za-z0-9_-]/', '_', $section);
        $this->xlsx_writer->download('emergency_contacts_' . $safe_section . '_' . date('Y-m-d') . '.xlsx');
    }

    private function middle_initial($middlename)
    {
        $middlename = trim((string) $middlename);
        if ($middlename === '') {
            return '';
        }
        return strtoupper(mb_substr($middlename, 0, 1, 'UTF-8')) . '.';
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

    public function active_participation($assessment_id = null)
    {
        $section_id = $this->input->get('section_id');
        $date = $this->input->get('date') ?? date('Y-m-d');

        // Fetch all sections for the dropdown
        $this->db->distinct();
        $this->db->select('section');
        $data['sections'] = $this->db->get('class_schedule')->result_array();

        // Fetch present students if section and date are provided
        if ($section_id) {
            $this->load->model('attendance');
            $data['students'] = $this->attendance->get_present_students($section_id, $date);
            $data['selected_section_id'] = $section_id;
            $data['date'] = $date;
        } else {
            $data['students'] = [];
            $data['selected_section_id'] = null;
            $data['date'] = $date;
        }

        // Pass the assessment ID for scoring
        $data['assessment_id'] = $assessment_id;

        // Load the view
        $this->load->view('admin/active_participation', $data);
    }

    public function check_new_submissions_by_assessment($assessment_id)
    {
        // Fetch the latest submissions for the assessment
        $submissions = $this->classworks->get_all_submissions($assessment_id);

        // Return the data as JSON
        echo json_encode($submissions);
    }

    public function uncleared_students_overview()
    {
        $this->load->model('class_student');
        $data['sections'] = $this->class_student->get_sections_with_uncleared_counts();
        $this->load->view('admin/uncleared_students_overview', $data);
    }

    public function uncleared_students($section)
    {
        $this->load->model('class_student');
        $data['students'] = $this->class_student->get_uncleared_students_by_section($section);
        $data['section'] = $section;
        $this->load->view('admin/uncleared_students', $data);
    }

    public function clear_student($id, $section)
    {
        $this->load->model('class_student');
        $this->class_student->clear_student($id);
        redirect('uncleared_students/' . urlencode($section));
    }

    public function manage_assessments()
    {
        $this->load->library('pagination');

        $schedule_id = $this->input->get('schedule_id');
        // No schedule_id in the query string at all (first page load, not an
        // explicit "All Sections" pick) — default the filter to whichever
        // class is scheduled right now, same as all_submissions().
        if ($schedule_id === null) {
            $day = date('D');
            $current_class = $this->class_schedule->class_today($day);
            $schedule_id = $current_class['schedule_id'] ?? null;
        }

        // Search + filter set — applied identically to the list, the pager total,
        // and the bulk-action id set (see assessments::_admin_filters_sql()).
        $filters = [
            'schedule_id' => $schedule_id ?: null,
            'q'           => trim($this->input->get('q') ?? ''),
            'iotype_id'   => $this->input->get('iotype_id') ?: null,
            'term'        => $this->input->get('term') ?: null,
            'status'      => ($this->input->get('status') !== null && $this->input->get('status') !== '') ? $this->input->get('status') : '',
            'submission'  => $this->input->get('submission') ?: '',
        ];

        $per_page = 20;
        $offset   = (int) $this->input->get('per_page');
        $total    = $this->assessments->count_all_for_admin($filters);

        // Preserve every active filter across page links.
        $qs = [];
        foreach (['schedule_id', 'q', 'iotype_id', 'term', 'status', 'submission'] as $k) {
            if ($filters[$k] !== null && $filters[$k] !== '') $qs[] = $k . '=' . urlencode($filters[$k]);
        }
        $base_url = base_url('manage_assessments') . ($qs ? '?' . implode('&', $qs) : '');

        $config = [
            'base_url'             => $base_url,
            'total_rows'           => $total,
            'per_page'             => $per_page,
            'page_query_string'    => TRUE,
            'query_string_segment' => 'per_page',
            'reuse_query_string'   => TRUE,
            'use_page_numbers'     => FALSE,
            'full_tag_open'        => '<ul class="pagination pagination-sm mb-0">',
            'full_tag_close'       => '</ul>',
            'first_link'           => '&laquo;',
            'first_tag_open'       => '<li class="page-item">',
            'first_tag_close'      => '</li>',
            'last_link'            => '&raquo;',
            'last_tag_open'        => '<li class="page-item">',
            'last_tag_close'       => '</li>',
            'next_link'            => '&rsaquo;',
            'next_tag_open'        => '<li class="page-item">',
            'next_tag_close'       => '</li>',
            'prev_link'            => '&lsaquo;',
            'prev_tag_open'        => '<li class="page-item">',
            'prev_tag_close'       => '</li>',
            'num_tag_open'         => '<li class="page-item">',
            'num_tag_close'        => '</li>',
            'cur_tag_open'         => '<li class="page-item active"><a class="page-link" href="#">',
            'cur_tag_close'        => '</a></li>',
            'attributes'           => ['class' => 'page-link'],
            'num_links'            => 4,
        ];
        $this->pagination->initialize($config);

        $rows = $this->assessments->get_all_for_admin($filters, $per_page, $offset);
        // Rows are pre-sorted by master_id (see get_all_for_admin()) so every
        // section sharing an assessment lands on consecutive rows within this
        // page. Mark the first row of each run with _rowspan = run length —
        // the view uses it to merge the shared content cells (title/type/
        // widget/term/max score) across the group with a single <td rowspan>
        // instead of repeating them per section.
        $prev_master = null;
        $group_start = null;
        foreach ($rows as $i => $row) {
            if ($row['master_id'] !== $prev_master) {
                if ($group_start !== null) {
                    $rows[$group_start]['_rowspan'] = $i - $group_start;
                }
                $group_start = $i;
                $prev_master = $row['master_id'];
            }
        }
        if ($group_start !== null) {
            $rows[$group_start]['_rowspan'] = count($rows) - $group_start;
        }

        $data['assessments']         = $rows;
        $data['all_assessment_ids']  = $this->assessments->get_all_ids_for_admin($filters);
        $data['pagination']          = $this->pagination->create_links();
        $data['total']               = $total;
        $data['per_page']            = $per_page;
        $data['offset']              = $offset;
        $data['schedules'] = $this->class_schedule->get_all_active();
        $data['io_types'] = $this->db->get('io_type')->result_array();
        $data['selected_schedule']   = $schedule_id;
        $data['search_q']            = $filters['q'];
        $data['selected_iotype']     = $filters['iotype_id'];
        $data['selected_term']       = $filters['term'];
        $data['selected_status']     = $filters['status'];
        $data['selected_submission'] = $filters['submission'];

        // Distinct classes behind those active sections, for the "Entire Class" apply mode.
        $seen_classes = [];
        foreach ($data['schedules'] as $s) {
            $seen_classes[$s['class_id']] = ['class_id' => $s['class_id'], 'class_code' => $s['class_code'], 'class_name' => $s['class_name']];
        }
        $data['classes'] = array_values($seen_classes);

        $this->load->model('Grouping_model');
        $data['grouping_sets'] = $this->Grouping_model->get_all_sets();

        $this->load->model('Widgets_model');
        $data['widgets'] = $this->Widgets_model->get_all();

        $data['copyable_assessments']  = $this->assessments->get_copyable_for_active_semester();
        $data['assignable_masters']    = $this->assessments->get_assignable_masters();

        // Topics available to the "Interactive Discussion/Quiz" widget — only
        // the lesson+quiz format InteractiveQuizController::discussion() can
        // render (sections[].quiz), not the multi-question sections[].questions
        // format used by the older topics/analytics flow.
        $data['iq_topics'] = [];
        // Question count per topic (one question per section.quiz) — the modal
        // JS auto-fills Max Score from this when a topic is picked, and
        // save_assessment() re-derives it server-side as the source of truth.
        $data['iq_topic_question_counts'] = [];
        // Class code per topic (its assets/json/{CLASS_CODE}/ folder, '' for
        // legacy/unfiled root files) — the modal JS filters the Topic dropdown
        // to the section/class selected above so admins can't pick a topic
        // that belongs to a different course.
        $data['iq_topic_classes'] = [];
        // Title/description straight from the topic JSON, keyed by slug — the
        // modal JS auto-fills the assessment's Title/Description fields from
        // this when a topic is picked, same as it does for Max Score.
        $data['iq_topic_meta'] = [];
        foreach ($this->_glob_json_topics() as $file) {
            $meta = json_decode(file_get_contents($file), true);
            if (!$meta || empty($meta['sections'])) {
                continue;
            }
            $is_discussion_format = true;
            foreach ($meta['sections'] as $s) {
                if (isset($s['questions'])) {
                    $is_discussion_format = false;
                    break;
                }
            }
            if ($is_discussion_format) {
                $slug = basename($file, '.json');
                $title = $meta['title'] ?? ucwords(str_replace('_', ' ', $slug));
                $data['iq_topics'][$slug] = $title;
                $data['iq_topic_question_counts'][$slug] = $this->_count_iq_topic_questions($meta);
                $data['iq_topic_classes'][$slug] = $this->_topic_class_code_from_path($file);
                $data['iq_topic_meta'][$slug] = [
                    'title'       => $title,
                    'description' => $meta['description'] ?? '',
                ];
            }
        }

        $this->load->view('admin/manage_assessments', $data);
    }

    // Number of gradable questions in a discussion-format topic — one per
    // section that actually has a quiz (sections can be lesson-only). Shared
    // by manage_assessments() (for the JS auto-fill) and save_assessment()
    // (server-side max_score derivation, the authoritative source).
    private function _count_iq_topic_questions(array $topic_meta)
    {
        $count = 0;
        foreach (($topic_meta['sections'] ?? []) as $s) {
            if (!empty($s['quiz'])) {
                $count++;
            }
        }
        return $count;
    }

    // $assessment_id posted here is always a SECTION id (assessment_section_id)
    // — the id space consumers see everywhere (URLs, classworks.assessment_id,
    // etc.) never changed across the master/assessment_section split (see
    // Assessment_normalize_model). Content (title/description/max_score/term/
    // widget/given) lives on the shared master; editing it updates every
    // section sharing that master. Per-section fields (due/status/
    // is_groupings) only ever touch the one section being edited.
    public function save_assessment()
    {
        $post = $this->input->post();
        $section_id = !empty($post['assessment_id']) ? (int)$post['assessment_id'] : null;
        $apply_mode = $post['apply_mode'] ?? 'section';

        $status = isset($post['status']) ? $post['status'] : 0;
        if ($status === 'open' || $status === 'closed') {
            $status = $status === 'open' ? '1' : '0';
        }

        $master_fields = [
            'iotype_id'   => $post['iotype_id'],
            'title'       => $post['title'],
            'description' => $post['description'],
            'max_score'   => $post['max_score'],
            'term'        => $post['term'],
            'widget_id'   => !empty($post['widget_id']) ? (int) $post['widget_id'] : null,
            'given'       => !empty($post['widget_id']) ? ($post['given'] ?? null) : null,
        ];

        // Interactive Discussion/Quiz: max_score isn't hand-entered — it's the
        // number of questions in the chosen topic (one per section.quiz), so a
        // student's raw quiz score (1 point per correct answer) always lines
        // up with the assessment's own max. Derived server-side, not trusted
        // from the posted "Max Score" field, since the modal JS's auto-fill
        // could be stale (e.g. topic file edited after the form loaded).
        if ($master_fields['widget_id']) {
            $this->load->model('Widgets_model');
            $widget = $this->Widgets_model->get($master_fields['widget_id']);
            if ($widget && $widget['widget_key'] === 'iq_discussion') {
                $topic = json_decode($master_fields['given'] ?? '', true)['topic'] ?? '';
                $topic_found = false;
                if ($topic) {
                    foreach ($this->_glob_json_topics() as $file) {
                        if (basename($file, '.json') !== $topic) {
                            continue;
                        }
                        $meta = json_decode(file_get_contents($file), true) ?: [];
                        $master_fields['max_score'] = max(1, $this->_count_iq_topic_questions($meta));
                        $topic_found = true;
                        break;
                    }
                }
                if (!$topic_found) {
                    $this->session->set_flashdata('error', 'Interactive Discussion/Quiz needs a topic — pick one from the Topic dropdown (the selected topic file could not be found).');
                    redirect('manage_assessments' . (!empty($post['schedule_id']) ? '?schedule_id=' . $post['schedule_id'] : ''));
                    return;
                }
            } elseif ($widget) {
                // All other widgets keep their config as a JSON string in
                // assessments.given (the standard — see CLAUDE.md). Reject
                // invalid/empty JSON here instead of storing it silently and
                // only breaking when a student opens the assessment.
                $given = trim((string) ($master_fields['given'] ?? ''));
                $config = $given !== '' ? json_decode($given, true) : null;
                if (!is_array($config) || empty($config)) {
                    if ($given === '') {
                        $reason = 'an empty config';
                    } elseif (json_last_error() !== JSON_ERROR_NONE) {
                        $reason = 'invalid JSON (' . json_last_error_msg() . ')';
                    } else {
                        $reason = 'JSON that is not an object';
                    }
                    $this->session->set_flashdata('error', 'Widget config not saved — "' . $widget['name'] . '" needs a JSON config, but the form contained ' . $reason . '.');
                    redirect('manage_assessments' . (!empty($post['schedule_id']) ? '?schedule_id=' . $post['schedule_id'] : ''));
                    return;
                }
            }
        }

        $auto_create = !empty($post['auto_create_submissions']);
        $section_fields = [
            'due'          => $post['due'],
            'status'       => (int) $status,
            'is_groupings' => !empty($post['is_groupings']) ? 1 : 0,
        ];

        // "Entire class" creates ONE shared master, assigned to every active
        // section of that class this semester, instead of a full duplicate
        // row per section. Only offered for brand-new assessments — an
        // existing assessment is already tied to a master. Group Submission
        // isn't offered in this mode since grouping sets are section-scoped
        // (see manage_assessments.php JS).
        if (!$section_id && $apply_mode === 'class' && !empty($post['class_id'])) {
            $schedules = $this->class_schedule->get_active_schedules_by_class((int) $post['class_id']);
            if (empty($schedules)) {
                $this->session->set_flashdata('error', 'That class has no active sections this semester.');
                redirect('manage_assessments');
            }

            $master_id = $this->assessments->create_master($master_fields);

            $created_count = 0;
            $submissions_created = 0;
            foreach ($schedules as $sched) {
                $new_section_id = $this->assessments->assign_to_schedule($master_id, $sched['schedule_id'], [
                    'due'          => $post['due'],
                    'status'       => (int) $status,
                    'is_groupings' => 0,
                ]);
                $created_count++;

                if ($auto_create) {
                    $submissions_created += $this->classworks->create_blank_for_schedule($new_section_id, $sched['schedule_id']);
                }
            }

            $flash = "Created 1 assessment, assigned to $created_count section(s).";
            if ($auto_create) {
                $flash .= " Created $submissions_created blank submission(s) across those sections.";
            }
            $this->session->set_flashdata('success', $flash);
            redirect('manage_assessments');
        }

        if ($section_id) {
            $master_id = $this->assessments->master_id_for_section($section_id);
            if (!$master_id) {
                $this->session->set_flashdata('error', 'Assessment not found — please try again.');
                redirect('manage_assessments');
                return;
            }
            // Content edits propagate to every section sharing this master —
            // that's the point of sharing (see CLAUDE.md widget config rule).
            // schedule_id is included here too: the modal's Section dropdown
            // stays editable on Edit (re-pointing a single section is a
            // supported correction), guarded by the same UNIQUE(assessment_id,
            // schedule_id) constraint that stops "class" mode from double-
            // assigning a section.
            $this->assessments->update_master($master_id, $master_fields);
            $this->assessments->update_section($section_id, $section_fields + ['schedule_id' => $post['schedule_id']]);
            $flash = 'Assessment updated successfully.';
        } else {
            $master_id = $this->assessments->create_master($master_fields);
            $section_id = $this->assessments->assign_to_schedule($master_id, $post['schedule_id'], $section_fields);
            $flash = 'Assessment added successfully.';
        }

        $grouping_set_id = !empty($post['grouping_set_id']) ? (int) $post['grouping_set_id'] : null;
        $this->db->where('assessment_id', $section_id)->delete('assessment_groupings');
        if ($section_fields['is_groupings'] && $grouping_set_id) {
            $this->db->insert('assessment_groupings', [
                'assessment_id' => $section_id,
                'set_id'        => $grouping_set_id,
            ]);
        }

        // Participation-style assessments: pre-create a blank (no score/code)
        // classworks row for every enrolled student in the section so the
        // admin can grade/randomize directly instead of students submitting.
        if ($auto_create) {
            $created = $this->classworks->create_blank_for_schedule($section_id, $post['schedule_id']);
            $flash .= $created > 0
                ? " Created $created blank submission(s) for the section."
                : ' All enrolled students already have a submission for this assessment.';
        }

        $this->session->set_flashdata('success', $flash);

        $qs = !empty($post['schedule_id']) ? '?schedule_id=' . $post['schedule_id'] : '';
        redirect('manage_assessments' . $qs);
    }

    // Attaches an EXISTING assessment (master) to an additional section,
    // instead of cloning its content into a new one — the true "shared
    // across sections" flow. Distinct from save_assessment()'s "Entire
    // Class" mode (which creates one master for every active section up
    // front) and from "Copy from existing assessment" (which pre-fills a
    // brand-new, independent master). Content fields aren't posted here at
    // all — only the target section and that section's own due/status/
    // grouping, since the master's content is fixed.
    public function assign_master()
    {
        $post = $this->input->post();
        $master_id = !empty($post['master_id']) ? (int) $post['master_id'] : null;
        $schedule_id = !empty($post['schedule_id']) ? $post['schedule_id'] : null;

        if (!$master_id || !$schedule_id) {
            $this->session->set_flashdata('error', 'Pick both an assessment and a target section.');
            redirect('manage_assessments');
            return;
        }

        // UNIQUE(assessment_id, schedule_id) would reject this anyway, but a
        // friendly flash message beats a silently-failed insert (db_debug is
        // off — see CLAUDE.md).
        $existing = $this->db->where(['assessment_id' => $master_id, 'schedule_id' => $schedule_id])
            ->get('assessment_section')->row_array();
        if ($existing) {
            $this->session->set_flashdata('error', 'That section is already assigned to this assessment.');
            redirect('manage_assessments');
            return;
        }

        $status = isset($post['status']) ? (int) $post['status'] : 0;
        $section_fields = [
            'due'          => $post['due'],
            'status'       => $status,
            'is_groupings' => !empty($post['is_groupings']) ? 1 : 0,
        ];

        $section_id = $this->assessments->assign_to_schedule($master_id, $schedule_id, $section_fields);

        $grouping_set_id = !empty($post['grouping_set_id']) ? (int) $post['grouping_set_id'] : null;
        if ($section_fields['is_groupings'] && $grouping_set_id) {
            $this->db->insert('assessment_groupings', [
                'assessment_id' => $section_id,
                'set_id'        => $grouping_set_id,
            ]);
        }

        $flash = 'Section assigned to the shared assessment.';
        if (!empty($post['auto_create_submissions'])) {
            $created = $this->classworks->create_blank_for_schedule($section_id, $schedule_id);
            $flash .= $created > 0 ? " Created $created blank submission(s) for the section." : '';
        }

        $this->session->set_flashdata('success', $flash);
        redirect('manage_assessments');
    }

    // Renders a widget's own input_view against admin-authored "given" JSON so
    // the Add/Edit Assessment modal can show a live preview underneath the
    // config textarea — same view file the student sees, just with
    // readonly=false/existing=null (a blank, unsubmitted form).
    public function preview_widget()
    {
        $widget_id = $this->input->post('widget_id');
        $given = $this->input->post('given');

        if (empty($widget_id)) {
            echo '<p class="text-muted mb-0">Select a widget above to see a preview.</p>';
            return;
        }

        $this->load->model('Widgets_model');
        $widget = $this->Widgets_model->get((int) $widget_id);
        if (!$widget) {
            echo '<p class="text-danger mb-0">Unknown widget.</p>';
            return;
        }

        $config = [];
        if (trim((string) $given) !== '') {
            $config = json_decode($given, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                echo '<p class="text-danger mb-0"><i class="fas fa-exclamation-triangle"></i> Invalid JSON &mdash; fix the config above to see a preview.</p>';
                return;
            }
        }

        echo $this->load->view($widget['input_view'], [
            'config'   => $config ?: [],
            'readonly' => false,
            'existing' => null,
        ], true);
    }

    public function update_assessment_status()
    {
        $assessment_id = (int)$this->input->post('assessment_id');
        $status = $this->input->post('status');

        if ($status === 'open' || $status === 'closed') {
            $status = $status === 'open' ? '1' : '0';
        }

        if (!$assessment_id || !in_array($status, ['0', '1'], true)) {
            echo json_encode(['success' => false]);
            return;
        }

        $this->db->where('assessment_section_id', $assessment_id)->update('assessment_section', ['status' => (int)$status]);
        echo json_encode(['success' => true]);
    }

    // Bulk open/close — used by the "Open All" / "Close All" buttons on
    // manage_assessments, applied only to the assessment_ids currently shown
    // in the table (i.e. respecting the Section filter).
    public function bulk_update_assessment_status()
    {
        $status = $this->input->post('status');
        $assessment_ids = $this->input->post('assessment_ids');
        $assessment_ids = is_array($assessment_ids) ? array_filter(array_map('intval', $assessment_ids)) : [];

        if (!in_array($status, ['0', '1'], true) || empty($assessment_ids)) {
            echo json_encode(['success' => false]);
            return;
        }

        $this->db->where_in('assessment_section_id', $assessment_ids)->update('assessment_section', ['status' => (int)$status]);
        echo json_encode(['success' => true]);
    }

    // Delete button on manage_assessments. Two-step: without `force`, a
    // pending student submission blocks the delete and reports how many exist
    // (so the modal JS can re-confirm with the admin using a fresh count —
    // never trusting the row count already rendered in the table, which can
    // go stale between page load and click). Only with `force=1` does it
    // cascade-delete the classworks rows too; otherwise a zero-submission
    // assessment is removed outright.
    public function delete_assessment($id)
    {
        header('Content-Type: application/json');

        if ($this->input->method() !== 'post') {
            echo json_encode(['success' => false, 'error' => 'Invalid request method.']);
            return;
        }

        $assessment_id = (int) $id;
        if (!$assessment_id || !$this->assessments->get($assessment_id)) {
            echo json_encode(['success' => false, 'error' => 'Assessment not found.']);
            return;
        }

        $submission_count = (int) $this->db
            ->where('assessment_id', $assessment_id)
            ->count_all_results('classworks');

        $force = $this->input->post('force') === '1';

        if ($submission_count > 0 && !$force) {
            echo json_encode(['success' => false, 'blocked' => true, 'submission_count' => $submission_count]);
            return;
        }

        if ($submission_count > 0) {
            $this->db->where('assessment_id', $assessment_id)->delete('classworks');
        }
        // assessment_groupings/assessment_live_state cascade-delete via their
        // FK to assessment_section — delete_section() handles both that and
        // deleting the now-orphaned master if this was its last section.
        $this->assessments->delete_section($assessment_id);

        echo json_encode(['success' => true]);
    }

    public function increment_randomized_count($classwork_id)
    {
        $this->classworks->set('randomized_count', 'randomized_count+1', FALSE)
            ->where('classwork_id', $classwork_id)
            ->update('classwork');
        echo json_encode(['success' => true]);
    }

    public function add_score($classwork_id, $score)
    {
        $error  = null;
        $result = $this->classworks->set_score($classwork_id, $score, $error);

        // A capped write still succeeds; $error carries the notice so the
        // grading UI can show what was actually stored.
        echo json_encode([
            'success' => $result,
            'notice'  => $error,
            'score'   => $this->db->select('score')
                ->where('classwork_id', $classwork_id)
                ->get('classworks')
                ->row('score'),
        ]);
    }

    public function add_rand_score_incremental($classwork_id, $points = 2)
    {
        $points = (int) $points;
        if ($points < 1) {
            $points = 1;
        }

        $result = $this->db->query(
            "UPDATE classworks c
             JOIN assessment_full a ON a.assessment_id = c.assessment_id
             SET c.score = LEAST(COALESCE(c.score, 0) + ?, a.max_score)
             WHERE c.classwork_id = ?",
            [$points, $classwork_id]
        );

        $score = $this->db->select('score')
            ->where('classwork_id', $classwork_id)
            ->get('classworks')
            ->row('score');

        echo json_encode(['success' => (bool)$result, 'score' => $score]);
    }

    /** Every classworks row currently scored above its assessment's max_score. */
    public function score_integrity()
    {
        $violations = $this->classworks->get_scores_exceeding_max();

        $this->load->view('admin/score_integrity', [
            'violations' => $violations,
        ]);
    }

    /**
     * Cap one over-max row down to its assessment's max_score. Reuses
     * set_score()'s own clamp — passing the row's current (over-max) score
     * back in is what triggers the cap, so there is exactly one place that
     * decides what "capped" means.
     */
    public function fix_score($classwork_id)
    {
        $row = $this->db->select('score')
            ->where('classwork_id', $classwork_id)
            ->get('classworks')
            ->row_array();

        if (!$row) {
            echo json_encode(['success' => FALSE, 'message' => 'Submission not found.']);
            return;
        }

        $error = null;
        $ok = $this->classworks->set_score($classwork_id, $row['score'], $error);

        echo json_encode([
            'success' => $ok,
            'message' => $error ?: 'Score capped.',
            'score'   => $this->db->select('score')->where('classwork_id', $classwork_id)->get('classworks')->row('score'),
        ]);
    }

    public function student_violations()
    {
        $student_id = $this->input->get('student_id');
        $status_filter = $this->input->get('status');
        $severity_filter = $this->input->get('severity');
        $data['students'] = json_decode(json_encode($this->student_master->get_all() ?: []), true);
        $data['violation_types'] = $this->violation->get_violation_types() ?: [];
        $data['violations'] = [];
        $data['selected_student_id'] = $student_id;
        $data['selected_status'] = $status_filter;
        $data['selected_severity'] = $severity_filter;
        $data['student'] = null;

        if ($student_id) {
            $data['student'] = $this->student_master->get_student_info($student_id);
            $filters = ['student_id' => $student_id];
            if ($status_filter) $filters['status'] = $status_filter;
            if ($severity_filter) $filters['severity'] = $severity_filter;
            $data['violations'] = $this->violation->get_all_violations($filters) ?: [];
            $data['violation_summary'] = $this->violation->get_violation_summary_by_student($student_id) ?: [];
        } else {
            $filters = [];
            if ($status_filter) $filters['status'] = $status_filter;
            if ($severity_filter) $filters['severity'] = $severity_filter;
            $data['violations'] = $this->violation->get_all_violations($filters) ?: [];
        }

        $this->load->view('admin/student_violations', $data);
    }

    public function add_violation()
    {
        if ($this->input->post()) {
            $student_id = $this->input->post('student_id');
            $violation_type = $this->input->post('violation_type');
            $description = $this->input->post('description');
            $severity = $this->input->post('severity');
            $date_of_violation = $this->input->post('date_of_violation');
            $reported_by = $this->input->post('reported_by') ?: 'Admin';
            $notes = $this->input->post('notes');

            if (!$student_id || !$violation_type || !$date_of_violation) {
                $this->session->set_flashdata('error', 'Please fill in all required fields.');
                redirect('admin/student_violations?student_id=' . $student_id);
                return;
            }

            $this->violation->add_violation($student_id, $violation_type, $description, $severity, $date_of_violation, $reported_by, $notes);
            $this->session->set_flashdata('success', 'Violation recorded successfully.');
            redirect('admin/student_violations?student_id=' . $student_id);
        } else {
            $data['violation_types'] = $this->violation->get_violation_types() ?: [];
            $data['students'] = json_decode(json_encode($this->student_master->get_all() ?: []), true);
            $this->load->view('admin/add_violation', $data);
        }
    }

    public function update_violation_status()
    {
        if ($this->input->post()) {
            $violation_id = $this->input->post('violation_id');
            $status = $this->input->post('status');
            $notes = $this->input->post('notes');

            if (!$violation_id || !$status) {
                echo json_encode(['success' => false, 'message' => 'Missing required fields']);
                return;
            }

            $this->violation->update_violation_status($violation_id, $status, $notes);
            echo json_encode(['success' => true, 'message' => 'Violation status updated']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Invalid request']);
        }
    }

    public function students_by_section()
    {
        $this->load->model('class_student');
        $section = $this->input->get('section');
        $data['sections'] = $this->class_student->get_sections_with_counts();
        $data['selected_section'] = $section;
        $data['students'] = [];

        if ($section) {
            $data['students'] = $this->class_student->get_students_with_profile_by_section($section);
        }

        $this->load->view('admin/students_by_section', $data);
    }

    public function student_summary($student_id = null)
    {
        if (!$student_id) {
            redirect('admin/students_by_section');
        }

        $student = $this->student_master->get_student_info($student_id);
        if (!$student) {
            $this->session->set_flashdata('error', 'Student not found.');
            redirect('admin/students_by_section');
        }

        $account = $this->accounts->as_array()->get(['student_id' => $student_id]);

        $this->load->model('classworks');
        $data['student']      = $student;
        $data['profile_pic']  = $account ? $account['profile_pic'] : null;
        $data['has_account']  = $account && $account['role'] === 'student';
        $data['attendance']   = $this->student_master->get_attendance_summary($student_id);
        $data['classworks']   = $this->classworks->get_submissions_by_student($student_id);
        $data['violations']   = $this->violation->get_all_violations(['student_id' => $student_id]);
        $data['vio_summary']  = $this->violation->get_violation_summary_by_student($student_id);
        $data['contacts']     = $this->emergency_contact->get_by_student($student_id);

        $this->load->view('admin/student_summary', $data);
    }

    // Admin-only "log in as" this student, for testing features from the
    // student's point of view. Stashes the admin's own account_id in
    // session['impersonator'] first so AuthenticationController::return_to_admin()
    // can restore it — otherwise the admin would be stuck as the student
    // once their own session data is overwritten below.
    public function login_as_student($student_id = null)
    {
        if (!$student_id) {
            redirect('admin/students_by_section');
        }

        $user = $this->accounts->with_student()->get(['student_id' => $student_id, 'role' => 'student']);
        if (!$user) {
            $this->session->set_flashdata('error', 'This student has no login account to log in as.');
            redirect('admin/student_summary/' . $student_id);
        }

        $active_semester = $this->db->where('is_active', 1)->get('semester_master')->row_array();
        $enrollment = null;
        if ($active_semester) {
            $enrollment = $this->class_student->get([
                'student_id'  => $user->student_id,
                'semester_id' => $active_semester['trans_no'],
            ]);
        }

        if (!$this->session->userdata('impersonator')) {
            $this->session->set_userdata('impersonator', [
                'account_id' => $this->session->userdata('account_id'),
                'username'   => $this->session->userdata('username'),
            ]);
        }

        $this->session->set_userdata([
            'account_id'   => $user->account_id,
            'student_id'   => $user->student_id,
            'student_no'   => $user->student->student_no,
            'lastname'     => $user->student->lastname,
            'firstname'    => $user->student->firstname,
            'course'       => $user->student->course,
            'current_year' => $user->student->current_year,
            'section'      => $enrollment ? $enrollment->section : null,
            'role'         => $user->role,
            'username'     => $user->username,
            'profile_pic'  => $user->profile_pic,
            'online'       => true,
            'exam_term'    => false,
            'exam_review'  => false,
        ]);

        redirect($enrollment ? 'attendance' : 'student/add_section');
    }

    public function register_student()
    {
        $data['schedules'] = $this->class_schedule->get_all_active();
        $data['active_semester'] = $this->db->where('is_active', 1)->get('semester_master')->row_array();

        if ($this->input->post()) {
            $student_no = trim($this->input->post('student_no'));
            $lastname   = trim($this->input->post('lastname'));
            $firstname  = trim($this->input->post('firstname'));
            $middlename = trim($this->input->post('middlename'));
            $username   = trim($this->input->post('username'));
            $password   = $this->input->post('password');
            $confirm    = $this->input->post('confirm_password');

            if ($password !== $confirm) {
                $this->session->set_flashdata('error', 'Passwords do not match.');
                $this->load->view('admin/register_student', $data);
                return;
            }

            if ($this->db->where('student_no', $student_no)->count_all_results('student_master')) {
                $this->session->set_flashdata('error', "Student number {$student_no} is already registered.");
                $this->load->view('admin/register_student', $data);
                return;
            }

            if ($this->db->where('username', $username)->count_all_results('accounts')) {
                $this->session->set_flashdata('error', "Username \"{$username}\" is already taken.");
                $this->load->view('admin/register_student', $data);
                return;
            }

            if ($this->db->where('lastname', $lastname)
                         ->where('firstname', $firstname)
                         ->where('middlename', $middlename)
                         ->count_all_results('student_master')) {
                $this->session->set_flashdata('error', "A student named \"{$firstname} {$middlename} {$lastname}\" is already registered.");
                $this->load->view('admin/register_student', $data);
                return;
            }

            $student_data = [
                'student_no'    => $student_no,
                'lastname'      => $lastname,
                'firstname'     => $firstname,
                'middlename'    => $middlename,
                'extname'       => trim($this->input->post('extname')),
                'gender'        => $this->input->post('gender'),
                'birthday'      => $this->input->post('birthday') ?: null,
                'course'        => trim($this->input->post('course')),
                'current_year'  => (int)$this->input->post('current_year'),
                'year_section'  => trim($this->input->post('year_section')),
                'SY'            => trim($this->input->post('SY')),
                'contact_no'    => trim($this->input->post('contact_no')),
                'email'         => trim($this->input->post('email')),
                'allowed_to_enroll' => 'Y',
                'status'        => 'E',
                'created_dt'    => date('Y-m-d H:i:s'),
            ];
            $this->db->insert('student_master', $student_data);
            $student_id = $this->db->insert_id();

            $this->db->insert('accounts', [
                'student_id'  => $student_id,
                'username'    => $username,
                'password'    => password_hash($password, PASSWORD_DEFAULT),
                'role'        => 'student',
                'created_at'  => date('Y-m-d'),
            ]);

            $schedule_id = (int)$this->input->post('schedule_id');
            if ($schedule_id) {
                $sched = $this->db->where('schedule_id', $schedule_id)->get('class_schedule')->row_array();
                if ($sched) {
                    $sem_id = $data['active_semester'] ? $data['active_semester']['trans_no'] : $sched['semester_id'];
                    $this->db->insert('class_student', [
                        'student_id'  => $student_id,
                        'class_id'    => $sched['class_id'],
                        'schedule_id' => $sched['schedule_id'],
                        'section'     => $sched['section'],
                        'semester_id' => $sem_id,
                        'status'      => 'enrolled',
                        'is_cleared'  => 0,
                    ]);
                }
            }

            $this->session->set_flashdata('success', "Student {$firstname} {$lastname} registered successfully.");
            redirect('admin/register_student');
            return;
        }

        $this->load->view('admin/register_student', $data);
    }

    public function semesters()
    {
        $data['semesters'] = $this->db->order_by('trans_no', 'DESC')->get('semester_master')->result_array();
        $edit_id = $this->input->get('edit');
        $data['editing'] = null;
        if ($edit_id) {
            $data['editing'] = $this->db->where('trans_no', (int)$edit_id)->get('semester_master')->row_array();
        }
        $this->load->view('admin/semesters', $data);
    }

    public function save_semester()
    {
        $post        = $this->input->post();
        $trans_no    = !empty($post['trans_no']) ? (int)$post['trans_no'] : null;

        $data = [
            'semcode'      => trim($post['semcode']),
            'description'  => trim($post['description']),
            'semtype'      => (int)$post['semtype'],
            'semyear'      => (int)$post['semyear'],
            'class_started'=> $post['class_started'] ?: null,
            'passing_rate' => (int)$post['passing_rate'],
        ];

        if ($trans_no) {
            $this->db->where('trans_no', $trans_no)->update('semester_master', $data);
            $this->session->set_flashdata('success', 'Semester updated.');
        } else {
            $this->db->insert('semester_master', $data);
            $this->session->set_flashdata('success', 'Semester added.');
        }

        redirect('admin/semesters');
    }

    public function activate_semester($id)
    {
        $this->db->update('semester_master', ['is_active' => null]);
        $this->db->where('trans_no', (int)$id)->update('semester_master', ['is_active' => 1]);
        $this->session->set_flashdata('success', 'Semester activated. Students without an enrollment record for this semester will be prompted to enroll on next login.');
        redirect('admin/semesters');
    }

    public function check_student_no()
    {
        header('Content-Type: application/json');
        $student_no = $this->input->get('student_no');
        $exists = $student_no && $this->db->where('student_no', $student_no)->count_all_results('student_master') > 0;
        echo json_encode(['exists' => $exists]);
    }

    public function check_username()
    {
        header('Content-Type: application/json');
        $username = $this->input->get('username');
        $exists = $username && $this->db->where('username', $username)->count_all_results('accounts') > 0;
        echo json_encode(['exists' => $exists]);
    }

    public function student_requests()
    {
        $this->load->library('pagination');

        $status   = $this->input->get('status') ?: null;
        $type     = $this->input->get('type')   ?: null;
        $per_page = 15;
        $total    = $this->student_request->count_requests($status, $type);
        $offset   = (int)$this->input->get('per_page') ?: 0;

        $qs_parts = [];
        if ($status) $qs_parts[] = 'status=' . urlencode($status);
        if ($type)   $qs_parts[] = 'type='   . urlencode($type);
        $base_url = base_url('admin/student_requests') . '?' . ($qs_parts ? implode('&', $qs_parts) . '&' : '');

        $config = [
            'base_url'              => $base_url,
            'total_rows'            => $total,
            'per_page'              => $per_page,
            'page_query_string'     => TRUE,
            'query_string_segment'  => 'per_page',
            'reuse_query_string'    => TRUE,
            'use_page_numbers'      => FALSE,
            'full_tag_open'         => '<ul class="pagination pagination-sm mb-0">',
            'full_tag_close'        => '</ul>',
            'first_link'            => '&laquo;',
            'first_tag_open'        => '<li class="page-item">',
            'first_tag_close'       => '</li>',
            'last_link'             => '&raquo;',
            'last_tag_open'         => '<li class="page-item">',
            'last_tag_close'        => '</li>',
            'next_link'             => '&rsaquo;',
            'next_tag_open'         => '<li class="page-item">',
            'next_tag_close'        => '</li>',
            'prev_link'             => '&lsaquo;',
            'prev_tag_open'         => '<li class="page-item">',
            'prev_tag_close'        => '</li>',
            'num_tag_open'          => '<li class="page-item">',
            'num_tag_close'         => '</li>',
            'cur_tag_open'          => '<li class="page-item active"><a class="page-link" href="#">',
            'cur_tag_close'         => '</a></li>',
            'attributes'            => ['class' => 'page-link'],
            'num_links'             => 4,
        ];
        $this->pagination->initialize($config);

        $data['requests']        = $this->student_request->get_all_requests($status, $type, $per_page, $offset);
        $data['selected_status'] = $status;
        $data['selected_type']   = $type;
        $data['pagination']      = $this->pagination->create_links();
        $data['total']           = $total;
        $data['per_page']        = $per_page;
        $data['offset']          = $offset;
        $this->load->view('admin/student_requests', $data);
    }

    public function process_student_request()
    {
        $post        = $this->input->post();
        $request_id  = (int)($post['request_id'] ?? 0);
        $action      = $post['action'] ?? '';
        $admin_notes = trim($post['admin_notes'] ?? '');

        if (!$request_id || !in_array($action, ['approved', 'rejected'])) {
            $this->session->set_flashdata('error', 'Invalid request.');
            redirect('admin/student_requests');
            return;
        }

        $request = $this->db->get_where('student_requests', ['request_id' => $request_id])->row_array();
        if (!$request) {
            $this->session->set_flashdata('error', 'Request not found.');
            redirect('admin/student_requests');
            return;
        }

        $this->db->where('request_id', $request_id)->update('student_requests', [
            'status'      => $action,
            'admin_notes' => $admin_notes,
            'updated_at'  => date('Y-m-d H:i:s'),
        ]);

        if ($action === 'approved' && $request['type'] === 'absence') {
            $this->db
                ->where('student_id', $request['student_id'])
                ->where('schedule_id', $request['schedule_id'])
                ->where('DATE(date)', $request['request_date'])
                ->update('attendance', ['status' => 'excuse', 'reason' => $request['reason']]);
        }

        $this->session->set_flashdata('success', 'Request ' . $action . '.');
        redirect('admin/student_requests');
    }

    // ── Password Reset Requests ──────────────────────────────────────────────

    public function password_resets()
    {
        $this->password_reset_request->install();

        $status = $this->input->get('status') ?: null;
        $data['requests']        = $this->password_reset_request->get_all($status);
        $data['selected_status'] = $status;
        $this->load->view('admin/password_resets', $data);
    }

    public function process_password_reset()
    {
        $post        = $this->input->post();
        $request_id  = (int) ($post['request_id'] ?? 0);
        $action      = $post['action'] ?? '';
        $admin_notes = trim($post['admin_notes'] ?? '');

        if (!$request_id || !in_array($action, ['approved', 'rejected'])) {
            $this->session->set_flashdata('error', 'Invalid request.');
            redirect('admin/password_resets');
            return;
        }

        $request = $this->db->get_where('password_reset_requests', ['request_id' => $request_id])->row_array();
        if (!$request || $request['status'] !== 'pending') {
            $this->session->set_flashdata('error', 'Request not found or already processed.');
            redirect('admin/password_resets');
            return;
        }

        $update = [
            'status'      => $action,
            'admin_notes' => $admin_notes,
            'updated_at'  => date('Y-m-d H:i:s'),
        ];

        if ($action === 'approved') {
            $account = $this->db->get_where('accounts', ['student_id' => $request['student_id']])->row_array();
            if (!$account) {
                $this->session->set_flashdata('error', 'No account linked to that student.');
                redirect('admin/password_resets');
                return;
            }

            $default = $request['student_no'];

            // Default username = student number, unless another account already
            // uses it — then keep the existing username and reset the password only.
            $username_taken = $this->db
                ->where('username', $default)
                ->where('account_id !=', $account['account_id'])
                ->count_all_results('accounts') > 0;
            $new_username = $username_taken ? $account['username'] : $default;

            $this->db->where('account_id', $account['account_id'])->update('accounts', [
                'username'             => $new_username,
                'password'             => password_hash($default, PASSWORD_DEFAULT),
                'must_change_password' => 1,
            ]);

            $update['default_username'] = $new_username;
            $update['default_password'] = $default;
        }

        $this->db->where('request_id', $request_id)->update('password_reset_requests', $update);

        $this->session->set_flashdata('success', 'Password reset request ' . $action . '.');
        redirect('admin/password_resets');
    }

    // ── Discussion Management ────────────────────────────────────────────────

    public function manage_discussions()
    {
        $this->load->model('discussions');

        $filter_class_id = $this->input->get('class_id') ?: '';
        $filter_type      = $this->input->get('type') ?: '';
        $filter_q         = trim($this->input->get('q') ?? '');

        $this->db->select('*')->from('discussions');
        if ($filter_class_id !== '') {
            $this->db->where('class_id', (int) $filter_class_id);
        }
        if ($filter_type === 'static' || $filter_type === 'interactive') {
            $this->db->where('type', $filter_type);
        }
        if ($filter_q !== '') {
            $this->db->group_start()
                ->like('title', $filter_q)
                ->or_like('description', $filter_q)
                ->or_like('link', $filter_q)
                ->group_end();
        }
        $data['discussions'] = $this->db
            ->order_by('class_id', 'asc')
            ->order_by('type', 'asc')
            ->order_by('created_at', 'desc')
            ->get()
            ->result_array();

        $data['selected_class_id'] = $filter_class_id;
        $data['selected_type']     = $filter_type;
        $data['search_q']          = $filter_q;

        $data['classes'] = $this->db->order_by('class_id')->get('classes')->result_array();

        // Build topic list: slug, title, and section count from each JSON file
        $data['json_topics'] = [];
        foreach ($this->_glob_json_topics() as $f) {
            $slug = basename($f, '.json');
            $meta = json_decode(file_get_contents($f), true);
            $data['json_topics'][] = [
                'slug'     => $slug,
                'title'    => $meta['title'] ?? ucwords(str_replace('_', ' ', $slug)),
                'sections' => count($meta['sections'] ?? []),
            ];
        }
        usort($data['json_topics'], function($a, $b) { return strcmp($a['title'], $b['title']); });

        // Static topic files, grouped by subfolder (application/views/discussions/{folder}/*.php)
        $discussions_view_path = APPPATH . 'views/discussions/';
        $data['static_topics'] = [];
        foreach (glob($discussions_view_path . '*', GLOB_ONLYDIR) ?: [] as $dir) {
            $folder = basename($dir);
            $files = [];
            foreach (glob($dir . '/*.php') ?: [] as $f) {
                $base = basename($f, '.php');
                $files[] = [
                    'path'  => "DiscussionController/topic/{$folder}/{$base}",
                    'label' => $base,
                ];
            }
            usort($files, function($a, $b) { return strcmp($a['label'], $b['label']); });
            if ($files) {
                $data['static_topics'][$folder] = $files;
            }
        }
        ksort($data['static_topics']);

        $this->load->view('admin/manage_discussions', $data);
    }

    public function save_discussion()
    {
        $this->load->model('discussions');

        $id       = (int) $this->input->post('id');
        $type     = $this->input->post('type') === 'interactive' ? 'interactive' : 'static';
        $link     = trim($this->input->post('link') ?? '');
        $class_id = (int) $this->input->post('class_id');

        if ($type === 'interactive') {
            if ($this->input->post('json_source') === 'new') {
                $slug = $this->_save_pasted_topic_json(
                    $class_id,
                    trim($this->input->post('new_slug') ?? ''),
                    $this->input->post('json_text') ?? ''
                );
                if ($slug === false) {
                    redirect('AdminController/manage_discussions');
                    return;
                }
                $link = $slug;
            } else {
                // Existing topic — the link field holds the slug — strip any accidental path prefix
                $link = preg_replace('/[^a-z0-9_]/', '', strtolower($link));
            }
        }

        $row = [
            'class_id'     => $class_id,
            'type'         => $type,
            'title'        => trim($this->input->post('title')),
            'description'  => trim($this->input->post('description') ?? ''),
            'link'         => $link,
            'display_date' => $this->input->post('display_date') ?: null,
            'updated_at'   => date('Y-m-d H:i:s'),
        ];

        if ($id) {
            // MY_Model::update() takes (data, where) — NOT (where, data).
            $this->discussions->update($row, $id);
            $this->session->set_flashdata('success', 'Discussion updated.');
        } else {
            $row['created_at'] = date('Y-m-d H:i:s');
            $this->discussions->insert($row);
            $this->session->set_flashdata('success', 'Discussion added.');
        }

        redirect('AdminController/manage_discussions');
    }

    // JSON topic files live either directly in assets/json/ (legacy/unfiled)
    // or one level down in a class-code folder (assets/json/{CLASS_CODE}/) —
    // see _save_pasted_topic_json(). Topic slugs stay globally unique and
    // unaware of the folder, so callers just need every *.json under either.
    private function _glob_json_topics()
    {
        $json_path = FCPATH . 'assets/json/';
        $root      = glob($json_path . '*.json') ?: [];
        $nested    = glob($json_path . '*/*.json') ?: [];
        return array_merge($root, $nested);
    }

    // Class code a topic file belongs to, derived from its parent folder
    // under assets/json/ (see _save_pasted_topic_json(), which writes new
    // topics to assets/json/{CLASS_CODE}/). Returns '' for legacy/unfiled
    // files sitting directly in assets/json/, meaning "available to every class".
    private function _topic_class_code_from_path($file)
    {
        $json_path = rtrim(FCPATH . 'assets/json', '/\\');
        $parent    = rtrim(dirname($file), '/\\');
        return ($parent === $json_path) ? '' : basename($parent);
    }

    // Validates a pasted interactive-discussion JSON template and writes it to
    // assets/json/{CLASS_CODE}/{slug}.json (falls back to assets/json/{slug}.json
    // if the class can't be resolved). Returns the slug on success, or false
    // (with a flashdata error already set) on failure.
    private function _save_pasted_topic_json($class_id, $slug, $json_text)
    {
        $slug = preg_replace('/[^a-z0-9_]/', '', strtolower($slug));
        if (!preg_match('/^[a-z0-9_]{1,100}$/', $slug)) {
            $this->session->set_flashdata('error', 'Slug is required and may only contain lowercase letters, digits, and underscores.');
            return false;
        }

        $data = json_decode(trim($json_text), true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            $this->session->set_flashdata('error', 'Invalid JSON: ' . json_last_error_msg());
            return false;
        }

        $validation_error = $this->_validate_discussion_json($data);
        if ($validation_error) {
            $this->session->set_flashdata('error', $validation_error);
            return false;
        }

        $json_path = FCPATH . 'assets/json/';
        if (!is_writable($json_path)) {
            $this->session->set_flashdata('error', 'assets/json/ is not writable. Contact your administrator.');
            return false;
        }

        $dest_dir = $json_path;
        if ($class_id) {
            $class = $this->db->select('class_code')->where('class_id', $class_id)->get('classes')->row_array();
            if (!empty($class['class_code'])) {
                $folder = preg_replace('/[^A-Za-z0-9_-]/', '_', $class['class_code']);
                $candidate = $json_path . $folder . '/';
                if (is_dir($candidate) || @mkdir($candidate, 0775, true)) {
                    $dest_dir = $candidate;
                }
            }
        }

        $data['topic'] = $slug;
        $pretty = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

        $dest = $dest_dir . $slug . '.json';
        $overwrite = file_exists($dest);
        if (file_put_contents($dest, $pretty) === false) {
            $this->session->set_flashdata('error', 'Failed to save JSON file. Check directory permissions.');
            return false;
        }

        $this->session->set_flashdata('success', $overwrite
            ? "Topic file \"{$slug}.json\" overwritten."
            : "Topic file \"{$slug}.json\" created.");
        return $slug;
    }

    // Same schema InteractiveQuizController::discussion() renders:
    // sections[].quiz = { question, options[], correct (index), code? }
    private function _validate_discussion_json($data)
    {
        if (!is_array($data)) {
            return 'JSON must decode to an object.';
        }
        if (empty($data['title']) || !is_string($data['title'])) {
            return 'JSON must have a non-empty "title" string field.';
        }
        if (empty($data['sections']) || !is_array($data['sections'])) {
            return 'JSON must have a non-empty "sections" array.';
        }
        foreach ($data['sections'] as $i => $section) {
            $n = $i + 1;
            if (empty($section['title'])) {
                return "Section {$n} is missing a \"title\" field.";
            }
            if (!isset($section['lesson'])) {
                return "Section {$n} is missing a \"lesson\" field.";
            }
            if (!isset($section['quiz']) || $section['quiz'] === null) {
                continue;
            }
            if (!is_array($section['quiz']) || empty($section['quiz'])) {
                return "Section {$n} has an invalid \"quiz\" value; use null or omit it when there is no quiz.";
            }
            $q = $section['quiz'];
            if (empty($q['question'])) {
                return "Section {$n} quiz is missing a \"question\" field.";
            }
            if (empty($q['options']) || !is_array($q['options']) || count($q['options']) < 2) {
                return "Section {$n} quiz must have at least 2 \"options\".";
            }
            if (!isset($q['correct']) || !is_int($q['correct']) || $q['correct'] < 0 || $q['correct'] >= count($q['options'])) {
                return "Section {$n} quiz \"correct\" must be a valid option index.";
            }
        }
        return '';
    }

    public function delete_discussion($id)
    {
        if ($this->input->method() !== 'post') {
            redirect('AdminController/manage_discussions');
            return;
        }

        $this->load->model('discussions');
        $this->discussions->delete((int) $id);
        $this->session->set_flashdata('success', 'Discussion deleted.');
        redirect('AdminController/manage_discussions');
    }

    public function search_students()
    {
        header('Content-Type: application/json');
        $q      = $this->input->get('q');
        $search = $this->input->get('search');
        $term   = $q ?: $search;
        $results = [];

        if (!empty($term)) {
            /** @var CI_DB_query_builder $db */
            $db = $this->db;
            $db->select('trans_no, firstname, lastname');
            $db->like('firstname', $term);
            $db->or_like('lastname', $term);
            $db->or_like('trans_no', $term);
            $db->limit(20);
            $rows = $db->get('student_master')->result_array();

            if ($q) {
                foreach ($rows as $student) {
                    $results[] = [
                        'id'   => $student['trans_no'],
                        'text' => $student['firstname'] . ' ' . $student['lastname'] . ' (' . $student['trans_no'] . ')',
                    ];
                }
            } else {
                $results = $rows;
            }
        }

        echo json_encode($results);
    }
}
