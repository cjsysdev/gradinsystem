<?php
defined('BASEPATH') or exit('No direct script access allowed');

// One-time admin actions for the assessments -> master/assessment_section
// normalization (see Assessment_normalize_model). Same pattern as
// WidgetsController/install — admin-gated, idempotent where possible, run
// manually once per phase.
class AssessmentNormalizeController extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        if ($this->session->userdata('role') !== 'admin') {
            redirect('login');
        }
        $this->load->model('Assessment_normalize_model');
    }

    // Phase 1 — safe, additive, reversible with DROP VIEW assessment_full.
    // Creates the assessment_full compat view over the UNCHANGED assessments
    // table. Run this first and let read-site code changes soak before
    // running install() below.
    public function install_view()
    {
        try {
            $log = $this->Assessment_normalize_model->install_compat_view();
            $this->_render($log, true);
        } catch (Exception $e) {
            $this->_render([$e->getMessage()], false);
        }
    }

    // Phase 2 — the actual schema normalization. NOT safely reversible once
    // admin write-activity resumes afterward (see rollback_sql() for the
    // manual recovery statements, valid only in the verification window
    // immediately after this completes). Take a mysqldump before running.
    public function install()
    {
        try {
            $log = $this->Assessment_normalize_model->install();
            $this->_render($log, true);
        } catch (Exception $e) {
            $this->_render([$e->getMessage()], false);
        }
    }

    private function _render($log, $success)
    {
        echo '<pre style="font:13px/1.5 monospace;padding:20px;white-space:pre-wrap;">';
        echo $success ? "=== SUCCESS ===\n\n" : "=== FAILED ===\n\n";
        foreach ($log as $line) {
            echo htmlspecialchars($line) . "\n";
        }
        echo '</pre>';
    }
}
