<?php
defined('BASEPATH') or exit('No direct script access allowed');

// One-time schema normalization for `assessments` (see
// C:\Users\CJ\.claude\plans\the-current-assessment-table-curried-knuth.md
// for the full plan). Today one row = one assessment PER SECTION, which
// duplicates shared content (title/description/given widget JSON/max_score)
// across every section it's given to. This splits `assessments` into a
// content-only master + a new `assessment_section` junction holding the
// per-section state (due/status/is_groupings).
//
// Two phases, run as two separate admin actions:
//   install_compat_view() — Phase 1: creates `assessment_full`, a view that
//     reproduces today's exact denormalized row shape over the UNCHANGED
//     `assessments` table. Read-only query sites swap their table name to
//     this view; nothing about the schema or the writable `assessments`
//     table changes yet. Fully reversible with DROP VIEW.
//   install() — Phase 2: the actual normalization. Builds the new tables,
//     backfills them from the live table (deduping this semester's repeated
//     assessments into shared masters; older semesters get 1:1 masters),
//     verifies row-for-row grade totals match, then swaps the table names
//     and re-points `assessment_groupings`/`assessment_live_state`'s FKs.
//     NOT reversible after admin write-activity resumes — see rollback_sql()
//     for the manual recovery statements, valid only in the verification
//     window right after running this.
//
// db_debug is OFF app-wide, so DB errors are normally silent. Every
// statement here is run through _run(), which checks $this->db->error()
// and throws immediately with the failing SQL — never continue past a
// failed DDL/DML step in a migration like this.
class Assessment_normalize_model extends CI_Model
{
    private $log = [];

    public function install_compat_view()
    {
        $this->log = [];

        $this->_run("
            CREATE OR REPLACE VIEW assessment_full AS
            SELECT
                a.assessment_id,
                a.assessment_id AS master_id,
                cs.class_id,
                a.schedule_id,
                a.iotype_id, a.term, a.title, a.description, a.max_score,
                a.widget_id, a.given, a.pdf_file_path, a.json_file_path,
                CASE WHEN a.status IN ('1','open','active') THEN 1 ELSE 0 END AS status,
                IFNULL(a.is_groupings, 0) AS is_groupings,
                a.due, a.created_at, a.updated_at
            FROM assessments a
            LEFT JOIN class_schedule cs ON cs.schedule_id = a.schedule_id
        ");

        $this->log[] = 'assessment_full view created over the existing assessments table.';
        return $this->log;
    }

    public function install()
    {
        $this->log = [];

        if ($this->db->table_exists('assessment_section')) {
            throw new Exception('assessment_section already exists — install() already ran. Nothing to do.');
        }
        if (!$this->_view_exists('assessment_full')) {
            throw new Exception('assessment_full view is missing — run install_compat_view() (Phase 1) first.');
        }

        $backup_table = 'assessments_backup_' . date('Ymd');

        // Pre-migration baseline: per (schedule_id, term, iotype_id) score
        // totals from the CURRENT (pre-swap) shape. Compared byte-for-byte
        // against the same aggregation over the new shape in step 8, before
        // the swap is allowed to happen.
        $before = $this->_grade_snapshot('assessments', 'schedule_id');
        $before_count = $this->_scalar("SELECT COUNT(*) c FROM assessments");
        $before_orphan_classworks = $this->_scalar("
            SELECT COUNT(*) c FROM classworks c
            WHERE NOT EXISTS (SELECT 1 FROM assessments a WHERE a.assessment_id = c.assessment_id)
        ");

        // --- Step 1: new tables, built alongside the live table ---------
        $this->_run("
            CREATE TABLE assessments_master_new (
                assessment_id     INT NOT NULL AUTO_INCREMENT,
                class_id          INT DEFAULT NULL,
                iotype_id         INT DEFAULT NULL,
                term              ENUM('midterm','tentative-final','final') DEFAULT 'midterm',
                title             VARCHAR(64) DEFAULT NULL,
                description       LONGTEXT,
                max_score         INT DEFAULT NULL,
                widget_id         INT UNSIGNED DEFAULT NULL,
                given             LONGTEXT,
                pdf_file_path     VARCHAR(255) DEFAULT NULL,
                json_file_path    VARCHAR(255) DEFAULT NULL,
                migrated_from_id  INT DEFAULT NULL,
                created_at        DATETIME DEFAULT NULL,
                updated_at        DATETIME DEFAULT NULL,
                PRIMARY KEY (assessment_id),
                KEY idx_master_class (class_id),
                KEY idx_master_migrated (migrated_from_id)
            ) ENGINE=InnoDB AUTO_INCREMENT=1000 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci
        ");

        $this->_run("
            CREATE TABLE assessment_section (
                assessment_section_id INT NOT NULL AUTO_INCREMENT,
                assessment_id         INT NOT NULL,
                schedule_id           INT DEFAULT NULL,
                due                   DATETIME DEFAULT NULL,
                status                TINYINT NOT NULL DEFAULT 0,
                is_groupings          TINYINT NOT NULL DEFAULT 0,
                created_at            DATETIME DEFAULT NULL,
                updated_at            DATETIME DEFAULT NULL,
                PRIMARY KEY (assessment_section_id),
                UNIQUE KEY uq_master_schedule (assessment_id, schedule_id),
                KEY idx_section_schedule (schedule_id),
                CONSTRAINT fk_asec_master FOREIGN KEY (assessment_id)
                    REFERENCES assessments_master_new (assessment_id) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci
        ");
        $this->log[] = 'Created assessments_master_new + assessment_section.';

        // --- Step 2: dedup mapping (real tables, not TEMPORARY — MySQL ---
        // --- can't reopen a temp table twice within the same statement) --
        $this->_run("DROP TABLE IF EXISTS zz_mig_active_map");
        $this->_run("
            CREATE TABLE zz_mig_active_map AS
            SELECT a.assessment_id AS old_id,
                   MIN(a.assessment_id) OVER (PARTITION BY
                        cs.class_id, a.title, a.term,
                        IFNULL(a.iotype_id, -1), IFNULL(a.widget_id, -1),
                        IFNULL(a.max_score, -1),
                        IFNULL(MD5(a.given), ''),
                        IFNULL(MD5(a.description), ''),
                        IFNULL(a.pdf_file_path, ''), IFNULL(a.json_file_path, '')
                   ) AS rep_id
            FROM assessments a
            JOIN class_schedule  cs  ON cs.schedule_id = a.schedule_id
            JOIN semester_master sem ON sem.trans_no = cs.semester_id AND sem.is_active = 1
        ");

        $this->_run("DROP TABLE IF EXISTS zz_mig_map");
        $this->_run("
            CREATE TABLE zz_mig_map AS
            SELECT a.assessment_id AS old_id,
                   COALESCE(t.rep_id, a.assessment_id) AS rep_id
            FROM assessments a
            LEFT JOIN zz_mig_active_map t ON t.old_id = a.assessment_id
        ");
        $this->log[] = 'Built dedup map (active semester grouped, everything else 1:1).';

        // Pre-check: a dedup group must never repeat a schedule_id (would
        // violate assessment_section's UNIQUE(assessment_id, schedule_id)).
        $collisions = $this->_scalar("
            SELECT COUNT(*) c FROM (
                SELECT m.rep_id, a.schedule_id
                FROM zz_mig_map m JOIN assessments a ON a.assessment_id = m.old_id
                GROUP BY m.rep_id, a.schedule_id HAVING COUNT(*) > 1
            ) x
        ");
        if ($collisions > 0) {
            throw new Exception("Dedup grouping would collide on $collisions (master, schedule) pair(s) — aborting before any destructive step. Widen the dedup key in zz_mig_active_map and re-run.");
        }

        // --- Step 3: masters, one per representative row -----------------
        $this->_run("
            INSERT INTO assessments_master_new
                (class_id, iotype_id, term, title, description, max_score, widget_id, given,
                 pdf_file_path, json_file_path, migrated_from_id, created_at, updated_at)
            SELECT cs.class_id, a.iotype_id, a.term, a.title, a.description, a.max_score,
                   a.widget_id, a.given, a.pdf_file_path, a.json_file_path,
                   a.assessment_id, a.created_at, a.updated_at
            FROM assessments a
            LEFT JOIN class_schedule cs ON cs.schedule_id = a.schedule_id
            WHERE a.assessment_id IN (SELECT DISTINCT rep_id FROM zz_mig_map)
        ");
        $master_count = $this->db->affected_rows();
        $this->log[] = "Inserted $master_count master row(s).";

        // --- Step 4: sections, IDs preserved from the old assessment_id --
        // (the trick: classworks/assessment_groupings/assessment_live_state
        // keep pointing at valid ids with zero data migration on their side)
        $this->_run("
            INSERT INTO assessment_section
                (assessment_section_id, assessment_id, schedule_id, due, status,
                 is_groupings, created_at, updated_at)
            SELECT a.assessment_id,
                   m.assessment_id,
                   a.schedule_id, a.due,
                   CASE WHEN a.status IN ('1','open','active') THEN 1 ELSE 0 END,
                   IFNULL(a.is_groupings, 0),
                   a.created_at, a.updated_at
            FROM assessments a
            JOIN zz_mig_map t             ON t.old_id = a.assessment_id
            JOIN assessments_master_new m ON m.migrated_from_id = t.rep_id
        ");
        $section_count = $this->db->affected_rows();
        $this->log[] = "Inserted $section_count section row(s) with preserved ids.";

        // --- Step 5: pre-swap verification (old table still fully live) --
        if ((int) $section_count !== (int) $before_count) {
            throw new Exception("Row count mismatch before swap: assessments has $before_count, assessment_section got $section_count. Aborting.");
        }
        $map_rows = $this->_scalar("SELECT COUNT(*) c FROM zz_mig_map");
        if ((int) $map_rows !== (int) $before_count) {
            throw new Exception("Dedup map has $map_rows rows, expected $before_count. Aborting.");
        }
        $orphan_sections = $this->_scalar("
            SELECT COUNT(*) c FROM assessment_section s
            WHERE NOT EXISTS (SELECT 1 FROM assessments_master_new m WHERE m.assessment_id = s.assessment_id)
        ");
        if ($orphan_sections > 0) {
            throw new Exception("$orphan_sections assessment_section row(s) point at no master. Aborting.");
        }

        $after = $this->_grade_snapshot('assessments_master_new', 'assessment_section.schedule_id', true);
        $diff = $this->_diff_grade_snapshots($before, $after);
        if (!empty($diff)) {
            throw new Exception('Pre-swap grade sanity check found ' . count($diff) . ' mismatched (schedule,term,iotype) group(s). Aborting — see log for details.', 0, null);
        }
        $this->log[] = 'Pre-swap verification passed: counts match, no orphan sections, grade totals identical.';

        // --- Step 6: the swap (the only moment of exposure) -------------
        $this->_run("ALTER TABLE assessment_groupings  DROP FOREIGN KEY assessment_groupings_ibfk_1");
        $this->_run("ALTER TABLE assessment_live_state DROP FOREIGN KEY assessment_live_state_ibfk_1");

        $this->_run("RENAME TABLE assessments TO `$backup_table`, assessments_master_new TO assessments");

        $this->_run("
            ALTER TABLE assessment_groupings
                ADD CONSTRAINT fk_ag_section FOREIGN KEY (assessment_id)
                REFERENCES assessment_section (assessment_section_id) ON DELETE CASCADE
        ");
        $this->_run("
            ALTER TABLE assessment_live_state
                ADD CONSTRAINT fk_als_section FOREIGN KEY (assessment_id)
                REFERENCES assessment_section (assessment_section_id) ON DELETE CASCADE
        ");

        $this->_run("
            CREATE OR REPLACE VIEW assessment_full AS
            SELECT s.assessment_section_id AS assessment_id,
                   m.assessment_id AS master_id,
                   m.class_id,
                   s.schedule_id,
                   m.iotype_id, m.term, m.title, m.description, m.max_score,
                   m.widget_id, m.given, m.pdf_file_path, m.json_file_path,
                   s.status, s.is_groupings, s.due, s.created_at, s.updated_at
            FROM assessment_section s
            JOIN assessments m ON m.assessment_id = s.assessment_id
        ");
        $this->log[] = "Swap complete: assessments is now the master table (backed up as $backup_table); assessment_full view repointed.";

        // --- Step 7: post-swap verification ------------------------------
        $after_orphan_classworks = $this->_scalar("
            SELECT COUNT(*) c FROM classworks c
            WHERE NOT EXISTS (SELECT 1 FROM assessment_section s WHERE s.assessment_section_id = c.assessment_id)
        ");
        if ((int) $after_orphan_classworks !== (int) $before_orphan_classworks) {
            throw new Exception("classworks orphan count changed: was $before_orphan_classworks, now $after_orphan_classworks. Data may have been lost — DO NOT proceed, restore from $backup_table.");
        }

        $after_swap = $this->_grade_snapshot($backup_table, 'schedule_id');
        $after_swap_new = $this->_grade_snapshot('assessment_full', 'schedule_id', false, true);
        $diff2 = $this->_diff_grade_snapshots($after_swap, $after_swap_new);
        if (!empty($diff2)) {
            throw new Exception('Post-swap grade sanity check found ' . count($diff2) . ' mismatched group(s) against the backup table. DO NOT proceed further — investigate before dropping anything.');
        }
        $this->log[] = 'Post-swap verification passed: classworks orphan count unchanged, grade totals identical to backup.';

        $this->_run("DROP TABLE zz_mig_active_map");
        $this->_run("DROP TABLE zz_mig_map");
        $this->log[] = "Cleanup done. $backup_table retained — keep until end of semester, then drop manually.";

        return $this->log;
    }

    // Manual recovery statements — only safe to run in the verification
    // window right after install(), before any new admin writes land on
    // the new assessments/assessment_section tables (any assessment CREATED
    // after the swap has an id > the preserved range and would be lost).
    public function rollback_sql($backup_table)
    {
        return [
            "DROP VIEW IF EXISTS assessment_full",
            "ALTER TABLE assessment_groupings  DROP FOREIGN KEY fk_ag_section",
            "ALTER TABLE assessment_live_state DROP FOREIGN KEY fk_als_section",
            "RENAME TABLE assessments TO zz_failed_master, `$backup_table` TO assessments",
            "DROP TABLE assessment_section",
            "DROP TABLE zz_failed_master",
            "ALTER TABLE assessment_groupings  ADD CONSTRAINT assessment_groupings_ibfk_1  FOREIGN KEY (assessment_id) REFERENCES assessments(assessment_id) ON DELETE CASCADE",
            "ALTER TABLE assessment_live_state ADD CONSTRAINT assessment_live_state_ibfk_1 FOREIGN KEY (assessment_id) REFERENCES assessments(assessment_id) ON DELETE CASCADE",
        ];
    }

    // Per (schedule_id, term, iotype_id) score totals — the grade-sanity
    // fingerprint compared before/after each risky step. $assessments_table
    // is either the live 'assessments' table (pre-swap / pre-migration
    // shape, schedule_id lives on it directly) or the new shape, where
    // schedule_id lives on assessment_section and must be joined in.
    private function _grade_snapshot($assessments_table, $schedule_expr, $new_shape = false, $via_view = false)
    {
        if ($via_view) {
            $sql = "
                SELECT a.schedule_id, a.term, a.iotype_id,
                       SUM(c.score) sum_score, SUM(a.max_score) sum_max, COUNT(*) n
                FROM classworks c
                JOIN `$assessments_table` a ON a.assessment_id = c.assessment_id
                GROUP BY a.schedule_id, a.term, a.iotype_id
            ";
        } elseif ($new_shape) {
            $sql = "
                SELECT s.schedule_id, m.term, m.iotype_id,
                       SUM(c.score) sum_score, SUM(m.max_score) sum_max, COUNT(*) n
                FROM classworks c
                JOIN assessment_section s ON s.assessment_section_id = c.assessment_id
                JOIN `$assessments_table` m ON m.assessment_id = s.assessment_id
                GROUP BY s.schedule_id, m.term, m.iotype_id
            ";
        } else {
            $sql = "
                SELECT a.schedule_id, a.term, a.iotype_id,
                       SUM(c.score) sum_score, SUM(a.max_score) sum_max, COUNT(*) n
                FROM classworks c
                JOIN `$assessments_table` a ON a.assessment_id = c.assessment_id
                GROUP BY a.schedule_id, a.term, a.iotype_id
            ";
        }

        $rows = $this->_query($sql)->result_array();
        $out = [];
        foreach ($rows as $r) {
            $key = ($r['schedule_id'] === null ? 'NULL' : $r['schedule_id']) . '|' . $r['term'] . '|' . $r['iotype_id'];
            $out[$key] = $r;
        }
        return $out;
    }

    private function _diff_grade_snapshots($before, $after)
    {
        $diff = [];
        $keys = array_unique(array_merge(array_keys($before), array_keys($after)));
        foreach ($keys as $k) {
            $b = $before[$k] ?? null;
            $a = $after[$k] ?? null;
            $bs = $b ? [$b['sum_score'], $b['sum_max'], $b['n']] : null;
            $as = $a ? [$a['sum_score'], $a['sum_max'], $a['n']] : null;
            if ($bs !== $as) {
                $diff[$k] = ['before' => $b, 'after' => $a];
                $this->log[] = "MISMATCH [$k]: before=" . json_encode($b) . " after=" . json_encode($a);
            }
        }
        return $diff;
    }

    private function _view_exists($name)
    {
        return $this->_scalar(
            "SELECT COUNT(*) c FROM information_schema.VIEWS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ?",
            [$name]
        ) > 0;
    }

    private function _scalar($sql, $params = [])
    {
        $row = $this->_query($sql, $params)->row_array();
        return $row ? reset($row) : 0;
    }

    private function _query($sql, $params = [])
    {
        $query = $this->db->query($sql, $params);
        if ($query === false) {
            $error = $this->db->error();
            throw new Exception('Query failed: ' . $error['message'] . "\nSQL: " . $sql);
        }
        return $query;
    }

    private function _run($sql)
    {
        $ok = $this->db->query($sql);
        if ($ok === false) {
            $error = $this->db->error();
            throw new Exception('Statement failed: ' . $error['message'] . "\nSQL: " . $sql);
        }
        $this->log[] = 'OK: ' . trim(preg_replace('/\s+/', ' ', $sql));
        return $ok;
    }
}
