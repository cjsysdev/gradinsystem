<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Main extends CI_Controller
{

    public function __construct()
    {
        parent::__construct();
        $this->load->model(['accounts', 'inputs', 'student_master', 'outputs']);
        $this->load->helper(['url']);
        $this->load->library(['session', 'upload']);
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
                'schedule' => $input_res->subject->schedule,
                'no_active' => FALSE,
            ];

            $this->session->set_userdata($data);
        } else {
            $this->session->set_userdata(['no_active' => TRUE]);
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
        $output = $this->outputs->where([
            'student_id'    => $_SESSION['student_id'],
            'input_id'      => $_SESSION['input_id'] ?? NULL
        ])->get();

        if (!$output) {
            $output = ['input_id' => FALSE];
        }

        $this->load->view('output_upload',  $output);
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
            $this->session->set_flashdata('error', 'Login Error');
            redirect();
        }
    }

    public function logout()
    {
        $this->session->sess_destroy();
        redirect();
    }

    public function signup_submit()
    {
        $input = $this->input->post();

        $acc_data = [
            'username' => $input['username'],
            'password' => $input['password']
        ];

        $student = [
            'lastname' => $input['lastname'],
            'firstname' => $input['firstname'],
            'gender' => $input['gender'],
        ];

        $this->db->trans_start(); // Start transaction

        try {
            $this->accounts->insert($acc_data);
            $this->student_master->insert($student);
            $this->db->trans_complete(); // Complete the transaction

            if ($this->db->trans_status() === FALSE) {
                // If the transaction failed
                throw new Exception('Transaction failed');
            }

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
        $this->inputs->insert($this->input->post());
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
}
