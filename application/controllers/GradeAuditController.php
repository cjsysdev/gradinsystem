<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Grade audit harness — proves the Grade_calculator refactor does not silently
 * change any student's grade for an unapproved reason.
 *
 * Workflow:
 *   1. /grade_audit/baseline  — run BEFORE touching legacy code. Snapshots the
 *      output of the legacy classworks methods to uploads/grade_audit/.
 *   2. /grade_audit/diff      — run AFTER Grade_calculator exists. Compares the
 *      new engine against the frozen baseline and classifies every difference.
 *
 * The gate for cutover is: zero differences classified UNEXPLAINED.
 *
 * READ-ONLY. This controller never writes to classworks or any grade table.
 */
class GradeAuditController extends CI_Controller
{
    const AUDIT_DIR = 'uploads/grade_audit/';

    public function __construct()
    {
        parent::__construct();
        // Runnable from the CLI (php index.php GradeAuditController baseline) so
        // the audit can be run without a browser session; over HTTP it is
        // admin-only, matching AdminController::__construct().
        if (!is_cli() && $this->session->userdata('role') !== 'admin') {
            redirect('login');
        }
        $this->load->model('classworks');
    }

    // ------------------------------------------------------------------
    // Step 1 — freeze the legacy behaviour
    // ------------------------------------------------------------------

    /**
     * Snapshot legacy grade output for every active section and both terms,
     * plus every student's per-iotype breakdown. Uses the LEGACY code paths
     * only — must be run before those methods are deleted.
     */
    public function baseline()
    {
        // The legacy methods are deleted once cutover completes. The snapshot
        // they produced is preserved as JSON under uploads/grade_audit/, which
        // is what diff() reads — this method is kept only so the capture step
        // remains readable and re-runnable against an older checkout.
        if (!method_exists($this->classworks, 'getGradesBySection')) {
            $this->output->set_content_type('text/plain')->set_output(
                "Legacy grade methods have been removed — a new baseline cannot be captured.\n"
                . "The frozen baseline is still available at "
                . self::AUDIT_DIR . "baseline_latest.json and diff() continues to use it.\n"
            );
            return;
        }

        $snapshot = [
            'generated_at' => date('Y-m-d H:i:s'),
            'note'         => 'Legacy grade output, captured before Grade_calculator cutover.',
            'sections'     => [],
        ];

        foreach ($this->_active_sections() as $row) {
            $section = $row['section'];
            foreach (['midterm', 'final'] as $term) {
                $rows = $this->classworks->getGradesBySection($term, $section);

                // Store the raw per-(student,iotype) facts, which is what every
                // downstream calculation is derived from.
                $components = [];
                foreach ($rows as $r) {
                    $components[$r['student_id']][$r['iotype_id']] = [
                        'total_score'        => $r['total_score'],
                        'total_max_score'    => $r['total_max_score'],
                        'percentage'         => $r['percentage'],
                        'grade_point'        => $r['grade_point'],
                        'iotype_percentage'  => $r['iotype_percentage'],
                    ];
                }

                $snapshot['sections'][$section][$term] = [
                    'row_count'     => count($rows),
                    'student_count' => count($components),
                    'students'      => $components,
                    // Replay the legacy controller arithmetic so we capture the
                    // final rendered numbers, not just the SQL output.
                    'derived'       => $this->_legacy_term_totals($rows),
                ];
            }
        }

        $path = $this->_write_snapshot('baseline', $snapshot);

        $this->output->set_content_type('text/plain')->set_output(
            "Baseline written to: $path\n\n" . $this->_summarise($snapshot)
        );
    }

    /**
     * Faithful re-implementation of the legacy GradesController weighted-sum
     * loop (GradesController.php:90-102 / 143-147), including its bugs, so the
     * baseline records what students actually saw.
     */
    private function _legacy_term_totals(array $rows)
    {
        $out = [];
        foreach ($rows as $grade) {
            $sid = $grade['student_id'];
            if (!isset($out[$sid])) {
                $out[$sid] = [
                    'total_grade'   => 0,
                    'has_iotype_2'  => false,
                    'has_iotype_3'  => false,
                    'is_incomplete' => false,
                ];
            }
            if ($grade['iotype_id'] == 2) {
                $out[$sid]['has_iotype_2'] = true;
                if (is_null($grade['total_score']) || is_null($grade['percentage'])) {
                    $out[$sid]['is_incomplete'] = true;
                }
            }
            if ($grade['iotype_id'] == 3) {
                $out[$sid]['has_iotype_3'] = true;
                if (is_null($grade['total_score']) || is_null($grade['percentage'])) {
                    $out[$sid]['is_incomplete'] = true;
                }
            }
            if (!$out[$sid]['is_incomplete']) {
                $out[$sid]['total_grade'] += $grade['percentage'] * ($grade['iotype_percentage'] / 100);
            }
        }
        foreach ($out as &$s) {
            $s['total_grade'] = round($s['total_grade'], 4);
            $s['legacy_grade_point'] = $this->_legacy_transmute($s['total_grade']);
        }
        return $out;
    }

    /**
     * The old conversion_helper::convertPercentageToGradePoint(), inlined so the
     * baseline stays a faithful record of legacy behaviour even after the helper
     * is changed. Note the hardcoded 60 — that was the bug.
     */
    private function _legacy_transmute($percentage)
    {
        $passingGrade = 60;
        if ($percentage <= $passingGrade) {
            $gradePoint = 5.0 - (2.0 / $passingGrade) * $percentage;
        } elseif ($percentage > $passingGrade && $percentage <= 100) {
            $gradePoint = 3.0 - (2.0 / (100 - $passingGrade)) * ($percentage - $passingGrade);
        } else {
            return null;
        }
        return number_format($gradePoint, 2);
    }

    // ------------------------------------------------------------------
    // Step 2 — diff the new engine against the frozen baseline
    // ------------------------------------------------------------------

    public function diff()
    {
        $baseline = $this->_read_snapshot('baseline');
        if (!$baseline) {
            show_error('No baseline snapshot found. Run /grade_audit/baseline first.', 500);
        }

        $this->load->model('Grade_calculator');

        $report = [
            'generated_at' => date('Y-m-d H:i:s'),
            'baseline_at'  => $baseline['generated_at'],
            'categories'   => [],
            'unexplained'  => [],
        ];

        foreach ($baseline['sections'] as $section => $terms) {
            $schedule = $this->_schedule_for_section($section);
            if (!$schedule) {
                $this->_tally($report, 'section_has_no_active_schedule', $section);
                continue;
            }

            foreach ($terms as $term => $old) {
                $new = $this->Grade_calculator->for_schedule($schedule['schedule_id'], $term);

                $old_ids = array_map('strval', array_keys($old['students']));
                $new_ids = array_map('strval', array_keys($new['students']));

                // Students the legacy query invented (stale semester / not enrolled).
                foreach (array_diff($old_ids, $new_ids) as $gone) {
                    $this->_tally($report, 'phantom_student_removed', "$section/$term/$gone");
                }
                // Students the legacy query missed.
                //
                // When a term has no assessments at all, the legacy query's
                // INNER JOIN on assessment_full returned zero rows, so the
                // whole section vanished from the sheet. The new engine starts
                // from the roster instead and reports every student as INC.
                // That is the intended correction, not a regression.
                $legacy_term_was_empty = ((int) $old['row_count'] === 0);

                foreach (array_diff($new_ids, $old_ids) as $added) {
                    if ($legacy_term_was_empty) {
                        $this->_tally($report, 'term_has_no_assessments_roster_now_shown', "$section/$term/$added");
                        continue;
                    }
                    $report['unexplained'][] = [
                        'kind'    => 'student_appeared',
                        'where'   => "$section/$term/$added",
                        'detail'  => 'New engine returns a student the legacy query did not, '
                                     . 'even though the term has assessments.',
                    ];
                }

                // Compare the students present in both.
                foreach (array_intersect($old_ids, $new_ids) as $sid) {
                    $this->_diff_student(
                        $report,
                        "$section/$term/$sid",
                        $old['students'][$sid],
                        $old['derived'][$sid] ?? null,
                        $new['students'][$sid]
                    );
                }
            }
        }

        $path = $this->_write_snapshot('diff', $report);

        $unexplained = count($report['unexplained']);
        $out  = "Diff written to: $path\n\n";
        $out .= "=== Classified differences ===\n";
        foreach ($report['categories'] as $cat => $items) {
            $out .= sprintf("  %-32s %d\n", $cat, count($items));
        }
        $out .= "\n=== UNEXPLAINED: $unexplained ===\n";
        if ($unexplained) {
            foreach (array_slice($report['unexplained'], 0, 50) as $u) {
                $out .= "  [{$u['kind']}] {$u['where']}: {$u['detail']}\n";
            }
            $out .= "\nGATE FAILED — do not cut over until UNEXPLAINED is zero.\n";
        } else {
            $out .= "GATE PASSED — every difference traces to an approved cause.\n";
        }

        $this->output->set_content_type('text/plain')->set_output($out);
    }

    /**
     * Compare one student's legacy vs new numbers, classifying each difference.
     */
    private function _diff_student(&$report, $where, $old_components, $old_derived, $new_student)
    {
        // --- per-component raw sums must match EXACTLY. -------------------
        // Decision #2 (NULL counts as 0) means the underlying arithmetic is
        // unchanged, so any drift here is a real regression, not a policy change.
        foreach ($old_components as $iotype => $old_c) {
            $new_c = $new_student['components'][$iotype] ?? null;
            if ($new_c === null) {
                $report['unexplained'][] = [
                    'kind'   => 'component_vanished',
                    'where'  => "$where/iotype$iotype",
                    'detail' => 'Legacy had this component, new engine does not.',
                ];
                continue;
            }
            if (!$this->_near($old_c['total_score'], $new_c['total_score'])) {
                $report['unexplained'][] = [
                    'kind'   => 'score_sum_changed',
                    'where'  => "$where/iotype$iotype",
                    'detail' => "total_score {$old_c['total_score']} -> {$new_c['total_score']}",
                ];
            }
            if (!$this->_near($old_c['total_max_score'], $new_c['total_max_score'])) {
                $report['unexplained'][] = [
                    'kind'   => 'max_sum_changed',
                    'where'  => "$where/iotype$iotype",
                    'detail' => "total_max {$old_c['total_max_score']} -> {$new_c['total_max_score']}",
                ];
            }
            if (!$this->_near($old_c['grade_point'], $new_c['grade_point'])) {
                // SQL used semester passing_rate; so does the new engine. Equal
                // inputs must give equal output — unless passing_rate is unusable.
                $this->_tally($report, 'component_grade_point_changed', "$where/iotype$iotype");
            }
        }

        // --- term grade: policy changes are expected here. ----------------
        if ($old_derived === null) {
            return;
        }

        $new_is_inc = ($new_student['term']['status'] === 'inc');
        $old_was_inc = (!$old_derived['has_iotype_2'] || !$old_derived['has_iotype_3'] || $old_derived['is_incomplete']);

        if ($new_is_inc && !$old_was_inc) {
            $this->_tally($report, 'inc_now_applied', $where);
        } elseif (!$new_is_inc && $old_was_inc) {
            $this->_tally($report, 'inc_now_cleared', $where);
        } elseif (!$new_is_inc && !$old_was_inc) {
            if (!$this->_near($old_derived['total_grade'], $new_student['term']['percentage'])) {
                $report['unexplained'][] = [
                    'kind'   => 'term_percentage_changed',
                    'where'  => $where,
                    'detail' => "{$old_derived['total_grade']} -> {$new_student['term']['percentage']}"
                                . ' (both non-INC, weights unchanged — should be identical)',
                ];
            }
        }
    }

    private function _near($a, $b, $eps = 0.005)
    {
        if ($a === null && $b === null) return true;
        if ($a === null || $b === null) return false;
        return abs((float)$a - (float)$b) < $eps;
    }

    private function _tally(&$report, $category, $item)
    {
        $report['categories'][$category][] = $item;
    }

    /**
     * Spot-check one student's computed grade — the same data the student sees
     * on /grades, without needing their session.
     */
    public function student($student_id)
    {
        $this->load->model('Grade_calculator');
        $result = $this->Grade_calculator->for_student($student_id);

        if (!$result) {
            $this->output->set_content_type('text/plain')
                ->set_output("Student $student_id has no active enrolment.\n");
            return;
        }

        $out = "STUDENT $student_id — schedule {$result['schedule_id']}, passing rate {$result['passing_rate']}\n";
        $out .= str_repeat('=', 70) . "\n";

        foreach (['midterm', 'final'] as $term) {
            $out .= strtoupper($term) . "\n";
            foreach ($result[$term . '_components'] as $c) {
                $out .= sprintf(
                    "  %-18s w=%-5s %6s / %-6s = %-7s gp=%-6s  n=%d ungraded=%d\n",
                    $c['iotype_name'],
                    $c['iotype_percentage'],
                    $c['total_score'],
                    $c['total_max_score'],
                    $c['percentage'] === null ? 'n/a' : $c['percentage'] . '%',
                    $c['grade_point'] === null ? 'n/a' : $c['grade_point'],
                    $c['n_assessments'],
                    $c['n_ungraded']
                );
            }
            $t = $result[$term];
            $out .= sprintf(
                "  => %s  pct=%s gp=%s%s\n\n",
                strtoupper($t['status']),
                $t['percentage'] ?? '-',
                $t['grade_point'] ?? '-',
                empty($t['missing_iotypes']) ? '' : ' missing=' . implode(',', $t['missing_iotypes'])
            );
        }

        $o = $result['overall'];
        $out .= sprintf(
            "OVERALL: %s  pct=%s gp=%s  reason=%s\n",
            strtoupper($o['status']), $o['percentage'] ?? '-', $o['grade_point'] ?? '-', $o['reason'] ?? '-'
        );

        $this->output->set_content_type('text/plain')->set_output($out);
    }

    // ------------------------------------------------------------------
    // Policy unit checks — pure functions, no DB
    // ------------------------------------------------------------------

    public function selftest()
    {
        $this->load->model('Grade_calculator');
        $gc = $this->Grade_calculator;
        $pass = 0;
        $fail = [];

        $check = function ($label, $actual, $expected) use (&$pass, &$fail) {
            $ok = ($expected === null)
                ? ($actual === null)
                : ($actual !== null && abs((float)$actual - (float)$expected) < 0.005);
            if ($ok) { $pass++; } else { $fail[] = "$label: expected " . var_export($expected, true) . ', got ' . var_export($actual, true); }
        };

        // --- scale anchors at the real semester passing rates ---
        foreach ([50, 60] as $pr) {
            $check("transmute(0, $pr)",   $gc->transmute(0, $pr),   5.0);
            $check("transmute($pr, $pr)", $gc->transmute($pr, $pr), 3.0);
            $check("transmute(100, $pr)", $gc->transmute(100, $pr), 1.0);
        }

        // --- midpoints stay on the two straight lines ---
        $check('transmute(30,60) midpoint of lower leg', $gc->transmute(30, 60), 4.0);
        $check('transmute(80,60) midpoint of upper leg', $gc->transmute(80, 60), 2.0);

        // --- unusable passing rates must yield NULL, not a division by zero ---
        $check('transmute(50, 0)',    $gc->transmute(50, 0),    null);
        $check('transmute(50, 100)',  $gc->transmute(50, 100),  null);
        $check('transmute(50, null)', $gc->transmute(50, null), null);

        // --- out-of-range percentages ---
        $check('transmute(-1, 60)',  $gc->transmute(-1, 60),  null);
        $check('transmute(101, 60)', $gc->transmute(101, 60), null);
        $check('transmute(null, 60)', $gc->transmute(null, 60), null);

        // --- the legacy 'INC' string bug: must NOT come back as 5.00 ---
        $check("transmute('INC', 60) is null", $gc->transmute('INC', 60), null);

        // --- return type must be numeric, never a formatted string ---
        if (!is_float($gc->transmute(75, 60))) {
            $fail[] = 'transmute must return float, got ' . gettype($gc->transmute(75, 60));
        } else { $pass++; }

        // --- term_grade: a missing required component forces INC ---
        $components = [
            1 => ['weighted_grade' => 10.0, 'n_assessments' => 3, 'n_ungraded' => 0],
            2 => ['weighted_grade' => 40.0, 'n_assessments' => 1, 'n_ungraded' => 0],
            3 => ['weighted_grade' => 0.0,  'n_assessments' => 0, 'n_ungraded' => 0],
            4 => ['weighted_grade' => 20.0, 'n_assessments' => 2, 'n_ungraded' => 0],
        ];
        $t = $gc->term_grade($components, [1, 2, 3, 4], 60);
        if ($t['status'] !== 'inc' || $t['missing_iotypes'] !== [3]) {
            $fail[] = 'term_grade should be INC with iotype 3 missing, got ' . json_encode($t);
        } else { $pass++; }

        // --- with every component present, the weighted sum is used as-is ---
        $components[3] = ['weighted_grade' => 30.0, 'n_assessments' => 1, 'n_ungraded' => 2];
        $t = $gc->term_grade($components, [1, 2, 3, 4], 60);
        $check('term_grade percentage (perfect)', $t['percentage'], 100.0);
        $check('term_grade grade_point (perfect)', $t['grade_point'], 1.0);
        if ($t['pending_count'] !== 2) {
            $fail[] = 'pending_count should be 2, got ' . var_export($t['pending_count'], true);
        } else { $pass++; }

        // --- INC propagates through the blend ---
        $ok  = ['status' => 'ok', 'percentage' => 90.0, 'grade_point' => 1.5];
        $inc = ['status' => 'inc', 'percentage' => null, 'grade_point' => null];
        if ($gc->final_grade($ok, $inc, 60)['status'] !== 'inc') {
            $fail[] = 'final_grade must be INC when the final term is INC';
        } else { $pass++; }

        $blend = $gc->final_grade($ok, ['status' => 'ok', 'percentage' => 70.0, 'grade_point' => 2.5], 60);
        $check('final_grade blend percentage', $blend['percentage'], 80.0);
        $check('final_grade blend grade_point', $blend['grade_point'], 2.0);

        // --- below-passing collapses to INC under the single unified cutoff ---
        $low = ['status' => 'ok', 'percentage' => 50.0, 'grade_point' => 3.33];
        if ($gc->final_grade($low, $low, 60)['status'] !== 'inc') {
            $fail[] = 'final_grade below passing should report INC under grading_fail_as_inc_above';
        } else { $pass++; }

        $out = "GRADE POLICY SELF-TEST\n" . str_repeat('=', 40) . "\n";
        $out .= "passed: $pass\nfailed: " . count($fail) . "\n";
        foreach ($fail as $f) {
            $out .= "  FAIL  $f\n";
        }
        $out .= empty($fail) ? "\nALL POLICY CHECKS PASSED\n" : "\nPOLICY CHECKS FAILED\n";

        $this->output->set_content_type('text/plain')->set_output($out);
    }

    /**
     * Exercise the score-write guardrails against a real row inside a
     * transaction that is always rolled back. Nothing is persisted.
     */
    public function scoretest()
    {
        $target = $this->db->query("
            SELECT c.classwork_id, c.score, a.max_score
            FROM classworks c
            JOIN assessment_full a ON a.assessment_id = c.assessment_id
            WHERE a.max_score > 0
            LIMIT 1
        ")->row_array();

        if (!$target) {
            $this->output->set_content_type('text/plain')->set_output("No scorable row found.\n");
            return;
        }

        $id       = $target['classwork_id'];
        $max      = (float) $target['max_score'];
        $original = $target['score'];
        $results  = [];

        $this->db->trans_begin();

        // Write a valid value that is clearly different from the original, so
        // the rollback check at the end cannot pass by coincidence.
        $probe = ($original !== null && (float) $original === round($max / 2, 2)) ? 0 : round($max / 2, 2);
        $err = null;
        $this->classworks->set_score($id, $probe, $err);
        $probed = $this->db->select('score')->where('classwork_id', $id)->get('classworks')->row('score');
        $results[] = sprintf(
            'write %s (valid): stored=%s -> %s',
            $probe, $probed, ((float) $probed === (float) $probe ? 'WRITTEN' : 'NOT WRITTEN')
        );

        $err = null;
        $ok  = $this->classworks->set_score($id, $max * 100, $err);
        $stored = $this->db->select('score')->where('classwork_id', $id)->get('classworks')->row('score');
        $results[] = sprintf(
            'write %s (max %s): ok=%s stored=%s notice=%s  -> %s',
            $max * 100, $max, var_export($ok, true), $stored, $err ?: '-',
            ((float) $stored === $max ? 'CLAMPED' : 'NOT CLAMPED')
        );

        $err = null;
        $ok  = $this->classworks->set_score($id, -5, $err);
        $results[] = sprintf('write -5: ok=%s notice=%s -> %s', var_export($ok, true), $err ?: '-', $ok ? 'ACCEPTED' : 'REJECTED');

        $err = null;
        $ok  = $this->classworks->set_score($id, 'abc', $err);
        $results[] = sprintf("write 'abc': ok=%s notice=%s -> %s", var_export($ok, true), $err ?: '-', $ok ? 'ACCEPTED' : 'REJECTED');

        $err = null;
        $ok  = $this->classworks->set_score(0, 5, $err);
        $results[] = sprintf('write to missing row: ok=%s notice=%s -> %s', var_export($ok, true), $err ?: '-', $ok ? 'ACCEPTED' : 'REJECTED');

        // Always roll back — this is a test, not a grading action.
        $this->db->trans_rollback();

        $after = $this->db->select('score')->where('classwork_id', $id)->get('classworks')->row('score');

        $out  = "SCORE GUARDRAIL TEST (rolled back — nothing persisted)\n" . str_repeat('=', 55) . "\n";
        $out .= "classwork_id=$id max_score=$max original_score=" . var_export($original, true) . "\n\n";
        foreach ($results as $r) {
            $out .= "  $r\n";
        }
        $out .= "\nscore after rollback: " . var_export($after, true);
        $out .= ((string) $after === (string) $original) ? "  (RESTORED)\n" : "  (!! NOT RESTORED !!)\n";

        $this->output->set_content_type('text/plain')->set_output($out);
    }

    // ------------------------------------------------------------------
    // Data integrity report (read-only, no mutation)
    // ------------------------------------------------------------------

    public function integrity()
    {
        $checks = [];

        // Single source of truth shared with AdminController::score_integrity() —
        // see classworks::get_scores_exceeding_max().
        $checks['scores_exceeding_max'] = $this->classworks->get_scores_exceeding_max();

        $checks['orphan_classworks'] = $this->db->query("
            SELECT c.classwork_id, c.student_id, c.assessment_id, c.score
            FROM classworks c
            LEFT JOIN assessment_full a ON a.assessment_id = c.assessment_id
            WHERE a.assessment_id IS NULL
        ")->result_array();

        $checks['negative_scores'] = $this->db->query("
            SELECT classwork_id, student_id, assessment_id, score
            FROM classworks WHERE score < 0
        ")->result_array();

        // Enrolled rows (status='enrolled' OR NULL — see Grade_calculator's
        // roster() docblock for why NULL counts) pointing at a student_master
        // row that no longer exists. These students are invisible on every
        // grade sheet, since Grade_calculator inner-joins student_master to
        // get a name — so they are silently ungraded.
        $checks['enrolled_without_student_master'] = $this->db->query("
            SELECT cs.id, cs.student_id, cs.section, cs.schedule_id, cs.class_id
            FROM class_student cs
            LEFT JOIN student_master sm ON sm.trans_no = cs.student_id
            JOIN class_schedule sched   ON sched.schedule_id = cs.schedule_id
            JOIN semester_master sem    ON sem.trans_no = sched.semester_id AND sem.is_active = 1
            WHERE (cs.status = 'enrolled' OR cs.status IS NULL) AND sm.trans_no IS NULL
            ORDER BY cs.section
        ")->result_array();

        // Enrolled rows whose schedule is not in the active semester — they
        // will never appear on any current grade sheet.
        $checks['enrolled_outside_active_semester'] = $this->db->query("
            SELECT cs.id, cs.student_id, cs.section, cs.schedule_id
            FROM class_student cs
            LEFT JOIN class_schedule sched ON sched.schedule_id = cs.schedule_id
            LEFT JOIN semester_master sem  ON sem.trans_no = sched.semester_id AND sem.is_active = 1
            WHERE (cs.status = 'enrolled' OR cs.status IS NULL) AND sem.trans_no IS NULL
        ")->result_array();

        // Renderable roster size per active schedule, so the counts on the
        // sheets can be reconciled against enrolment at a glance. Matches
        // Grade_calculator::roster()'s definition exactly.
        $checks['roster_counts'] = $this->db->query("
            SELECT sched.schedule_id, sched.section,
                   COUNT(DISTINCT cs.student_id)  AS enrolled_rows,
                   COUNT(DISTINCT sm.trans_no)    AS renderable
            FROM class_schedule sched
            JOIN semester_master sem  ON sem.trans_no = sched.semester_id AND sem.is_active = 1
            LEFT JOIN class_student cs ON cs.schedule_id = sched.schedule_id
                                       AND (cs.status = 'enrolled' OR cs.status IS NULL)
            LEFT JOIN student_master sm ON sm.trans_no = cs.student_id
            GROUP BY sched.schedule_id, sched.section
            ORDER BY sched.section
        ")->result_array();

        // is_cleared: writers set 0, most readers test IS NULL. See
        // class_student.php:29,48 vs class_student.php:100 / AdminController.php:1349.
        $checks['is_cleared_distribution'] = $this->db->query("
            SELECT is_cleared, COUNT(*) AS n
            FROM class_student
            WHERE status = 'enrolled' OR status IS NULL
            GROUP BY is_cleared
        ")->result_array();

        // io_type.percentage is varchar(50) and is used in arithmetic.
        $checks['io_type_weights'] = $this->db->query("
            SELECT iotype_id, type, percentage FROM io_type ORDER BY iotype_id
        ")->result_array();

        $sum = 0;
        foreach ($checks['io_type_weights'] as $w) {
            $sum += (float)$w['percentage'];
        }
        $checks['io_type_weight_sum'] = $sum;

        $checks['multiple_active_semesters'] = $this->db->query("
            SELECT trans_no, semcode, passing_rate FROM semester_master WHERE is_active = 1
        ")->result_array();

        $out = "GRADE DATA INTEGRITY REPORT (read-only — nothing was modified)\n";
        $out .= str_repeat('=', 62) . "\n\n";
        foreach ($checks as $name => $result) {
            if (is_scalar($result)) {
                $out .= sprintf("%-28s %s\n", $name, $result);
                continue;
            }
            $out .= sprintf("%-28s %d row(s)\n", $name, count($result));
            foreach (array_slice($result, 0, 25) as $r) {
                $out .= '    ' . json_encode($r) . "\n";
            }
            $out .= "\n";
        }

        $this->output->set_content_type('text/plain')->set_output($out);
    }

    // ------------------------------------------------------------------
    // Helpers
    // ------------------------------------------------------------------

    /** Sections that have a schedule in the active semester. */
    private function _active_sections()
    {
        return $this->db->query("
            SELECT DISTINCT cs.section
            FROM class_schedule cs
            JOIN semester_master sem ON cs.semester_id = sem.trans_no AND sem.is_active = 1
            ORDER BY cs.section
        ")->result_array();
    }

    private function _schedule_for_section($section)
    {
        return $this->db->query("
            SELECT cs.schedule_id, cs.class_id
            FROM class_schedule cs
            JOIN semester_master sem ON cs.semester_id = sem.trans_no AND sem.is_active = 1
            WHERE cs.section = ?
            ORDER BY cs.schedule_id
            LIMIT 1
        ", [$section])->row_array();
    }

    private function _write_snapshot($kind, array $data)
    {
        $dir = FCPATH . self::AUDIT_DIR;
        if (!is_dir($dir)) {
            mkdir($dir, 0775, true);
        }
        $path = $dir . $kind . '_' . date('Ymd_His') . '.json';
        file_put_contents($path, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
        // Also keep a stable "latest" pointer so diff() needs no argument.
        file_put_contents($dir . $kind . '_latest.json', json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
        return $path;
    }

    private function _read_snapshot($kind)
    {
        $path = FCPATH . self::AUDIT_DIR . $kind . '_latest.json';
        if (!is_file($path)) {
            return null;
        }
        return json_decode(file_get_contents($path), true);
    }

    private function _summarise(array $snapshot)
    {
        $out = "=== Baseline summary ===\n";
        foreach ($snapshot['sections'] as $section => $terms) {
            foreach ($terms as $term => $d) {
                $out .= sprintf(
                    "  %-6s %-8s students=%-4d rows=%-5d\n",
                    $section, $term, $d['student_count'], $d['row_count']
                );
            }
        }
        return $out;
    }
}
