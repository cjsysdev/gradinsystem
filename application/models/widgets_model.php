<?php
defined('BASEPATH') or exit('No direct script access allowed');

// Registry of reusable classwork "widgets" (Worksheet Form, Card Sort, etc. —
// see root/docs/paperless-midterm-plan.md). Adding a new widget later means
// "add a row + drop a view file", not editing a controller's if/else chain.
class Widgets_model extends CI_Model
{
    public function install()
    {
        $this->db->query("CREATE TABLE IF NOT EXISTS `widgets` (
            `widget_id`         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            `widget_key`        VARCHAR(32) NOT NULL,
            `name`              VARCHAR(64) NOT NULL,
            `input_view`        VARCHAR(128) NOT NULL,
            `admin_config_view` VARCHAR(128) DEFAULT NULL,
            `created_at`        DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            UNIQUE KEY `uq_widget_key` (`widget_key`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

        // assessments.given is referenced by CLAUDE.md/the paperless-midterm plan
        // as "currently unused" — but it does not actually exist in the live
        // schema yet (verified against the current DB dump), so it's added here
        // alongside widget_id rather than assumed to pre-exist.
        // Note: "ADD COLUMN IF NOT EXISTS" is a MariaDB-only extension — real
        // MySQL rejects it as a syntax error (silently, since db_debug=FALSE),
        // so existence is checked via information_schema instead, which works
        // on both.
        $this->_add_column_if_missing('assessments', 'given', 'LONGTEXT DEFAULT NULL');
        $this->_add_column_if_missing('assessments', 'widget_id', 'INT UNSIGNED DEFAULT NULL');

        // Only Widget B (Worksheet Form) is built so far — other widget_key
        // rows get added when their input_view actually exists, so the admin
        // dropdown never offers a widget with no view behind it.
        $this->db->query("INSERT IGNORE INTO widgets (widget_key, name, input_view, admin_config_view)
            VALUES ('worksheet', 'Worksheet Form', 'widgets/worksheet', NULL)");
    }

    public function get_all()
    {
        return $this->db->order_by('name')->get('widgets')->result_array();
    }

    public function get($widget_id)
    {
        return $this->db->where('widget_id', $widget_id)->get('widgets')->row_array();
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
}
