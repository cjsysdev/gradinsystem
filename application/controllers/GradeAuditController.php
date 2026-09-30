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

        // --- provisional_grade: renormalises over the recorded components ---
        // Weights 30/30/40 with only Activity (30) recorded at 90% must read as
        // 90, not 27 — that rescaling is the whole point of the mode.
        $partial = [
            1 => ['weighted_grade' => 27.0, 'percentage' => 90.0, 'iotype_percentage' => 30, 'n_assessments' => 2, 'n_ungraded' => 1],
            2 => ['weighted_grade' => null, 'percentage' => null, 'iotype_percentage' => 30, 'n_assessments' => 0, 'n_ungraded' => 0],
            3 => ['weighted_grade' => null, 'percentage' => null, 'iotype_percentage' => 40, 'n_assessments' => 0, 'n_ungraded' => 0],
        ];
        $p = $gc->provisional_grade($partial, 60);
        $check('provisional_grade percentage (one component)', $p['percentage'], 90.0);
        $check('provisional_grade grade_point (one component)', $p['grade_point'], 1.5);
        $check('provisional_grade weight_covered', $p['weight_covered'], 30.0);
        if ($p['status'] !== 'provisional' || $p['pending_count'] !== 1) {
            $fail[] = 'provisional_grade status/pending wrong: ' . json_encode($p);
        } else { $pass++; }

        // A recorded component with no measurable percentage (max_score 0) must
        // stay out of the denominator instead of dragging the result down.
        $partial[2] = ['weighted_grade' => null, 'percentage' => null, 'iotype_percentage' => 30, 'n_assessments' => 4, 'n_ungraded' => 0];
        $check('provisional_grade ignores unmeasurable component', $gc->provisional_grade($partial, 60)['percentage'], 90.0);

        // Nothing recorded at all has no standing to report.
        $empty = [
            1 => ['weighted_grade' => null, 'percentage' => null, 'iotype_percentage' => 30, 'n_assessments' => 0, 'n_ungraded' => 0],
        ];
        $e = $gc->provisional_grade($empty, 60);
        if ($e['status'] !== 'none' || $e['grade_point'] !== null) {
            $fail[] = 'provisional_grade with nothing recorded should be status=none/null, got ' . json_encode($e);
        } else { $pass++; }

        // A complete term must produce the same number either way — the mode
        // may only ever change what an INCOMPLETE term shows.
        $complete = [
            1 => ['weighted_grade' => 24.0, 'percentage' => 80.0, 'iotype_percentage' => 30, 'n_assessments' => 1, 'n_ungraded' => 0],
            2 => ['weighted_grade' => 24.0, 'percentage' => 80.0, 'iotype_percentage' => 30, 'n_assessments' => 1, 'n_ungraded' => 0],
            3 => ['weighted_grade' => 32.0, 'percentage' => 80.0, 'iotype_percentage' => 40, 'n_assessments' => 1, 'n_ungraded' => 0],
        ];
        $check(
            'provisional == official when the term is complete',
            $gc->provisional_grade($complete, 60)['percentage'],
            $gc->term_grade($complete, [1, 2, 3], 60)['percentage']
        );

        // --- provisional_final_grade: an unstarted term drops out of the blend
        // rather than counting as a zero ---
        $check('provisional_final_grade midterm only', $gc->provisional_final_grade(90.0, null, 60)['percentage'], 90.0);
        $check('provisional_final_grade both terms',   $gc->provisional_final_grade(90.0, 70.0, 60)['percentage'], 80.0);
        if ($gc->provisional_final_grade(null, null, 60)['status'] !== 'none') {
            $fail[] = 'provisional_final_grade with no terms should be status=none';
        } else { $pass++; }

        // Provisional mode deliberately shows a failing number where the
        // official sheet reports INC — that is what makes it actionable.
        $failing = $gc->provisional_final_grade(40.0, 40.0, 60);
        if ($failing['status'] !== 'provisional' || $failing['grade_point'] === null) {
            $fail[] = 'provisional_final_grade must report failing grades as numbers, got ' . json_encode($failing);
        } else { $pass++; }

        // --- display mode: MODE_INC must be unaffected by a provisional block ---
        $inc_block = [
            'status' => 'inc', 'grade_point' => null,
            'provisional' => ['status' => 'provisional', 'grade_point' => 1.5],
        ];
        if ($gc->display_grade_point($inc_block, 2) !== 'INC') {
            $fail[] = 'display_grade_point default mode must still render INC';
        } else { $pass++; }
        if ($gc->display_grade_point($inc_block, 2, Grade_calculator::MODE_CURRENT) !== '1.50') {
            $fail[] = 'display_grade_point MODE_CURRENT should render the provisional figure';
        } else { $pass++; }
        if ($gc->is_provisional($inc_block, Grade_calculator::MODE_INC)
            || !$gc->is_provisional($inc_block, Grade_calculator::MODE_CURRENT)) {
            $fail[] = 'is_provisional must be false in MODE_INC and true in MODE_CURRENT';
        } else { $pass++; }

        // No provisional figure to fall back on -> still INC in either mode.
        if ($gc->display_grade_point(['status' => 'inc', 'grade_point' => null], 2, Grade_calculator::MODE_CURRENT) !== 'INC') {
            $fail[] = 'MODE_CURRENT must stay INC when there is no provisional figure';
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

    // ------------------------------------------------------------------
    // PHP <-> JS policy parity
    // ------------------------------------------------------------------

    /**
     * Emit a grid of policy inputs together with THIS MODEL'S answers, as JSON.
     *
     * assets/js/grade-estimator.js re-implements the POLICY layer so the
     * student dashboard can show live "what if" estimates without a round trip.
     * Two implementations of one rule set is exactly the duplication this
     * codebase spent a refactor removing, so it is tolerated only because it is
     * machine-checked: these vectors are generated BY Grade_calculator, and
     * jsparity() replays them through the JavaScript. Any drift fails loudly.
     *
     * Expectations are never hand-written here — only the inputs are. Whatever
     * the PHP answers today is by definition correct, so this file can never
     * disagree with the engine it is testing.
     */
    public function policyvectors()
    {
        $this->load->model('Grade_calculator');
        $gc = $this->Grade_calculator;

        $vectors = [];

        // --- transmute: anchors, both legs, boundaries, invalid inputs ------
        $percentages = [0, 0.01, 0.5, 1, 12.34, 25, 30, 49.99, 50, 59.99, 60,
                        60.01, 66.666, 75, 80, 87.5, 99.99, 100,
                        1.005, 2.675, 33.335];
        $invalid_pct = [-1, 101, -0.01, 100.01, null, '', 'INC', 'abc'];
        $rates       = [50, 60, 75];
        $bad_rates   = [0, 100, -5, 105, null, 'x'];

        foreach ($rates as $pr) {
            foreach (array_merge($percentages, $invalid_pct) as $pct) {
                $vectors[] = [
                    'fn' => 'transmute', 'args' => [$pct, $pr],
                    'expected' => $gc->transmute($pct, $pr),
                ];
            }
        }
        foreach ($bad_rates as $pr) {
            $vectors[] = [
                'fn' => 'transmute', 'args' => [50, $pr],
                'expected' => $gc->transmute(50, $pr),
            ];
        }

        // --- component: including the zero-max and repeating-decimal cases --
        $pairs = [[0, 0], [0, 50], [1, 3], [2, 3], [37, 50], [50, 50],
                  [100.5, 137.25], [7, 9], [123, 200], [45.5, 60], [0.5, 3]];
        foreach ($rates as $pr) {
            foreach ($pairs as $pair) {
                foreach ([10, 20, 30, 40] as $weight) {
                    $vectors[] = [
                        'fn' => 'component',
                        'args' => [$pair[0], $pair[1], $pr, $weight],
                        'expected' => $gc->component($pair[0], $pair[1], $pr, $weight),
                    ];
                }
            }
        }

        // --- term_grade: complete, partially missing, fully missing ---------
        $required = [1, 2, 3, 4];
        $sets = [
            [[1, 10.0, 3, 0], [2, 40.0, 1, 0], [3, 30.0, 1, 2], [4, 20.0, 2, 0]],
            [[1, 10.0, 3, 0], [2, 40.0, 1, 0], [3, 0.0,  0, 0], [4, 20.0, 2, 0]],
            [[1, 0.0,  0, 0], [2, 0.0,  0, 0], [3, 0.0,  0, 0], [4, 0.0,  0, 0]],
            [[1, 7.335, 2, 1], [2, 22.225, 4, 0], [3, 14.445, 1, 3], [4, 11.115, 2, 0]],
            [[1, 9.9999, 1, 0], [2, 29.9999, 1, 0], [3, 19.9999, 1, 0], [4, 14.9999, 1, 0]],
        ];
        foreach ($rates as $pr) {
            foreach ($sets as $set) {
                $components = [];
                $flat = [];
                foreach ($set as $row) {
                    list($iotype_id, $weighted, $n_assess, $n_ungraded) = $row;
                    $components[$iotype_id] = [
                        'weighted_grade' => $weighted,
                        'n_assessments'  => $n_assess,
                        'n_ungraded'     => $n_ungraded,
                    ];
                    $flat[] = [
                        'iotype_id'      => $iotype_id,
                        'weighted_grade' => $weighted,
                        'n_assessments'  => $n_assess,
                        'n_ungraded'     => $n_ungraded,
                    ];
                }
                $vectors[] = [
                    'fn' => 'term_grade',
                    'args' => [$flat, $required, $pr],
                    'expected' => $gc->term_grade($components, $required, $pr),
                ];
            }
        }

        // --- final_grade: blends, INC propagation, the below-passing cutoff -
        $terms = [
            ['status' => 'ok',  'percentage' => 90.0, 'grade_point' => 1.5],
            ['status' => 'ok',  'percentage' => 70.0, 'grade_point' => 2.5],
            ['status' => 'ok',  'percentage' => 60.0, 'grade_point' => 3.0],
            ['status' => 'ok',  'percentage' => 59.99, 'grade_point' => 3.0],
            ['status' => 'ok',  'percentage' => 50.0, 'grade_point' => 3.33],
            ['status' => 'ok',  'percentage' => 0.0,  'grade_point' => 5.0],
            ['status' => 'ok',  'percentage' => 100.0, 'grade_point' => 1.0],
            ['status' => 'inc', 'percentage' => null, 'grade_point' => null],
        ];
        foreach ($rates as $pr) {
            foreach ($terms as $m) {
                foreach ($terms as $f) {
                    $vectors[] = [
                        'fn' => 'final_grade',
                        'args' => [$m, $f, $pr],
                        'expected' => $gc->final_grade($m, $f, $pr),
                    ];
                }
            }
        }

        // --- deterministic spread, weighted towards rounding boundaries -----
        // A uniform random sweep is not enough. The failure mode that matters
        // is a value sitting exactly on a .xx5 boundary, where PHP rounds the
        // shortest decimal representation and a naive JS `Math.round(x * 100)`
        // does not. Those disagree on roughly 1.5% of realistic grades — enough
        // to shift a percentage by 0.01 and, at the cutoff, flip an INC.
        //
        // Boundaries are therefore generated on purpose: averaging two 2-place
        // percentages (exactly what final_grade() does) produces a 3-place .xx5
        // value whenever their hundredths sum is odd, and weighting a 2-place
        // percentage produces 5-place values that straddle the 4-place rounding
        // of weighted_grade.
        //
        // Fixed seed: the vector set must be identical run to run, or a parity
        // failure could not be reproduced.
        $seed = 20260819;
        $rand = function () use (&$seed) {
            $seed = ($seed * 1103515245 + 12345) % 2147483648;
            return $seed / 2147483648;
        };

        for ($i = 0; $i < 1200; $i++) {
            $pr = 50 + $rand() * 25;

            // Straightforward score/max component.
            $max = 1 + floor($rand() * 300);
            $sc  = round($rand() * $max, 2);
            $w   = 5 + floor($rand() * 45);
            $vectors[] = [
                'fn' => 'component', 'args' => [$sc, $max, $pr, $w],
                'expected' => $gc->component($sc, $max, $pr, $w),
            ];

            // Ratios that do not terminate, so the unrounded percentage differs
            // from the reported one.
            $den = 3 + floor($rand() * 26);
            $num = round($rand() * $den, 2);
            $vectors[] = [
                'fn' => 'component', 'args' => [$num, $den, $pr, $w],
                'expected' => $gc->component($num, $den, $pr, $w),
            ];

            $pct = round($rand() * 100, 4);
            $vectors[] = [
                'fn' => 'transmute', 'args' => [$pct, $pr],
                'expected' => $gc->transmute($pct, $pr),
            ];

            // Deliberate 3-place .xx5 values.
            $boundary = round($rand() * 1000) / 10 + 0.005;
            if ($boundary > 100) { $boundary = 100 - 0.005; }
            $vectors[] = [
                'fn' => 'transmute', 'args' => [$boundary, $pr],
                'expected' => $gc->transmute($boundary, $pr),
            ];

            // final_grade over two 2-place percentages: the blend lands on a
            // .xx5 boundary roughly half the time.
            $mp = round($rand() * 10000) / 100;
            $fp = round($rand() * 10000) / 100;
            $m_term = ['status' => 'ok', 'percentage' => $mp, 'grade_point' => $gc->transmute($mp, $pr)];
            $f_term = ['status' => 'ok', 'percentage' => $fp, 'grade_point' => $gc->transmute($fp, $pr)];
            $vectors[] = [
                'fn' => 'final_grade', 'args' => [$m_term, $f_term, $pr],
                'expected' => $gc->final_grade($m_term, $f_term, $pr),
            ];

            // term_grade over 4-place weighted contributions.
            $flat = [];
            $comp = [];
            for ($k = 1; $k <= 4; $k++) {
                $wg = round($rand() * 400000) / 10000;
                $na = ($i % 19 === 0 && $k === 3) ? 0 : 1 + floor($rand() * 4);
                $nu = floor($rand() * 4);
                $flat[] = ['iotype_id' => $k, 'weighted_grade' => $wg, 'n_assessments' => $na, 'n_ungraded' => $nu];
                $comp[$k] = ['weighted_grade' => $wg, 'n_assessments' => $na, 'n_ungraded' => $nu];
            }
            $vectors[] = [
                'fn' => 'term_grade', 'args' => [$flat, $required, $pr],
                'expected' => $gc->term_grade($comp, $required, $pr),
            ];
        }

        // --- end-to-end: an UNTOUCHED estimate must equal the real grade -----
        // The strongest property the estimator has to hold. A student opening
        // the sliders sees the same term totals and overall grade the server
        // printed above them; only moving a slider may change a number. This
        // catches drift the per-function vectors cannot — payload rounding, the
        // rounded-vs-unrounded percentage seed, the INC rule for components
        // with nothing recorded.
        $weights = [1 => 10, 2 => 40, 3 => 30, 4 => 20];
        $scenarios = [
            // [ [iotype => [sum_score, sum_max, n_assessments, n_ungraded]], ... ]
            'complete' => [
                'midterm' => [1 => [45, 50, 3, 0], 2 => [80, 100, 1, 0], 3 => [70, 75, 1, 2], 4 => [18, 20, 2, 0]],
                'final'   => [1 => [40, 50, 3, 0], 2 => [95, 100, 1, 0], 3 => [60, 75, 1, 0], 4 => [15, 20, 2, 0]],
            ],
            'midterm_missing_major_exam' => [
                'midterm' => [1 => [45, 50, 3, 0], 2 => [80, 100, 1, 0], 3 => [0, 0, 0, 0], 4 => [18, 20, 2, 0]],
                'final'   => [1 => [40, 50, 3, 0], 2 => [95, 100, 1, 0], 3 => [60, 75, 1, 0], 4 => [15, 20, 2, 0]],
            ],
            'nothing_recorded' => [
                'midterm' => [1 => [0, 0, 0, 0], 2 => [0, 0, 0, 0], 3 => [0, 0, 0, 0], 4 => [0, 0, 0, 0]],
                'final'   => [1 => [0, 0, 0, 0], 2 => [0, 0, 0, 0], 3 => [0, 0, 0, 0], 4 => [0, 0, 0, 0]],
            ],
            'repeating_decimals' => [
                'midterm' => [1 => [1, 3, 2, 1], 2 => [7, 9, 3, 0], 3 => [321.81, 584, 4, 0], 4 => [2, 3, 1, 0]],
                'final'   => [1 => [5, 7, 2, 0], 2 => [11, 13, 3, 0], 3 => [17, 19, 4, 1], 4 => [23, 29, 1, 0]],
            ],
            'below_passing' => [
                'midterm' => [1 => [10, 50, 3, 0], 2 => [30, 100, 1, 0], 3 => [20, 75, 1, 0], 4 => [5, 20, 2, 0]],
                'final'   => [1 => [12, 50, 3, 0], 2 => [35, 100, 1, 0], 3 => [25, 75, 1, 0], 4 => [6, 20, 2, 0]],
            ],
        ];

        foreach ($rates as $pr) {
            foreach ($scenarios as $name => $scenario) {
                $shaped = [];
                $terms_out = [];
                foreach (['midterm', 'final'] as $term_key) {
                    $components = [];
                    $shaped[$term_key] = [];
                    foreach ($scenario[$term_key] as $iotype_id => $row) {
                        list($sum_score, $sum_max, $n_assess, $n_ungraded) = $row;
                        $c = $gc->component($sum_score, $sum_max, $pr, $weights[$iotype_id]);
                        $c['n_assessments'] = $n_assess;
                        $c['n_ungraded']    = $n_ungraded;
                        $components[$iotype_id] = $c;

                        // Exactly the projection _estimator_payload() sends.
                        $shaped[$term_key][] = [
                            'iotype_id'         => $iotype_id,
                            'iotype_name'       => 'io_type ' . $iotype_id,
                            'iotype_percentage' => (float) $weights[$iotype_id],
                            'total_score'       => $c['total_score'],
                            'total_max_score'   => $c['total_max_score'],
                            'percentage'        => $c['percentage'],
                            'n_assessments'     => $n_assess,
                            'n_ungraded'        => $n_ungraded,
                        ];
                    }
                    $terms_out[$term_key] = $gc->term_grade($components, $required, $pr);
                }

                $shaped['passing_rate']     = $pr;
                $shaped['required_iotypes'] = $required;
                $shaped['policy']           = $gc->policy();

                $vectors[] = [
                    'fn'       => 'estimate_initial',
                    'label'    => $name,
                    'args'     => [$shaped],
                    'expected' => [
                        'midterm' => $terms_out['midterm'],
                        'final'   => $terms_out['final'],
                        'overall' => $gc->final_grade($terms_out['midterm'], $terms_out['final'], $pr),
                    ],
                ];
            }
        }

        // The hand-written scenarios above cover the shapes; these cover the
        // arithmetic. Awkward score/max ratios are the case where seeding a
        // slider from the payload's ROUNDED percentage instead of recomputing
        // from the raw sums shifts a term total by 0.01 — invisible in a tidy
        // scenario, wrong on the student's screen.
        for ($i = 0; $i < 200; $i++) {
            $pr = 50 + $rand() * 25;
            $shaped = [];
            $terms_out = [];

            foreach (['midterm', 'final'] as $term_key) {
                $components = [];
                $shaped[$term_key] = [];
                foreach ($weights as $iotype_id => $weight) {
                    $recorded = !($i % 23 === 0 && $iotype_id === 3);
                    if ($recorded) {
                        $sum_max   = 3 + floor($rand() * 297);
                        $sum_score = round($rand() * $sum_max, 2);
                        $n_assess  = 1 + floor($rand() * 5);
                        $n_ungrad  = floor($rand() * 3);
                    } else {
                        $sum_max = 0; $sum_score = 0; $n_assess = 0; $n_ungrad = 0;
                    }

                    $c = $gc->component($sum_score, $sum_max, $pr, $weight);
                    $c['n_assessments'] = $n_assess;
                    $c['n_ungraded']    = $n_ungrad;
                    $components[$iotype_id] = $c;

                    $shaped[$term_key][] = [
                        'iotype_id'         => $iotype_id,
                        'iotype_name'       => 'io_type ' . $iotype_id,
                        'iotype_percentage' => (float) $weight,
                        'total_score'       => $c['total_score'],
                        'total_max_score'   => $c['total_max_score'],
                        'percentage'        => $c['percentage'],
                        'n_assessments'     => $n_assess,
                        'n_ungraded'        => $n_ungrad,
                    ];
                }
                $terms_out[$term_key] = $gc->term_grade($components, $required, $pr);
            }

            $shaped['passing_rate']     = $pr;
            $shaped['required_iotypes'] = $required;
            $shaped['policy']           = $gc->policy();

            $vectors[] = [
                'fn'       => 'estimate_initial',
                'label'    => 'randomised #' . $i,
                'args'     => [$shaped],
                'expected' => [
                    'midterm' => $terms_out['midterm'],
                    'final'   => $terms_out['final'],
                    'overall' => $gc->final_grade($terms_out['midterm'], $terms_out['final'], $pr),
                ],
            ];
        }

        $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode([
                'generated_at' => date('Y-m-d H:i:s'),
                'policy'       => $gc->policy(),
                'vector_count' => count($vectors),
                'vectors'      => $vectors,
            ]));
    }

    /**
     * Run the JavaScript policy port against policyvectors() in the browser.
     *
     * Must be opened in a browser — the assertions execute in the same engine
     * the students' page uses, which is the only environment where a parity
     * claim means anything. Run alongside selftest and diff after any change to
     * grading, in PHP or in JS.
     */
    public function jsparity()
    {
        if (is_cli()) {
            $this->output->set_content_type('text/plain')->set_output(
                "jsparity must be run in a browser — it executes assets/js/grade-estimator.js.\n"
                . "Open /grade_audit/jsparity while logged in as admin.\n"
            );
            return;
        }
        $this->load->view('grade_js_parity');
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
