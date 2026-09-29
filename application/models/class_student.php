<?php
defined('BASEPATH') or exit('No direct script access allowed');

class class_student extends MY_Model
{
    public $table = 'class_student';
    public $primary_key = 'id';
    public $protected = array('id');

    public function __construct()
    {
        $this->timestamps = TRUE;
        $this->has_one['student_master'] =  array(
            'foreign_model' => 'student_master',
            'foreign_table' => 'student_master',
            'foreign_key' => 'trans_no',
            'local_key' => 'trans_no'
        );
        parent::__construct();
    }

    // Clearance moved to the `student_clearance` table (see Student_clearance):
    // it is per student, per SEMESTER and per TERM, which a single column on a
    // per-enrollment row could not express. get_uncleared_students_by_section(),
    // clear_student() and get_sections_with_uncleared_counts() lived here and
    // are now Student_clearance::students_by_section() / clear() /
    // sections_with_counts(). The `is_cleared` column is left in place for the
    // old rows the backfill read, but nothing writes it any more.

    public function add_section($id, $section, $semester_id = null)
    {
        $semester_id = $semester_id ?: $this->active_semester_id();
        return $this->db
            ->where('student_id', $id)
            ->where('semester_id', $semester_id)
            ->update($this->table, ['section' => $section]);
    }

    public function update_class($id, $class, $semester_id = null)
    {
        $semester_id = $semester_id ?: $this->active_semester_id();
        return $this->db
            ->where('student_id', $id)
            ->where('semester_id', $semester_id)
            ->update($this->table, ['class_id' => $class]);
    }

    public function re_enroll($student_id, $class_id, $section, $semester_id, $schedule_id = null)
    {
        $exists = $this->db
            ->where('student_id', $student_id)
            ->where('semester_id', $semester_id)
            ->count_all_results($this->table);

        if ($exists) {
            return $this->db
                ->where('student_id', $student_id)
                ->where('semester_id', $semester_id)
                ->update($this->table, [
                    'class_id'    => $class_id,
                    'schedule_id' => $schedule_id,
                    'section'     => $section,
                    'status'      => 'enrolled',
                ]);
        }

        return $this->db->insert($this->table, [
            'student_id'  => $student_id,
            'class_id'    => $class_id,
            'schedule_id' => $schedule_id,
            'section'     => $section,
            'semester_id' => $semester_id,
            'status'      => 'enrolled',
            'is_cleared'  => 0,
        ]);
    }

    // The official roster for a schedule (CLAUDE.md: schedule_id + enrolled +
    // active semester), with names — for anything that needs to list actual
    // students on a section rather than just count/check them, e.g.
    // AdminAssessmentController::snippet_batches()'s batch-assignment table.
    // LEFT JOIN on student_master for the same reason as
    // classworks::get_missing_submissions(): a roster slot whose
    // student_master row was deleted is still a real enrollment to show.
    public function roster_for_schedule($schedule_id)
    {
        $sql = "
            SELECT cst.student_id,
                   COALESCE(sm.firstname, '') AS firstname,
                   COALESCE(sm.lastname, CONCAT('[no student record #', cst.student_id, ']')) AS lastname
            FROM class_student cst
            JOIN class_schedule sched ON sched.schedule_id = cst.schedule_id
            JOIN semester_master sem ON sem.trans_no = sched.semester_id AND sem.is_active = 1
            LEFT JOIN student_master sm ON sm.trans_no = cst.student_id
            WHERE cst.schedule_id = ?
              AND cst.status = 'enrolled'
              AND cst.student_id IS NOT NULL
            ORDER BY lastname, firstname
        ";

        $query = $this->db->query($sql, [$schedule_id]);
        return $query ? $query->result_array() : [];
    }

    public function is_enrolled_in_schedule($student_id, $schedule_id)
    {
        return $this->db
            ->where('student_id', $student_id)
            ->where('schedule_id', $schedule_id)
            ->where('status', 'enrolled')
            ->count_all_results($this->table) > 0;
    }

    // Public because anything scoped to "the current term" needs it — the
    // officer designations, for one. Keeping it private meant a fourth copy of
    // this lookup every time something else needed the active semester.
    public function active_semester_id()
    {
        $row = $this->db->select('trans_no')->where('is_active', 1)->get('semester_master')->row();
        return $row ? $row->trans_no : null;
    }

    public function get_students_with_names_by_section($section)
    {
        $sql = "
                SELECT class_student.student_id, student_master.firstname, student_master.lastname
                FROM class_student
                LEFT JOIN student_master ON class_student.student_id = student_master.student_id
                JOIN semester_master ON class_student.semester_id = semester_master.trans_no
                WHERE class_student.section = ? AND semester_master.is_active = 1
                ";

        $query = $this->db->query($sql, [$section]);
        if ($query && $query->num_rows() > 0) {
            return $query->result_array();
        } else {
            return [];
        }
    }

    public function get_class_student_info($student_id)
    {
        $sql = "
            SELECT
                cs.id,
                cs.student_id,
                cs.class_id,
                cs.section,
                cs.is_cleared,
                cs.semester_id,
                cs.status,
                sm.semcode,
                sm.description AS semester_description,
                sm.semyear
            FROM class_student cs
            INNER JOIN semester_master sm
                ON cs.semester_id = sm.trans_no
            WHERE sm.is_active = 1 AND cs.student_id = ?
        ";

        $query = $this->db->query($sql, [$student_id]);
        return $query ? $query->row_array() : [];
    }

    // One card per student on students_by_section. The GROUP BY is load-bearing:
    // class_student holds one row per enrollment, so a student taking two
    // schedules under the same section string would otherwise render twice —
    // which is also why get_sections_with_counts() counts DISTINCT student_id.
    public function get_students_with_profile_by_section($section)
    {
        $sql = "
            SELECT
                cs.student_id,
                sm.firstname,
                sm.lastname,
                a.profile_pic
            FROM class_student cs
            JOIN student_master sm ON cs.student_id = sm.trans_no
            LEFT JOIN accounts a ON a.student_id = cs.student_id
            JOIN semester_master sem ON cs.semester_id = sem.trans_no
            WHERE cs.section = ? AND sem.is_active = 1
            GROUP BY cs.student_id, sm.firstname, sm.lastname, a.profile_pic
            ORDER BY sm.lastname, sm.firstname
        ";

        $query = $this->db->query($sql, [$section]);
        return $query ? $query->result_array() : [];
    }

    // Distinct sections in the active semester, each with its enrolled-student
    // count — powers the clickable section cards on students_by_section.
    public function get_sections_with_counts()
    {
        $sql = "
            SELECT
                cs.section,
                COUNT(DISTINCT cs.student_id) AS student_count
            FROM class_student cs
            JOIN semester_master sem ON cs.semester_id = sem.trans_no
            WHERE sem.is_active = 1
            GROUP BY cs.section
            ORDER BY cs.section
        ";

        $query = $this->db->query($sql);
        return $query ? $query->result_array() : [];
    }

}
