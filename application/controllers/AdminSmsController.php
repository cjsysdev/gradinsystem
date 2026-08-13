<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * SMS announcements to a section's students, their guardians, or just its
 * class officers — sent through PhilSMS.
 *
 * Sending is a client-driven chunk loop: the browser asks for one chunk at a
 * time and the server re-resolves the recipient list from the database on every
 * call, so the browser never supplies a phone number and a tampered request
 * can't redirect a blast. There is no queue or background worker anywhere in
 * this project, and a 51-recipient send inside one request would run blind
 * against the time limit.
 *
 * Every message costs real credits and lands on a parent's phone, so the send
 * path is gated four ways: a preview that sends nothing, a balance check, a
 * per-blast cap from config, and a unique key that makes a replayed chunk a
 * no-op. See application/models/Sms_model.php.
 */
class AdminSmsController extends CI_Controller
{
    const AUDIENCES = ['students', 'guardians', 'officers', 'picked'];

    public function __construct()
    {
        parent::__construct();
        // Extends CI_Controller rather than Admin_Controller so selftest() can
        // run from the CLI (php index.php AdminSmsController selftest) without a
        // browser session — Admin_Controller's constructor redirects, which in
        // CLI just produces silence. Same trade GradeAuditController makes, and
        // the HTTP gate below is identical to the inherited one.
        if (!is_cli() && $this->session->userdata('role') !== 'admin') {
            redirect('login');
        }
    }

    // ── Screens ─────────────────────────────────────────────────────────────
    public function announcements()
    {
        $this->load->model('sms_model');
        $this->load->model('emergency_contact');
        $this->load->library('philsms_client');

        $section = trim((string) $this->input->get('section'));

        $data['sections']       = $this->emergency_contact->get_exportable_sections();
        $data['selected_section'] = $section;
        $data['students']       = $section ? $this->sms_model->students_in_section($section) : [];
        $data['is_configured']  = $this->philsms_client->is_configured();
        $data['sender_id']      = $this->philsms_client->sender_id();
        $data['tables_ready']   = $this->sms_model->table_ready();
        $data['max_recipients'] = (int) $this->config->item('philsms_max_recipients');
        $data['recent']         = $this->sms_model->recent(10);

        $data['balance'] = null;
        if ($data['is_configured']) {
            $balance = $this->philsms_client->balance();
            $data['balance'] = $balance['ok'] ? $balance['credits'] : null;
        }

        $this->load->view('admin/announcements', $data);
    }

    public function history($announcement_id = null)
    {
        $this->load->model('sms_model');

        if (!$this->sms_model->table_ready()) {
            $this->session->set_flashdata('error', 'SMS tables are not installed yet.');
            redirect('admin/announcements');
            return;
        }

        $data['recent']       = $this->sms_model->recent(50);
        $data['announcement'] = $announcement_id ? $this->sms_model->get_announcement($announcement_id) : null;
        $data['logs']         = $announcement_id ? $this->sms_model->logs_for($announcement_id) : [];

        $this->load->view('admin/sms_history', $data);
    }

    // ── Preview (sends nothing, costs nothing) ──────────────────────────────
    public function preview()
    {
        header('Content-Type: application/json');

        $request = $this->read_request();
        if ($request['error']) {
            echo json_encode(['ok' => false, 'error' => $request['error']]);
            return;
        }

        $resolved = $this->resolve($request);
        $summary  = $this->summarize($request, $resolved);

        echo json_encode(['ok' => true] + $summary);
    }

    // ── Send ────────────────────────────────────────────────────────────────
    public function start_send()
    {
        header('Content-Type: application/json');

        $this->load->model('sms_model');
        $this->load->library('philsms_client');

        if (!$this->philsms_client->is_configured()) {
            echo json_encode(['ok' => false, 'error' => 'PhilSMS API token is not configured.']);
            return;
        }

        if (!$this->sms_model->table_ready()) {
            echo json_encode(['ok' => false, 'error' => 'SMS tables are not installed yet.']);
            return;
        }

        $request = $this->read_request();
        if ($request['error']) {
            echo json_encode(['ok' => false, 'error' => $request['error']]);
            return;
        }

        $resolved = $this->resolve($request);
        $summary  = $this->summarize($request, $resolved);

        if ($summary['valid_count'] === 0) {
            echo json_encode(['ok' => false, 'error' => 'No reachable numbers in this selection.']);
            return;
        }
        if ($summary['over_cap']) {
            echo json_encode(['ok' => false, 'error' => 'This blast would reach ' . $summary['valid_count']
                . ' numbers, over the configured cap of ' . $summary['cap'] . '.']);
            return;
        }
        if ($summary['insufficient']) {
            echo json_encode(['ok' => false, 'error' => 'Not enough PhilSMS credits: need '
                . $summary['credits_needed'] . ', balance is ' . $summary['balance'] . '.']);
            return;
        }

        $announcement_id = $this->sms_model->create_announcement([
            'section'         => $request['section'],
            'audience'        => implode(',', $request['audience']),
            'picked_ids'      => implode(',', $request['picked_ids']),
            'message'         => $request['message'],
            'encoding'        => $summary['encoding'],
            'segments'        => $summary['segments'],
            'recipient_count' => $summary['valid_count'],
            'status'          => 'sending',
            'created_by'      => (int) $this->session->userdata('student_id') ?: null,
        ]);

        echo json_encode([
            'ok'              => true,
            'announcement_id' => $announcement_id,
            'chunk_count'     => (int) ceil($summary['valid_count'] / $this->chunk_size()),
            'recipient_count' => $summary['valid_count'],
        ]);
    }

    public function send_chunk()
    {
        header('Content-Type: application/json');

        // One bulk API call per chunk; the client timeout is well under this.
        set_time_limit(120);

        $this->load->model('sms_model');
        $this->load->library('philsms_client');

        $announcement_id = (int) $this->input->post('announcement_id');
        $chunk_index     = (int) $this->input->post('chunk_index');

        $announcement = $this->sms_model->get_announcement($announcement_id);
        if (!$announcement) {
            echo json_encode(['ok' => false, 'error' => 'Unknown announcement.']);
            return;
        }

        if ($this->sms_model->already_sent_chunk($announcement_id, $chunk_index)) {
            echo json_encode(['ok' => true, 'skipped' => true, 'sent' => 0, 'failed' => 0]);
            return;
        }

        // Re-resolved from the database every time — the browser posts an index,
        // never a number.
        $resolved = $this->sms_model->resolve_recipients(
            $announcement['section'],
            array_filter(explode(',', $announcement['audience'])),
            array_filter(explode(',', (string) $announcement['picked_ids']))
        );

        $chunk = array_slice($resolved['valid'], $chunk_index * $this->chunk_size(), $this->chunk_size());

        if (empty($chunk)) {
            echo json_encode(['ok' => true, 'skipped' => true, 'sent' => 0, 'failed' => 0]);
            return;
        }

        $result = $this->philsms_client->send(array_column($chunk, 'msisdn'), $announcement['message']);

        $rows = [];
        foreach ($chunk as $recipient) {
            $rows[] = [
                'announcement_id' => $announcement_id,
                'chunk_index'     => $chunk_index,
                'student_id'      => $recipient['student_id'],
                'recipient_type'  => $recipient['type'],
                'recipient_name'  => mb_substr((string) $recipient['name'], 0, 120),
                'msisdn'          => $recipient['msisdn'],
                'status'          => $result['ok'] ? 'sent' : 'failed',
                'provider_uid'    => $result['uid'],
                'error'           => $result['ok'] ? null : mb_substr($result['error'], 0, 255),
            ];
        }

        $this->sms_model->log_batch($rows);

        $sent   = $result['ok'] ? count($rows) : 0;
        $failed = $result['ok'] ? 0 : count($rows);
        $this->sms_model->bump_counts($announcement_id, $sent, $failed);

        echo json_encode([
            'ok'     => $result['ok'],
            'sent'   => $sent,
            'failed' => $failed,
            'error'  => $result['error'],
        ]);
    }

    public function finish_send()
    {
        header('Content-Type: application/json');
        $this->load->model('sms_model');
        $this->sms_model->finish((int) $this->input->post('announcement_id'));
        echo json_encode(['ok' => true]);
    }

    /** Single message to one number — no roster, no announcement row. */
    public function test_send()
    {
        header('Content-Type: application/json');
        set_time_limit(120);

        $this->load->model('sms_model');
        $this->load->library(['philsms_client', 'sms_message']);

        $number  = trim((string) $this->input->post('number'));
        $message = trim((string) $this->input->post('message'));
        $msisdn  = Sms_message::to_msisdn($number);

        if ($msisdn === null) {
            echo json_encode(['ok' => false, 'error' => 'That does not look like a Philippine mobile number.']);
            return;
        }
        if ($message === '') {
            echo json_encode(['ok' => false, 'error' => 'Write the message first.']);
            return;
        }

        $result = $this->philsms_client->send($msisdn, $message);

        if ($this->sms_model->table_ready()) {
            $this->sms_model->log_one([
                'announcement_id' => null,
                'chunk_index'     => 0,
                'student_id'      => null,
                'recipient_type'  => 'test',
                'recipient_name'  => 'Test send',
                'msisdn'          => $msisdn,
                'status'          => $result['ok'] ? 'sent' : 'failed',
                'provider_uid'    => $result['uid'],
                'error'           => $result['ok'] ? null : mb_substr($result['error'], 0, 255),
            ]);
        }

        echo json_encode([
            'ok'    => $result['ok'],
            'error' => $result['error'],
            'sent_to' => $msisdn,
        ]);
    }

    // ── Setup ───────────────────────────────────────────────────────────────
    public function install()
    {
        $this->load->library('schema_guard');
        $this->load->model('sms_model');
        $tables = ['sms_announcements', 'sms_logs'];

        if (!$this->schema_guard->confirmed('SMS announcements tables setup', 'admin/sms_install', $tables)) {
            return;
        }

        $backup   = $this->schema_guard->backup($tables, 'sms');
        $failures = $this->sms_model->install();

        if (!empty($failures)) {
            $this->session->set_flashdata('error',
                'SMS schema finished with ' . count($failures) . ' failed statement(s) — see application/logs/. '
                . 'Backup: ' . ($backup ?: 'NOT WRITTEN'));
        } else {
            $this->session->set_flashdata('success',
                'SMS tables ready.' . ($backup ? ' Backup written to ' . basename($backup) . '.' : ''));
        }

        redirect('admin/announcements');
    }

    /**
     * Policy self-test — number normalisation, segment counting, dedupe.
     * Runnable from the CLI (php index.php AdminSmsController selftest) and
     * spends zero credits. Modelled on GradeAuditController::selftest.
     */
    public function selftest()
    {
        $this->load->library('sms_message');

        $passed = 0;
        $failed = [];

        $check = function ($label, $actual, $expected) use (&$passed, &$failed) {
            if ($actual === $expected) {
                $passed++;
            } else {
                $failed[] = sprintf('%s: expected %s, got %s',
                    $label, var_export($expected, true), var_export($actual, true));
            }
        };

        // to_msisdn — the formats actually present in student_emergency_contacts.
        $check('09171234567',    Sms_message::to_msisdn('09171234567'),    '639171234567');
        $check('0955 336 6401',  Sms_message::to_msisdn('0955 336 6401'),  '639553366401');
        $check('0917-123-4567',  Sms_message::to_msisdn('0917-123-4567'),  '639171234567');
        $check('+639171234567',  Sms_message::to_msisdn('+639171234567'),  '639171234567');
        $check('639171234567',   Sms_message::to_msisdn('639171234567'),   '639171234567');
        $check('9171234567',     Sms_message::to_msisdn('9171234567'),     '639171234567');
        $check('00639171234567', Sms_message::to_msisdn('00639171234567'), '639171234567');
        $check('(0917) 123 4567', Sms_message::to_msisdn('(0917) 123 4567'), '639171234567');
        $check('empty',          Sms_message::to_msisdn(''),               null);
        $check('junk',           Sms_message::to_msisdn('n/a'),            null);
        $check('landline',       Sms_message::to_msisdn('088-123-4567'),   null);
        $check('too short',      Sms_message::to_msisdn('0917123'),        null);
        $check('too long',       Sms_message::to_msisdn('091712345678'),   null);
        $check('non-9 mobile',   Sms_message::to_msisdn('08171234567'),    null);

        // to_national — what gets stored.
        $check('national from +63', Sms_message::to_national('+639171234567'), '09171234567');
        $check('national spaced',   Sms_message::to_national('0955 336 6401'), '09553366401');
        $check('national junk',     Sms_message::to_national('n/a'),           null);

        // segments — the credit estimate.
        $plain_160 = str_repeat('a', 160);
        $plain_161 = str_repeat('a', 161);
        $check('empty segments',  Sms_message::segments('')['segments'],          0);
        $check('short segments',  Sms_message::segments('Hello')['segments'],     1);
        $check('160 segments',    Sms_message::segments($plain_160)['segments'],  1);
        $check('161 segments',    Sms_message::segments($plain_161)['segments'],  2);
        $check('plain encoding',  Sms_message::segments('Hello')['encoding'],     'plain');
        $check('emoji encoding',  Sms_message::segments('Hi 🙂')['encoding'],     'unicode');
        $check('emoji segments',  Sms_message::segments('Hi 🙂')['segments'],     1);
        $check('unicode 71',      Sms_message::segments('🙂' . str_repeat('a', 70))['segments'], 2);
        // '{' is a GSM extension char — two septets, so 80 of them overflow 160.
        $check('extension chars', Sms_message::segments(str_repeat('{', 81))['segments'], 2);

        // dedupe — siblings sharing a guardian number.
        $deduped = Sms_message::dedupe([
            ['msisdn' => '639171234567', 'name' => 'Maria'],
            ['msisdn' => '639171234567', 'name' => 'Maria again'],
            ['msisdn' => '639209876543', 'name' => 'Jose'],
        ]);
        $check('dedupe count', count($deduped), 2);
        $check('dedupe keeps first', $deduped[0]['name'], 'Maria');

        $out  = "SMS POLICY SELF-TEST\n";
        $out .= "========================================\n";
        $out .= 'passed: ' . $passed . "\n";
        $out .= 'failed: ' . count($failed) . "\n\n";

        if (empty($failed)) {
            $out .= "ALL POLICY CHECKS PASSED\n";
        } else {
            foreach ($failed as $failure) {
                $out .= '  [FAIL] ' . $failure . "\n";
            }
        }

        if (is_cli()) {
            echo $out;
            return;
        }
        $this->output->set_content_type('text/plain')->set_output($out);
    }

    // ── Internals ───────────────────────────────────────────────────────────
    private function chunk_size()
    {
        $this->config->load('philsms', FALSE, TRUE);
        return max(1, (int) $this->config->item('philsms_chunk_size') ?: 50);
    }

    /** Pull and validate the compose form's fields. */
    private function read_request()
    {
        $section  = trim((string) $this->input->post('section'));
        $message  = trim((string) $this->input->post('message'));
        $audience = $this->input->post('audience');
        $picked   = $this->input->post('picked_ids');

        $audience = is_array($audience) ? array_values(array_intersect($audience, self::AUDIENCES)) : [];
        $picked   = is_array($picked) ? array_values(array_filter(array_map('intval', $picked))) : [];

        $error = '';
        if ($section === '') {
            $error = 'Pick a section first.';
        } elseif (!in_array('students', $audience, true) && !in_array('guardians', $audience, true)) {
            $error = 'Choose whether to message the students, their guardians, or both.';
        } elseif ($message === '') {
            $error = 'Write the message first.';
        } elseif (in_array('picked', $audience, true) && empty($picked)) {
            $error = 'No students are ticked in the hand-picked list.';
        }

        return [
            'section'    => $section,
            'message'    => $message,
            'audience'   => $audience,
            'picked_ids' => $picked,
            'error'      => $error,
        ];
    }

    private function resolve(array $request)
    {
        $this->load->model('sms_model');
        return $this->sms_model->resolve_recipients(
            $request['section'],
            $request['audience'],
            $request['picked_ids']
        );
    }

    /** Recipient counts, credit cost, and every reason a send could be refused. */
    private function summarize(array $request, array $resolved)
    {
        $this->load->library(['sms_message', 'philsms_client']);
        $this->config->load('philsms', FALSE, TRUE);

        $segments    = Sms_message::segments($request['message']);
        $valid_count = count($resolved['valid']);
        $cap         = max(1, (int) $this->config->item('philsms_max_recipients'));
        $credits     = $segments['segments'] * $valid_count;

        $balance = null;
        if ($this->philsms_client->is_configured()) {
            $result  = $this->philsms_client->balance();
            $balance = $result['ok'] ? $result['credits'] : null;
        }

        return [
            'valid_count'    => $valid_count,
            'skipped'        => $resolved['skipped'],
            'skipped_count'  => count($resolved['skipped']),
            'encoding'       => $segments['encoding'],
            'chars'          => $segments['chars'],
            'segments'       => $segments['segments'],
            'credits_needed' => $credits,
            'balance'        => $balance,
            'cap'            => $cap,
            'over_cap'       => $valid_count > $cap,
            // Only block on a balance we actually managed to read.
            'insufficient'   => ($balance !== null && $credits > $balance),
            'sample'         => array_slice(array_column($resolved['valid'], 'name'), 0, 8),
        ];
    }
}
