<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Adds the missing secondary indexes on the hot join/filter columns that
 * nearly every grade, attendance and classwork query relies on. Before this,
 * `classworks`, `attendance` and `assessments` carried only their PRIMARY KEY
 * (see assets/database.sql), so the multi-JOIN grade aggregates in
 * application/models/classworks.php (getGradesBySection, getActivitiesGrade,
 * etc.) full-scanned `attendance` (~9k rows) and `classworks` (~4.8k rows) on
 * every grade-page load.
 *
 * Every ADD/DROP is guarded against information_schema so this migration is:
 *   - idempotent   (re-running never errors on an already-present index)
 *   - drift-safe   (an index is only added when every target column really
 *                   exists in the live table — the SQL dumps in assets/ are
 *                   known to be stale, e.g. class_student.schedule_id/status
 *                   exist in code but not in the dump)
 *   - engine-neutral (plain ADD INDEX; works on the current MyISAM tables and
 *                   on InnoDB after any future assets/updated.sql conversion)
 *
 * This mirrors the defensive DDL style already used in the codebase, e.g.
 * Widgets_model::_add_column_if_missing() and Grouping_model.
 */
class Migration_Add_performance_indexes extends CI_Migration
{
    /**
     * index name => [table, [columns...]]
     * Column order in a composite matters: the leftmost prefix is usable on
     * its own, so idx_cw_student_assessment also covers student_id-only lookups.
     */
    private $indexes = [
        // classworks: filtered/joined by both student_id and assessment_id
        'idx_cw_assessment'         => ['classworks', ['assessment_id']],
        'idx_cw_student_assessment' => ['classworks', ['student_id', 'assessment_id']],
        // attendance: joined by schedule_id, grouped by student_id, ranged by date
        'idx_att_schedule'          => ['attendance', ['schedule_id']],
        'idx_att_student'           => ['attendance', ['student_id']],
        'idx_att_date'              => ['attendance', ['date']],
        // assessments: joined by schedule_id and iotype_id in every grade query
        'idx_ass_schedule'          => ['assessments', ['schedule_id']],
        'idx_ass_iotype'            => ['assessments', ['iotype_id']],
        // class_student: enrollment lookups by student and by class/section
        'idx_cs_student'            => ['class_student', ['student_id']],
        'idx_cs_class'              => ['class_student', ['class_id']],
        // class_schedule: joined by class_id
        'idx_sched_class'           => ['class_schedule', ['class_id']],
    ];

    public function up()
    {
        foreach ($this->indexes as $name => $spec) {
            list($table, $columns) = $spec;
            $this->add_index_if_missing($table, $name, $columns);
        }
    }

    public function down()
    {
        foreach ($this->indexes as $name => $spec) {
            $this->drop_index_if_present($spec[0], $name);
        }
    }

    // ---- helpers ---------------------------------------------------------

    private function table_exists($table)
    {
        $sql = 'SELECT COUNT(*) AS c FROM information_schema.TABLES
                WHERE table_schema = DATABASE() AND table_name = ?';
        return (int) $this->db->query($sql, [$table])->row()->c > 0;
    }

    private function column_exists($table, $column)
    {
        $sql = 'SELECT COUNT(*) AS c FROM information_schema.COLUMNS
                WHERE table_schema = DATABASE() AND table_name = ? AND column_name = ?';
        return (int) $this->db->query($sql, [$table, $column])->row()->c > 0;
    }

    private function index_exists($table, $index)
    {
        $sql = 'SELECT COUNT(*) AS c FROM information_schema.STATISTICS
                WHERE table_schema = DATABASE() AND table_name = ? AND index_name = ?';
        return (int) $this->db->query($sql, [$table, $index])->row()->c > 0;
    }

    private function add_index_if_missing($table, $index, array $columns)
    {
        if (!$this->table_exists($table) || $this->index_exists($table, $index)) {
            return;
        }
        foreach ($columns as $column) {
            if (!$this->column_exists($table, $column)) {
                return; // schema drift: skip rather than fail the whole migration
            }
        }
        $cols = implode(', ', array_map([$this->db, 'protect_identifiers'], $columns));
        $this->db->query(
            'ALTER TABLE ' . $this->db->protect_identifiers($table)
            . ' ADD INDEX ' . $this->db->protect_identifiers($index) . ' (' . $cols . ')'
        );
    }

    private function drop_index_if_present($table, $index)
    {
        if ($this->table_exists($table) && $this->index_exists($table, $index)) {
            $this->db->query(
                'ALTER TABLE ' . $this->db->protect_identifiers($table)
                . ' DROP INDEX ' . $this->db->protect_identifiers($index)
            );
        }
    }
}
