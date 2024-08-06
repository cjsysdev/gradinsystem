<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Main extends CI_Controller
{

    public function __construct()
    {
        parent::__construct();
        $this->load->model(['accounts', 'inputs', 'student_master']);
        $this->load->helper(['url']);
        $this->load->library(['session']);
    }

    public function index()
    {
        $input_res = $this->inputs->where('status', 'active')->with_type()->with_subject()->get();

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
                'schedule' => $input_res->subject->schedule
            ];

            $this->session->set_userdata($data);
        }

        $this->load->view('login');
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
        $this->load->view('output_upload');
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

            var_dump($this->session->userdata);

            redirect('output_upload');
        } else {
            echo 'asdf';
        }
    }

    public function logout()
    {
        $this->session->unset_userdata($this->session->userdata);
        redirect();
    }

    public function signup_submit()
    {
        $input = $this->input->post();
        var_dump($input);

        $data = [
            'username' => $input['username'],
            'password' => $input['password']
        ];

        $this->accounts->insert($data);
    }

    public function input_submit()
    {
        $this->inputs->insert($this->input->post());
    }

    public function upload_activity()
    {
        $config['upload_path']          = './uploads/';
        $config['allowed_types']        = 'gif|jpg|jpeg|png';
        $config['max_size']             = 2048; // 2MB
        $config['encrypt_name']         = TRUE; // Encrypt the file name for security

        $this->upload->initialize($config);

        if (!$this->upload->do_upload('photo-upload')) {
            $error = array('error' => $this->upload->display_errors());

            // Load the view and pass the error
            $this->load->view('upload_form', $error);
        } else {
            $upload_data = $this->upload->data();
            $activity_score = $this->input->post('activities-score');

            // Here you can save the $activity_score and $upload_data['file_name'] to the database if needed

            $data = array('upload_data' => $upload_data);

            // Load the success view and pass the upload data
            $this->load->view('upload_success', $data);
        }
    }
}
