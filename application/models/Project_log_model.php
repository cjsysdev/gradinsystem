<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Project_log_model extends CI_Model
{
    protected $table = 'project_logs';

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
            `status`      ENUM('planned','in-progress','done') NOT NULL DEFAULT 'planned',
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

    public function get_by_student_class($student_id, $class_id)
    {
        return $this->db
            ->where(['student_id' => $student_id, 'class_id' => $class_id])
            ->order_by('created_at', 'DESC')
            ->get($this->table)->result_array();
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
    public function get_by_group($group_id)
    {
        return $this->db
            ->select('pl.*, sm.firstname, sm.lastname')
            ->from($this->table . ' pl')
            ->join('student_master sm', 'pl.student_id = sm.trans_no', 'left')
            ->where('pl.group_id', $group_id)
            ->order_by('pl.created_at', 'DESC')
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

    public function delete_entry($log_id, $student_id)
    {
        return $this->db
            ->where(['log_id' => $log_id, 'student_id' => $student_id])
            ->delete($this->table);
    }

    // Admin read-only browse, optionally filtered by course and/or section.
    public function get_all_for_admin($class_id = null, $section = null)
    {
        $this->db
            ->select('pl.*, c.class_code, c.class_name, cls.section,
                      sm.lastname, sm.firstname, g.group_name')
            ->from('project_logs pl')
            ->join('classes c', 'pl.class_id = c.class_id', 'left')
            ->join('student_master sm', 'pl.student_id = sm.trans_no', 'left')
            ->join('class_student cls', 'cls.student_id = pl.student_id', 'left')
            ->join('groupings g', 'pl.group_id = g.group_id', 'left')
            ->group_by('pl.log_id')
            ->order_by('c.class_code')
            ->order_by('sm.lastname')
            ->order_by('pl.created_at', 'DESC');

        if (!empty($class_id)) {
            $this->db->where('pl.class_id', $class_id);
        }
        if (!empty($section)) {
            $this->db->where('cls.section', $section);
        }

        return $this->db->get()->result_array();
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
