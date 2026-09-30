<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Semester lookups for the read-only "view another semester" feature.
 *
 * `semester_master.is_active` still decides where NEW writes go (enrolment,
 * submissions, attendance). This model only resolves which semester a READ
 * should target: an explicit id when the caller passes one, else the active
 * semester. Views never change the active semester.
 */
class Semester_model extends CI_Model
{
    public function all()
    {
        return $this->db->order_by('trans_no', 'DESC')->get('semester_master')->result_array();
    }

    public function active()
    {
        return $this->db->where('is_active', 1)->get('semester_master')->row_array();
    }

    public function get($id)
    {
        return $this->db->where('trans_no', (int) $id)->get('semester_master')->row_array();
    }

    public function active_id()
    {
        $row = $this->active();
        return $row ? (int) $row['trans_no'] : null;
    }

    /** Explicit id if it names a real semester, else the active one, else null. */
    public function resolve_id($requested = null)
    {
        if ($requested !== null && $requested !== '' && $this->get($requested)) {
            return (int) $requested;
        }
        return $this->active_id();
    }

    public function is_active($id)
    {
        return $id !== null && (int) $id === $this->active_id();
    }

    /** Every semester the student has an enrolment row in, newest first. */
    public function for_student($student_id)
    {
        return $this->db->query("
            SELECT DISTINCT sem.*
            FROM class_student cs
            JOIN semester_master sem ON sem.trans_no = cs.semester_id
            WHERE cs.student_id = ?
            ORDER BY sem.trans_no DESC
        ", [$student_id])->result_array();
    }

    /**
     * Students may see a past semester once an admin has released it. Active
     * semester is always visible. If the `grades_released` column has not been
     * migrated yet (scripts/semester_release_migration.sql), past semesters
     * are treated as released so the feature still works.
     */
    public function is_released($semester_id)
    {
        if ($this->is_active($semester_id)) {
            return true;
        }
        $sem = $this->get($semester_id);
        if (!$sem) {
            return false;
        }
        if (!array_key_exists('grades_released', $sem)) {
            return true;
        }
        return !empty($sem['grades_released']);
    }

    public function set_released($semester_id, $released)
    {
        if (!$this->db->field_exists('grades_released', 'semester_master')) {
            return false;
        }
        $this->db->where('trans_no', (int) $semester_id)
                 ->update('semester_master', ['grades_released' => $released ? 1 : 0]);
        return true;
    }

    /** Semester an assessment (by assessment_section id) belongs to. */
    public function assessment_semester_id($assessment_id)
    {
        $row = $this->db->query("
            SELECT sched.semester_id
            FROM assessment_section a
            JOIN class_schedule sched ON sched.schedule_id = a.schedule_id
            WHERE a.assessment_section_id = ?
        ", [$assessment_id])->row_array();
        return $row ? (int) $row['semester_id'] : null;
    }
}
