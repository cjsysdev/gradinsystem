<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Project_log_model extends CI_Model
{
    protected $table = 'project_logs';

    /**
     * The project log's kanban columns, in board order, plus everything the UI
     * needs to draw one. This is the single source of truth for status: the DB
     * ENUM (install()), ProjectLogController::_clean_status(), the student
     * board and the admin browse all derive from it. Adding a column here and
     * re-running ProjectLogController/install is the whole change.
     *
     * Order matters — it is the left-to-right order of the board and the order
     * of the status <select>.
     */
    const STATUSES = [
        'planned'     => ['label' => 'Planned',     'badge' => 'secondary', 'icon' => 'fa-clipboard-list', 'color' => '#6c757d', 'hint' => 'Not started yet'],
        'in-progress' => ['label' => 'In Progress', 'badge' => 'warning',   'icon' => 'fa-person-digging', 'color' => '#ffc107', 'hint' => 'Being worked on right now'],
        'blocked'     => ['label' => 'Blocked',     'badge' => 'danger',    'icon' => 'fa-ban',            'color' => '#dc3545', 'hint' => 'Stuck — needs help or a decision'],
        'done'        => ['label' => 'Done',        'badge' => 'success',   'icon' => 'fa-check',          'color' => '#28a745', 'hint' => 'Finished — includes shipped/demoed work'],
    ];

    const DEFAULT_STATUS = 'planned';

    public static function statuses()
    {
        return self::STATUSES;
    }

    public static function status_keys()
    {
        return array_keys(self::STATUSES);
    }

    // Any value not in STATUSES falls back to the default rather than reaching
    // the ENUM, where an unknown value is stored as '' without an error
    // (db_debug is off).
    public static function clean_status($status)
    {
        return isset(self::STATUSES[$status]) ? $status : self::DEFAULT_STATUS;
    }

    // Never returns null — rows written before a column existed still render.
    public static function status_meta($status)
    {
        if (isset(self::STATUSES[$status])) {
            return self::STATUSES[$status];
        }
        // A blank status is what a pre-widening ENUM leaves behind on a rejected
        // write — label it rather than render an empty badge.
        $label = ($status === null || $status === '') ? 'Unset' : ucfirst((string) $status);
        return ['label' => $label, 'badge' => 'secondary',
                'icon' => 'fa-circle-question', 'color' => '#6c757d', 'hint' => ''];
    }

    // One-time (idempotent) schema setup — run once as admin via
    // ProjectLogController/install. Mirrors Grouping_model::install().
    // A project log is a running, per-student list of progress entries for a
    // course project (WS/DSA), not an assessment submission — hence its own
    // table rather than a widget storing JSON in classworks.code.
    public function install()
    {
        $this->db->query("CREATE TABLE IF NOT EXISTS `project_logs` (
            `log_id`      INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            `student_id`  INT NOT NULL,
            `class_id`    INT NOT NULL,
            `title`       VARCHAR(150) NOT NULL,
            `description` TEXT NULL,
            `status`      " . $this->_status_enum_sql() . ",
            `link`        VARCHAR(512) NULL,
            `file_upload` VARCHAR(512) NULL,
            `code`        LONGTEXT NULL,
            `created_at`  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            `updated_at`  DATETIME NULL,
            KEY `idx_student_class` (`student_id`, `class_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

        // Bridge: which grouping set(s) govern a course's project log. Mirrors
        // assessment_groupings, but a class can span several sections, so this
        // is one-to-many (class_id -> multiple set_id) rather than a PK on
        // class_id alone.
        $this->db->query("CREATE TABLE IF NOT EXISTS `project_log_groupings` (
            `class_id` INT NOT NULL,
            `set_id`   INT UNSIGNED NOT NULL,
            PRIMARY KEY (`class_id`, `set_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

        // Group-scoped entries: NULL = individual entry (existing behavior),
        // set = shared team entry. Added via a safe column check since
        // project_logs already exists in live installs.
        $this->_add_column_if_missing('project_logs', 'group_id', 'INT UNSIGNED NULL');
        $this->_add_index_if_missing('project_logs', 'idx_group', '(`group_id`)');

        // Live installs still carry the original ENUM, without 'blocked'.
        $this->_sync_status_enum();
    }

    private function _status_enum_sql()
    {
        return "ENUM('" . implode("','", self::status_keys()) . "') NOT NULL DEFAULT '" . self::DEFAULT_STATUS . "'";
    }

    // The ENUM only ever grows here — statuses are added, never dropped, so
    // this MODIFY cannot strand an existing row (MySQL re-matches each row by
    // value name, not by index, so inserting 'blocked' mid-list is safe).
    // Widening is what makes a new column writable at all: MySQL rejects an
    // out-of-list ENUM value, and with db_debug off that rejection is
    // invisible — the row just saves as ''.
    private function _sync_status_enum()
    {
        if ($this->status_enum_ready()) {
            return;
        }
        $this->db->query("ALTER TABLE `project_logs` MODIFY `status` " . $this->_status_enum_sql());
    }

    // Board columns the live ENUM does not accept yet, as labels. Empty means
    // the schema is current. The admin banner lists these rather than naming
    // them in the view, so it stays right when STATUSES changes again.
    public function missing_status_labels()
    {
        $row = $this->db->query(
            "SELECT COLUMN_TYPE AS t FROM information_schema.COLUMNS
             WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'project_logs' AND COLUMN_NAME = 'status'"
        )->row();

        if (!$row) {
            return []; // not installed yet; install() will create it correctly
        }

        $missing = [];
        foreach (self::STATUSES as $key => $meta) {
            if (strpos($row->t, "'" . $key . "'") === false) {
                $missing[] = $meta['label'];
            }
        }
        return $missing;
    }

    // True when the live column already accepts every status in STATUSES.
    public function status_enum_ready()
    {
        return $this->missing_status_labels() === [];
    }

    private function _add_column_if_missing($table, $column, $definition)
    {
        $exists = $this->db->query(
            "SELECT 1 FROM information_schema.COLUMNS
             WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? AND COLUMN_NAME = ?",
            [$table, $column]
        )->num_rows() > 0;

        if (!$exists) {
            $this->db->query("ALTER TABLE `$table` ADD COLUMN `$column` $definition");
        }
    }

    private function _add_index_if_missing($table, $index_name, $columns_sql)
    {
        $exists = $this->db->query(
            "SELECT 1 FROM information_schema.STATISTICS
             WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? AND INDEX_NAME = ?",
            [$table, $index_name]
        )->num_rows() > 0;

        if (!$exists) {
            $this->db->query("ALTER TABLE `$table` ADD KEY `$index_name` $columns_sql");
        }
    }

    // Courses the student is enrolled in this active semester. Same
    // class_student -> class_schedule -> classes join used by
    // StudentController::requests(), returning the course identity fields.
    public function get_courses_for_student($student_id)
    {
        return $this->db
            ->distinct()
            ->select('c.class_id, c.class_code, c.class_name')
            ->from('class_student cls')
            ->join('class_schedule cs', 'cls.section = cs.section')
            ->join('classes c', 'cs.class_id = c.class_id')
            ->join('semester_master sem', 'cs.semester_id = sem.trans_no')
            ->where('cls.student_id', $student_id)
            ->where('sem.is_active', 1)
            ->order_by('c.class_code')
            ->get()->result_array();
    }

    // The subset of the student's courses that actually have a project log
    // set up — i.e. a grouping set designated in project_log_groupings. This
    // is what the student sees in the course picker, and what decides whether
    // the Project Log button appears in the nav bar at all.
    public function get_designated_courses_for_student($student_id)
    {
        // Guard: CI turns a NULL here into `student_id IS NULL`, which matches
        // the stray NULL-student rows in class_student.
        if (empty($student_id)) {
            return [];
        }

        return $this->db
            ->distinct()
            ->select('c.class_id, c.class_code, c.class_name')
            ->from('class_student cls')
            ->join('class_schedule cs', 'cls.section = cs.section')
            ->join('classes c', 'cs.class_id = c.class_id')
            ->join('semester_master sem', 'cs.semester_id = sem.trans_no')
            ->join('project_log_groupings plg', 'plg.class_id = c.class_id')
            ->where('cls.student_id', $student_id)
            ->where('sem.is_active', 1)
            ->order_by('c.class_code')
            ->get()->result_array();
    }

    // $limit = null returns the whole log — that is what the kanban board asks
    // for, since a paged board would show empty columns that aren't empty.
    public function get_by_student_class($student_id, $class_id, $limit = null, $offset = 0)
    {
        $this->db
            ->where(['student_id' => $student_id, 'class_id' => $class_id])
            ->order_by('created_at', 'DESC')
            ->order_by('log_id', 'DESC');

        if ($limit !== null) {
            $this->db->limit($limit, $offset);
        }

        return $this->db->get($this->table)->result_array();
    }

    // ── Groupings integration ────────────────────────────────────────────────

    // Grouping set(s) designated to govern a course's project log. A class can
    // span several sections, so this is one-to-many rather than a single set.
    public function get_set_ids_for_class($class_id)
    {
        $rows = $this->db
            ->select('set_id')
            ->where('class_id', $class_id)
            ->get('project_log_groupings')->result_array();
        return array_map('intval', array_column($rows, 'set_id'));
    }

    // Grouping sets available to designate for a course: those whose
    // section_id matches one of the class's sections this active semester.
    public function get_available_sets_for_class($class_id)
    {
        return $this->db
            ->distinct()
            ->select('gs.set_id, gs.name, gs.section_id, gs.self_select')
            ->from('grouping_sets gs')
            ->join('class_schedule cs', 'cs.section = gs.section_id')
            ->join('semester_master sem', 'cs.semester_id = sem.trans_no')
            ->where('cs.class_id', $class_id)
            ->where('sem.is_active', 1)
            ->order_by('gs.created_at')
            ->get()->result_array();
    }

    // Admin designation write: replace a course's designated set(s) entirely.
    public function set_class_groupings($class_id, array $set_ids)
    {
        $this->db->where('class_id', $class_id)->delete('project_log_groupings');

        $set_ids = array_unique(array_filter(array_map('intval', $set_ids)));
        if (empty($set_ids)) {
            return;
        }

        $rows = [];
        foreach ($set_ids as $set_id) {
            $rows[] = ['class_id' => (int) $class_id, 'set_id' => $set_id];
        }
        $this->db->insert_batch('project_log_groupings', $rows);
    }

    // Shared team entries, each tagged with its author's name.
    public function get_by_group($group_id, $limit = null, $offset = 0)
    {
        $this->db
            ->select('pl.*, sm.firstname, sm.lastname')
            ->from($this->table . ' pl')
            ->join('student_master sm', 'pl.student_id = sm.trans_no', 'left')
            ->where('pl.group_id', $group_id)
            ->order_by('pl.created_at', 'DESC')
            ->order_by('pl.log_id', 'DESC');

        if ($limit !== null) {
            $this->db->limit($limit, $offset);
        }

        return $this->db->get()->result_array();
    }

    // Teams available to filter the admin browse by: every group of the
    // grouping set(s) designated for this course, each with how many log
    // entries it has. The LEFT JOIN keeps teams that have logged nothing —
    // those are usually the ones worth spotting.
    public function get_teams_for_class($class_id)
    {
        if (empty($class_id)) {
            return [];
        }

        return $this->db
            ->select('g.group_id, g.group_name, gs.name AS set_name, gs.section_id,
                      COUNT(pl.log_id) AS entry_count')
            ->from('project_log_groupings plg')
            ->join('groupings g', 'g.set_id = plg.set_id')
            ->join('grouping_sets gs', 'gs.set_id = g.set_id')
            // escape=FALSE: the condition is compound, and the only value in it
            // is the int-cast class id.
            ->join('project_logs pl', 'pl.group_id = g.group_id AND pl.class_id = ' . (int) $class_id, 'left', false)
            ->where('plg.class_id', (int) $class_id)
            ->group_by('g.group_id')
            ->order_by('gs.section_id')
            ->order_by('g.group_id')
            ->get()->result_array();
    }

    // Ownership-scoped fetch: only returns the row if it belongs to $student_id.
    public function get_one($log_id, $student_id)
    {
        return $this->db
            ->get_where($this->table, ['log_id' => $log_id, 'student_id' => $student_id])
            ->row_array();
    }

    public function create($data)
    {
        return $this->db->insert($this->table, $data);
    }

    // Every write is scoped by student_id so one student can't touch another's row.
    public function update_entry($log_id, $student_id, $data)
    {
        return $this->db
            ->where(['log_id' => $log_id, 'student_id' => $student_id])
            ->update($this->table, $data);
    }

    // Kanban drag-and-drop write. Ownership-scoped like every other write, so
    // a teammate can read a card on the shared board but only its author can
    // move it — the same rule the edit/delete buttons already follow.
    // $status is validated by the controller through clean_status().
    public function set_status($log_id, $student_id, $status)
    {
        return $this->db
            ->where(['log_id' => $log_id, 'student_id' => $student_id])
            ->update($this->table, [
                'status'     => $status,
                'updated_at' => date('Y-m-d H:i:s'),
            ]);
    }

    public function delete_entry($log_id, $student_id)
    {
        return $this->db
            ->where(['log_id' => $log_id, 'student_id' => $student_id])
            ->delete($this->table);
    }

    // Joins + WHEREs shared by the admin browse and its row count.
    // $group_id: a group_id to show one team's entries, the string 'none' for
    // individual (team-less) entries, or empty for no team filter.
    // $status: one of STATUSES, or empty for no status filter.
    private function _admin_filters($class_id = null, $section = null, $group_id = null, $status = null)
    {
        $this->db
            ->from('project_logs pl')
            ->join('classes c', 'pl.class_id = c.class_id', 'left')
            ->join('student_master sm', 'pl.student_id = sm.trans_no', 'left')
            ->join('class_student cls', 'cls.student_id = pl.student_id', 'left')
            ->join('groupings g', 'pl.group_id = g.group_id', 'left');

        if (!empty($class_id)) {
            $this->db->where('pl.class_id', $class_id);
        }
        if (!empty($section)) {
            $this->db->where('cls.section', $section);
        }
        if ($group_id === 'none') {
            $this->db->where('pl.group_id IS NULL', null, false);
        } elseif (!empty($group_id)) {
            $this->db->where('pl.group_id', (int) $group_id);
        }
        if (!empty($status) && isset(self::STATUSES[$status])) {
            $this->db->where('pl.status', $status);
        }
    }

    // Admin read-only browse, optionally filtered by course, section and/or
    // team. Pass $limit to page through the results.
    public function get_all_for_admin($class_id = null, $section = null, $group_id = null, $limit = null, $offset = 0, $status = null)
    {
        $this->_admin_filters($class_id, $section, $group_id, $status);

        $this->db
            ->select('pl.*, c.class_code, c.class_name, cls.section,
                      sm.lastname, sm.firstname, g.group_name')
            ->group_by('pl.log_id')
            ->order_by('c.class_code')
            ->order_by('sm.lastname')
            ->order_by('pl.created_at', 'DESC')
            ->order_by('pl.log_id', 'DESC'); // tiebreaker: paging must be stable

        if ($limit !== null) {
            $this->db->limit($limit, $offset);
        }

        return $this->db->get()->result_array();
    }

    // Row count for the same filters. Counted DISTINCT rather than with
    // count_all_results(): the class_student join fans a student out once per
    // enrolment (the GROUP BY above collapses that), and CI's
    // count_all_results() on a grouped query returns the first group's count.
    public function count_all_for_admin($class_id = null, $section = null, $group_id = null, $status = null)
    {
        $this->_admin_filters($class_id, $section, $group_id, $status);

        $row = $this->db->select('COUNT(DISTINCT pl.log_id) AS n')->get()->row();
        return $row ? (int) $row->n : 0;
    }

    // Board summary for the admin browse: how many entries sit in each column
    // under the current course/section/team filter. Deliberately ignores the
    // status filter — the strip is how you see the whole board and click into
    // one column, so filtering it by the selected column would empty it.
    // Counted DISTINCT for the same class_student fan-out reason as above.
    public function status_counts_for_admin($class_id = null, $section = null, $group_id = null)
    {
        $this->_admin_filters($class_id, $section, $group_id);

        $rows = $this->db
            ->select('pl.status, COUNT(DISTINCT pl.log_id) AS n')
            ->group_by('pl.status')
            ->get()->result_array();

        $counts = array_fill_keys(self::status_keys(), 0);
        foreach ($rows as $r) {
            if (isset($counts[$r['status']])) {
                $counts[$r['status']] = (int) $r['n'];
            }
        }
        return $counts;
    }

    // Distinct courses that have at least one log entry — for the admin filter.
    public function get_logged_courses()
    {
        return $this->db
            ->distinct()
            ->select('c.class_id, c.class_code, c.class_name')
            ->from('project_logs pl')
            ->join('classes c', 'pl.class_id = c.class_id')
            ->order_by('c.class_code')
            ->get()->result_array();
    }
}
