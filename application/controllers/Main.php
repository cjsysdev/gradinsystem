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
}
