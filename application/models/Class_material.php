<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Class_material
 * ─────────────────────────────────────────────────────────────────────────
 * Course materials: the demo files, handouts, reviewers and sample databases
 * that used to be dumped loose into uploads/ root and surfaced only by
 * hand-pasting a path into `discussions.link`. One row per uploaded file,
 * assigned to one or more class sections through class_material_sections.
 *
 * Files live at assets/materials/{CLASS_CODE}/{stored_name}, mirroring the
 * assets/json/{CLASS_CODE}/ convention in Iq_topic_helper.
 *
 * IMPORTANT — scoping is a LISTING filter, not an access control. Everything
 * under assets/ is served straight off disk by Apache (the root .htaccess only
 * rewrites paths that do NOT exist), so any material is fetchable by URL
 * without a session. That is fine for handouts; if something genuinely private
 * ever needs to live here, add a serve() endpoint that readfile()s behind
 * class_student::is_enrolled_in_schedule() and stop linking the raw path.
 *
 * Extends CI_Model, not MY_Model, because it owns install(): MY_Model's
 * constructor calls list_fields() on a table that doesn't exist yet. Same
 * reason as Section_officer, Sms_model, Grouping_model, Project_log_model.
 */
class Class_material extends CI_Model
{
    protected $table    = 'class_materials';
    protected $sections = 'class_material_sections';

    /** Where uploads land, relative to FCPATH. */
    const BASE_DIR = 'assets/materials/';

    // ── Schema bootstrap ────────────────────────────────────────────────────
    // Idempotent — run via AdminMaterialController/install, which handles
    // confirmation + backup. Never add a DROP here (see the Schema_guard
    // docblock for what a DROP in an install() once cost us).
    public function install()
    {
        $this->load->library('schema_guard');
        $this->schema_guard->reset();

        $this->schema_guard->ddl("CREATE TABLE IF NOT EXISTS `class_materials` (
            `material_id`   INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            `title`         VARCHAR(200) NOT NULL,
            `description`   TEXT NULL,
            -- Free text, not an enum: 'Demo 1', 'Week 3', 'Finals Reviewer'.
            -- Purely a grouping label for the student list.
            `category`      VARCHAR(64) NULL,
            -- class_id drives the on-disk subfolder; class_code is the
            -- sanitized copy so the path stays reproducible even if the
            -- classes row is later renamed.
            `class_id`      INT NULL,
            `class_code`    VARCHAR(32) NULL,
            `stored_name`   VARCHAR(255) NOT NULL,
            `original_name` VARCHAR(255) NOT NULL,
            `extension`     VARCHAR(16) NOT NULL,
            `file_size`     INT UNSIGNED NOT NULL DEFAULT 0,
            `is_active`     TINYINT(1) NOT NULL DEFAULT 1,
            `created_by`    INT NULL,
            `created_at`    DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            KEY `idx_class`    (`class_id`),
            KEY `idx_category` (`category`),
            KEY `idx_created`  (`created_at`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

        // uq_material_schedule is load-bearing: set_sections() is replace-all,
        // so the unique key turns a double-submitted form into a no-op instead
        // of duplicate assignments (which would double-list the material).
        $this->schema_guard->ddl("CREATE TABLE IF NOT EXISTS `class_material_sections` (
            `id`          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            `material_id` INT UNSIGNED NOT NULL,
            `schedule_id` INT NOT NULL,
            UNIQUE KEY `uq_material_schedule` (`material_id`, `schedule_id`),
            KEY `idx_schedule` (`schedule_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

        $this->ensure_base_dir();

        return $this->schema_guard->failures();
    }

    public function table_ready()
    {
        return $this->db->table_exists($this->table)
            && $this->db->table_exists($this->sections);
    }

    /**
     * Creates assets/materials/ and drops in the .htaccess that keeps it from
     * becoming a script-execution hole. Returns TRUE if the directory exists
     * and is writable afterwards.
     */
    public function ensure_base_dir()
    {
        $dir = FCPATH . self::BASE_DIR;

        if (!is_dir($dir) && !@mkdir($dir, 0775, true)) {
            log_message('error', 'Class_material: could not create ' . $dir);
            return false;
        }

        // This directory is inside the docroot and served directly by Apache,
        // so an uploaded .php would otherwise execute. The extension whitelist
        // in AdminMaterialController is the first line of defence; this is the
        // second, because a whitelist is one refactor away from being loosened.
        // Note: NOT "Require all denied" — unlike the schema backups, these
        // files are meant to stay downloadable.
        // Written only when absent, so a hand-tuned guard is never clobbered
        // (same rule as Schema_guard::backup()).
        //
        // Every php_flag is wrapped in <IfModule>. An unwrapped `php_flag` is
        // "Invalid command" on any server where PHP is php-fpm / mod_proxy_fcgi
        // rather than mod_php — which would turn this hardening into a blanket
        // HTTP 500 on every material download. The RemoveHandler / RemoveType /
        // AddType lines are the portable teeth that actually neutralise fpm.
        $htaccess = $dir . '.htaccess';
        if (!file_exists($htaccess)) {
            $exts = '.php .phtml .php3 .php4 .php5 .php6 .php7 .php8 .phps .pht .phar';
            @file_put_contents($htaccess,
                "# Uploaded course materials. Static downloads only — never executable.\n"
                . "# See Class_material::ensure_base_dir() for why each line is here.\n"
                . "<IfModule mod_php.c>\n    php_flag engine off\n</IfModule>\n"
                . "<IfModule mod_php5.c>\n    php_flag engine off\n</IfModule>\n"
                . "<IfModule mod_php7.c>\n    php_flag engine off\n</IfModule>\n"
                . "\n"
                . "RemoveHandler " . $exts . " .cgi .pl .py .sh .asp .aspx .jsp\n"
                . "RemoveType " . $exts . "\n"
                . "AddType text/plain " . $exts . " .cgi .pl .py .sh\n"
                . "\n"
                . "Options -ExecCGI -Indexes\n"
                . "\n"
                . "<IfModule mod_headers.c>\n    Header set X-Content-Type-Options \"nosniff\"\n</IfModule>\n"
                . "\n"
                . "<FilesMatch \"\\.(?i:php[0-9]?|phtml|phps|pht|phar|cgi|pl|sh|hta|htaccess)$\">\n"
                . "    <IfModule mod_authz_core.c>\n        Require all denied\n    </IfModule>\n"
                . "    <IfModule !mod_authz_core.c>\n        Order allow,deny\n        Deny from all\n    </IfModule>\n"
                . "</FilesMatch>\n");
        }

        return is_writable($dir);
    }

    /**
     * Absolute directory for one class code, created on demand.
     * Returns FALSE if it could not be created or is not writable.
     */
    public function dir_for_class_code($class_code)
    {
        if (!$this->ensure_base_dir()) {
            return false;
        }

        $safe = $this->sanitize_class_code($class_code);
        $dir  = FCPATH . self::BASE_DIR . ($safe === '' ? '_unsorted' : $safe) . '/';

        if (!is_dir($dir) && !@mkdir($dir, 0775, true)) {
            log_message('error', 'Class_material: could not create ' . $dir);
            return false;
        }

        return is_writable($dir) ? $dir : false;
    }

    /** Same rule as Iq_topic_helper uses for assets/json/{CLASS_CODE}/. */
    public function sanitize_class_code($class_code)
    {
        return preg_replace('/[^A-Za-z0-9_-]/', '_', (string) $class_code);
    }

    /** Web-relative path for a row, for base_url() / file_exists(FCPATH . …). */
    public function relative_path(array $row)
    {
        $folder = $this->sanitize_class_code($row['class_code']);
        if ($folder === '') {
            $folder = '_unsorted';
        }
        return self::BASE_DIR . $folder . '/' . $row['stored_name'];
    }

    public function absolute_path(array $row)
    {
        return FCPATH . $this->relative_path($row);
    }

    // ── Writes ──────────────────────────────────────────────────────────────

    /** Inserts the master row plus its section assignments. Returns the new id. */
    public function create(array $data, array $schedule_ids)
    {
        $this->db->insert($this->table, $data);
        $material_id = (int) $this->db->insert_id();

        if ($material_id) {
            $this->set_sections($material_id, $schedule_ids);
        }

        return $material_id;
    }

    /** Metadata only — never touches stored_name/extension/file_size. */
    public function update_meta($material_id, array $data)
    {
        $allowed = ['title', 'description', 'category', 'is_active'];
        $clean   = array_intersect_key($data, array_flip($allowed));

        if (!$clean) {
            return false;
        }

        return $this->db->where('material_id', (int) $material_id)
                        ->update($this->table, $clean);
    }

    /**
     * Replace-all: a material's assignments are exactly what the form
     * submitted. Deliberately not a merge — an unchecked box has to mean
     * "unassign", and a partial save is how a whole section silently loses
     * access (cf. the project-log designation incident).
     */
    public function set_sections($material_id, array $schedule_ids)
    {
        $material_id = (int) $material_id;

        $ids = array_values(array_unique(array_filter(array_map('intval', $schedule_ids))));

        $this->db->trans_start();
        $this->db->where('material_id', $material_id)->delete($this->sections);

        if ($ids) {
            $rows = [];
            foreach ($ids as $schedule_id) {
                $rows[] = ['material_id' => $material_id, 'schedule_id' => $schedule_id];
            }
            $this->db->insert_batch($this->sections, $rows);
        }

        $this->db->trans_complete();

        return $this->db->trans_status();
    }

    /**
     * Removes the assignments and the master row, returning the row that was
     * deleted so the caller can unlink the file. Returns NULL if not found.
     */
    public function delete($material_id)
    {
        $material_id = (int) $material_id;
        $row = $this->get_by_id($material_id);

        if (!$row) {
            return null;
        }

        $this->db->where('material_id', $material_id)->delete($this->sections);
        $this->db->where('material_id', $material_id)->delete($this->table);

        return $row;
    }

    // ── Reads ───────────────────────────────────────────────────────────────

    public function get_by_id($material_id)
    {
        return $this->db->where('material_id', (int) $material_id)
                        ->get($this->table)->row_array() ?: null;
    }

    /** schedule_ids currently assigned to one material. */
    public function get_section_ids($material_id)
    {
        $rows = $this->db->select('schedule_id')
                         ->where('material_id', (int) $material_id)
                         ->get($this->sections)->result_array();

        return array_map('intval', array_column($rows, 'schedule_id'));
    }

    /**
     * Admin table: every material with a readable summary of where it is
     * assigned. GROUP_CONCAT over the junction so one row renders one line.
     */
    public function get_all_for_admin()
    {
        $sql = "
            SELECT
                m.*,
                GROUP_CONCAT(
                    DISTINCT CONCAT(cl.class_code, ' ', cs.section, ' (', COALESCE(cs.type, 'NA'), ')')
                    ORDER BY cl.class_code, cs.section SEPARATOR ', '
                ) AS assigned_sections,
                COUNT(DISTINCT ms.schedule_id) AS section_count
            FROM class_materials m
            LEFT JOIN class_material_sections ms ON ms.material_id = m.material_id
            LEFT JOIN class_schedule cs         ON cs.schedule_id = ms.schedule_id
            LEFT JOIN classes cl                ON cl.class_id = cs.class_id
            GROUP BY m.material_id
            ORDER BY m.created_at DESC
        ";

        return $this->db->query($sql)->result_array();
    }

    /**
     * What one student may see.
     *
     * Roster rule (CLAUDE.md): class_student.schedule_id + status='enrolled' +
     * active semester. Never join class_student.section = class_schedule.section
     * — that ignores both the semester and the enrolment status.
     *
     * DISTINCT is required: a student enrolled in both the LEC and the LAB
     * schedule of the same class matches two junction rows for one material.
     */
    public function get_for_student($student_id)
    {
        if (empty($student_id) || !$this->table_ready()) {
            return [];
        }

        $sql = "
            SELECT DISTINCT
                m.material_id, m.title, m.description, m.category,
                m.class_id, m.class_code, m.stored_name, m.original_name,
                m.extension, m.file_size, m.created_at
            FROM class_materials m
            JOIN class_material_sections ms ON ms.material_id = m.material_id
            JOIN class_student cs           ON cs.schedule_id = ms.schedule_id
            JOIN semester_master sm         ON cs.semester_id = sm.trans_no AND sm.is_active = 1
            WHERE cs.student_id = ?
              AND cs.status = 'enrolled'
              AND m.is_active = 1
            ORDER BY m.category IS NULL, m.category, m.created_at DESC
        ";

        return $this->db->query($sql, [$student_id])->result_array();
    }

    /**
     * Cheap count behind the student nav link, so "Materials" only appears for
     * students who actually have some. Same joins as get_for_student().
     */
    public function count_for_student($student_id)
    {
        if (empty($student_id) || !$this->table_ready()) {
            return 0;
        }

        $sql = "
            SELECT COUNT(DISTINCT m.material_id) AS n
            FROM class_materials m
            JOIN class_material_sections ms ON ms.material_id = m.material_id
            JOIN class_student cs           ON cs.schedule_id = ms.schedule_id
            JOIN semester_master sm         ON cs.semester_id = sm.trans_no AND sm.is_active = 1
            WHERE cs.student_id = ?
              AND cs.status = 'enrolled'
              AND m.is_active = 1
        ";

        $row = $this->db->query($sql, [$student_id])->row_array();

        return $row ? (int) $row['n'] : 0;
    }
}
