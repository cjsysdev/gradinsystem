<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Randomizer_model
 * ─────────────────────────────────────────────────────────────────────────
 * The "who has already been called" tracker behind the All Submissions
 * randomizer (admin/all_submission.php).
 *
 * The randomizer draws without replacement: every eligible student must be
 * called once before anyone repeats. That round used to live in the browser's
 * localStorage, so a refresh — or a different machine, or a cleared profile —
 * started everyone over at zero, and there was no record of who actually had
 * a turn. Both tables below exist to make the round durable and auditable.
 *
 * `assessment_id` here is an **assessment_section_id** — the same value
 * classworks.assessment_id holds (see CLAUDE.md); the column name is legacy,
 * the value it carries is a section id.
 *
 * Install once as admin via AdminSubmissionController::randomizer_install(),
 * which wraps this in Schema_guard (confirmation + pre-flight backup).
 */
class Randomizer_model extends CI_Model
{
    const PICKS  = 'randomizer_picks';
    const ROUNDS = 'randomizer_rounds';

    private $installed = null;

    /**
     * Whether the tracker tables exist yet. Every read and write is gated on
     * this, the same way classworks::has_switch_count() gates a column that
     * only exists after an installer has run: db_debug is off, so querying a
     * missing table fails silently and would take the whole page down with it.
     *
     * Memoized, because CI3's table_exists() caches the table list on its
     * first call for the whole request — so a check made before install()
     * would otherwise keep answering FALSE for the rest of that request even
     * though the tables now exist. install() clears both caches.
     */
    public function installed()
    {
        if ($this->installed === null) {
            $this->installed = $this->db->table_exists(self::PICKS)
                && $this->db->table_exists(self::ROUNDS);
        }
        return $this->installed;
    }

    /** The round currently in play. No row yet = round 1. */
    public function current_round($assessment_id)
    {
        if (!$this->installed()) {
            return 1;
        }

        $row = $this->db->select('round_no')
            ->where('assessment_id', (int) $assessment_id)
            ->get(self::ROUNDS)
            ->row_array();

        return $row ? (int) $row['round_no'] : 1;
    }

    /** student_id (trans_no) of everyone already called in this round. */
    public function picked_student_ids($assessment_id, $round)
    {
        if (!$this->installed()) {
            return [];
        }

        $rows = $this->db->select('student_id')
            ->where('assessment_id', (int) $assessment_id)
            ->where('round_no', (int) $round)
            ->get(self::PICKS)
            ->result_array();

        return array_map('intval', array_column($rows, 'student_id'));
    }

    /**
     * Picks in call order, with names, for the "who's been called" panel.
     * $round = null returns every round (the full history for the assessment).
     *
     * student_master is LEFT joined for the same reason as
     * classworks::get_all_submissions(): a pick whose student record was later
     * deleted is still a turn that was taken.
     */
    public function picks($assessment_id, $round = null)
    {
        if (!$this->installed()) {
            return [];
        }

        $this->db->select("p.pick_id, p.student_id, p.classwork_id, p.round_no, p.picked_at,
                COALESCE(s.firstname, '') AS firstname,
                COALESCE(s.lastname, CONCAT('[no student record #', p.student_id, ']')) AS lastname", false)
            ->from(self::PICKS . ' p')
            ->join('student_master s', 's.trans_no = p.student_id', 'left')
            ->where('p.assessment_id', (int) $assessment_id)
            ->order_by('p.pick_id', 'ASC');

        if ($round !== null) {
            $this->db->where('p.round_no', (int) $round);
        }

        return $this->db->get()->result_array();
    }

    /**
     * Records one turn. Returns TRUE when the row was written, FALSE when it
     * was not — which, thanks to the uq_turn unique key, is exactly the case
     * where this student had already been called in this round (a
     * double-clicked button, or a second tab drawing at the same instant).
     * The caller treats that as "draw again", not as an error.
     */
    public function record_pick($assessment_id, $round, $student_id, $classwork_id, $admin_id = null)
    {
        if (!$this->installed()) {
            return false;
        }

        $ok = $this->db->insert(self::PICKS, [
            'assessment_id' => (int) $assessment_id,
            'round_no'      => (int) $round,
            'student_id'    => (int) $student_id,
            'classwork_id'  => $classwork_id === null ? null : (int) $classwork_id,
            'picked_at'     => date('Y-m-d H:i:s'),
            'picked_by'     => $admin_id === null ? null : (int) $admin_id,
        ]);

        // db_debug is off, so a duplicate-key rejection arrives as a quiet
        // FALSE. Log it — a race is expected and harmless, anything else is
        // worth being able to see afterwards.
        if (!$ok) {
            $error = $this->db->error();
            log_message('debug', 'Randomizer_model::record_pick refused: '
                . (isset($error['message']) ? $error['message'] : 'unknown'));
        }

        return (bool) $ok;
    }

    /**
     * Starts the next round and returns its number. History is never deleted —
     * past picks keep their old round_no, so "who was called, and when"
     * survives every reset.
     *
     * The round lives in its own table rather than being derived as
     * MAX(round_no) over the picks, because a reset has to be visible before
     * the new round has any picks in it.
     */
    public function start_new_round($assessment_id, $admin_id = null)
    {
        $next = $this->current_round($assessment_id) + 1;

        if (!$this->installed()) {
            return $next;
        }

        $this->set_round($assessment_id, $next, $admin_id);
        return $next;
    }

    /** Upserts the current-round marker. */
    public function set_round($assessment_id, $round, $admin_id = null)
    {
        if (!$this->installed()) {
            return false;
        }

        $exists = $this->db->where('assessment_id', (int) $assessment_id)
            ->count_all_results(self::ROUNDS) > 0;

        $fields = [
            'round_no'   => (int) $round,
            'started_at' => date('Y-m-d H:i:s'),
            'started_by' => $admin_id === null ? null : (int) $admin_id,
        ];

        if ($exists) {
            return (bool) $this->db->where('assessment_id', (int) $assessment_id)
                ->update(self::ROUNDS, $fields);
        }

        $fields['assessment_id'] = (int) $assessment_id;
        return (bool) $this->db->insert(self::ROUNDS, $fields);
    }

    /**
     * One-time (idempotent) schema setup — run once as admin via
     * AdminSubmissionController/randomizer_install.
     *
     * CREATE TABLE IF NOT EXISTS only. No DROP, no TRUNCATE, ever: on
     * 2026-07-23 a DROP inside Grouping_model::install() silently wiped 464
     * group memberships (see application/libraries/Schema_guard.php). Each
     * statement goes through Schema_guard::ddl() so a failure is logged and
     * collected instead of vanishing behind db_debug = FALSE.
     */
    public function install()
    {
        $this->load->library('schema_guard');

        $this->schema_guard->ddl("CREATE TABLE IF NOT EXISTS `" . self::ROUNDS . "` (
            `assessment_id` INT NOT NULL,
            `round_no`      INT UNSIGNED NOT NULL DEFAULT 1,
            `started_at`    DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            `started_by`    INT NULL,
            PRIMARY KEY (`assessment_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

        // uq_turn is what actually enforces "one turn per student per round":
        // a double-clicked Randomize button or a second open tab is refused by
        // the database, not by a client-side check.
        $this->schema_guard->ddl("CREATE TABLE IF NOT EXISTS `" . self::PICKS . "` (
            `pick_id`       INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            `assessment_id` INT NOT NULL,
            `round_no`      INT UNSIGNED NOT NULL DEFAULT 1,
            `student_id`    INT NOT NULL,
            `classwork_id`  INT NULL,
            `picked_at`     DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            `picked_by`     INT NULL,
            UNIQUE KEY `uq_turn` (`assessment_id`,`round_no`,`student_id`),
            KEY `idx_assessment_round` (`assessment_id`,`round_no`),
            KEY `idx_student` (`student_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

        // CI3 caches the table list on the first table_exists() of a request
        // (DB_driver::list_tables() short-circuits on isset(), so the key must
        // be UNSET, not emptied). Without this the tables we just created stay
        // invisible for the rest of the request and every write below would be
        // skipped silently.
        unset($this->db->data_cache['table_names']);
        $this->installed = null;

        return !$this->schema_guard->failed();
    }
}
