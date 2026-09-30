<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * SMS announcements: recipient resolution and the delivery record.
 *
 * Recipient resolution deliberately writes NO new roster SQL.
 * Emergency_contact::get_by_section() already returns exactly what's needed —
 * one row per student enrolled in the section this semester, carrying both
 * sm.contact_no (the student) and their primary guardian's contact_no,
 * left-joined so students with no contact still come back (which is what makes
 * the "skipped" report honest). Section_officer::get_map() supplies the
 * officers-only filter. Everything else is PHP.
 *
 * Extends CI_Model, not MY_Model, because it owns install(): MY_Model's
 * constructor calls list_fields() on a table that doesn't exist yet. Same
 * reason as Section_officer, Grouping_model, Project_log_model.
 */
class Sms_model extends CI_Model
{
    // ── Schema bootstrap ────────────────────────────────────────────────────
    // Idempotent — run via AdminSmsController/install, which handles
    // confirmation + backup. Never add a DROP here.
    public function install()
    {
        $this->load->library('schema_guard');
        $this->schema_guard->reset();

        $this->schema_guard->ddl("CREATE TABLE IF NOT EXISTS `sms_announcements` (
            `announcement_id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            `section`         VARCHAR(64) NULL,
            `audience`        VARCHAR(64) NOT NULL,
            -- Stored so send_chunk() can re-resolve the recipient list from the
            -- database on every chunk. The browser never supplies phone numbers.
            `picked_ids`      TEXT NULL,
            `message`         TEXT NOT NULL,
            `encoding`        VARCHAR(10) NOT NULL,
            `segments`        INT UNSIGNED NOT NULL,
            `recipient_count` INT UNSIGNED NOT NULL,
            `sent_count`      INT UNSIGNED NOT NULL DEFAULT 0,
            `failed_count`    INT UNSIGNED NOT NULL DEFAULT 0,
            `status`          VARCHAR(16) NOT NULL DEFAULT 'sending',
            `created_by`      INT NULL,
            `created_at`      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            KEY `idx_created` (`created_at`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

        // uq_ann_msisdn is load-bearing twice over: it collapses siblings who
        // share a guardian number, and it makes a re-POSTed chunk impossible to
        // bill twice. MySQL allows repeated NULLs in a unique key, so test
        // sends (announcement_id NULL) to the same number still work.
        $this->schema_guard->ddl("CREATE TABLE IF NOT EXISTS `sms_logs` (
            `log_id`          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            `announcement_id` INT UNSIGNED NULL,
            `chunk_index`     INT UNSIGNED NOT NULL DEFAULT 0,
            `student_id`      INT NULL,
            `recipient_type`  VARCHAR(16) NOT NULL,
            `recipient_name`  VARCHAR(120) NULL,
            `msisdn`          VARCHAR(20) NOT NULL,
            `status`          VARCHAR(16) NOT NULL,
            `provider_uid`    VARCHAR(64) NULL,
            `error`           VARCHAR(255) NULL,
            `created_at`      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            UNIQUE KEY `uq_ann_msisdn` (`announcement_id`, `msisdn`),
            KEY `idx_ann_chunk` (`announcement_id`, `chunk_index`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

        return $this->schema_guard->failures();
    }

    public function table_ready()
    {
        return $this->db->table_exists('sms_announcements')
            && $this->db->table_exists('sms_logs');
    }

    // ── Recipient resolution ────────────────────────────────────────────────
    /**
     * Work out who a blast would actually reach.
     *
     * @param string $section     Section string, active semester.
     * @param array  $audience    Any of: students, guardians, officers, picked.
     *                            'officers' and 'picked' are FILTERS on top of
     *                            the student/guardian choice, not sources.
     * @param array  $picked_ids  student_master.trans_no values, for 'picked'.
     * @return array ['valid' => [...], 'skipped' => [...]]
     */
    public function resolve_recipients($section, array $audience, array $picked_ids = [])
    {
        $this->load->model('emergency_contact');
        $this->load->library('sms_message');

        $want_students  = in_array('students', $audience, true);
        $want_guardians = in_array('guardians', $audience, true);

        if (!$section || (!$want_students && !$want_guardians)) {
            return ['valid' => [], 'skipped' => []];
        }

        $rows = $this->emergency_contact->get_by_section($section);

        // Filters, applied to the student list before numbers are picked.
        if (in_array('officers', $audience, true)) {
            $this->load->model('Section_officer');
            $this->load->model('class_student');
            $officers = $this->Section_officer->get_map($section, $this->class_student->active_semester_id());
            $rows = array_filter($rows, function ($row) use ($officers) {
                return isset($officers[(int) $row['trans_no']]);
            });
        }

        if (in_array('picked', $audience, true)) {
            $picked_ids = array_map('intval', $picked_ids);
            $rows = array_filter($rows, function ($row) use ($picked_ids) {
                return in_array((int) $row['trans_no'], $picked_ids, true);
            });
        }

        $valid   = [];
        $skipped = [];

        foreach ($rows as $row) {
            $student_name = trim($row['lastname'] . ', ' . $row['firstname']);

            if ($want_students) {
                $this->sort_recipient(
                    $valid,
                    $skipped,
                    (int) $row['trans_no'],
                    'student',
                    $student_name,
                    $row['student_contact'],
                    $student_name
                );
            }

            if ($want_guardians) {
                $guardian = trim((string) $row['guardian_name']);
                $label    = $guardian !== ''
                    ? $guardian . ' (' . $row['guardian_relationship'] . ' of ' . $student_name . ')'
                    : 'Guardian of ' . $student_name;

                $this->sort_recipient(
                    $valid,
                    $skipped,
                    (int) $row['trans_no'],
                    'guardian',
                    $guardian !== '' ? $guardian : ('Guardian of ' . $student_name),
                    $row['guardian_contact'],
                    $label
                );
            }
        }

        return [
            'valid'   => Sms_message::dedupe($valid),
            'skipped' => $skipped,
        ];
    }

    private function sort_recipient(&$valid, &$skipped, $student_id, $type, $name, $raw_number, $label)
    {
        $raw = trim((string) $raw_number);

        if ($raw === '') {
            $skipped[] = ['who' => $label, 'type' => $type, 'reason' => 'no number on file'];
            return;
        }

        $msisdn = Sms_message::to_msisdn($raw);

        if ($msisdn === null) {
            $skipped[] = ['who' => $label, 'type' => $type, 'reason' => 'unrecognized number: ' . $raw];
            return;
        }

        $valid[] = [
            'student_id' => $student_id,
            'type'       => $type,
            'name'       => $name,
            'msisdn'     => $msisdn,
        ];
    }

    /** The section's students, for the hand-picked checkbox list. */
    public function students_in_section($section)
    {
        $this->load->model('emergency_contact');
        $rows = $this->emergency_contact->get_by_section($section);

        $out = [];
        foreach ($rows as $row) {
            $out[] = [
                'student_id'       => (int) $row['trans_no'],
                'name'             => trim($row['lastname'] . ', ' . $row['firstname']),
                'has_student_no'   => trim((string) $row['student_contact']) !== '',
                'has_guardian_no'  => trim((string) $row['guardian_contact']) !== '',
            ];
        }
        return $out;
    }

    // ── Delivery record ─────────────────────────────────────────────────────
    public function create_announcement(array $fields)
    {
        $this->db->insert('sms_announcements', $fields);
        return (int) $this->db->insert_id();
    }

    public function get_announcement($announcement_id)
    {
        return $this->db
            ->where('announcement_id', (int) $announcement_id)
            ->get('sms_announcements')
            ->row_array();
    }

    /** Replay guard — a double-clicked or retried chunk must not re-send. */
    public function already_sent_chunk($announcement_id, $chunk_index)
    {
        return $this->db
            ->where('announcement_id', (int) $announcement_id)
            ->where('chunk_index', (int) $chunk_index)
            ->count_all_results('sms_logs') > 0;
    }

    public function log_batch(array $rows)
    {
        if (empty($rows)) {
            return 0;
        }
        $this->db->insert_batch('sms_logs', $rows);
        return count($rows);
    }

    public function log_one(array $row)
    {
        return $this->db->insert('sms_logs', $row);
    }

    public function bump_counts($announcement_id, $sent, $failed)
    {
        $this->db->set('sent_count', 'sent_count + ' . (int) $sent, FALSE);
        $this->db->set('failed_count', 'failed_count + ' . (int) $failed, FALSE);
        $this->db->where('announcement_id', (int) $announcement_id);
        return $this->db->update('sms_announcements');
    }

    public function finish($announcement_id)
    {
        $row = $this->get_announcement($announcement_id);
        if (!$row) {
            return false;
        }

        $status = ((int) $row['sent_count'] === 0 && (int) $row['failed_count'] > 0)
            ? 'failed'
            : 'sent';

        return $this->db
            ->where('announcement_id', (int) $announcement_id)
            ->update('sms_announcements', ['status' => $status]);
    }

    public function recent($limit = 25)
    {
        if (!$this->table_ready()) {
            return [];
        }
        return $this->db
            ->order_by('created_at', 'DESC')
            ->limit((int) $limit)
            ->get('sms_announcements')
            ->result_array();
    }

    public function logs_for($announcement_id)
    {
        return $this->db
            ->where('announcement_id', (int) $announcement_id)
            ->order_by('log_id', 'ASC')
            ->get('sms_logs')
            ->result_array();
    }
}
