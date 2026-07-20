<?php
defined('BASEPATH') or exit('No direct script access allowed');

class assessments extends MY_Model
{
    // Reads go through the compat view (assessment_id here is always a
    // SECTION id — see Assessment_normalize_model) so get()/with_assessments()
    // keep returning the old denormalized shape (schedule_id/status/due
    // alongside the shared master's title/description/given/etc.). Never
    // write through this model's inherited insert()/update()/delete() — a
    // join view rejects writes. All writes go through the explicit
    // create_master()/update_master()/assign_to_schedule()/update_section()/
    // delete_section() helpers at the bottom of this class instead.
    public $table = 'assessment_full';
    public $primary_key = 'assessment_id';
    public $protected = array('assessment_id');
    public $timestamps = TRUE;

    public function __construct()
    {
        $this->timestamps = TRUE;
        $this->has_one['type'] =  array(
            'foreign_model' => 'io_type',
            'foreign_table' => 'io_type',
            'foreign_key' => 'iotype_id',
            'local_key' => 'iotype_id'
        );
        $this->has_one['class_schedule'] =  array(
            'foreign_model' => 'class_schedule',
            'foreign_table' => 'class_schedule',
            'foreign_key' => 'schedule_id',
            'local_key' => 'schedule_id'
        );
        $this->has_many['classworks'] =  array(
            'foreign_model' => 'classworks',
            'foreign_table' => 'classworks',
            'foreign_key' => 'assessment_id',
            'local_key' => 'assessment_id'
        );
        parent::__construct();
    }

    public function get_students_assessments($student_id, $section)
    {
        $sql = "
            SELECT 
                a.assessment_id,
                a.iotype_id,
                a.title,
                a.description,
                a.max_score,
                a.created_at,
                a.due,
                iot.type,
                cs.section
            FROM
                assessment_full a
            LEFT JOIN
                classworks c
                ON a.assessment_id = c.assessment_id 
                AND c.student_id = ?
            JOIN 
                class_schedule cs
                ON a.schedule_id = cs.schedule_id
            JOIN
                io_type iot
                ON iot.iotype_id = a.iotype_id
            JOIN
                semester_master sem
                ON cs.semester_id = sem.trans_no
                AND sem.is_active = 1
            WHERE
                c.classwork_id IS NULL AND cs.section = ? AND a.status = 1
            ORDER BY
                a.created_at DESC
        ";

        $query = $this->db->query($sql, [$student_id, $section]);

        if ($query === false) {
            $error = $this->db->error();
            log_message('error', 'Database error: ' . $error['message']);
            return []; // Return an empty array or handle the error as needed
        }

        return $query->result_array();
    }

    public function get_submitted_assessments($student_id)
    {
        $sql = "
            SELECT *, a.description
            FROM classworks c 
            JOIN assessment_full a ON c.assessment_id = a.assessment_id
            JOIN io_type iot ON a.iotype_id = iot.iotype_id
            JOIN class_schedule cs ON a.schedule_id = cs.schedule_id
            JOIN semester_master sem ON cs.semester_id = sem.trans_no 
            AND sem.is_active = 1
            WHERE student_id = ? ORDER BY c.created_at DESC";

        $query = $this->db->query($sql, [$student_id]);

        if ($query === false) {
            $error = $this->db->error();
            log_message('error', 'Database error: ' . $error['message']);
            return []; // Return an empty array or handle the error as needed
        }

        return $query->result_array();
    }

    // Shared WHERE builder for the manage_assessments admin methods below. Every
    // condition references only `a` (assessments) or `cs` (class_schedule, joined
    // in all three methods) or a correlated classworks subquery, so it is safe in
    // the count/ids queries that DON'T join io_type/classes/classworks. Keeping the
    // filter logic in one place stops the list, the pager total, and the bulk-id
    // set from drifting out of sync.
    private function _admin_filters_sql($filters, &$params)
    {
        $conds = [];

        if (!empty($filters['schedule_id'])) { $conds[] = 'a.schedule_id = ?'; $params[] = (int) $filters['schedule_id']; }
        if (!empty($filters['iotype_id']))   { $conds[] = 'a.iotype_id = ?';   $params[] = (int) $filters['iotype_id']; }
        if (!empty($filters['term']))        { $conds[] = 'a.term = ?';        $params[] = $filters['term']; }

        if (isset($filters['status']) && $filters['status'] !== '') {
            // status is stored inconsistently (numeric 1/0 or legacy 'open'/'closed') — match both.
            if ((string) $filters['status'] === '1') {
                $conds[] = "(a.status = '1' OR a.status = 'open')";
            } else {
                $conds[] = "(a.status = '0' OR a.status = 'closed' OR a.status = '' OR a.status IS NULL)";
            }
        }

        if (!empty($filters['q'])) {
            $conds[] = '(a.title LIKE ? OR cs.section LIKE ?)';
            $like = '%' . $filters['q'] . '%';
            $params[] = $like;
            $params[] = $like;
        }

        switch ($filters['submission'] ?? '') {
            case 'none':
                $conds[] = 'NOT EXISTS (SELECT 1 FROM classworks cwf WHERE cwf.assessment_id = a.assessment_id)';
                break;
            case 'has':
                $conds[] = 'EXISTS (SELECT 1 FROM classworks cwf WHERE cwf.assessment_id = a.assessment_id)';
                break;
            case 'unscored':
                $conds[] = 'EXISTS (SELECT 1 FROM classworks cwf WHERE cwf.assessment_id = a.assessment_id AND cwf.score IS NULL)';
                break;
            case 'missing':
                $conds[] = "(SELECT COUNT(*) FROM class_student cst WHERE cst.schedule_id = a.schedule_id AND cst.status = 'enrolled')"
                         . " > (SELECT COUNT(DISTINCT cwf.student_id) FROM classworks cwf WHERE cwf.assessment_id = a.assessment_id)";
                break;
        }

        return $conds ? ' WHERE ' . implode(' AND ', $conds) : '';
    }

    public function get_all_for_admin(array $filters = [], $limit = null, $offset = 0)
    {
        $base = "
            SELECT
                a.*,
                cs.section,
                cs.type AS schedule_type,
                iot.type AS iotype,
                iot.percentage,
                cl.class_name,
                cl.class_code,
                ag.set_id AS grouping_set_id,
                w.name AS widget_name,
                COUNT(cw.classwork_id) AS submission_count,
                SUM(CASE WHEN cw.classwork_id IS NOT NULL AND cw.score IS NULL THEN 1 ELSE 0 END) AS unscored_count,
                (SELECT COUNT(*) FROM class_student cst WHERE cst.schedule_id = a.schedule_id AND cst.status = 'enrolled') AS enrolled_count,
                COUNT(DISTINCT cw.student_id) AS submitted_student_count,
                (SELECT COUNT(*) FROM assessment_section s2 WHERE s2.assessment_id = a.master_id) AS sibling_count,
                (SELECT GROUP_CONCAT(cs2.section ORDER BY cs2.section SEPARATOR ', ')
                    FROM assessment_section s2 JOIN class_schedule cs2 ON cs2.schedule_id = s2.schedule_id
                    WHERE s2.assessment_id = a.master_id) AS sibling_sections_csv
            FROM assessment_full a
            JOIN class_schedule cs ON a.schedule_id = cs.schedule_id
            JOIN io_type iot ON a.iotype_id = iot.iotype_id
            JOIN classes cl ON cs.class_id = cl.class_id
            JOIN semester_master sem ON cs.semester_id = sem.trans_no AND sem.is_active = 1
            LEFT JOIN classworks cw ON cw.assessment_id = a.assessment_id
            LEFT JOIN assessment_groupings ag ON ag.assessment_id = a.assessment_id
            LEFT JOIN widgets w ON w.widget_id = a.widget_id
        ";

        $params = [];
        $where  = $this->_admin_filters_sql($filters, $params);
        // Grouped by master_id (not assessment_id/section id) so sections
        // sharing an assessment land on adjacent rows within a page — the
        // manage_assessments view rowspans the shared content columns
        // (title/type/widget/term/max score) across a same-master run.
        $sql    = $base . $where . " GROUP BY a.assessment_id ORDER BY a.master_id DESC, cs.section ASC, a.term, a.created_at DESC";

        if ($limit !== null) {
            $sql .= " LIMIT ? OFFSET ?";
            $params[] = (int) $limit;
            $params[] = (int) $offset;
        }

        $query = $this->db->query($sql, $params);

        return $query ? $query->result_array() : [];
    }

    // Total assessments matching the same filter/joins as get_all_for_admin(),
    // for the manage_assessments pager — no aggregate/LEFT JOINs needed since
    // we're only counting assessment rows, not classwork submissions.
    public function count_all_for_admin(array $filters = [])
    {
        $sql = "
            SELECT COUNT(*) AS c
            FROM assessment_full a
            JOIN class_schedule cs ON a.schedule_id = cs.schedule_id
            JOIN semester_master sem ON cs.semester_id = sem.trans_no AND sem.is_active = 1
        ";

        $params = [];
        $sql   .= $this->_admin_filters_sql($filters, $params);

        $query = $this->db->query($sql, $params);
        return $query ? (int) $query->row_array()['c'] : 0;
    }

    // Every assessment_id matching the same filter as get_all_for_admin(), for
    // the manage_assessments "Open All"/"Close All" buttons to act on every
    // filtered assessment, not just the ones on the current page.
    public function get_all_ids_for_admin(array $filters = [])
    {
        $sql = "
            SELECT a.assessment_id
            FROM assessment_full a
            JOIN class_schedule cs ON a.schedule_id = cs.schedule_id
            JOIN semester_master sem ON cs.semester_id = sem.trans_no AND sem.is_active = 1
        ";

        $params = [];
        $sql   .= $this->_admin_filters_sql($filters, $params);

        $query = $this->db->query($sql, $params);
        return $query ? array_column($query->result_array(), 'assessment_id') : [];
    }

    // Every assessment in the active semester, for the manage_assessments Add
    // modal's "Copy from existing assessment" picker — pre-fills the form from
    // another assessment so admins can drop one onto a different section
    // instead of re-typing title/type/description/widget/config by hand.
    // class_code is included (not just section) so the modal JS can filter the
    // picker to the target section's class, same as it does for iq_topics.
    public function get_copyable_for_active_semester()
    {
        $sql = "
            SELECT a.assessment_id, a.title, a.iotype_id, a.description,
                   a.max_score, a.term, a.due, a.widget_id, a.given, a.is_groupings,
                   cs.section, cl.class_code
            FROM assessment_full a
            JOIN class_schedule cs ON a.schedule_id = cs.schedule_id
            JOIN classes cl ON cs.class_id = cl.class_id
            JOIN semester_master sem ON cs.semester_id = sem.trans_no AND sem.is_active = 1
            ORDER BY cl.class_code, cs.section, a.title";
        $query = $this->db->query($sql);
        return $query ? $query->result_array() : [];
    }

    // Every shared master in the active semester, for the "Assign existing
    // assessment" picker — attaches an ADDITIONAL section to an assessment
    // that already exists elsewhere, instead of cloning its content into a
    // brand-new one (that's what "Copy from existing assessment" above is
    // for). assigned_schedule_ids lets the modal JS exclude sections the
    // master is already on from the target-section picker.
    public function get_assignable_masters()
    {
        $sql = "
            SELECT m.assessment_id AS master_id, m.title, m.iotype_id, m.term,
                   m.max_score, m.widget_id, cl.class_code, cl.class_id,
                   GROUP_CONCAT(DISTINCT s.schedule_id) AS assigned_schedule_ids
            FROM assessments m
            JOIN assessment_section s ON s.assessment_id = m.assessment_id
            JOIN class_schedule cs ON cs.schedule_id = s.schedule_id
            JOIN classes cl ON cl.class_id = cs.class_id
            JOIN semester_master sem ON sem.trans_no = cs.semester_id AND sem.is_active = 1
            GROUP BY m.assessment_id
            ORDER BY cl.class_code, m.title";
        $query = $this->db->query($sql);
        return $query ? $query->result_array() : [];
    }

    public function get_for_schedule($schedule_id = null)
    {
        if ($schedule_id) {
            $sql = "
                SELECT 
                    a.*,
                    cs.*,
                    iot.type AS iotype
                FROM
                    assessment_full a
                JOIN
                    class_schedule cs ON a.schedule_id = cs.schedule_id
                JOIN
                    io_type iot ON a.iotype_id = iot.iotype_id
                JOIN
                    semester_master sem ON cs.semester_id = sem.trans_no AND sem.is_active = 1
                WHERE
                    a.schedule_id = ? AND a.status = 1
                ORDER BY 
                    a.created_at DESC
            ";
            $query = $this->db->query($sql, [$schedule_id]);
        } else {
            $sql = "
                SELECT 
                    a.*,
                    cs.*,
                    iot.type AS iotype
                FROM
                    assessment_full a
                JOIN
                    class_schedule cs ON a.schedule_id = cs.schedule_id
                JOIN
                    io_type iot ON a.iotype_id = iot.iotype_id
                JOIN
                    semester_master sem ON cs.semester_id = sem.trans_no AND sem.is_active = 1
                WHERE
                    sem.is_active = 1
                ORDER BY 
                    cs.section, a.created_at DESC
            ";
            $query = $this->db->query($sql);
        }

        if ($query === false) {
            $error = $this->db->error();
            log_message('error', 'Database error: ' . $error['message']);
            return [];
        }

        return $query->result_array();
    }

    // --- Explicit-table write helpers (post-normalization) -----------------
    // $table is 'assessments' (still the master content table) for reads via
    // get()/with_assessments() done BEFORE the schema swap; once
    // Assessment_normalize_model::install() has run, callers that need the
    // old denormalized shape (schedule_id/status/due) read `assessment_full`
    // via raw queries above instead of MY_Model's built-in get(). Writes
    // NEVER go through MY_Model's insert()/update()/delete() for section
    // data — those operate on $table, and once section fields (due/status/
    // is_groupings) move to assessment_section, only these explicit methods
    // know how to route a write to the right table.

    public function master_id_for_section($section_id)
    {
        return $this->db->select('assessment_id')
            ->where('assessment_section_id', $section_id)
            ->get('assessment_section')
            ->row('assessment_id');
    }

    public function create_master(array $content)
    {
        $content['created_at'] = date('Y-m-d H:i:s');
        $this->db->insert('assessments', $content);
        return $this->db->insert_id();
    }

    public function update_master($master_id, array $content)
    {
        $content['updated_at'] = date('Y-m-d H:i:s');
        return $this->db->where('assessment_id', $master_id)->update('assessments', $content);
    }

    public function assign_to_schedule($master_id, $schedule_id, array $section_fields)
    {
        $data = $section_fields + [
            'assessment_id' => $master_id,
            'schedule_id'   => $schedule_id,
            'created_at'    => date('Y-m-d H:i:s'),
        ];
        $this->db->insert('assessment_section', $data);
        return $this->db->insert_id();
    }

    public function update_section($section_id, array $section_fields)
    {
        $section_fields['updated_at'] = date('Y-m-d H:i:s');
        return $this->db->where('assessment_section_id', $section_id)->update('assessment_section', $section_fields);
    }

    public function sections_of_master($master_id)
    {
        return $this->db->where('assessment_id', $master_id)->get('assessment_section')->result_array();
    }

    // Deletes one section assignment. FK CASCADE on assessment_section takes
    // its assessment_groupings/assessment_live_state rows with it. If that
    // was the master's last section, the now-unreachable master is deleted
    // too — nothing links to a master with zero section assignments.
    public function delete_section($section_id)
    {
        $master_id = $this->master_id_for_section($section_id);
        $this->db->where('assessment_section_id', $section_id)->delete('assessment_section');

        if ($master_id) {
            $remaining = $this->db->where('assessment_id', $master_id)->count_all_results('assessment_section');
            if ($remaining === 0) {
                $this->db->where('assessment_id', $master_id)->delete('assessments');
            }
        }
    }
}
