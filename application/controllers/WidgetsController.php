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
        $this->load->library('schema_guard');
    }

    // One-time (idempotent) schema setup/upgrade — run once as admin.
    // Confirmation + pre-flight backup: see Schema_guard.
    public function install()
    {
        // classworks is in the list because Widgets_model::install() now adds
        // switch_count to it — anything install() touches must be dumped first,
        // which is the whole point of Schema_guard. It is the biggest table
        // here, so expect the backup to take a moment.
        $tables = ['widgets', 'assessments', 'classworks'];

        if (!$this->schema_guard->confirmed('Widget tables setup', 'WidgetsController/install', $tables)) {
            return;
        }

        $backup = $this->schema_guard->backup($tables, 'widgets');
        $this->Widgets_model->install();

        $this->session->set_flashdata('success',
            'Widget tables ready.' . ($backup ? ' Backup written to ' . basename($backup) . '.' : ''));
        redirect('manage_assessments');
    }
}
