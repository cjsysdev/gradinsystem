<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Main extends CI_Controller
{

    public function __construct()
    {
        parent::__construct();
        $this->load->model(['accounts', 'assessments', 'student_master', 'classworks', 'class_schedule', 'attendance', 'class_student']);
        $this->load->helper(['url']);
        $this->load->library(['session', 'upload']);
    }

    public function index()
    {
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
            'student_no' => generate_random_numbers(),
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

    public function student_submissions()
    {
        $this->load->view('student_submissions');
    }

    public function view($classwork_id)
    {
        $classwork = $this->assessments->as_array()->get($classwork_id);

        if (!$classwork) {
            show_404();
        }

        $data = [
            'classwork' =>  $classwork
        ];

        $this->load->view('assessment_view_code', $data);
    }

    public function classwork()
    {

        $student_id = $this->session->student_id;
        $student = $this->class_student->where('student_id', $student_id)->get();

        if (!$student) {
            $this->session->set_flashdata('error', 'Student section not found');
            redirect('attendance');
        }

        $missing = $this->assessments->get_students_assessments(
            $student_id,
            $student->section
        );

        $submmitted = $this->assessments->get_submmited_assessments(
            $student_id
        );

        $data = [
            'assessments' => $missing,
            'submitted' => $submmitted
        ];

        $this->load->view('classwork', $data);
    }

    public function submit_classwork()
    {

        $post = $this->input->post();
        $value = $this->classworks->where(
            [
                'student_id' => $this->session->student_id,
                'assessment_id' => $post['assessment_id']
            ]
        )->get();

        if (!$value) {
            $this->classworks->insert($post);
            $this->session->set_flashdata('success', 'Classwork submitted successfully');
        } else {
            $this->session->set_flashdata('warning', 'Classwork already submitted');
        }

        redirect('classwork');
    }

    public function start_class()
    {
        $schedule_id = 1;
        $section = "1C";
        $this->attendance->start_class($schedule_id, $section, get_date_today());
    }
}
