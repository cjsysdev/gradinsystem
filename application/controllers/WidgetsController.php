<?php
defined('BASEPATH') or exit('No direct script access allowed');

class WidgetsController extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        if ($this->session->userdata('role') !== 'admin') {
            redirect('login');
        }
        $this->load->model('Widgets_model');
    }

    // One-time (idempotent) schema setup/upgrade — run once as admin.
    public function install()
    {
        $this->Widgets_model->install();
        $this->session->set_flashdata('success', 'Widget tables ready.');
        redirect('manage_assessments');
    }
}
