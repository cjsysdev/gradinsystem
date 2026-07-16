<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Main extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        date_default_timezone_set('Asia/Manila');
        $this->load->helper(['url']);
        $this->load->library(['session', 'upload']);
    }

    public function index()
    {
        $this->load->view('login');
    }

    public function test()
    {
        $this->load->view('test');
    }

    public function signup()
    {
        $this->load->view('signup');
    }

    public function signup_submit()
    {
        $post = $this->input->post();
        // Handle signup logic here (e.g., save user data to the database)
        $this->session->set_flashdata('success', 'Signup successful!');
        redirect('signup');
    }

    public function input_submit()
    {
        $post = $this->input->post();
        // Handle input submission logic here (e.g., save data to the database)
        $this->session->set_flashdata('success', 'Input submitted successfully!');
        redirect('test');
    }

    public function output_upload()
    {
        $this->load->view('output_upload');
    }

    public function register()
    {
        $data['schedules'] = $this->class_schedule->get_all_active();
        $data['active_semester'] = $this->db->where('is_active', 1)->get('semester_master')->row_array();

        if ($this->input->post()) {
            $lastname   = trim($this->input->post('lastname'));
            $firstname  = trim($this->input->post('firstname'));
            $middlename = trim($this->input->post('middlename'));
            $username   = trim($this->input->post('username'));
            $password   = $this->input->post('password');
            $confirm    = $this->input->post('confirm_password');

            if ($password !== $confirm) {
                $this->session->set_flashdata('error', 'Passwords do not match.');
                $this->load->view('register', $data);
                return;
            }

            if ($this->db->where('username', $username)->count_all_results('accounts')) {
                $this->session->set_flashdata('error', "Username \"{$username}\" is already taken.");
                $this->load->view('register', $data);
                return;
            }

            if ($this->db->where('lastname', $lastname)
                         ->where('firstname', $firstname)
                         ->where('middlename', $middlename)
                         ->count_all_results('student_master')) {
                $this->session->set_flashdata('error', "A student named \"{$firstname} {$middlename} {$lastname}\" is already registered.");
                $this->load->view('register', $data);
                return;
            }

            $temp_no = $this->generate_next_temp_no();

            $student_data = [
                'temp_no'       => $temp_no,
                'student_no'    => $temp_no,
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

            $this->session->set_flashdata('success', "Registration successful! You may now log in.");
            redirect('register');
            return;
        }

        $this->load->view('register', $data);
    }

    private function generate_next_temp_no()
    {
        $row = $this->db
            ->query("SELECT MAX(CAST(REPLACE(temp_no, 'TMP', '') AS UNSIGNED)) AS max_seq FROM student_master WHERE temp_no LIKE '%TMP'")
            ->row_array();
        $next = (int)($row['max_seq'] ?? 0) + 1;
        return str_pad($next, 6, '0', STR_PAD_LEFT) . 'TMP';
    }

    public function check_username_public()
    {
        header('Content-Type: application/json');
        $username = $this->input->get('username');
        $exists = $username && $this->db->where('username', $username)->count_all_results('accounts') > 0;
        echo json_encode(['exists' => $exists]);
    }
}
