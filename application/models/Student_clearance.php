<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Student clearance — per semester AND per term.
 *
 * Replaces the single `class_student.is_cleared` flag, which could only say
 * "cleared this semester": a student cleared for the midterm exam stayed
 * cleared for the finals even if they had a fresh balance or unreturned
 * equipment. Clearance at CMC is issued once per term, so the term has to be
 * part of the key.
 *
 * Why a separate table instead of two more columns on class_student:
 * class_student holds one row per ENROLLMENT (per schedule_id), so a student
 * can have several rows sharing one `section` string — which is why
 * get_students_with_profile_by_section() groups by student_id. A clearance
 * column there would have to be written to every one of those rows and could
 * disagree between them. Clearance is a student-per-semester fact, not a
 * per-subject one. Same bridge-table shape as `section_officers`.
 *
 * A row's PRESENCE means cleared. There is no is_cleared column to keep in
 * sync, so unclear() is a plain DELETE and "uncleared" is a LEFT JOIN ... IS
 * NULL against the roster — a student who was never touched is uncleared by
 * definition, which is the correct default at the start of a term.
 *
 * Extends CI_Model, not MY_Model, because it owns install(): MY_Model's
 * constructor calls list_fields() on $table, which fails before the table
 * exists. Same reason Section_officer and Grouping_model extend CI_Model.
 */
class Student_clearance extends CI_Model
{
    protected $table = 'student_clearance';

    /** The two clearance terms, in display order. Keys are what's stored. */
    const TERMS = [
        'midterm' => 'Midterm',
        'final'   => 'Final',
    ];

    // ── Schema bootstrap ────────────────────────────────────────────────────
    // Idempotent — run via AdminStudentController/student_clearance_install,
    // which handles confirmation + backup. Never add a DROP here; later schema
    // changes go in as separate guarded ALTERs.
    public function install()
    {
        $this->load->library('schema_guard');
        $this->schema_guard->reset();

        // The UNIQUE key is the rule: one clearance per student per semester
        // per term. clear() relies on it (INSERT IGNORE) rather than
        // re-checking in PHP.
        $this->schema_guard->ddl("CREATE TABLE IF NOT EXISTS `student_clearance` (
            `clearance_id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            `student_id`   INT NOT NULL,
            `semester_id`  INT NOT NULL,
            `term`         VARCHAR(16) NOT NULL,
            `cleared_at`   DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            `cleared_by`   VARCHAR(64) DEFAULT NULL,
            UNIQUE KEY `uq_student_sem_term` (`student_id`, `semester_id`, `term`),
            KEY `idx_sem_term` (`semester_id`, `term`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

        // Backfill from the legacy flag. The old column had no term, and it
        // was checked when a student sat a MAJOR EXAM — in practice the
        // midterm exam — so it carries over as midterm clearance only. Finals
        // clearance starts empty on purpose: it is a separate issuance, and
        // inheriting it would silently clear everyone for the finals.
        // INSERT IGNORE + the UNIQUE key make a re-run a no-op.
        $this->schema_guard->ddl("INSERT IGNORE INTO `student_clearance`
                (`student_id`, `semester_id`, `term`, `cleared_by`)
            SELECT DISTINCT cs.student_id, cs.semester_id, 'midterm', 'backfill:class_student.is_cleared'
            FROM `class_student` cs
            WHERE cs.is_cleared = 1
              AND cs.student_id IS NOT NULL
              AND cs.semester_id IS NOT NULL");

        return $this->schema_guard->failures();
    }

    public function table_ready()
    {
        return $this->db->table_exists($this->table);
    }

    // ── Term helpers ────────────────────────────────────────────────────────
    /** TRUE for a storable term key. */
    public function valid_term($term)
    {
        return isset(self::TERMS[$term]);
    }

    /** Falls back to midterm so a missing/garbage query string can't 500. */
    public function normalize_term($term)
    {
        $term = strtolower(trim((string) $term));
        return $this->valid_term($term) ? $term : 'midterm';
    }

    /**
     * Which clearance an assessment's term requires.
     * `assessments.term` has three values (midterm | tentative-final | final);
     * clearance has two, and both final-period terms sit behind the finals
     * clearance.
     */
    public function term_for_assessment($assessment_term)
    {
        return strtolower(trim((string) $assessment_term)) === 'midterm' ? 'midterm' : 'final';
    }

    /** trans_no of the active semester, or NULL if none is flagged. */
    public function active_semester_id()
    {
        $row = $this->db->select('trans_no')->where('is_active', 1)->get('semester_master')->row();
        return $row ? (int) $row->trans_no : null;
    }

    /** Semesters for the picker, newest first, with the active one flagged. */
    public function semesters()
    {
        return $this->db
            ->select('trans_no, semcode, description, is_active')
            ->order_by('trans_no', 'DESC')
            ->get('semester_master')
            ->result_array();
    }

    // ── Reads ───────────────────────────────────────────────────────────────
    /** TRUE if this student holds clearance for that semester + term. */
    public function is_cleared($student_id, $semester_id, $term)
    {
        if (!$student_id || !$semester_id || !$this->table_ready()) {
            return false;
        }

        return $this->db
            ->where('student_id', (int) $student_id)
            ->where('semester_id', (int) $semester_id)
            ->where('term', $this->normalize_term($term))
            ->count_all_results($this->table) > 0;
    }

    /**
     * One row per section in the semester: how many enrolled students are
     * cleared for this term and how many are not.
     *
     * DISTINCT student_id throughout — class_student is per enrollment, so a
     * student on two schedules in one section must still count once.
     */
    public function sections_with_counts($semester_id, $term)
    {
        if (!$semester_id || !$this->table_ready()) {
            return [];
        }

        $sql = "
            SELECT cs.section,
                   COUNT(DISTINCT cs.student_id) AS total_count,
                   COUNT(DISTINCT sc.student_id) AS cleared_count,
                   COUNT(DISTINCT cs.student_id) - COUNT(DISTINCT sc.student_id) AS uncleared_count
            FROM class_student cs
            LEFT JOIN student_clearance sc
                   ON sc.student_id  = cs.student_id
                  AND sc.semester_id = cs.semester_id
                  AND sc.term        = ?
            WHERE cs.semester_id = ?
              AND (cs.status = 'enrolled' OR cs.status IS NULL)
              AND cs.section IS NOT NULL AND cs.section <> ''
            GROUP BY cs.section
            ORDER BY cs.section
        ";

        $query = $this->db->query($sql, [$this->normalize_term($term), (int) $semester_id]);
        return $query ? $query->result_array() : [];
    }

    /**
     * The section roster with each student's clearance state for one term.
     * Uncleared first, then by name, so the work to be done is at the top.
     */
    public function students_by_section($section, $semester_id, $term)
    {
        if (!$section || !$semester_id || !$this->table_ready()) {
            return [];
        }

        $sql = "
            SELECT cs.student_id,
                   sm.student_no,
                   sm.lastname,
                   sm.firstname,
                   sm.middlename,
                   MAX(sc.clearance_id) IS NOT NULL AS is_cleared,
                   MAX(sc.cleared_at)   AS cleared_at,
                   MAX(sc.cleared_by)   AS cleared_by
            FROM class_student cs
            JOIN student_master sm ON sm.trans_no = cs.student_id
            LEFT JOIN student_clearance sc
                   ON sc.student_id  = cs.student_id
                  AND sc.semester_id = cs.semester_id
                  AND sc.term        = ?
            WHERE cs.section = ?
              AND cs.semester_id = ?
              AND (cs.status = 'enrolled' OR cs.status IS NULL)
            GROUP BY cs.student_id, sm.student_no, sm.lastname, sm.firstname, sm.middlename
            ORDER BY is_cleared ASC, sm.lastname, sm.firstname
        ";

        $query = $this->db->query($sql, [$this->normalize_term($term), $section, (int) $semester_id]);
        return $query ? $query->result_array() : [];
    }

    // ── Policy: who may take what ───────────────────────────────────────────
    // The rules are in application/config/clearance.php; nothing below
    // hardcodes an io_type or a widget key. Every assessment entry point goes
    // through may_take() (via clearance_helper.php) so the gate cannot be
    // walked around by opening a widget's own URL directly.

    /** io_type ids that require clearance. Defaults to Major Exam. */
    public function gated_io_types()
    {
        $this->config->load('clearance', FALSE, TRUE);
        $types = $this->config->item('clearance_gated_io_types');
        return is_array($types) ? array_map('intval', $types) : [3];
    }

    /** Widget keys gated regardless of io_type. Defaults to none. */
    public function gated_widgets()
    {
        $this->config->load('clearance', FALSE, TRUE);
        $keys = $this->config->item('clearance_gated_widgets');
        return is_array($keys) ? $keys : [];
    }

    /** The blocked-student message, with the term label filled in. */
    public function gate_message($term)
    {
        $this->config->load('clearance', FALSE, TRUE);
        $tpl = $this->config->item('clearance_gate_message');
        $label = $this->normalize_term($term) === 'midterm' ? 'midterm' : 'finals';
        return $tpl
            ? sprintf($tpl, $label)
            : 'Only students with cleared clearance requirements may take the exam.';
    }

    /** TRUE when this assessment may only be taken by a cleared student. */
    public function requires_clearance($assessment)
    {
        $a = (array) $assessment;

        if (in_array((int) ($a['iotype_id'] ?? 0), $this->gated_io_types(), true)) {
            return true;
        }

        $gated_widgets = $this->gated_widgets();
        if ($gated_widgets && !empty($a['widget_id'])) {
            $this->load->model('Widgets_model');
            $widget = $this->Widgets_model->get($a['widget_id']);
            if ($widget && in_array($widget['widget_key'], $gated_widgets, true)) {
                return true;
            }
        }

        return false;
    }

    /**
     * May this student take this assessment?
     *
     * TRUE for anything the policy doesn't gate. For a gated assessment the
     * student needs clearance for the active semester and the assessment's own
     * term. While `student_clearance` is not installed yet it falls back to the
     * legacy per-semester class_student.is_cleared flag, so the rollout can
     * never lock out a whole cohort.
     */
    public function may_take($student_id, $assessment)
    {
        if (!$this->requires_clearance($assessment)) {
            return true;
        }

        if (!$student_id) {
            return false;
        }

        $a           = (array) $assessment;
        $semester_id = $this->active_semester_id();

        if (!$this->table_ready()) {
            $legacy = $this->db
                ->select('is_cleared')
                ->where('student_id', (int) $student_id)
                ->where('semester_id', $semester_id)
                ->get('class_student')
                ->row();
            return $legacy ? !empty($legacy->is_cleared) : false;
        }

        return $this->is_cleared(
            $student_id,
            $semester_id,
            $this->term_for_assessment($a['term'] ?? '')
        );
    }

    // ── Writes ──────────────────────────────────────────────────────────────
    /** Issue clearance. Idempotent — the UNIQUE key absorbs a double click. */
    public function clear($student_id, $semester_id, $term, $by = null)
    {
        if (!$student_id || !$semester_id || !$this->table_ready()) {
            return false;
        }

        $sql = "INSERT IGNORE INTO `student_clearance`
                    (`student_id`, `semester_id`, `term`, `cleared_by`)
                VALUES (?, ?, ?, ?)";

        return $this->db->query($sql, [
            (int) $student_id,
            (int) $semester_id,
            $this->normalize_term($term),
            $by !== null ? substr((string) $by, 0, 64) : null,
        ]);
    }

    /** Revoke clearance (the "clear by mistake" undo). */
    public function unclear($student_id, $semester_id, $term)
    {
        if (!$student_id || !$semester_id || !$this->table_ready()) {
            return false;
        }

        return $this->db
            ->where('student_id', (int) $student_id)
            ->where('semester_id', (int) $semester_id)
            ->where('term', $this->normalize_term($term))
            ->delete($this->table);
    }
}
