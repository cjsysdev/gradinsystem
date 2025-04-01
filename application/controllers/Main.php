<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Main extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->helper(['url']);
        $this->load->library(['session', 'upload']);
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

    public function upload_activity()
    {
        $config['upload_path'] = './uploads/outputs';
        $config['allowed_types'] = '*';
        $config['max_size'] = 51200; // 50MB
        $config['file_name'] = 'activity-' . time();

        $this->upload->initialize($config);

        if (!$this->upload->do_upload('photo-upload')) {
            $this->session->set_flashdata('error', $this->upload->display_errors());
            redirect('output_upload');
        } else {
            $this->upload->data();
            $this->session->set_flashdata('success', 'Upload successful!');
            redirect('output_upload');
        }
    }
}
