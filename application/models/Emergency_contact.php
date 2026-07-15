<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Emergency_contact extends MY_Model
{
    public $table = 'student_emergency_contacts';
    public $primary_key = 'contact_id';
    public $protected = ['contact_id'];

    public function get_by_student($student_id)
    {
        return $this->db
            ->order_by('is_primary', 'DESC')
            ->order_by('created_at', 'ASC')
            ->get_where('student_emergency_contacts', ['student_id' => $student_id])
            ->result_array();
    }

    public function count_all_contacts($section = null)
    {
        if ($section) {
            $this->db
                ->from('student_emergency_contacts ec')
                ->where($this->section_filter_where($section), null, false);
            return (int) $this->db->count_all_results();
        }
        return $this->db->count_all('student_emergency_contacts');
    }

    public function get_all_paged($limit, $offset, $section = null)
    {
        $this->db
            ->select('ec.*, sm.firstname, sm.lastname, sm.student_no')
            ->from('student_emergency_contacts ec')
            ->join('student_master sm', 'sm.trans_no = ec.student_id', 'left');

        if ($section) {
            $this->db->where($this->section_filter_where($section), null, false);
        }

        return $this->db
            ->order_by('sm.lastname', 'ASC')
            ->order_by('ec.is_primary', 'DESC')
            ->limit($limit, $offset)
            ->get()
            ->result_array();
    }

    // Restricts ec.* rows to students currently enrolled in $section for the
    // active semester. Built as a raw IN-subquery (rather than a join) so
    // callers can freely chain select/order/limit without worrying about
    // duplicate contact rows from multiple class_student enrollments.
    private function section_filter_where($section)
    {
        return "ec.student_id IN (
            SELECT cs.student_id
            FROM class_student cs
            JOIN semester_master sem ON cs.semester_id = sem.trans_no AND sem.is_active = 1
            WHERE cs.section = " . $this->db->escape($section) . "
        )";
    }

    // Sections in the active semester, each with the number of students the
    // export would actually produce. Deliberately mirrors get_by_section()'s
    // join instead of reusing class_student::get_sections_with_counts(): some
    // class_student rows point at a student_master record that no longer
    // exists (or have a NULL student_id), and counting those would advertise
    // more rows than the file contains.
    public function get_exportable_sections()
    {
        $sql = "
            SELECT
                cs.section,
                COUNT(DISTINCT cs.student_id) AS student_count
            FROM class_student cs
            JOIN student_master sm ON sm.trans_no = cs.student_id
            JOIN semester_master sem ON cs.semester_id = sem.trans_no AND sem.is_active = 1
            WHERE cs.section IS NOT NULL AND cs.section <> ''
            GROUP BY cs.section
            ORDER BY cs.section ASC
        ";

        $query = $this->db->query($sql);
        return $query ? $query->result_array() : [];
    }

    // One row per student enrolled in the section for the active semester,
    // carrying that student's primary emergency contact (or their earliest one
    // if none is flagged primary). Students with no contact on file still come
    // back — with blank contact columns — so the export doubles as a checklist
    // of who hasn't submitted one yet.
    public function get_by_section($section)
    {
        $sql = "
            SELECT DISTINCT
                sm.trans_no,
                sm.lastname,
                sm.firstname,
                sm.middlename,
                sm.contact_no          AS student_contact,
                ec.full_name           AS guardian_name,
                ec.relationship        AS guardian_relationship,
                ec.contact_no          AS guardian_contact
            FROM class_student cs
            JOIN student_master sm ON sm.trans_no = cs.student_id
            JOIN semester_master sem ON cs.semester_id = sem.trans_no AND sem.is_active = 1
            LEFT JOIN student_emergency_contacts ec ON ec.contact_id = (
                SELECT ec2.contact_id
                FROM student_emergency_contacts ec2
                WHERE ec2.student_id = cs.student_id
                ORDER BY ec2.is_primary DESC, ec2.created_at ASC
                LIMIT 1
            )
            WHERE cs.section = ?
            ORDER BY sm.lastname ASC, sm.firstname ASC
        ";

        $query = $this->db->query($sql, [$section]);
        return $query ? $query->result_array() : [];
    }

    public function set_primary($contact_id, $student_id)
    {
        $this->db->where('student_id', $student_id)->update('student_emergency_contacts', ['is_primary' => 0]);
        $this->db->where('contact_id', $contact_id)->update('student_emergency_contacts', ['is_primary' => 1]);
    }
}
