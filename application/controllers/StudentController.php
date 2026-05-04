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

        if (empty($input['username'])) {
            $this->session->set_flashdata('error', 'Username is required.');
            redirect('update_account_form');
        }

        if (!empty($input['password']) && $input['password'] !== $input['confirm_password']) {
            $this->session->set_flashdata('error', 'Passwords do not match.');
            redirect('update_account_form');
        }

        $update_data = ['username' => $input['username']];

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
        $this->session->set_userdata('username', $input['username']);
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
        redirect('attendance');
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
