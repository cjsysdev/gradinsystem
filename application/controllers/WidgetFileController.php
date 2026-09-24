<?php
defined('BASEPATH') or exit('No direct script access allowed');

// Backing endpoints for the File Upload widget (widgets/file_upload.php).
//
// The widget itself never posts a file through submit_classwork(): each file
// is uploaded here over AJAX the moment it's picked, and only its metadata
// ({id, name, size, ext, path, ...}) goes into the widget's JSON state. That
// JSON rides the normal hidden `code` field (solo) or the shared live-state
// blob (group), so AssessmentController::submit_classwork() and
// GroupWorkController::submit_group() need zero changes — same contract as
// every other widget.
//
// Storage: uploads/widget_files/{assessment_id}/{owner}/{id}.upload plus an
// {id}.json sidecar holding the original name. {owner} is "s{student_id}"
// for a solo submission or "g{group_id}" for a group one, so a whole group
// shares one folder and every member can download what a teammate uploaded.
// Files are stored under a neutral extension in a deny-all directory and are
// only ever served back through download(), never linked directly — a
// student's .php/.html upload can't execute or render on this origin.
class WidgetFileController extends CI_Controller
{
    // Never accepted, whatever the assessment's allowed_extensions says —
    // server-side scripts and Windows executables have no place in a
    // classwork submission.
    const BLOCKED_EXTENSIONS = [
        'php', 'php3', 'php4', 'php5', 'php7', 'php8', 'phtml', 'pht', 'phps', 'phar',
        'cgi', 'pl', 'asp', 'aspx', 'jsp', 'htaccess', 'htpasswd',
        'exe', 'msi', 'bat', 'cmd', 'com', 'scr', 'vbs', 'dll',
    ];

    // Hard ceiling regardless of config — matches submit_classwork()'s 50MB.
    const MAX_SIZE_MB = 50;

    // Served inline (for the widget's Preview button); everything else is
    // forced to download. Text-like files are always served as text/plain so
    // an uploaded .html/.svg/.js is shown as source, never rendered.
    const INLINE_IMAGE = ['png' => 'image/png', 'jpg' => 'image/jpeg', 'jpeg' => 'image/jpeg', 'gif' => 'image/gif', 'webp' => 'image/webp'];
    const INLINE_TEXT  = [
        'c', 'h', 'cpp', 'hpp', 'cc', 'cs', 'java', 'py', 'js', 'ts', 'html', 'htm', 'css',
        'sql', 'txt', 'md', 'csv', 'json', 'xml', 'yml', 'yaml', 'ini', 'log', 'sh', 'svg',
    ];

    public function __construct()
    {
        parent::__construct();
        if (!isset($_SESSION['online'])) {
            redirect('login');
        }
        $this->load->model(['Widgets_model', 'Grouping_model']);
    }

    private function _root()
    {
        return FCPATH . 'uploads/widget_files/';
    }

    // AJAX (multipart POST, field "file"). Returns the file's metadata for the
    // widget to add to its JSON state.
    public function upload($assessment_id)
    {
        $student_id = $this->session->student_id;
        if (empty($student_id)) {
            $this->_json(['ok' => false, 'error' => 'Only students can upload submissions.'], 403);
            return;
        }

        $assessment = $this->assessments->as_array()->get((int) $assessment_id);
        if (!$assessment) {
            $this->_json(['ok' => false, 'error' => 'Assessment not found.'], 404);
            return;
        }
        $widget = !empty($assessment['widget_id']) ? $this->Widgets_model->get($assessment['widget_id']) : null;
        if (!$widget || $widget['widget_key'] !== 'file_upload') {
            $this->_json(['ok' => false, 'error' => 'This assessment does not accept file uploads.'], 400);
            return;
        }

        // Same authoritative "graded is final" rule as submit_classwork() /
        // submit_group() — a group is graded atomically, so the student's own
        // row is a reliable proxy either way.
        $row = $this->classworks->where(['student_id' => $student_id, 'assessment_id' => $assessment_id])->get();
        if ($row && $row->score !== null) {
            $this->_json(['ok' => false, 'error' => 'This work has already been graded and can no longer be edited.'], 403);
            return;
        }

        $owner = $this->_owner_for($assessment, $student_id);
        if ($owner === null) {
            $this->_json(['ok' => false, 'error' => 'Join a group before uploading files.'], 403);
            return;
        }

        $file = $_FILES['file'] ?? null;
        if (!$file || ($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK || !is_uploaded_file($file['tmp_name'])) {
            $err = $file['error'] ?? UPLOAD_ERR_NO_FILE;
            $msg = in_array($err, [UPLOAD_ERR_INI_SIZE, UPLOAD_ERR_FORM_SIZE], true)
                ? 'The file is larger than the server allows.'
                : 'No file was received — please try again.';
            $this->_json(['ok' => false, 'error' => $msg], 400);
            return;
        }

        $config = json_decode($assessment['given'] ?? '', true) ?: [];
        $name = $this->_clean_name($file['name']);
        $ext  = strtolower(pathinfo($name, PATHINFO_EXTENSION));

        if (in_array($ext, self::BLOCKED_EXTENSIONS, true)) {
            $this->_json(['ok' => false, 'error' => '.' . $ext . ' files are not allowed.'], 400);
            return;
        }
        $allowed = $this->_allowed_extensions($config);
        if ($allowed && !in_array($ext, $allowed, true)) {
            $this->_json(['ok' => false, 'error' => 'Only these file types are accepted: .' . implode(', .', $allowed)], 400);
            return;
        }

        $max_mb = $this->_max_size_mb($config);
        if ($file['size'] > $max_mb * 1024 * 1024) {
            $this->_json(['ok' => false, 'error' => 'File is too large — the limit is ' . $max_mb . ' MB.'], 400);
            return;
        }

        $dir = $this->_root() . (int) $assessment_id . '/' . $owner . '/';
        if (!$this->_ensure_dir($dir)) {
            $this->_json(['ok' => false, 'error' => 'Could not save the file on the server.'], 500);
            return;
        }

        $id = bin2hex(random_bytes(8));
        if (!move_uploaded_file($file['tmp_name'], $dir . $id . '.upload')) {
            $this->_json(['ok' => false, 'error' => 'Could not save the file on the server.'], 500);
            return;
        }

        date_default_timezone_set('Asia/Manila');
        $meta = [
            'id'          => $id,
            'name'        => $name,
            'size'        => (int) $file['size'],
            'ext'         => $ext,
            'path'        => (int) $assessment_id . '/' . $owner . '/' . $id,
            'uploaded_at' => date('Y-m-d H:i:s'),
            'uploaded_by' => trim(($this->session->firstname ?? '') . ' ' . ($this->session->lastname ?? '')),
        ];
        file_put_contents($dir . $id . '.json', json_encode($meta + ['student_id' => $student_id]));

        $this->_json(['ok' => true, 'file' => $meta]);
    }

    // GET download/{assessment_id}/{owner}/{id}[?inline=1]. Admins can fetch
    // anything; a student only their own ("s{id}") or their group's
    // ("g{group_id}") folder.
    public function download($assessment_id, $owner, $id)
    {
        if (!ctype_digit((string) $assessment_id) || !preg_match('/^[sg][A-Za-z0-9_-]+$/', $owner) || !preg_match('/^[a-f0-9]{16}$/', $id)) {
            show_404();
        }
        if (!$this->_can_access($owner)) {
            show_error('You do not have access to this file.', 403);
        }

        $base = $this->_root() . $assessment_id . '/' . $owner . '/' . $id;
        if (!is_file($base . '.upload')) {
            show_404();
        }
        $meta = json_decode((string) @file_get_contents($base . '.json'), true) ?: [];
        $name = $this->_clean_name($meta['name'] ?? ($id . '.bin'));
        $ext  = strtolower(pathinfo($name, PATHINFO_EXTENSION));

        $inline = (bool) $this->input->get('inline');
        if ($inline && isset(self::INLINE_IMAGE[$ext])) {
            $type = self::INLINE_IMAGE[$ext];
            $disposition = 'inline';
        } elseif ($inline && $ext === 'pdf') {
            $type = 'application/pdf';
            $disposition = 'inline';
        } elseif ($inline && in_array($ext, self::INLINE_TEXT, true)) {
            $type = 'text/plain; charset=utf-8';
            $disposition = 'inline';
        } else {
            $type = 'application/octet-stream';
            $disposition = 'attachment';
        }

        // Headers set directly (not via CI's Output) so readfile() can
        // stream a large file without buffering it all into memory.
        header('Content-Type: ' . $type);
        header('Content-Length: ' . filesize($base . '.upload'));
        header('Content-Disposition: ' . $disposition . '; filename="' . str_replace('"', '', $name) . '"; filename*=UTF-8\'\'' . rawurlencode($name));
        header('X-Content-Type-Options: nosniff');
        // Chrome refuses to run its PDF viewer in a sandboxed document, so
        // the sandbox is skipped for PDFs only (they're never text/html).
        if ($type !== 'application/pdf') {
            header("Content-Security-Policy: default-src 'none'; img-src 'self'; style-src 'unsafe-inline'; sandbox");
        }
        header('Cache-Control: private, max-age=0');
        readfile($base . '.upload');
        exit;
    }

    // "s{student_id}" for a solo submission, "g{group_id}" when the
    // assessment is a grouping assessment — null if it is one but the student
    // isn't in a group yet (mirrors GroupWorkController::_resolve()).
    private function _owner_for($assessment, $student_id)
    {
        if (!empty($assessment['is_groupings'])) {
            $set_id = $this->Grouping_model->get_set_for_assessment($assessment['assessment_id']);
            if ($set_id) {
                $group = $this->Grouping_model->get_student_group($student_id, $set_id);
                return $group ? 'g' . (int) $group['group_id'] : null;
            }
        }
        return 's' . preg_replace('/[^A-Za-z0-9_-]/', '', (string) $student_id);
    }

    private function _can_access($owner)
    {
        if ($this->session->userdata('role') === 'admin') {
            return true;
        }
        $student_id = (string) $this->session->student_id;
        if ($student_id === '') {
            return false;
        }
        if ($owner[0] === 's') {
            return substr($owner, 1) === preg_replace('/[^A-Za-z0-9_-]/', '', $student_id);
        }
        $group_id = substr($owner, 1);
        return ctype_digit($group_id) && $this->db
            ->where(['group_id' => (int) $group_id, 'student_id' => $student_id])
            ->count_all_results('group_members') > 0;
    }

    // Normalised, lower-case, dot-less list from config; [] = anything not
    // in BLOCKED_EXTENSIONS. Kept in sync with the widget view's own copy.
    private function _allowed_extensions($config)
    {
        $list = $config['allowed_extensions'] ?? [];
        if (!is_array($list)) return [];
        $out = [];
        foreach ($list as $e) {
            $e = strtolower(ltrim(trim((string) $e), '.'));
            if ($e !== '') $out[] = $e;
        }
        return array_values(array_unique($out));
    }

    private function _max_size_mb($config)
    {
        $mb = (float) ($config['max_size_mb'] ?? 10);
        if ($mb <= 0) $mb = 10;
        return min($mb, self::MAX_SIZE_MB);
    }

    // Display name only — the stored file never uses it.
    private function _clean_name($name)
    {
        $name = basename(str_replace('\\', '/', (string) $name));
        $name = preg_replace('/[\x00-\x1F\x7F"<>:|?*]/u', '', $name);
        $name = trim($name, " .");
        if ($name === '') $name = 'file';
        return mb_substr($name, 0, 150);
    }

    // Creates $dir, and on first use drops a deny-all .htaccess + blank
    // index.html at the widget_files root so nothing under it is reachable by
    // direct URL, only through download().
    private function _ensure_dir($dir)
    {
        $root = $this->_root();
        if (!is_dir($root) && !@mkdir($root, 0755, true)) {
            return false;
        }
        if (!is_file($root . '.htaccess')) {
            @file_put_contents($root . '.htaccess',
                "# Files here are served only via WidgetFileController::download().\n"
                . "<IfModule mod_authz_core.c>\n    Require all denied\n</IfModule>\n"
                . "<IfModule !mod_authz_core.c>\n    Deny from all\n</IfModule>\n");
        }
        if (!is_file($root . 'index.html')) {
            @file_put_contents($root . 'index.html', '');
        }
        return is_dir($dir) || @mkdir($dir, 0755, true);
    }

    private function _json($data, $status = 200)
    {
        $this->output
            ->set_status_header($status)
            ->set_content_type('application/json')
            ->set_output(json_encode($data));
    }
}
