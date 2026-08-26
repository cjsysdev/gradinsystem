<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * The authoritative grade engine.
 *
 * Replaces four near-duplicate SQL queries in classworks.php, eight copy-pasted
 * weighted-sum loops in GradesController.php, and convertPercentageToGradePoint()
 * in conversion_helper.php.
 *
 * Three strictly separated layers:
 *
 *   1. DATA    — _raw_components() / _roster() return raw sums only. No
 *                transmutation, no weighting, no rounding-for-display. SQL
 *                answers "what did this student score", never "what grade".
 *   2. POLICY  — transmute() / component() / term_grade() / final_grade() are
 *                pure PHP, take every rule as an argument, and touch no DB.
 *                These are the functions to unit-test.
 *   3. API     — for_student() / for_schedule() / for_all_schedules() compose
 *                the two and hand controllers a ready-to-render structure.
 *
 * Invariants deliberately preserved from the legacy code:
 *   - A NULL classworks.score counts as 0 (so grades do not silently change).
 *     Ungraded work is surfaced separately as `pending_count`.
 *   - assessment_full.assessment_id is a SECTION id, so joining classworks on
 *     it is 1:1 and SUM(max_score) does not double-count across sibling
 *     sections. Do not "simplify" that join.
 *
 * Invariants deliberately corrected:
 *   - The roster is keyed on class_student.schedule_id in the active semester,
 *     accepting status='enrolled' OR status IS NULL. The old
 *     `cs.section = sched.section` join pulled in prior-semester rows (90
 *     students rendered on a 51-student section). status IS NULL is
 *     deliberately included: only two values ever appear in this column
 *     across the whole table (NULL and 'enrolled') and there is no
 *     'dropped'/'withdrawn' value — NULL is a backfill gap from an older bulk
 *     import path, not an intentional exclusion. If a real "not currently
 *     enrolled" status is ever introduced, this must become an explicit
 *     exclusion (`status NOT IN (...)`) rather than reverting to `= 'enrolled'`,
 *     or every legacy-imported semester's roster goes empty again.
 *   - The passing rate comes from semester_master, not a hardcoded 60.
 */
class Grade_calculator extends CI_Model
{
    private $io_types = null; // lazy cache: iotype_id => ['type', 'percentage']

    public function __construct()
    {
        parent::__construct();
        $this->load->config('grading', TRUE);
    }

    private function cfg($key)
    {
        return $this->config->item($key, 'grading');
    }

    // ==================================================================
    // LAYER 2 — POLICY (pure functions, no DB, no side effects)
    // ==================================================================

    /**
     * Convert a 0-100 percentage into a grade point on the 5.0 - 1.0 scale.
     *
     * Two straight lines meeting at $passing_rate:
     *   0 -> 5.0,  $passing_rate -> 3.0,  100 -> 1.0
     *
     * Returns float, or NULL when the inputs cannot produce a meaningful grade.
     * Never returns a string — the old helper returned number_format() output,
     * which callers then compared with `>`, and fed 'INC' into, silently
     * casting it to 0 and producing 5.00.
     */
    public function transmute($percentage, $passing_rate)
    {
        if ($percentage === null || $percentage === '' || !is_numeric($percentage)) {
            return null;
        }
        if ($passing_rate === null || !is_numeric($passing_rate)) {
            return null;
        }

        $pct = (float) $percentage;
        $pr  = (float) $passing_rate;

        // A passing rate of 0 or 100 makes one of the two segments a division
        // by zero. getActivitiesGrade() lacked this guard.
        if ($pr <= 0 || $pr >= 100) {
            return null;
        }
        if ($pct < 0 || $pct > 100) {
            return null;
        }

        $floor   = (float) $this->cfg('grading_point_floor');    // 5.0
        $passing = (float) $this->cfg('grading_point_passing');  // 3.0
        $ceiling = (float) $this->cfg('grading_point_ceiling');  // 1.0

        if ($pct <= $pr) {
            $gp = $floor - (($floor - $passing) / $pr) * $pct;
        } else {
            $gp = $passing - (($passing - $ceiling) / (100 - $pr)) * ($pct - $pr);
        }

        return round($gp, 2);
    }

    /**
     * Turn one component's raw sums into its displayable numbers.
     *
     * @param float      $sum_score  SUM(IFNULL(score,0)) for this io_type
     * @param float      $sum_max    SUM(max_score) for this io_type
     * @param int|float  $weight     io_type.percentage (e.g. 40 for 40%)
     */
    public function component($sum_score, $sum_max, $passing_rate, $weight)
    {
        $sum_score = (float) $sum_score;
        $sum_max   = (float) $sum_max;

        $percentage = ($sum_max > 0) ? ($sum_score / $sum_max) * 100 : null;

        return [
            'total_score'     => round($sum_score, 2),
            'total_max_score' => round($sum_max, 2),
            'percentage'      => ($percentage === null) ? null : round($percentage, 2),
            'grade_point'     => $this->transmute($percentage, $passing_rate),
            'weighted_grade'  => ($percentage === null) ? null : round($percentage * ((float) $weight / 100), 4),
        ];
    }

    /**
     * Combine a student's components into one term grade.
     *
     * Policy: the grade is INC unless every required io_type has at least one
     * assessment for this schedule + term. No renormalisation — a term missing
     * its Major Exam does not get its remaining components scaled up to 100%.
     *
     * @param array $components       iotype_id => component() output, plus n_assessments
     * @param array $required_iotypes iotype_ids that must be present
     */
    public function term_grade(array $components, array $required_iotypes, $passing_rate)
    {
        $present = [];
        $pending = 0;
        foreach ($components as $iotype_id => $c) {
            if (!empty($c['n_assessments'])) {
                $present[] = (int) $iotype_id;
            }
            $pending += (int) ($c['n_ungraded'] ?? 0);
        }

        $missing = array_values(array_diff($required_iotypes, $present));

        if (!empty($missing)) {
            return [
                'status'          => 'inc',
                'reason'          => 'missing_components',
                'percentage'      => null,
                'grade_point'     => null,
                'missing_iotypes' => $missing,
                'pending_count'   => $pending,
            ];
        }

        $percentage = 0.0;
        foreach ($components as $c) {
            $percentage += (float) ($c['weighted_grade'] ?? 0);
        }
        $percentage = round($percentage, 2);

        return [
            'status'          => 'ok',
            'reason'          => null,
            'percentage'      => $percentage,
            'grade_point'     => $this->transmute($percentage, $passing_rate),
            'missing_iotypes' => [],
            'pending_count'   => $pending,
        ];
    }

    /**
     * The "grade so far" — the same weighted sum as term_grade(), but
     * renormalised over only the components that actually have assessments.
     *
     * This is the ONE place renormalisation is allowed, and it exists solely so
     * a monitoring sheet can answer "where does this student stand today"
     * mid-semester. It is NOT a term grade and must never be reported as one:
     * term_grade() remains the only official answer, still INC, still
     * un-renormalised. Every caller renders this behind an explicit
     * display mode and labels it provisional.
     *
     * Renormalising is what makes the number readable. Without it a student
     * with 90% in the only recorded component (weight 30) scores 27 and the
     * whole sheet reads as failing, which is worse than showing INC.
     *
     * A component counts as recorded only when it has an assessment AND a
     * measurable percentage — an assessment with max_score 0 yields a NULL
     * percentage, and letting its weight into the denominator would silently
     * drag the result toward zero.
     */
    public function provisional_grade(array $components, $passing_rate)
    {
        $covered = 0.0; // weight of the components that have been recorded
        $earned  = 0.0; // their weighted_grade contributions
        $pending = 0;

        foreach ($components as $c) {
            $pending += (int) ($c['n_ungraded'] ?? 0);

            if (empty($c['n_assessments']) || ($c['percentage'] ?? null) === null) {
                continue;
            }
            $covered += (float) ($c['iotype_percentage'] ?? 0);
            $earned  += (float) ($c['weighted_grade'] ?? 0);
        }

        if ($covered <= 0) {
            return [
                'status'         => 'none',
                'reason'         => 'nothing_recorded',
                'percentage'     => null,
                'grade_point'    => null,
                'weight_covered' => 0.0,
                'pending_count'  => $pending,
            ];
        }

        $percentage = round($earned * (100 / $covered), 2);

        return [
            'status'         => 'provisional',
            'reason'         => null,
            'percentage'     => $percentage,
            'grade_point'    => $this->transmute($percentage, $passing_rate),
            'weight_covered' => round($covered, 2),
            'pending_count'  => $pending,
        ];
    }

    /**
     * The provisional counterpart of final_grade(): blend whichever terms have
     * a percentage, renormalised across their term weights.
     *
     * Takes two nullable percentages rather than term blocks so it stays a pure
     * function of the policy — the caller decides what each term's effective
     * percentage is (see _effective_percentage()).
     *
     * Deliberately does NOT apply grading_fail_as_inc_above. That rule exists so
     * an official sheet never reports a failing number; this function exists so
     * a teacher can see the failing number and act on it before it becomes
     * official. Suppressing it here would make the mode pointless for exactly
     * the students it is meant to surface.
     */
    public function provisional_final_grade($midterm_pct, $final_pct, $passing_rate)
    {
        $weights = $this->cfg('grading_term_weights');

        $covered = 0.0;
        $acc     = 0.0;
        foreach (['midterm' => $midterm_pct, 'final' => $final_pct] as $term => $pct) {
            if ($pct === null || !is_numeric($pct)) {
                continue;
            }
            $acc     += (float) $pct * (float) $weights[$term];
            $covered += (float) $weights[$term];
        }

        if ($covered <= 0) {
            return [
                'status'         => 'none',
                'reason'         => 'nothing_recorded',
                'percentage'     => null,
                'grade_point'    => null,
                'weight_covered' => 0.0,
            ];
        }

        $percentage = round($acc / $covered, 2);

        return [
            'status'         => 'provisional',
            'reason'         => null,
            'percentage'     => $percentage,
            'grade_point'    => $this->transmute($percentage, $passing_rate),
            'weight_covered' => round($covered * 100, 2),
        ];
    }

    /**
     * Blend the two term grades into the overall grade.
     *
     * INC in either term propagates. A single configurable cutoff replaces the
     * four that had drifted apart (> 3.1, >= 3.09, > 3.00, >= 3.1).
     */
    public function final_grade($midterm, $final, $passing_rate)
    {
        if (($midterm['status'] ?? '') !== 'ok' || ($final['status'] ?? '') !== 'ok') {
            return [
                'status'      => 'inc',
                'reason'      => 'term_incomplete',
                'percentage'  => null,
                'grade_point' => null,
            ];
        }

        $weights = $this->cfg('grading_term_weights');
        $percentage = round(
            ($midterm['percentage'] * $weights['midterm']) + ($final['percentage'] * $weights['final']),
            2
        );
        $grade_point = $this->transmute($percentage, $passing_rate);

        $fail_above = $this->cfg('grading_fail_as_inc_above');
        if ($fail_above !== null && $grade_point !== null && $grade_point > (float) $fail_above) {
            return [
                'status'      => 'inc',
                'reason'      => 'below_passing',
                'percentage'  => $percentage,
                'grade_point' => $grade_point,
            ];
        }

        return [
            'status'      => 'ok',
            'reason'      => null,
            'percentage'  => $percentage,
            'grade_point' => $grade_point,
        ];
    }

    // ==================================================================
    // LAYER 1 — DATA (raw facts only)
    // ==================================================================

    /** io_type rows, cached per request. `percentage` is varchar in the DB. */
    public function io_types()
    {
        if ($this->io_types === null) {
            $this->io_types = [];
            foreach ($this->db->query("SELECT iotype_id, type, percentage FROM io_type ORDER BY iotype_id")->result_array() as $r) {
                $this->io_types[(int) $r['iotype_id']] = [
                    'iotype_id'  => (int) $r['iotype_id'],
                    'type'       => $r['type'],
                    'percentage' => (float) $r['percentage'],
                ];
            }
        }
        return $this->io_types;
    }

    /**
     * The io_types a term must contain before it can be graded at all.
     * Public because the front-end estimator has to apply the same INC rule as
     * term_grade() does — see policy().
     */
    public function required_iotypes()
    {
        $configured = $this->cfg('grading_required_iotypes');
        if (is_array($configured)) {
            return array_map('intval', $configured);
        }
        return array_keys($this->io_types());
    }

    /**
     * Every tunable the POLICY layer reads, in one serialisable bundle.
     *
     * Exists so the read-only front-end estimator (assets/js/grade-estimator.js)
     * can mirror transmute()/component()/term_grade()/final_grade() without
     * hardcoding a single constant of its own. Nothing here is a new rule — it
     * is the same config/grading.php values these methods already use, exposed
     * so there remains exactly ONE place a rule is defined.
     *
     * Change a rule in config/grading.php and the estimator follows it; the
     * estimator must never carry a literal that could drift from this.
     */
    public function policy()
    {
        return [
            'point_floor'          => (float) $this->cfg('grading_point_floor'),
            'point_passing'        => (float) $this->cfg('grading_point_passing'),
            'point_ceiling'        => (float) $this->cfg('grading_point_ceiling'),
            'term_weights'         => $this->cfg('grading_term_weights'),
            'fail_as_inc_above'    => $this->cfg('grading_fail_as_inc_above'),
            'passing_rate_fallback'=> (float) $this->cfg('grading_passing_rate_fallback'),
            'required_iotypes'     => $this->required_iotypes(),
        ];
    }

    /**
     * The passing rate governing a schedule, from its semester. Falls back to
     * the configured default only when the DB value is missing or unusable.
     */
    public function passing_rate_for_schedule($schedule_id)
    {
        $row = $this->db->query("
            SELECT sem.passing_rate
            FROM class_schedule sched
            JOIN semester_master sem ON sem.trans_no = sched.semester_id
            WHERE sched.schedule_id = ?
        ", [$schedule_id])->row_array();

        $rate = $row['passing_rate'] ?? null;
        if ($rate === null || !is_numeric($rate) || $rate <= 0 || $rate >= 100) {
            return (float) $this->cfg('grading_passing_rate_fallback');
        }
        return (float) $rate;
    }

    /**
     * Enrolled students on a schedule, in the active semester.
     *
     * Keyed on class_student.schedule_id — the same roster definition already
     * used by classworks::get_missing_submissions() and
     * create_blank_for_schedule(). The legacy grade queries instead joined
     * `cs.section = sched.section`, which ignored semester and enrolment status.
     */
    public function roster($schedule_id)
    {
        return $this->db->query("
            SELECT cs.student_id,
                   sm.student_no, sm.firstname, sm.lastname, sm.middlename,
                   cs.is_cleared
            FROM class_student cs
            JOIN class_schedule sched ON sched.schedule_id = cs.schedule_id
            JOIN semester_master sem  ON sem.trans_no = sched.semester_id AND sem.is_active = 1
            JOIN student_master sm    ON sm.trans_no = cs.student_id
            WHERE cs.schedule_id = ?
              AND (cs.status = 'enrolled' OR cs.status IS NULL)
            GROUP BY cs.student_id, sm.student_no, sm.firstname, sm.lastname, sm.middlename, cs.is_cleared
            ORDER BY sm.lastname, sm.firstname
        ", [$schedule_id])->result_array();
    }

    /**
     * Raw per-(student, io_type) sums for one schedule + term.
     *
     * Returns sums only — the CASE/transmutation that used to live here is now
     * in transmute(). SUM(IFNULL(c.score,0)) is retained deliberately: an
     * ungraded submission still counts as 0, exactly as before, and is reported
     * separately via n_ungraded.
     */
    private function raw_components($schedule_id, $term)
    {
        return $this->db->query("
            SELECT cs.student_id,
                   a.iotype_id,
                   SUM(IFNULL(c.score, 0))                        AS sum_score,
                   SUM(a.max_score)                               AS sum_max,
                   COUNT(a.assessment_id)                         AS n_assessments,
                   SUM(CASE WHEN c.score IS NULL THEN 1 ELSE 0 END) AS n_ungraded
            FROM class_student cs
            JOIN class_schedule sched ON sched.schedule_id = cs.schedule_id
            JOIN semester_master sem  ON sem.trans_no = sched.semester_id AND sem.is_active = 1
            JOIN assessment_full a    ON a.schedule_id = sched.schedule_id AND a.term = ?
            LEFT JOIN classworks c    ON c.assessment_id = a.assessment_id
                                     AND c.student_id = cs.student_id
            WHERE cs.schedule_id = ?
              AND (cs.status = 'enrolled' OR cs.status IS NULL)
            GROUP BY cs.student_id, a.iotype_id
        ", [$term, $schedule_id])->result_array();
    }

    /**
     * Attendance summary for a schedule, from the semester start date.
     *
     * Restricted to this schedule. The legacy subqueries grouped by student
     * with no schedule or class restriction, so a student taking two courses
     * saw both courses' absences on either grade sheet.
     */
    public function attendance_for_schedule($schedule_id)
    {
        $late_minutes = (int) $this->cfg('grading_late_threshold_minutes');

        $rows = $this->db->query("
            SELECT att.student_id,
                   SUM(att.status = 'absent')  AS absences,
                   SUM(att.status = 'present') AS presents,
                   SUM(att.status = 'present'
                       AND TIMESTAMPDIFF(MINUTE,
                             CONCAT(DATE(att.date), ' ', sched.time_start),
                             att.date) > ?)    AS lates
            FROM attendance att
            JOIN class_schedule sched ON sched.schedule_id = att.schedule_id
            JOIN semester_master sem  ON sem.trans_no = sched.semester_id AND sem.is_active = 1
            WHERE att.schedule_id = ?
              AND DATE(att.date) >= sem.class_started
            GROUP BY att.student_id
        ", [$late_minutes, $schedule_id])->result_array();

        $out = [];
        foreach ($rows as $r) {
            $out[$r['student_id']] = [
                'present' => (int) $r['presents'],
                'absent'  => (int) $r['absences'],
                'late'    => (int) $r['lates'],
            ];
        }
        return $out;
    }

    // ==================================================================
    // LAYER 3 — PUBLIC API
    // ==================================================================

    /**
     * Every enrolled student's grade for one schedule + term.
     *
     * @return array{
     *   schedule_id:int, term:string, passing_rate:float,
     *   io_types:array, required_iotypes:array, students:array
     * }
     */
    public function for_schedule($schedule_id, $term, $with_attendance = FALSE)
    {
        $io_types     = $this->io_types();
        $required     = $this->required_iotypes();
        $passing_rate = $this->passing_rate_for_schedule($schedule_id);

        $raw = [];
        foreach ($this->raw_components($schedule_id, $term) as $r) {
            $raw[$r['student_id']][(int) $r['iotype_id']] = $r;
        }

        $attendance = $with_attendance ? $this->attendance_for_schedule($schedule_id) : [];

        $students = [];
        foreach ($this->roster($schedule_id) as $s) {
            $sid = $s['student_id'];

            $components = [];
            foreach ($io_types as $iotype_id => $io) {
                $r = $raw[$sid][$iotype_id] ?? null;

                // A component with no assessments still gets an entry, so the
                // view can show the category and term_grade() can see it is
                // missing rather than silently dropping its weight.
                $c = $this->component(
                    $r['sum_score'] ?? 0,
                    $r['sum_max'] ?? 0,
                    $passing_rate,
                    $io['percentage']
                );
                $c['iotype_id']         = $iotype_id;
                $c['iotype_name']       = $io['type'];
                $c['iotype_percentage'] = $io['percentage'];
                $c['n_assessments']     = (int) ($r['n_assessments'] ?? 0);
                $c['n_ungraded']        = (int) ($r['n_ungraded'] ?? 0);

                $components[$iotype_id] = $c;
            }

            // The official grade and the "so far" grade travel together on the
            // same block, so choosing between them is a rendering decision at
            // the call site rather than a second trip through the engine.
            $term = $this->term_grade($components, $required, $passing_rate);
            $term['provisional'] = $this->provisional_grade($components, $passing_rate);

            $students[$sid] = [
                'student_id' => $sid,
                'student_no' => $s['student_no'],
                'firstname'  => $s['firstname'],
                'lastname'   => $s['lastname'],
                'middlename' => $s['middlename'],
                'is_cleared' => $s['is_cleared'],
                'components' => $components,
                'term'       => $term,
                'attendance' => $attendance[$sid] ?? ['present' => 0, 'absent' => 0, 'late' => 0],
            ];
        }

        return [
            'schedule_id'      => (int) $schedule_id,
            'term'             => $term,
            'passing_rate'     => $passing_rate,
            'io_types'         => $io_types,
            'required_iotypes' => $required,
            'students'         => $students,
        ];
    }

    /**
     * final_grade() with its provisional counterpart attached, so both overall
     * views are built the same way wherever an overall grade is produced.
     */
    public function overall_grade(array $midterm, array $final, $passing_rate)
    {
        $overall = $this->final_grade($midterm, $final, $passing_rate);
        $overall['provisional'] = $this->provisional_final_grade(
            $this->effective_percentage($midterm),
            $this->effective_percentage($final),
            $passing_rate
        );
        return $overall;
    }

    /**
     * The percentage a term contributes in provisional mode: its own when the
     * term is complete, otherwise its renormalised "so far" figure, and NULL
     * when the term has nothing recorded at all (so an unstarted final term
     * drops out of the blend instead of counting as a zero).
     */
    private function effective_percentage(array $term)
    {
        if (($term['status'] ?? '') === 'ok') {
            return $term['percentage'];
        }
        return $term['provisional']['percentage'] ?? null;
    }

    /**
     * Both terms plus the overall blend, for every student on a schedule.
     * Backs the section final-grade sheets.
     */
    public function for_schedule_final($schedule_id, $with_attendance = TRUE)
    {
        $midterm = $this->for_schedule($schedule_id, 'midterm', $with_attendance);
        $final   = $this->for_schedule($schedule_id, 'final', $with_attendance);
        $passing_rate = $midterm['passing_rate'];

        $students = [];
        foreach ($midterm['students'] as $sid => $m) {
            $f = $final['students'][$sid] ?? null;
            $f_term = $f['term'] ?? ['status' => 'inc', 'percentage' => null, 'grade_point' => null];

            $students[$sid] = [
                'student_id'  => $m['student_id'],
                'student_no'  => $m['student_no'],
                'firstname'   => $m['firstname'],
                'lastname'    => $m['lastname'],
                'middlename'  => $m['middlename'],
                'is_cleared'  => $m['is_cleared'],
                'attendance'  => $m['attendance'],
                'midterm'     => $m['term'],
                'final'       => $f_term,
                'overall'     => $this->overall_grade($m['term'], $f_term, $passing_rate),
            ];
        }

        return [
            'schedule_id'  => (int) $schedule_id,
            'passing_rate' => $passing_rate,
            'io_types'     => $midterm['io_types'],
            'students'     => $students,
        ];
    }

    /**
     * One student's own grades. Resolves the schedule from their active
     * enrolment rather than reading $this->session->section — the old query
     * used the session directly and silently returned nothing when it was stale.
     */
    public function for_student($student_id, $schedule_id = null)
    {
        if ($schedule_id === null) {
            $row = $this->db->query("
                SELECT cs.schedule_id
                FROM class_student cs
                JOIN class_schedule sched ON sched.schedule_id = cs.schedule_id
                JOIN semester_master sem  ON sem.trans_no = sched.semester_id AND sem.is_active = 1
                WHERE cs.student_id = ? AND (cs.status = 'enrolled' OR cs.status IS NULL)
                ORDER BY cs.schedule_id
                LIMIT 1
            ", [$student_id])->row_array();

            if (!$row) {
                return null;
            }
            $schedule_id = $row['schedule_id'];
        }

        $midterm = $this->for_schedule($schedule_id, 'midterm');
        $final   = $this->for_schedule($schedule_id, 'final');

        $m = $midterm['students'][$student_id] ?? null;
        if (!$m) {
            return null;
        }
        $f = $final['students'][$student_id] ?? null;
        $f_term = $f['term'] ?? ['status' => 'inc', 'percentage' => null, 'grade_point' => null, 'pending_count' => 0];

        return [
            'schedule_id'        => (int) $schedule_id,
            'passing_rate'       => $midterm['passing_rate'],
            'io_types'           => $midterm['io_types'],
            'required_iotypes'   => $midterm['required_iotypes'],
            'policy'             => $this->policy(),
            'midterm_components' => $m['components'],
            'final_components'   => $f['components'] ?? [],
            'midterm'            => $m['term'],
            'final'              => $f_term,
            'overall'            => $this->overall_grade($m['term'], $f_term, $midterm['passing_rate']),
        ];
    }

    /** Every active schedule, for the all-sections sheet. */
    public function active_schedules()
    {
        return $this->db->query("
            SELECT sched.schedule_id, sched.section, sched.type,
                   sched.time_start, sched.time_end, sched.day,
                   cl.class_id, cl.class_code, cl.class_name
            FROM class_schedule sched
            JOIN semester_master sem ON sem.trans_no = sched.semester_id AND sem.is_active = 1
            JOIN classes cl ON cl.class_id = sched.class_id
            ORDER BY sched.section, sched.schedule_id
        ")->result_array();
    }

    /** Resolve a section name to its schedule(s) in the active semester. */
    public function schedules_for_section($section)
    {
        return $this->db->query("
            SELECT sched.schedule_id, sched.section, sched.type,
                   sched.time_start, sched.time_end, sched.day,
                   cl.class_id, cl.class_code, cl.class_name
            FROM class_schedule sched
            JOIN semester_master sem ON sem.trans_no = sched.semester_id AND sem.is_active = 1
            JOIN classes cl ON cl.class_id = sched.class_id
            WHERE sched.section = ?
            ORDER BY sched.schedule_id
        ", [$section])->result_array();
    }

    /**
     * The schedules one student is actually enrolled in this semester.
     *
     * Same roster rule as roster() — schedule_id + enrolment status + active
     * semester — so a student profile can show a grade block per schedule
     * without falling back to the section string. for_student() resolves only
     * the first of these when no schedule is given.
     */
    public function schedules_for_student($student_id)
    {
        return $this->db->query("
            SELECT sched.schedule_id, sched.section, sched.type,
                   sched.time_start, sched.time_end, sched.day,
                   cl.class_id, cl.class_code, cl.class_name
            FROM class_student cs
            JOIN class_schedule sched ON sched.schedule_id = cs.schedule_id
            JOIN semester_master sem  ON sem.trans_no = sched.semester_id AND sem.is_active = 1
            JOIN classes cl           ON cl.class_id = sched.class_id
            WHERE cs.student_id = ?
              AND (cs.status = 'enrolled' OR cs.status IS NULL)
            GROUP BY sched.schedule_id, sched.section, sched.type,
                     sched.time_start, sched.time_end, sched.day,
                     cl.class_id, cl.class_code, cl.class_name
            ORDER BY cl.class_code, sched.schedule_id
        ", [(int) $student_id])->result_array();
    }

    /**
     * All active schedules' final grades, flattened for the all-sections sheet.
     */
    public function for_all_schedules_final()
    {
        $out = [];
        foreach ($this->active_schedules() as $sched) {
            $result = $this->for_schedule_final($sched['schedule_id']);
            foreach ($result['students'] as $sid => $student) {
                $student['section']    = $sched['section'];
                $student['class_code'] = $sched['class_code'];
                $student['class_name'] = $sched['class_name'];
                $student['schedule']   = $this->format_schedule($sched);
                $out[] = $student;
            }
        }
        return $out;
    }

    /** "8:00AM - 11:00AM (Mon)" — was inlined in four places in the controller. */
    public function format_schedule(array $sched)
    {
        if (empty($sched['time_start']) || empty($sched['time_end'])) {
            return '';
        }
        return date('g:iA', strtotime($sched['time_start']))
             . ' - ' . date('g:iA', strtotime($sched['time_end']))
             . ' (' . ($sched['day'] ?? '') . ')';
    }

    // ==================================================================
    // Display helpers — so views never do arithmetic
    // ==================================================================

    /** The two ways an incomplete grade may be rendered. */
    const MODE_INC     = 'inc';     // official: an incomplete term reads 'INC'
    const MODE_CURRENT = 'current'; // monitoring: fall back to the "so far" grade

    /**
     * Render a term/overall grade block as either a number or 'INC'.
     *
     * $mode defaults to MODE_INC, which is the official rendering and the only
     * one any grade-submission sheet may use. MODE_CURRENT falls back to the
     * block's provisional figure when the official grade is not 'ok' — a
     * teacher-facing view of where the student stands today. It still returns
     * 'INC' when nothing has been recorded, because there is no standing to
     * report yet.
     *
     * Callers using MODE_CURRENT must label the result as provisional; see
     * is_provisional() for deciding which values need the label.
     */
    public function display_grade_point(array $grade, $decimals = 2, $mode = self::MODE_INC)
    {
        if (($grade['status'] ?? '') === 'ok' && $grade['grade_point'] !== null) {
            return number_format($grade['grade_point'], $decimals);
        }

        if ($mode === self::MODE_CURRENT) {
            $gp = $grade['provisional']['grade_point'] ?? null;
            if ($gp !== null) {
                return number_format($gp, $decimals);
            }
        }

        return 'INC';
    }

    /**
     * TRUE when display_grade_point() would fall back to the provisional figure
     * for this block — i.e. the rendered number is not the official grade.
     */
    public function is_provisional(array $grade, $mode = self::MODE_INC)
    {
        if ($mode !== self::MODE_CURRENT) {
            return FALSE;
        }
        if (($grade['status'] ?? '') === 'ok' && $grade['grade_point'] !== null) {
            return FALSE;
        }
        return ($grade['provisional']['grade_point'] ?? null) !== null;
    }

    public function display_percentage(array $grade, $decimals = 2)
    {
        if (($grade['status'] ?? '') !== 'ok' || $grade['percentage'] === null) {
            return 'INC';
        }
        return number_format($grade['percentage'], $decimals);
    }

    /**
     * Passed / Failed / INC for a term or overall block.
     *
     * The cutoff is grading_point_passing (the transmutation of exactly the
     * passing rate), so this stays in step with transmute() instead of being a
     * second 3.0 hardcoded into a view. Anything that is not an 'ok' grade is
     * INC — a slip must never label an incomplete term as Failed.
     *
     * @return array ['key' => 'passed'|'failed'|'inc', 'label' => string]
     */
    public function remark(array $grade)
    {
        if (($grade['status'] ?? '') !== 'ok' || ($grade['grade_point'] ?? null) === null) {
            return ['key' => 'inc', 'label' => 'INC'];
        }

        $passing = (float) $this->cfg('grading_point_passing');

        return ((float) $grade['grade_point'] <= $passing)
            ? ['key' => 'passed', 'label' => 'Passed']
            : ['key' => 'failed', 'label' => 'Failed'];
    }

    /**
     * Human-readable explanation of why a term or overall grade is not 'ok'.
     * Empty string when it is.
     *
     * $io_types is optional — it exists so a caller that already has the map
     * from for_schedule() can pass it instead of re-reading the table.
     */
    public function inc_reason(array $grade, array $io_types = null)
    {
        if (($grade['status'] ?? '') === 'ok') {
            return '';
        }

        if (($grade['reason'] ?? '') === 'missing_components' && !empty($grade['missing_iotypes'])) {
            $io_types = ($io_types === null) ? $this->io_types() : $io_types;

            $names = [];
            foreach ($grade['missing_iotypes'] as $id) {
                $names[] = $io_types[$id]['type'] ?? "io_type $id";
            }
            return 'No ' . implode(', ', $names) . ' recorded yet';
        }

        if (($grade['reason'] ?? '') === 'below_passing') {
            return 'Below passing';
        }

        return 'Incomplete';
    }
}
