<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * The clearance gate.
 *
 * An uncleared student must not be able to TAKE a gated assessment — and
 * "taking" it has many doors: the assessment page itself, the timed/secure quiz
 * player, the legacy quiz player, the group workspace, the brainstorm board,
 * the interactive-quiz players, and each of their submit endpoints. A check in
 * one controller is not a gate; anyone who knows the URL of the next screen
 * walks around it. So the rule lives in one function that all of them call, on
 * BOTH the open and the submit path.
 *
 * Policy (which assessments are gated) is in application/config/clearance.php;
 * the clearance lookup itself is Student_clearance::may_take().
 *
 * Autoloaded — see $autoload['helper'] in application/config/autoload.php.
 */

if (!function_exists('clearance_allows')) {
    /**
     * TRUE if the logged-in user may take this assessment.
     *
     * @param mixed $assessment assessment id, or an already-fetched row
     *                          (array or object) with iotype_id/term/widget_id.
     */
    function clearance_allows($assessment)
    {
        $CI =& get_instance();

        // Admins reach these screens to preview and to run a class; the gate is
        // about students sitting an exam.
        if ($CI->session->userdata('role') === 'admin') {
            return true;
        }

        $row = clearance_resolve_assessment($assessment);
        if (!$row) {
            return true; // nothing to gate; the caller's own 404 handling applies
        }

        $CI->load->model('Student_clearance');
        return $CI->Student_clearance->may_take($CI->session->student_id, $row);
    }
}

if (!function_exists('clearance_gate')) {
    /**
     * Page guard. Blocks an uncleared student with a flash message and sends
     * them back, and returns TRUE so the caller can stop:
     *
     *     if (clearance_gate($assessment_id)) return;
     *
     * CI's redirect() exits, so the `return` is belt-and-braces.
     */
    function clearance_gate($assessment, $redirect_to = 'attendance')
    {
        if (clearance_allows($assessment)) {
            return false;
        }

        $CI =& get_instance();
        $row = clearance_resolve_assessment($assessment);
        $CI->load->model('Student_clearance');
        $CI->session->set_flashdata(
            'warning',
            $CI->Student_clearance->gate_message(is_array($row) ? ($row['term'] ?? '') : '')
        );

        redirect($redirect_to);
        return true;
    }
}

if (!function_exists('clearance_gate_json')) {
    /**
     * Same guard for AJAX/submit endpoints, which must answer with JSON rather
     * than a redirect. Returns TRUE (after emitting 403 JSON) when blocked.
     */
    function clearance_gate_json($assessment)
    {
        if (clearance_allows($assessment)) {
            return false;
        }

        $CI =& get_instance();
        $row = clearance_resolve_assessment($assessment);
        $CI->load->model('Student_clearance');

        $CI->output
            ->set_status_header(403)
            ->set_content_type('application/json')
            ->set_output(json_encode([
                'ok'      => false,
                'success' => false,
                'error'   => 'not_cleared',
                'message' => $CI->Student_clearance->gate_message(is_array($row) ? ($row['term'] ?? '') : ''),
            ]));
        return true;
    }
}

if (!function_exists('clearance_resolve_assessment')) {
    /**
     * Accepts an id or an already-fetched row; always returns an array or null.
     *
     * A row that is missing `term` or `iotype_id` is re-fetched rather than
     * used as-is: a partial SELECT would otherwise read as term '' and quietly
     * demand FINALS clearance for a midterm exam.
     */
    function clearance_resolve_assessment($assessment)
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

        if (array_key_exists('term', $row) && array_key_exists('iotype_id', $row)) {
            return $row;
        }

        if (empty($row['assessment_id'])) {
            return $row;
        }

        $CI =& get_instance();
        $CI->load->model('assessments');
        $full = $CI->assessments->as_array()->get((int) $row['assessment_id']);
        return $full ?: $row;
    }
}
