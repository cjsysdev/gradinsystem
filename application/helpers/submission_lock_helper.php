<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * The submission lock.
 *
 * An assessment is LOCKED once it is closed (status 0/'closed') or past its
 * due date. A locked assessment cannot be opened or submitted by a student
 * unless an instructor approved a late_submission request whose
 * granted_until is still in the future (student_requests).
 *
 * Same shape as clearance_helper.php, and called at the same doors, on both
 * the open and the submit path — a check in one controller is not a gate.
 *
 * Exempt: code_snippet (own server-side timer and after-due attachments),
 * and assessments outside the active semester (already read-only elsewhere).
 *
 * Autoloaded — see $autoload['helper'] in application/config/autoload.php.
 */

if (!function_exists('submission_resolve_assessment')) {
    /** Id or row in; array (with status/due/widget_id/schedule_id) or null out. */
    function submission_resolve_assessment($assessment)
    {
        $row = null;
        if (is_array($assessment)) {
            $row = $assessment;
        } elseif (is_object($assessment)) {
            $row = (array) $assessment;
        } elseif ($assessment) {
            $row = ['assessment_id' => (int) $assessment];
        }
        if (!$row) {
            return null;
        }

        // A partial SELECT would read as "no due date, status unknown" and
        // quietly skip the lock, so re-fetch when a needed key is missing.
        $need = ['status', 'due', 'widget_id'];
        $complete = true;
        foreach ($need as $k) {
            if (!array_key_exists($k, $row)) { $complete = false; break; }
        }
        if ($complete || empty($row['assessment_id'])) {
            return $row;
        }

        $CI =& get_instance();
        $CI->load->model('assessments');
        $full = $CI->assessments->as_array()->get((int) $row['assessment_id']);
        return $full ?: $row;
    }
}

if (!function_exists('submission_status_open')) {
    // status is stored inconsistently (1/0 or legacy 'open'/'closed').
    function submission_status_open($status)
    {
        return (string) $status === '1' || $status === 'open';
    }
}

if (!function_exists('submission_past_due')) {
    function submission_past_due($row)
    {
        $row = (array) $row;
        return !empty($row['due']) && strtotime($row['due']) !== false
            && strtotime($row['due']) < time();
    }
}

if (!function_exists('submission_locked')) {
    /** TRUE when the assessment is closed or past due, ignoring any grant. */
    function submission_locked($assessment)
    {
        $row = submission_resolve_assessment($assessment);
        if (!$row) {
            return false;
        }

        $CI =& get_instance();
        $CI->load->model('widgets_model');
        if (!empty($row['widget_id'])) {
            $w = $CI->widgets_model->get($row['widget_id']);
            if ($w && $w['widget_key'] === 'code_snippet') {
                return false;
            }
        }

        if (!submission_status_open($row['status'] ?? null)) {
            return true;
        }

        if (!empty($row['due']) && strtotime($row['due']) !== false
            && strtotime($row['due']) < time()) {
            return true;
        }

        return false;
    }
}

if (!function_exists('submission_allows')) {
    /** TRUE if the logged-in user may open/submit this assessment now. */
    function submission_allows($assessment)
    {
        $CI =& get_instance();

        if ($CI->session->userdata('role') === 'admin') {
            return true;
        }

        $row = submission_resolve_assessment($assessment);
        if (!$row || empty($row['assessment_id'])) {
            return true; // nothing to gate; the caller's own 404 handling applies
        }

        if (!submission_locked($row)) {
            return true;
        }

        // Past semesters are read-only through their own checks; don't also
        // dangle a request button at them.
        $CI->load->model('Semester_model');
        if (!$CI->Semester_model->is_active($CI->Semester_model->assessment_semester_id($row['assessment_id']))) {
            return true;
        }

        return $CI->student_request->active_late_grant(
            $CI->session->student_id,
            $row['assessment_id']
        ) !== null;
    }
}

if (!function_exists('submission_gate')) {
    /** Page guard: flash + redirect + TRUE when blocked. */
    function submission_gate($assessment, $redirect_to = 'classwork')
    {
        if (submission_allows($assessment)) {
            return false;
        }

        $CI =& get_instance();
        $CI->session->set_flashdata(
            'warning',
            'This classwork is closed. Request access from your instructor on the Classwork page.'
        );
        redirect($redirect_to);
        return true;
    }
}

if (!function_exists('submission_gate_json')) {
    /** Same guard for AJAX endpoints: emits 403 JSON, returns TRUE when blocked. */
    function submission_gate_json($assessment)
    {
        if (submission_allows($assessment)) {
            return false;
        }

        $CI =& get_instance();
        $CI->output
            ->set_status_header(403)
            ->set_content_type('application/json')
            ->set_output(json_encode([
                'ok'      => false,
                'success' => false,
                'error'   => 'closed',
                'message' => 'This classwork is closed. Request access from your instructor.',
            ]));
        return true;
    }
}
