<?php
defined('BASEPATH') or exit('No direct script access allowed');

class StudentController extends CI_Controller
{
    public function student_info()
    {
        $this->load->view('student_info');
    }

    public function get_id()
    {
        $input = $this->input->post();
        $student = $this->student_master->where([
            'lastname' => $input['lastname'],
            'firstname' => $input['firstname']
        ])->get();

        if (!$student) {
            $this->session->set_flashdata('error', 'Student not found');
            redirect('find_id');
        }

        $this->load->view('student_details', $student);
    }

    public function find_id()
    {
        $this->load->view('find_id');
    }

    public function update_account()
    {
        $input = $this->input->post();

        if ($this->session->student_id != $input['student_id']) {
            $this->session->set_flashdata('error', 'You are not authorized to update this account.');
            redirect('update_account_form');
        }

        $username = !empty($input['username']) ? $input['username'] : $this->session->username;

        if (!empty($input['password']) && $input['password'] !== $input['confirm_password']) {
            $this->session->set_flashdata('error', 'Passwords do not match.');
            redirect('update_account_form');
        }

        $update_data = ['username' => $username];

        if (!empty($input['password'])) {
            $update_data['password'] = $input['password'];
        }

        // Handle profile picture upload
        $profile_pic = null;
        if (!empty($_FILES['profile_pic']['name']) && $_FILES['profile_pic']['error'] === UPLOAD_ERR_OK) {
            $upload_dir = realpath(FCPATH . 'uploads' . DIRECTORY_SEPARATOR . 'profile_pics');
            if (!$upload_dir) {
                mkdir(FCPATH . 'uploads' . DIRECTORY_SEPARATOR . 'profile_pics', 0755, true);
                $upload_dir = realpath(FCPATH . 'uploads' . DIRECTORY_SEPARATOR . 'profile_pics');
            }

            // Validate MIME type
            $finfo    = new finfo(FILEINFO_MIME_TYPE);
            $mime     = $finfo->file($_FILES['profile_pic']['tmp_name']);
            $allowed  = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
            if (!in_array($mime, $allowed)) {
                $this->session->set_flashdata('error', 'Only JPG, PNG, GIF, or WEBP images are allowed.');
                redirect('update_account_form');
            }

            // Validate file size (10 MB max)
            if ($_FILES['profile_pic']['size'] > 10 * 1024 * 1024) {
                $this->session->set_flashdata('error', 'Image must be 10 MB or smaller.');
                redirect('update_account_form');
            }

            $ext       = pathinfo($_FILES['profile_pic']['name'], PATHINFO_EXTENSION) ?: 'jpg';
            $filename  = 'student_' . $input['student_id'] . '_' . time() . '.' . strtolower($ext);
            $full_path = $upload_dir . DIRECTORY_SEPARATOR . $filename;

            if (!move_uploaded_file($_FILES['profile_pic']['tmp_name'], $full_path)) {
                $this->session->set_flashdata('error', 'Could not save the uploaded file.');
                redirect('update_account_form');
            }

            // Center-crop to square, then resize to 300×300 at quality 80
            list($orig_w, $orig_h) = getimagesize($full_path);
            $square   = min($orig_w, $orig_h);
            $x_offset = (int)(($orig_w - $square) / 2);
            $y_offset = (int)(($orig_h - $square) / 2);

            $this->load->library('image_lib');

            $this->image_lib->initialize([
                'image_library' => 'gd2',
                'source_image'  => $full_path,
                'x_axis'        => $x_offset,
                'y_axis'        => $y_offset,
                'width'         => $square,
                'height'        => $square,
            ]);
            $this->image_lib->crop();
            $this->image_lib->clear();

            $this->image_lib->initialize([
                'image_library'  => 'gd2',
                'source_image'   => $full_path,
                'width'          => 300,
                'height'         => 300,
                'maintain_ratio' => FALSE,
                'quality'        => 80,
            ]);
            $this->image_lib->resize();
            $this->image_lib->clear();

            $profile_pic = $filename;
            $update_data['profile_pic'] = $profile_pic;
        }

        $this->db->where('student_id', $input['student_id']);
        $this->db->update('accounts', $update_data);

        // Refresh session data
        $this->session->set_userdata('username', $username);
        if ($profile_pic) {
            $this->session->set_userdata('profile_pic', $profile_pic);
        }

        $this->session->set_flashdata('success', 'Account updated successfully.');
        redirect('update_account_form');
    }

    public function update_account_form()
    {
        $this->load->view('update_account_form');
    }

    // Method to get the current discussion mode
    public function get_discussion_mode()
    {
        // Load the database library
        $this->load->database();

        // Query the database for the discussion mode
        $query = $this->db->get_where('global_settings', [
            'setting_key' => 'discussion_mode',
        ]);

        // Check if the setting exists and return the value
        if ($query->num_rows() > 0) {
            $discussion_mode = $query->row()->setting_value === '1';
            echo json_encode(['discussion_mode' => $discussion_mode]);
        } else {
            echo json_encode(['error' => 'Discussion mode setting not found.']);
        }
    }

    public function get_exam_mode()
    {
        // Load the database library
        $this->load->database();

        // Query the database for the discussion mode
        $query = $this->db->get_where('global_settings', [
            'setting_key' => 'exam_mode',
        ]);

        // Check if the setting exists and return the value
        if ($query->num_rows() > 0) {
            $exam_mode = $query->row()->setting_value === '1';
            echo json_encode(['exam_mode' => $exam_mode]);
        } else {
            echo json_encode(['error' => 'Discussion mode setting not found.']);
        }
    }

    public function add_section()
    {
        $student_id = $this->session->student_id;

        $active_semester = $this->db->where('is_active', 1)->get('semester_master')->row_array();

        if (!$active_semester) {
            $this->session->set_flashdata('error', 'No active semester found. Please contact your administrator.');
            redirect('attendance');
            return;
        }

        $already_enrolled = $this->db
            ->where('student_id', $student_id)
            ->where('semester_id', $active_semester['trans_no'])
            ->count_all_results('class_student') > 0;

        if ($already_enrolled) {
            redirect('attendance');
            return;
        }

        $data['active_semester'] = $active_semester;
        $data['schedules'] = $this->class_schedule->get_all_active();
        $this->load->view('add_section', $data);
    }

    public function section()
    {
        $post = $this->input->post();
        $student_id = $this->session->student_id;

        $schedule_id = (int)($post['schedule_id'] ?? 0);

        if (!$schedule_id) {
            $this->session->set_flashdata('error', 'Please select a class/section.');
            redirect('student/add_section');
            return;
        }

        $sched = $this->db->where('schedule_id', $schedule_id)->get('class_schedule')->row_array();
        if (!$sched) {
            $this->session->set_flashdata('error', 'Invalid class selected.');
            redirect('student/add_section');
            return;
        }

        $active_semester = $this->db->where('is_active', 1)->get('semester_master')->row_array();
        $semester_id = $active_semester ? $active_semester['trans_no'] : $sched['semester_id'];

        $this->class_student->re_enroll($student_id, $sched['class_id'], $sched['section'], $semester_id);
        $this->session->set_flashdata('success', 'Enrolled successfully.');
        redirect();
    }

    public function emergency_contacts()
    {
        $student_id = $this->session->student_id;
        $data['contacts'] = $this->emergency_contact->get_by_student($student_id);
        $this->load->view('emergency_contacts', $data);
    }

    public function save_emergency_contact()
    {
        $student_id = $this->session->student_id;
        $input = $this->input->post();

        if (empty($input['full_name']) || empty($input['relationship']) || empty($input['contact_no'])) {
            $this->session->set_flashdata('error', 'Name, relationship, and contact number are required.');
            redirect('emergency_contacts');
        }

        $is_primary = isset($input['is_primary']) ? 1 : 0;

        if ($is_primary) {
            $this->db->where('student_id', $student_id)
                     ->update('student_emergency_contacts', ['is_primary' => 0]);
        }

        $this->db->insert('student_emergency_contacts', [
            'student_id'   => $student_id,
            'full_name'    => $input['full_name'],
            'relationship' => $input['relationship'],
            'contact_no'   => $input['contact_no'],
            'email'        => $input['email'] ?? null,
            'address'      => $input['address'] ?? null,
            'is_primary'   => $is_primary,
        ]);

        $this->session->set_flashdata('success', 'Emergency contact added.');
        redirect('emergency_contacts');
    }

    public function delete_emergency_contact($contact_id)
    {
        $student_id = $this->session->student_id;

        $contact = $this->db->get_where('student_emergency_contacts', [
            'contact_id' => $contact_id,
            'student_id' => $student_id,
        ])->row_array();

        if (!$contact) {
            $this->session->set_flashdata('error', 'Contact not found.');
            redirect('emergency_contacts');
        }

        $this->db->where('contact_id', $contact_id)->delete('student_emergency_contacts');
        $this->session->set_flashdata('success', 'Contact removed.');
        redirect('emergency_contacts');
    }

    public function set_primary_contact($contact_id)
    {
        $student_id = $this->session->student_id;

        $contact = $this->db->get_where('student_emergency_contacts', [
            'contact_id' => $contact_id,
            'student_id' => $student_id,
        ])->row_array();

        if (!$contact) {
            $this->session->set_flashdata('error', 'Contact not found.');
            redirect('emergency_contacts');
        }

        $this->emergency_contact->set_primary($contact_id, $student_id);
        $this->session->set_flashdata('success', 'Primary contact updated.');
        redirect('emergency_contacts');
    }

    public function requests()
    {
        $student_id = $this->session->student_id;

        $schedules = $this->db
            ->select('cs.schedule_id, cs.section, cs.day, cs.time_start, cs.time_end, c.class_code, c.class_name')
            ->from('class_student cls')
            ->join('class_schedule cs', 'cls.section = cs.section')
            ->join('classes c', 'cs.class_id = c.class_id')
            ->join('semester_master sem', 'cs.semester_id = sem.trans_no')
            ->where('cls.student_id', $student_id)
            ->where('sem.is_active', 1)
            ->get()->result_array();

        $data['schedules'] = $schedules;
        $data['requests']  = $this->student_request->get_student_requests($student_id, 'absence');
        $data['passes']    = $this->student_request->get_student_requests($student_id, 'pass');
        $this->load->view('requests', $data);
    }

    public function advance_excuse()
    {
        $student_id = $this->session->student_id;

        $schedules = $this->db
            ->select('cs.schedule_id, cs.section, cs.day, cs.time_start, cs.time_end, c.class_code, c.class_name')
            ->from('class_student cls')
            ->join('class_schedule cs', 'cls.section = cs.section')
            ->join('classes c', 'cs.class_id = c.class_id')
            ->join('semester_master sem', 'cs.semester_id = sem.trans_no')
            ->where('cls.student_id', $student_id)
            ->where('sem.is_active', 1)
            ->get()->result_array();

        $data['schedules'] = $schedules;
        $data['requests'] = $this->student_request->get_student_requests($student_id, 'absence');
        $this->load->view('advance_excuse', $data);
    }

    public function submit_advance_excuse()
    {
        $student_id = $this->session->student_id;
        $post = $this->input->post();

        $schedule_id  = (int)($post['schedule_id'] ?? 0);
        $request_date = $post['request_date'] ?? '';
        $reason       = trim($post['reason'] ?? '');

        if (!$schedule_id || !$request_date || !$reason) {
            $this->session->set_flashdata('error', 'All fields are required.');
            redirect('requests');
            return;
        }

        if ($request_date < date('Y-m-d', strtotime('-7 days'))) {
            $this->session->set_flashdata('error', 'Excuse date cannot be more than 7 days in the past.');
            redirect('requests');
            return;
        }

        if ($this->student_request->has_duplicate($student_id, $schedule_id, $request_date, 'absence')) {
            $this->session->set_flashdata('error', 'You already have a pending or approved excuse for this class on that date.');
            redirect('requests');
            return;
        }

        $this->db->insert('student_requests', [
            'type'         => 'absence',
            'student_id'   => $student_id,
            'schedule_id'  => $schedule_id,
            'request_date' => $request_date,
            'reason'       => $reason,
            'status'       => 'pending',
            'created_at'   => date('Y-m-d H:i:s'),
        ]);

        $this->session->set_flashdata('success', 'Excuse request submitted successfully.');
        redirect('requests');
    }

    public function cancel_advance_excuse($request_id)
    {
        $student_id = $this->session->student_id;

        $request = $this->db->get_where('student_requests', [
            'request_id' => $request_id,
            'student_id' => $student_id,
            'status'     => 'pending',
        ])->row_array();

        if (!$request) {
            $this->session->set_flashdata('error', 'Request not found or cannot be cancelled.');
            redirect('requests');
            return;
        }

        $this->db->where('request_id', $request_id)->delete('student_requests');
        $this->session->set_flashdata('success', 'Request cancelled.');
        redirect('requests');
    }

    public function leaving_pass()
    {
        $student_id = $this->session->student_id;

        $schedules = $this->db
            ->select('cs.schedule_id, cs.section, cs.day, cs.time_start, cs.time_end, c.class_code, c.class_name')
            ->from('class_student cls')
            ->join('class_schedule cs', 'cls.section = cs.section')
            ->join('classes c', 'cs.class_id = c.class_id')
            ->join('semester_master sem', 'cs.semester_id = sem.trans_no')
            ->where('cls.student_id', $student_id)
            ->where('sem.is_active', 1)
            ->get()->result_array();

        $data['schedules'] = $schedules;
        $data['passes'] = $this->student_request->get_student_requests($student_id, 'pass');
        $this->load->view('leaving_pass', $data);
    }

    public function submit_leaving_pass()
    {
        $student_id = $this->session->student_id;
        $post = $this->input->post();

        $schedule_id = (int)($post['schedule_id'] ?? 0);
        $request_date = $post['request_date'] ?? '';
        $reason       = trim($post['reason'] ?? '');

        if (!$schedule_id || !$request_date || !$reason) {
            $this->session->set_flashdata('error', 'All fields are required.');
            redirect('requests');
            return;
        }

        if ($request_date < date('Y-m-d')) {
            $this->session->set_flashdata('error', 'Pass date cannot be in the past.');
            redirect('requests');
            return;
        }

        if ($request_date > date('Y-m-d', strtotime('+30 days'))) {
            $this->session->set_flashdata('error', 'Pass date cannot be more than 30 days in the future.');
            redirect('requests');
            return;
        }

        if ($this->student_request->has_duplicate($student_id, $schedule_id, $request_date, 'pass')) {
            $this->session->set_flashdata('error', 'You already have a pending or approved leaving pass for this class on that date.');
            redirect('requests');
            return;
        }

        $this->db->insert('student_requests', [
            'type'         => 'pass',
            'student_id'   => $student_id,
            'schedule_id'  => $schedule_id,
            'request_date' => $request_date,
            'reason'       => $reason,
            'status'       => 'pending',
            'created_at'   => date('Y-m-d H:i:s'),
        ]);

        $this->session->set_flashdata('success', 'Leaving pass request submitted successfully.');
        redirect('requests');
    }

    public function cancel_leaving_pass($pass_id)
    {
        $student_id = $this->session->student_id;

        $pass = $this->db->get_where('student_requests', [
            'request_id' => $pass_id,
            'student_id' => $student_id,
            'status'     => 'pending',
        ])->row_array();

        if (!$pass) {
            $this->session->set_flashdata('error', 'Request not found or cannot be cancelled.');
            redirect('requests');
            return;
        }

        $this->db->where('request_id', $pass_id)->delete('student_requests');
        $this->session->set_flashdata('success', 'Request cancelled.');
        redirect('requests');
    }

    public function performance_sheet()
    {
        $student_id = $this->session->student_id;

        $attendance_record = $this->attendance->where(['status' => 'absent'])->get_all(
            ['student_id' => $student_id]
        );

        $classworks = $this->classworks->get_submissions_by_student($student_id);

        foreach ($classworks as $submission) {
            if ($submission['iotype_id'] == 4) {
                $quiz['each_score'][] =  $submission['score'];
                $quiz['score'] += $submission['score'];
                $quiz['max'] += $submission['max_score'];
            }

            if ($submission['iotype_id'] == 3) {
                $exam['score'] =  $submission['score'];
                $exam['max'] = $submission['max_score'];
            }

            if ($submission['iotype_id'] == 2) {
                $project['score'] =  $submission['score'];
                $project['max'] = $submission['max_score'];
            }

            if ($submission['iotype_id'] == 1) {
                $activity['score'] +=  $submission['score'];
                $activity['max'] += $submission['max_score'];
            }
        }

        $data = [
            'exam' => $exam,
            'quiz' => $quiz,
            'project' => $project,
            'activity' => $activity
        ];

        $this->load->view('performance_sheet', $data);
    }
}
