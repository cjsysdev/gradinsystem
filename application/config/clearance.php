<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Clearance policy — which assessments an uncleared student may not take.
 *
 * The rule itself lives here, not in the controllers: the gate is enforced at
 * every entry point into an assessment (see application/helpers/clearance_helper.php),
 * and those entry points must all agree on what "an exam" is.
 *
 * Clearance is checked against the student's clearance for the SEMESTER and the
 * TERM the assessment belongs to — `assessments.term` midterm => midterm
 * clearance, tentative-final/final => finals clearance. See Student_clearance.
 */

/**
 * io_type ids that require clearance. 1=Activity, 2=Performance Task,
 * 3=Major Exam, 4=Quiz (table `io_type`).
 *
 * Default: Major Exam only. Add 4 here if quizzes should need clearance too —
 * every entry point picks the change up, nothing else needs editing.
 */
$config['clearance_gated_io_types'] = [3];

/**
 * Widget keys that require clearance regardless of their io_type, for exams
 * filed under another type. E.g. ['secure_quiz'] would gate every timed/
 * lockdown quiz even when it is recorded as a Quiz.
 */
$config['clearance_gated_widgets'] = [];

/** Shown to the student when the gate blocks them. %s = term label. */
$config['clearance_gate_message'] =
    'You are not cleared for the %s. Settle your clearance requirements first — only cleared students may take the exam.';
