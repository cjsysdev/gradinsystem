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
            `revision`       INT UNSIGNED NOT NULL DEFAULT 0,
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

        // Self-select groupings: students form/join their own groups (up to
        // min_members, which doubles as the target group size) instead of the
        // admin's shuffle+round-robin assignment in Groupings::store(). Added
        // via a safe column check rather than folding into the CREATE TABLE
        // above, since grouping_sets already exists in live installs.
        $this->_add_column_if_missing('grouping_sets', 'self_select', 'TINYINT(1) NOT NULL DEFAULT 0');

        // Monotonic revision for the shared live-state row. The old sync token
        // was the DATETIME updated_at, whose 1-second resolution made two saves
        // in the same second look unchanged — a client already holding that
        // second was told "no change" and never received a teammate's answers.
        // A counter bumped on every save is collision-free. Added here (not in
        // the CREATE TABLE) so live installs get it too.
        $this->_add_column_if_missing('assessment_live_state', 'revision', 'INT UNSIGNED NOT NULL DEFAULT 0');
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

    // ── Grouping sets ────────────────────────────────────────────────────────

    public function create_set($section_id, $name, $min_members, $self_select = false)
    {
        $this->db->insert('grouping_sets', [
            'section_id'  => $section_id,
            'name'        => $name,
            'min_members' => $min_members,
            'self_select' => $self_select ? 1 : 0,
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

    public function rename_group($group_id, $name)
    {
        $this->db->where('group_id', $group_id)->update($this->table, ['group_name' => $name]);
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

    // ── Self-select support ──────────────────────────────────────────────────

    public function count_members($group_id)
    {
        return (int) $this->db->where('group_id', $group_id)->count_all_results('group_members');
    }

    // Groups in a set plus their current members — same shape
    // Groupings::view_set() already builds inline, made reusable for the
    // student-facing self-select picker.
    public function get_groups_with_members($set_id)
    {
        $this->load->model('Group_member_model');
        $groups = $this->get_groups_by_set($set_id);
        foreach ($groups as &$g) {
            $g['members'] = $this->Group_member_model->get_members_by_group($g['group_id']);
        }
        unset($g);
        return $groups;
    }

    // Creates a new group in $set_id with $student_id as its first member.
    public function create_group_with_member($set_id, $group_name, $student_id)
    {
        $group_id = $this->create_group([
            'set_id'     => $set_id,
            'group_name' => $group_name,
        ]);
        $this->db->insert('group_members', [
            'group_id'   => $group_id,
            'student_id' => $student_id,
        ]);
        return $group_id;
    }

    public function join_group($group_id, $student_id)
    {
        $this->db->insert('group_members', [
            'group_id'   => $group_id,
            'student_id' => $student_id,
        ]);
    }

    // Removes a student from a still-forming group; deletes the group too
    // if that leaves it empty, so the picker doesn't accumulate orphans.
    public function leave_group($group_id, $student_id)
    {
        $this->db->where(['group_id' => $group_id, 'student_id' => $student_id])->delete('group_members');
        if ($this->count_members($group_id) === 0) {
            $this->db->where('group_id', $group_id)->delete('groupings');
        }
    }
}
