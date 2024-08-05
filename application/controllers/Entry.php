<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Entry extends CI_Controller {

    public function __construct()
    {
        parent::__construct();
        $this->load->model('accounts');  
        $this->load->helper(['url_helper']);      
    }

	public function index()
	{
		$this->load->view('login');
	}

    public function signup(){
        $this->load->view('signup');
    }

    public function student_info(){
        $this->load->view('student_info');
    }

    public function home(){
        $this->load->view('home');
    }

    public function output_upload(){
        $this->load->view('output_upload');
    }

    public function admin(){
        $this->load->view('admin');
    }

    public function login(){
        $post = $this->input->post();
        $user = $this->accounts->get(['username' => $post['username']]);

        if($user && $user->password == $post['password']){
            echo 'login true';
        } else {
            echo 'asdf';
        }

        var_dump($user, $post);
    }

}