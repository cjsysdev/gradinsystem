<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Class officers — who holds which position in a section, per semester.
 *
 * Why a separate table instead of a column on class_student: class_student
 * holds one row per ENROLLMENT (per schedule_id), so a student can have several
 * rows sharing the same `section` string — which is why get_sections_with_counts()
 * counts DISTINCT student_id. A column there would have to be written to every
 * one of those rows and could disagree between them. This is a section-scoped
 * bridge table instead, the same shape as project_log_groupings.
 *
 * The position keys come from application/config/officers.php and are validated
 * there, not here. `semester_id` scopes a designation to one term, so a new
 * semester starts with no officers instead of inheriting last term's.
 *
 * Extends CI_Model, not MY_Model, because it owns install(): MY_Model's
 * constructor calls list_fields() on $table, which fails before the table
 * exists. Same reason Grouping_model and Project_log_model extend CI_Model.
 */
class Section_officer extends CI_Model
{
    protected $table = 'section_officers';

    // ── Schema bootstrap ────────────────────────────────────────────────────
    // Idempotent — run via AdminStudentController/section_officers_install,
    // which handles confirmation + backup. Never add a DROP here; later schema
    // changes go in as separate guarded ALTERs (see Grouping_model::install()).
    public function install()
    {
        $this->load->library('schema_guard');
        $this->schema_guard->reset();

        // The two UNIQUE keys are the rules: one holder per position per
        // section, and one position per student per section. assign() relies
        // on them rather than re-checking in PHP.
        $this->schema_guard->ddl("CREATE TABLE IF NOT EXISTS `section_officers` (
            `officer_id`  INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            `section`     VARCHAR(64) NOT NULL,
            `semester_id` INT NOT NULL,
            `student_id`  INT NOT NULL,
            `position`    VARCHAR(32) NOT NULL,
            `created_at`  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            UNIQUE KEY `uq_section_position` (`section`, `semester_id`, `position`),
            UNIQUE KEY `uq_section_student`  (`section`, `semester_id`, `student_id`),
            KEY `idx_student` (`student_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

        return $this->schema_guard->failures();
    }

    public function table_ready()
    {
        return $this->db->table_exists($this->table);
    }

    // ── Reads ───────────────────────────────────────────────────────────────
    /** [student_id => position_key] for one section, for badge rendering. */
    public function get_map($section, $semester_id)
    {
        if (!$section || !$semester_id || !$this->table_ready()) {
            return [];
        }

        $rows = $this->db
            ->select('student_id, position')
            ->where('section', $section)
            ->where('semester_id', $semester_id)
            ->get($this->table)
            ->result_array();

        $map = [];
        foreach ($rows as $row) {
            $map[(int) $row['student_id']] = $row['position'];
        }
        return $map;
    }

    /** Officer rows joined to student details, for the xlsx export. */
    public function get_for_export($section, $semester_id)
    {
        if (!$section || !$semester_id || !$this->table_ready()) {
            return [];
        }

        return $this->db
            ->select('so.position, so.student_id, sm.student_no, sm.lastname,'
                . ' sm.firstname, sm.middlename, sm.contact_no')
            ->from($this->table . ' so')
            ->join('student_master sm', 'sm.trans_no = so.student_id', 'left')
            ->where('so.section', $section)
            ->where('so.semester_id', $semester_id)
            ->get()
            ->result_array();
    }

    // ── Writes ──────────────────────────────────────────────────────────────
    /**
     * Give one student a position in a section. Clears whatever position that
     * student already held and unseats whoever else held this position, so the
     * two UNIQUE keys can never be violated by a legitimate save.
     *
     * Returns the student_ids that lost a designation (so the UI can blank
     * their badges without a reload), or FALSE if the write failed.
     */
    public function assign($section, $semester_id, $student_id, $position)
    {
        $student_id = (int) $student_id;
        $displaced  = [];

        $this->db->trans_start();

        // Whoever currently holds this position loses it.
        $holders = $this->db
            ->select('student_id')
            ->where('section', $section)
            ->where('semester_id', $semester_id)
            ->where('position', $position)
            ->where('student_id !=', $student_id)
            ->get($this->table)
            ->result_array();

        foreach ($holders as $holder) {
            $displaced[] = (int) $holder['student_id'];
        }

        // Drop both conflicting rows (this student's old position, and the
        // current holders of the new one) before inserting the new pairing.
        $this->db
            ->where('section', $section)
            ->where('semester_id', $semester_id)
            ->group_start()
                ->where('student_id', $student_id)
                ->or_where('position', $position)
            ->group_end()
            ->delete($this->table);

        $this->db->insert($this->table, [
            'section'     => $section,
            'semester_id' => (int) $semester_id,
            'student_id'  => $student_id,
            'position'    => $position,
        ]);

        $this->db->trans_complete();

        return $this->db->trans_status() === FALSE ? FALSE : $displaced;
    }

    /** Remove a student's designation in a section (the "— none —" choice). */
    public function clear($section, $semester_id, $student_id)
    {
        return $this->db
            ->where('section', $section)
            ->where('semester_id', $semester_id)
            ->where('student_id', (int) $student_id)
            ->delete($this->table);
    }
}
