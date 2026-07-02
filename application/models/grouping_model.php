<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Grouping_model extends CI_Model
{
    protected $table = 'groupings';

    // ── Schema bootstrap ────────────────────────────────────────────────────
    // Rebuilds groupings/group_members around a grouping_sets concept, so a
    // section can have several independently-managed named group schemes
    // (e.g. "Lab Groups", "Project Teams") instead of one flat group list.
    // The old groupings.section_id column was typed INT while the app stores
    // the section CODE (e.g. "2A") in it, which fails inserts under strict
    // SQL mode — rebuilt clean with section_id living on grouping_sets as VARCHAR.
    public function install()
    {
        $this->db->query("CREATE TABLE IF NOT EXISTS `grouping_sets` (
            `set_id`      INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            `section_id`  VARCHAR(32) NOT NULL,
            `name`        VARCHAR(100) NOT NULL,
            `min_members` INT UNSIGNED NOT NULL DEFAULT 1,
            `created_at`  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            KEY `idx_section` (`section_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

        $this->db->query("DROP TABLE IF EXISTS `group_members`");
        $this->db->query("DROP TABLE IF EXISTS `groupings`");

        $this->db->query("CREATE TABLE `groupings` (
            `group_id`   INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            `set_id`     INT UNSIGNED NOT NULL,
            `group_name` VARCHAR(50) NOT NULL,
            FOREIGN KEY (`set_id`) REFERENCES `grouping_sets`(`set_id`) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

        $this->db->query("CREATE TABLE `group_members` (
            `member_id`  INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            `group_id`   INT UNSIGNED NOT NULL,
            `student_id` INT NOT NULL,
            UNIQUE KEY `uq_group_student` (`group_id`, `student_id`),
            FOREIGN KEY (`group_id`) REFERENCES `groupings`(`group_id`) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

        $this->db->query("CREATE TABLE IF NOT EXISTS `assessment_groupings` (
            `assessment_id` INT NOT NULL PRIMARY KEY,
            `set_id`        INT UNSIGNED NOT NULL,
            FOREIGN KEY (`assessment_id`) REFERENCES `assessments`(`assessment_id`) ON DELETE CASCADE,
            FOREIGN KEY (`set_id`) REFERENCES `grouping_sets`(`set_id`) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

        // Generic, reusable live/collaborative state: one row per
        // (assessment, optional group). group_id NULL = section-wide shared
        // state (e.g. a future brainstorm-board widget); group_id set = one
        // group's private live draft (used by group assessment submission).
        $this->db->query("CREATE TABLE IF NOT EXISTS `assessment_live_state` (
            `state_id`       INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            `assessment_id`  INT NOT NULL,
            `group_id`       INT UNSIGNED DEFAULT NULL,
            `content`        LONGTEXT,
            `last_edited_by` VARCHAR(50) DEFAULT NULL,
            `updated_at`     DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            UNIQUE KEY `uq_assessment_group` (`assessment_id`, `group_id`),
            FOREIGN KEY (`assessment_id`) REFERENCES `assessments`(`assessment_id`) ON DELETE CASCADE,
            FOREIGN KEY (`group_id`) REFERENCES `groupings`(`group_id`) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

        // Soft "ready to submit" flag per student per live-state row.
        $this->db->query("CREATE TABLE IF NOT EXISTS `assessment_live_state_ready` (
            `id`         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            `state_id`   INT UNSIGNED NOT NULL,
            `student_id` VARCHAR(50) NOT NULL,
            `ready`      TINYINT(1) NOT NULL DEFAULT 0,
            `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            UNIQUE KEY `uq_state_student` (`state_id`, `student_id`),
            FOREIGN KEY (`state_id`) REFERENCES `assessment_live_state`(`state_id`) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    }

    // ── Grouping sets ────────────────────────────────────────────────────────

    public function create_set($section_id, $name, $min_members)
    {
        $this->db->insert('grouping_sets', [
            'section_id'  => $section_id,
            'name'        => $name,
            'min_members' => $min_members,
        ]);
        return $this->db->insert_id();
    }

    public function get_set($set_id)
    {
        return $this->db->where('set_id', $set_id)->get('grouping_sets')->row_array();
    }

    public function get_sets_by_section($section_id)
    {
        return $this->db->where('section_id', $section_id)
            ->order_by('created_at')
            ->get('grouping_sets')->result_array();
    }

    public function get_all_sets()
    {
        return $this->db->order_by('section_id')->order_by('created_at')->get('grouping_sets')->result_array();
    }

    public function delete_set($set_id)
    {
        // groupings + group_members rows cascade via FK
        $this->db->where('set_id', $set_id)->delete('grouping_sets');
    }

    // ── Groups within a set ──────────────────────────────────────────────────

    public function create_group($data)
    {
        $this->db->insert($this->table, $data);
        return $this->db->insert_id();
    }

    public function get_groups_by_set($set_id)
    {
        return $this->db->where('set_id', $set_id)->order_by('group_id')->get($this->table)->result_array();
    }

    public function get($group_id)
    {
        return $this->db->where('group_id', $group_id)->get($this->table)->row_array();
    }

    // ── Assessment linkage ──────────────────────────────────────────────────

    public function get_set_for_assessment($assessment_id)
    {
        $row = $this->db->where('assessment_id', $assessment_id)->get('assessment_groupings')->row_array();
        return $row ? (int) $row['set_id'] : null;
    }

    public function get_student_group($student_id, $set_id)
    {
        return $this->db->select('g.*')
            ->from('groupings g')
            ->join('group_members gm', 'gm.group_id = g.group_id')
            ->where('g.set_id', $set_id)
            ->where('gm.student_id', $student_id)
            ->get()->row_array();
    }
}
