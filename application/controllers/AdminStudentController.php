<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Student records: registration, emergency contacts, violations, clearance,
 * semesters, and the request/password-reset queues.
 *
 * Split out of AdminController; see Admin_Controller in
 * application/core/MY_Controller.php.
 */
class AdminStudentController extends Admin_Controller
{
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
        // Anything that isn't an admin is a student: most accounts predate the
        // signup/register flows that set role='student' explicitly and carry an
        // empty role instead. AuthenticationController::login() treats them the
        // same way (only 'admin' is special-cased), so requiring the literal
        // string here hid this button for every bulk-imported section.
        $data['has_account']  = $account && $account['role'] !== 'admin';
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

        // Match on the student alone, then rule out admins — see the note in
        // student_summary(): filtering on role='student' only ever matched the
        // accounts created through signup/register, not the bulk-imported ones
        // whose role is empty, so impersonation failed for whole sections.
        $user = $this->accounts->with_student()->get(['student_id' => $student_id]);
        if (!$user || $user->role === 'admin') {
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
}
