<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Schema_guard
 * ─────────────────────────────────────────────────────────────────────────
 * Safety rails for the one-time `install()` / schema-migration routes.
 *
 * WHY THIS EXISTS
 * On 2026-07-23 22:58:28, `/Groupings/install` silently destroyed every group
 * membership in the system. `Grouping_model::install()` ran:
 *
 *     DROP TABLE IF EXISTS group_members;   -- succeeded: wiped all rosters
 *     DROP TABLE IF EXISTS groupings;       -- REFUSED (FK from assessment_live_state)
 *     CREATE TABLE groupings ...            -- failed: already exists
 *     CREATE TABLE group_members ...        -- succeeded: empty table
 *
 * Every one of those failures was invisible, because `db_debug = FALSE`
 * (config/database.php) suppresses DB errors and `log_threshold` was 0 so
 * nothing was logged. The run reported "Grouping tables ready." and 464
 * memberships were gone. Nobody noticed for four days.
 *
 * The three rails below each break that chain at a different point:
 *
 *   ddl()        — a failed schema statement can no longer pass unnoticed.
 *                  Errors are logged AND collected, so the caller can refuse
 *                  to report success.
 *   backup()     — the affected tables are dumped to a restorable .sql file
 *                  BEFORE anything runs, so a mistake is always recoverable.
 *   confirmed()  — install routes are plain GET URLs reachable from history,
 *                  a bookmark, or a browser prefetch. Now they require an
 *                  explicit POST from a confirmation screen.
 *
 * Usage in a controller:
 *
 *     if (!$this->schema_guard->confirmed('Grouping tables', 'Groupings/install')) return;
 *     $this->schema_guard->backup(['grouping_sets', 'groupings', 'group_members']);
 *     $this->Grouping_model->install();
 *     if ($this->schema_guard->failed()) { ... show $this->schema_guard->failures() ... }
 */
class Schema_guard
{
    private $CI;
    private $failures = [];
    private $last_backup = null;

    /** Tables bigger than this are still backed up, but a warning is logged. */
    const BACKUP_ROW_WARN = 50000;

    public function __construct()
    {
        $this->CI = &get_instance();
        $this->CI->load->database();
        $this->CI->load->helper('url');
    }

    // ── Rail 1: DDL that cannot fail silently ────────────────────────────
    /**
     * Runs one schema statement, checking for failure even though db_debug
     * is off. Returns TRUE on success, FALSE on failure (also recorded in
     * failures() and written to application/logs/).
     */
    public function ddl($sql)
    {
        $result = $this->CI->db->query($sql);
        $error  = $this->CI->db->error();

        if ($result === false || !empty($error['code'])) {
            $message = sprintf(
                'SCHEMA DDL FAILED [%s] %s | SQL: %s',
                isset($error['code']) ? $error['code'] : '?',
                isset($error['message']) ? $error['message'] : 'unknown error',
                preg_replace('/\s+/', ' ', trim($sql))
            );
            log_message('error', $message);
            $this->failures[] = $message;
            return false;
        }
        return true;
    }

    public function failed()
    {
        return !empty($this->failures);
    }

    public function failures()
    {
        return $this->failures;
    }

    public function reset()
    {
        $this->failures = [];
    }

    // ── Rail 2: pre-flight backup ────────────────────────────────────────
    /**
     * Dumps the given tables (structure + rows) to a timestamped .sql file
     * under uploads/schema_backups/. Call this BEFORE any install()/migration.
     * Returns the file path, or FALSE if nothing could be written.
     */
    public function backup(array $tables, $label = 'install')
    {
        $dir = FCPATH . 'uploads/schema_backups/';
        if (!is_dir($dir) && !@mkdir($dir, 0777, true)) {
            log_message('error', 'Schema_guard: could not create backup dir ' . $dir);
            return false;
        }

        // uploads/ is served directly by Apache and has no access rules of its
        // own, so a dump left here would be downloadable by anyone who guessed
        // the filename — these files contain the full student roster. Deny the
        // whole directory at the web level (2.4 syntax + 2.2 fallback).
        $htaccess = $dir . '.htaccess';
        if (!file_exists($htaccess)) {
            @file_put_contents($htaccess,
                "# Database dumps — never web-accessible.\n"
                . "<IfModule mod_authz_core.c>\n    Require all denied\n</IfModule>\n"
                . "<IfModule !mod_authz_core.c>\n    Order allow,deny\n    Deny from all\n</IfModule>\n");
        }

        $safe_label = preg_replace('/[^A-Za-z0-9_-]/', '', $label);
        $path = $dir . $safe_label . '_' . date('Ymd_His') . '.sql';

        // Stream to disk instead of building one string: classworks holds
        // whole submissions (code/JSON blobs), and loading the table with
        // result_array() plus the growing $out blew past memory_limit.
        $fh = @fopen($path, 'wb');
        if (!$fh) {
            log_message('error', 'Schema_guard: failed writing backup to ' . $path);
            return false;
        }

        $ok  = true;
        $out = "-- Schema_guard pre-install backup
";
        $out .= '-- label: ' . $safe_label . "
";
        $out .= '-- taken: ' . date('Y-m-d H:i:s') . "
";
        $out .= '-- by admin session: ' . (string) $this->CI->session->userdata('student_id') . "
";
        $out .= "-- Restore: mysql -u root -p <db> < this_file.sql
";
        $out .= "SET FOREIGN_KEY_CHECKS=0;

";
        $ok = $ok && fwrite($fh, $out) !== false;

        foreach ($tables as $table) {
            if (!$this->CI->db->table_exists($table)) {
                $ok = $ok && fwrite($fh, "-- (table `$table` did not exist at backup time)

") !== false;
                continue;
            }

            $create = $this->CI->db->query('SHOW CREATE TABLE `' . $table . '`')->row_array();
            $out  = "--
-- Table: $table
--
";
            $out .= 'DROP TABLE IF EXISTS `' . $table . "`;
";
            $out .= (isset($create['Create Table']) ? $create['Create Table'] : '') . ";

";
            $ok = $ok && fwrite($fh, $out) !== false;

            $count = (int) $this->CI->db->count_all($table);
            if ($count > self::BACKUP_ROW_WARN) {
                log_message('error', "Schema_guard: backing up large table $table ($count rows)");
            }
            if ($count === 0) {
                $ok = $ok && fwrite($fh, "-- (no rows)

") !== false;
                continue;
            }

            // Unbuffered read: rows are pulled from the server one at a time
            // rather than copied into PHP memory. It must be fully consumed
            // and freed before the connection runs another query.
            $result = $this->CI->db->conn_id->query('SELECT * FROM `' . $table . '`', MYSQLI_USE_RESULT);
            if ($result === false) {
                log_message('error', "Schema_guard: could not read $table for backup");
                $ok = false;
                break;
            }

            $cols = null;
            while ($row = $result->fetch_assoc()) {
                if ($cols === null) {
                    $cols = implode(',', array_map(function ($c) { return '`' . $c . '`'; }, array_keys($row)));
                }
                $vals = array_map(function ($v) {
                    return $v === null ? 'NULL' : $this->CI->db->escape($v);
                }, array_values($row));
                $ok = $ok && fwrite($fh, 'INSERT INTO `' . $table . '` (' . $cols . ') VALUES (' . implode(',', $vals) . ");
") !== false;
            }
            $result->free();
            $ok = $ok && fwrite($fh, "
") !== false;
        }

        $ok = $ok && fwrite($fh, "SET FOREIGN_KEY_CHECKS=1;
") !== false;
        fclose($fh);

        if (!$ok) {
            @unlink($path);
            log_message('error', 'Schema_guard: failed writing backup to ' . $path);
            return false;
        }

        $this->last_backup = $path;
        return $path;
    }

    public function last_backup()
    {
        return $this->last_backup;
    }

    // ── Rail 3: explicit confirmation ────────────────────────────────────
    /**
     * Returns TRUE only when the admin has POSTed the confirmation form for
     * this exact route. On a plain GET it renders the confirmation screen and
     * returns FALSE — so the caller must `return` immediately.
     *
     * A bare GET must never mutate the schema: these URLs live in browser
     * history and bookmarks, and prefetchers follow them.
     */
    public function confirmed($what, $route, array $tables = [])
    {
        if (strtoupper($this->CI->input->method()) === 'POST'
            && $this->CI->input->post('schema_guard_confirm') === 'yes') {
            return true;
        }

        $this->CI->load->view('schema_confirm', [
            'what'   => $what,
            'route'  => $route,
            'tables' => $tables,
        ]);
        return false;
    }
}
