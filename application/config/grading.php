<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Grading policy — the single place every grade rule lives.
 *
 * Before this file existed the transmutation formula was duplicated in four SQL
 * queries plus one PHP helper (which hardcoded the passing rate to 60 and
 * ignored semester_master.passing_rate), and the INC cutoff existed in four
 * mutually inconsistent forms (> 3.1, >= 3.09, > 3.00, >= 3.1). Change a rule
 * here, not in a query or a view.
 */

/*
| Fallback used only when semester_master.passing_rate is NULL or out of range.
| The DB value always wins when it is usable.
*/
$config['grading_passing_rate_fallback'] = 60;

/*
| Grade-point scale anchors. percentage 0 -> 5.0, percentage == passing_rate
| -> 3.0, percentage 100 -> 1.0. Two straight lines meeting at the passing rate.
*/
$config['grading_point_floor']   = 5.0; // worst
$config['grading_point_passing'] = 3.0; // exactly at passing_rate
$config['grading_point_ceiling'] = 1.0; // best

/*
| How midterm and final combine into the overall grade.
*/
$config['grading_term_weights'] = [
    'midterm' => 0.5,
    'final'   => 0.5,
];

/*
| A term grade is INC unless every io_type listed here has at least one
| assessment for that schedule + term. NULL means "every row in the io_type
| table", which is the intended behaviour — set an explicit array only to
| exempt a component.
|
| Replaces the hardcoded has_iotype_2 / has_iotype_3 checks that used to live in
| GradesController and covered only Performance Task and Major Exam.
*/
$config['grading_required_iotypes'] = null;

/*
| Grade points strictly above this are reported as INC rather than as a failing
| number. This reproduces the legacy "if final_grade > 3.1 then INC" behaviour,
| but with ONE value instead of the four that had drifted apart.
|
| 3.0 is the transmutation of exactly the passing rate, so "above 3.0" means
| "below passing". Set to NULL to report failing grades as numbers instead.
*/
$config['grading_fail_as_inc_above'] = 3.0;

/*
| Minutes after a class start time before a 'present' attendance record counts
| as late. Previously a bare 20 buried in two copies of the grade SQL.
*/
$config['grading_late_threshold_minutes'] = 20;
