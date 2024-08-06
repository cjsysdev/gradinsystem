<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Main extends CI_Controller
{

    public function __construct()
    {
        parent::__construct();
        $this->load->model(['accounts', 'inputs', 'student_master']);
        $this->load->helper(['url_helper']);
        $this->load->library(['session']);
    }

    public function index()
    {
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
        } else {
            echo 'asdf';
        }
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
}
