<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Main extends CI_Controller
{

    public function __construct()
    {
        parent::__construct();
        $this->load->model(['accounts', 'assessments', 'student_master', 'outputs', 'class_schedule', 'attendance']);
        $this->load->helper(['url']);
        $this->load->library(['session', 'upload']);
    }

    public function index()
    {
        $input_res = $this->assessments->where('status', 'active')->with_type()->with_subject()->get();

        if ($input_res) {
            $data = [
                'input_id' => $input_res->input_id,
                'description' => $input_res->description,
                'max_score' => $input_res->max_score,
                'type' => $input_res->type->type,
                'subject_code' => $input_res->subject->subject_code,
                'room' => $input_res->subject->room,
                'subject_title' => $input_res->subject->description,
                'section' => $input_res->subject->section,
                'year_level' => $input_res->subject->year_level,
                'schedule' => $input_res->subject->schedule,
                'no_active' => FALSE,
            ];

            $this->session->set_userdata($data);
        } else {
            $this->session->set_userdata(['no_active' => TRUE]);
        }

        $this->load->view('login');
    }

    public function find_id()
    {
        $this->load->view('find_id');
    }

    public function signup()
    {
        $this->load->view('signup');
    }

    public function student_info()
    {
        $this->load->view('student_info');
    }

    public function home()
    {
        $this->load->view('home');
    }

    public function output_upload()
    {
        $output = $this->outputs->where([
            'student_id'    => $_SESSION['student_id'],
            'input_id'      => $_SESSION['input_id'] ?? NULL
        ])->get();

        if (!$output) {
            $output = ['input_id' => FALSE];
        }

        $this->load->view('output_upload',  $output);
    }

    public function attendance_main()
    {

        date_default_timezone_set('Asia/Manila');
        $day = date('D');
        $date = date('Y-m-d');
        $class = $this->class_schedule->class_today($day);
        $student_id = $this->session->student_id;

        if (!$class) {
            $this->session->set_flashdata('error', 'No available class');
        } else {
            $check_student = $this->attendance->where([
                'student_id' => $student_id,
                'schedule_id' => $class['schedule_id'],
                'date like' => "$date%"
            ])->get();

            if (!$check_student) {
                $client_ip = $this->input->ip_address();

                $this->attendance->insert_data(
                    [
                        'schedule_id' => $class['schedule_id'],
                        'student_id' => $student_id,
                        'status' => "present",
                        'ip_address' =>  $client_ip
                    ]
                );
            }
        }

        $attendance_record = $this->attendance->get_student_attendance($student_id);

        $data = [
            "class" => $class,
            "record" => $attendance_record
        ];

        $this->load->view('attendance_view', $data);
    }

    public function add_inputs()
    {
        $this->load->view('add_inputs');
    }

    public function admin()
    {
        $this->load->view('admin');
    }

    public function login()
    {
        $post = $this->input->post();
        $user = $this->accounts->with_student()->get(['username' => $post['username']]);

        if ($user && $user->password == $post['password']) {

            $session_data = [
                'account_id' => $user->account_id,
                'student_id' => $user->student_id,
                'student_no' => $user->student->student_no,
                'lastname' => $user->student->lastname,
                'firstname' => $user->student->firstname,
                'course' => $user->student->course,
                'current_year' => $user->student->current_year,
            ];

            $this->session->set_userdata($session_data);

            redirect('attendance');
        } else {
            $this->session->set_flashdata('error', 'Login Error');
            redirect();
        }
    }

    public function logout()
    {
        $this->session->sess_destroy();
        redirect();
    }

    function generate_random_numbers($count = 10, $min = 1, $max = 9)
    {
        $random_numbers = '';
        for ($i = 0; $i < $count; $i++) {
            $random_numbers .= rand($min, $max);
        }
        return $random_numbers;
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

    public function signup_submit()
    {
        $input = $this->input->post();

        $student = [
            'student_no' => $this->generate_random_numbers(),
            'lastname' => strtoupper($input['lastname']),
            'firstname' => strtoupper($input['firstname']),
            'gender' => $input['gender'],
            'course' => 'BSIS',
            'current_year' => '1'
        ];

        $this->db->trans_start(); // Start transaction

        try {
            $this->student_master->insert($student);
            $trans_no = ($this->student_master->where('student_no', $student['student_no'])->get()->trans_no);

            $acc_data = [
                'student_id' => $trans_no,
                'username' => $input['username'],
                'password' => $input['password']
            ];

            $this->accounts->insert($acc_data);

            $this->db->trans_complete(); // Complete the transaction

            if ($this->db->trans_status() === FALSE) {
                // If the transaction failed
                throw new Exception('Transaction failed');
            }

            $this->session->set_flashdata('success', 'Signup Successful');
            redirect();
        } catch (Exception $e) {
            $this->db->trans_rollback(); // Rollback transaction
            $this->session->set_flashdata('error', 'Signup Error');

            redirect('signup');
            if ($e->getCode() == 1062) {
                $this->session->set_flashdata('error', 'Signup Error');

                redirect('signup');
                // Handle duplicate entry error
                echo 'Error: Duplicate entry for key.';
            } else {
                $this->session->set_flashdata('error', 'Signup Error');
                redirect('signup');
                echo 'Error: ' . $e->getMessage();
            }
        }

        $this->session->set_flashdata('error', 'Signup Error');
        redirect('signup');
    }


    public function input_submit()
    {
        $this->assessment->insert($this->input->post());
    }

    public function upload_activity()
    {
        $filename = implode('-', [$_SESSION['student_id'], $_SESSION['input_id'], $_SESSION['account_id'], $_SESSION['section']]);

        $config['upload_path']          = './uploads/outputs';
        $config['allowed_types']        = 'gif|jpg|jpeg|png';
        $config['max_size']             = 51200; // 50MB
        // $config['encrypt_name']      = TRUE; // Encrypt the file name for security
        $config['file_name']            = $filename;

        $this->upload->initialize($config);


        if (!$this->upload->do_upload('photo-upload')) {
            $error = array('error' => $this->upload->display_errors());
            redirect('output_upload');
        } else {
            $upload_data = $this->upload->data();
            $score = $this->input->post('score');

            $insert_data = [
                'student_id' => $_SESSION['student_id'],
                'input_id' => $_SESSION['input_id'],
                'score' =>  $score,
                'file_upload' => $upload_data['file_name'],
            ];

            $this->outputs->insert($insert_data);

            redirect('output_upload');
        }
    }

    public function assessment_view()
    {
        $this->load->view('assessment_view_code');
    }
}
