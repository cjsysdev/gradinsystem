<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Admin-only runner for CodeIgniter database migrations.
 *
 * Replaces the ad-hoc per-model install() endpoints (WidgetsController/install,
 * Groupings/install, poll/install) as the single, ordered, tracked way to apply
 * schema changes — the `migrations` table records how far the schema has been
 * advanced so each migration runs exactly once.
 *
 * Usage (logged in as admin): visit /Migrate to advance to the latest
 * migration. Migrations must be enabled in application/config/migration.php.
 */
class Migrate extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        if ($this->session->userdata('role') !== 'admin') {
            redirect('login');
        }
        $this->load->library('migration');
    }

    public function index()
    {
        if ($this->migration->latest() === FALSE) {
            show_error($this->migration->error_string());
            return;
        }

        $this->output
            ->set_content_type('text/plain')
            ->set_output("Migrations applied. Schema is at the latest version.\n");
    }
}
