<?php
defined('BASEPATH') or exit('No direct script access allowed');

class AdminController extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
    }

    public function dashboard()
    {
        $class = $this->class_schedule->class_today(date('D'));

        if (!$class) {
            $data['attendance'] = [];
            $data['lates'] = [];
            $data['discussion_mode'] = false;
            $this->load->view('admin/dashboard', $data);
            return;
        }

        // Get the current discussion mode from the database
        $query = $this->db->get_where('global_settings', [
            'setting_key' => 'discussion_mode',
        ]);
        $data['discussion_mode'] = $query->row()->setting_value === '1';

        $data['attendance'] = $this->attendance->get_double_entry(date('Y-m-d'), $class['schedule_id']);
        $data['lates'] = $this->attendance->get_student_status($class['schedule_id'], date('Y-m-d'), 'late');
        $data['absents'] = $this->attendance->get_student_status($class['schedule_id'], date('Y-m-d'), 'absent');

        $this->load->view('admin/dashboard', $data);
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

        $term = 'midterm';

        $data['assessments'] = $this->assessments->get_for_schedule($class['schedule_id']);

        // Fetch submissions for the selected assessment
        if ($assessment_id) {
            $data['submissions'] = $this->classworks->get_all_submissions(
                $assessment_id
            );
            $data['selected_assessment_id'] = $assessment_id;
        } else {
            $data['submissions'] = [];
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

        $data['assessments'] = $this->db->get('assessments')->result_array();
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

        if ($student_id) {
            $data['student'] = $this->student_master->get_student_info($student_id);
            if ($data['student']) {
                $data['contacts'] = $this->emergency_contact->get_by_student($student_id);
                $data['total']    = count($data['contacts']);
            } else {
                $this->session->set_flashdata('error', 'Student not found.');
            }
        } else {
            $per_page = 20;
            $offset   = (int)($this->input->get('per_page') ?? 0);
            $total    = $this->emergency_contact->count_all_contacts();

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

            $data['contacts']   = $this->emergency_contact->get_all_paged($per_page, $offset);
            $data['pagination'] = $this->pagination->create_links();
            $data['total']      = $total;
            $data['per_page']   = $per_page;
            $data['offset']     = $offset;
        }

        $this->load->view('admin/emergency_contacts', $data);
    }

    public function view_attendance()
    {
        $section_id = $this->input->get('section_id');
        $start_date = $this->input->get('start_date');

        // Fetch all sections for the dropdown
        $data['sections'] = $this->class_schedule->get_sections();

        // Fetch attendance data if section and date are provided
        if ($section_id && $start_date) {
            $data['attendance'] = $this->attendance->get_attendance_by_section($section_id, $start_date);
            $data['selected_section_id'] = $section_id;
            $data['start_date'] = $start_date;
        } else {
            $data['attendance'] = [];
            $data['selected_section_id'] = null;
            $data['start_date'] = null;
        }

        $this->load->view('admin/view_attendance', $data);
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

    public function uncleared_students($section)
    {
        $this->load->model('class_student');
        $data['students'] = $this->class_student->get_uncleared_students_by_section($section);
        $data['section'] = $section;

        // var_dump($data);
        $this->load->view('admin/uncleared_students', $data);
    }

    public function clear_student($id, $section)
    {
        $this->load->model('class_student');
        $this->class_student->clear_student($id);
        redirect('AdminController/uncleared_students/' . $section);
    }

    public function manage_assessments()
    {
        $schedule_id = $this->input->get('schedule_id');
        $data['assessments'] = $this->assessments->get_all_for_admin($schedule_id ?: null);
        $data['schedules'] = $this->class_schedule->get_all_active();
        $data['io_types'] = $this->db->get('io_type')->result_array();
        $data['selected_schedule'] = $schedule_id;
        $this->load->view('admin/manage_assessments', $data);
    }

    public function save_assessment()
    {
        $post = $this->input->post();
        $assessment_id = !empty($post['assessment_id']) ? (int)$post['assessment_id'] : null;

        $status = isset($post['status']) ? $post['status'] : 0;
        if ($status === 'open' || $status === 'closed') {
            $status = $status === 'open' ? '1' : '0';
        }

        $data = [
            'iotype_id'    => $post['iotype_id'],
            'schedule_id'  => $post['schedule_id'],
            'title'        => $post['title'],
            'description'  => $post['description'],
            'max_score'    => $post['max_score'],
            'due'          => $post['due'],
            'term'         => $post['term'],
            'status'       => (int)$status,
            'is_groupings' => !empty($post['is_groupings']) ? 1 : 0,
        ];

        if ($assessment_id) {
            $this->assessments->update($assessment_id, $data);
            $this->session->set_flashdata('success', 'Assessment updated successfully.');
        } else {
            $data['created_at'] = date('Y-m-d H:i:s');
            $this->assessments->insert($data);
            $this->session->set_flashdata('success', 'Assessment added successfully.');
        }

        $qs = !empty($post['schedule_id']) ? '?schedule_id=' . $post['schedule_id'] : '';
        redirect('manage_assessments' . $qs);
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

        $this->db->where('assessment_id', $assessment_id)->update('assessments', ['status' => (int)$status]);
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
        $result = $this->classworks->add_score($classwork_id, $score);
        echo json_encode(['success' => $result]);
    }

    public function add_rand_score_incremental($classwork_id)
    {
        $result = $this->db->query(
            "UPDATE classworks c
             JOIN assessments a ON a.assessment_id = c.assessment_id
             SET c.score = LEAST(COALESCE(c.score, 0) + 2, a.max_score)
             WHERE c.classwork_id = ?",
            [$classwork_id]
        );
        echo json_encode(['success' => (bool)$result]);
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
        $section = $this->input->get('section');
        $data['sections'] = $this->class_schedule->get_sections();
        $data['selected_section'] = $section;
        $data['students'] = [];

        if ($section) {
            $this->load->model('class_student');
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
        $data['attendance']   = $this->student_master->get_attendance_summary($student_id);
        $data['classworks']   = $this->classworks->get_submissions_by_student($student_id);
        $data['violations']   = $this->violation->get_all_violations(['student_id' => $student_id]);
        $data['vio_summary']  = $this->violation->get_violation_summary_by_student($student_id);
        $data['contacts']     = $this->emergency_contact->get_by_student($student_id);

        $this->load->view('admin/student_summary', $data);
    }

    public function register_student()
    {
        $data['schedules'] = $this->class_schedule->get_all_active();
        $data['active_semester'] = $this->db->where('is_active', 1)->get('semester_master')->row_array();

        if ($this->input->post()) {
            $student_no = trim($this->input->post('student_no'));
            $lastname   = trim($this->input->post('lastname'));
            $firstname  = trim($this->input->post('firstname'));
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

            $student_data = [
                'student_no'    => $student_no,
                'lastname'      => $lastname,
                'firstname'     => $firstname,
                'middlename'    => trim($this->input->post('middlename')),
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
                'password'    => $password,
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

    public function advance_excuses()
    {
        $this->load->library('pagination');

        $status    = $this->input->get('status') ?: null;
        $per_page  = 15;
        $total     = $this->advance_excuse->count_requests($status);
        $cur_page  = max(1, (int)$this->input->get('per_page') ?: 1);
        // CI query-string pagination passes the offset via the per_page key
        $offset    = (int)$this->input->get('per_page') ?: 0;

        $base_url = base_url('admin/advance_excuses') . '?'
            . ($status ? 'status=' . urlencode($status) . '&' : '');

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

        $data['requests']        = $this->advance_excuse->get_all_requests($status, $per_page, $offset);
        $data['selected_status'] = $status;
        $data['pagination']      = $this->pagination->create_links();
        $data['total']           = $total;
        $data['per_page']        = $per_page;
        $data['offset']          = $offset;
        $this->load->view('admin/advance_excuses', $data);
    }

    public function process_excuse()
    {
        $post        = $this->input->post();
        $request_id  = (int)($post['request_id'] ?? 0);
        $action      = $post['action'] ?? '';
        $admin_notes = trim($post['admin_notes'] ?? '');

        if (!$request_id || !in_array($action, ['approved', 'rejected'])) {
            $this->session->set_flashdata('error', 'Invalid request.');
            redirect('admin/advance_excuses');
            return;
        }

        $request = $this->db->get_where('advance_excuse_requests', ['request_id' => $request_id])->row_array();
        if (!$request) {
            $this->session->set_flashdata('error', 'Request not found.');
            redirect('admin/advance_excuses');
            return;
        }

        $this->db->where('request_id', $request_id)->update('advance_excuse_requests', [
            'status'      => $action,
            'admin_notes' => $admin_notes,
            'updated_at'  => date('Y-m-d H:i:s'),
        ]);

        if ($action === 'approved') {
            $this->db
                ->where('student_id', $request['student_id'])
                ->where('schedule_id', $request['schedule_id'])
                ->where('DATE(date)', $request['absence_date'])
                ->update('attendance', ['status' => 'excuse', 'reason' => $request['reason']]);
        }

        $this->session->set_flashdata('success', 'Request ' . $action . '.');
        redirect('admin/advance_excuses');
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
