<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Entry extends CI_Controller {

	public function index()
	{
		$this->load->view('login');
	}

    public function login(){
        echo 'login';
        var_dump($_POST);
    }

}