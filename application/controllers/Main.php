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
            //insert data of students per subject
            $this->attendance->start_class($class['schedule_id'], $class['section'], $date);

            $check_student = $this->attendance->where([
                'student_id' => $student_id,
                'schedule_id' => $class['schedule_id'],
                'date(date)' => $date
            ])->get();

            if ($check_student->status == 'absent') {
                $client_ip = $this->input->ip_address();
                $this->attendance->update_status('present', $client_ip, $student_id, $date);
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

    public function input_submit()
    {
        $this->assessment->insert($this->input->post());
    }

    public function student_submission($classwork_id)
    {
        $submission = $this->classworks->as_array()->get($classwork_id);

        $data = [
            'classwork' =>  $submission
        ];

        $this->load->view('student_submission', $data);
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
            $this->session->set_flashdata('warning', 'NAKA PASS NA LAGE KA!!!!');
        }

        redirect('classwork');
    }

    public function start_class()
    {
        $schedule_id = 1;
        $section = "1C";
        $this->attendance->start_class($schedule_id, $section, get_date_today());
    }

    public function all_submissions()
    {
        $submissions = $this->classworks->get_all_submissions(10);

        $data = ["submissions" => $submissions];

        $this->load->view('all_submission', $data);
    }

    public function add_score($classwork_id, $type)
    {

        switch ($type) {
            case 1:
                $score = randomizeNumber(5.0, 7.4);
                break;
            case 2:
                $score = randomizeNumber(7.5, 7.9);
                break;
            case 3:
                $score = randomizeNumber(8.0, 8.9);
                break;
            case 4:
                $score = randomizeNumber(9.0, 9.4);
                break;
            case 5:
                $score = randomizeNumber(9.5, 10.0);
                break;
            default:
                $score = null;
                break;
        }

        $update = $this->classworks->add_score($classwork_id, $score);
        redirect('all_submissions');
    }
}
