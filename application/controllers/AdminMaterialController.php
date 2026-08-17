<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Class Materials: upload course/demo files, assign them to sections, and let
 * the enrolled students list and download them.
 *
 * Fills the gap that used to be filled by hand — dumping a PDF into uploads/
 * root and pasting its path into `discussions.link`. Files land in
 * assets/materials/{CLASS_CODE}/; metadata and section assignment live in
 * class_materials / class_material_sections (see Class_material).
 *
 * Extends Admin_Controller (application/core/MY_Controller.php), whose
 * constructor is the admin gate — no per-method role check needed.
 *
 * SECURITY NOTE. assets/ is served straight off disk by Apache, so an uploaded
 * .php would execute and an uploaded .html/.svg would run script in our own
 * origin against a logged-in student. Three rails, all required:
 *   1. UPLOAD_WHITELIST below — never widen this to '*'.
 *   2. NEUTRALIZED_EXTS — .php IS accepted (demo source is the point of the
 *      module) but never stored under a name the webserver would hand to an
 *      interpreter; see _stored_name().
 *   3. The .htaccess written by Class_material::ensure_base_dir().
 * Rails 2 and 3 overlap on purpose: rail 3 is one AllowOverride None, one
 * nginx migration, or one hand-edited vhost away from doing nothing, and rail 2
 * survives all three because it is a property of the filename itself.
 * Section assignment is a LISTING filter, not an access control: the bytes are
 * reachable by anyone who knows the URL.
 */
class AdminMaterialController extends Admin_Controller
{
    /**
     * Extensions accepted on upload. Doubles as CI's allowed_types string.
     *
     * 'php' is here because PHP demo source is course material in a course
     * about PHP — but it is accepted, NOT trusted: see NEUTRALIZED_EXTS.
     *
     * Deliberately absent:
     *   phtml/phar/cgi/pl/sh/exe/bat — executable in or from the docroot, and
     *                                  none of them is teaching material.
     *   html/htm/svg                 — can carry <script>; served same-origin
     *                                  that is stored XSS against a student's
     *                                  session.
     * Nine of these (md, webp, sql, c, cpp, h, py, java, ts) required new
     * entries in application/config/mimes.php — CI3 rejects any extension
     * missing from that array regardless of allowed_types. 'php' is already in
     * stock mimes.php, but its type list needed widening for what finfo
     * actually reports on a real demo file (see the note there).
     */
    const UPLOAD_WHITELIST = [
        'pdf', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx', 'txt', 'csv', 'md', 'rtf', 'odt',
        'png', 'jpg', 'jpeg', 'gif', 'webp',
        'zip', 'rar', '7z',
        'sql', 'json', 'php', 'c', 'cpp', 'h', 'py', 'java', 'css', 'js', 'ts',
        'mp4', 'webm', 'mp3',
    ];

    /**
     * Accepted, but stored with '.txt' welded onto the end of the filename so
     * no webserver anywhere will ever hand the bytes to an interpreter. The
     * upload keeps its real extension in `original_name` and in the download
     * link's `download=` attribute, so the student still saves `demo.php`.
     *
     * This is the rail that does not depend on configuration. Do not drop it in
     * favour of the .htaccess alone.
     */
    const NEUTRALIZED_EXTS = ['php'];

    /**
     * Names that must never reach the filesystem, checked against every
     * dot-segment of the filename EXCEPT the last — so `demo.php.pdf` (a PHP
     * file wearing a PDF hat) is rejected while plain `demo.php`, whose final
     * segment the whitelist above already vetted, is allowed through.
     */
    const FORBIDDEN_PARTS = [
        'php', 'php3', 'php4', 'php5', 'php6', 'php7', 'php8', 'phtml', 'phps', 'phar',
        'cgi', 'pl', 'sh', 'bash', 'htaccess', 'htpasswd', 'hta',
        'exe', 'dll', 'bat', 'cmd', 'com', 'jar', 'msi', 'scr',
        'html', 'htm', 'xhtml', 'shtml', 'svg',
    ];

    /** Kilobytes, matching ProjectLogController::_handle_upload(). */
    const MAX_SIZE_KB = 51200;

    public function __construct()
    {
        parent::__construct();
        $this->load->model('Class_material');
    }

    // ── Screens ─────────────────────────────────────────────────────────────

    public function manage_materials()
    {
        $data = [
            'table_ready' => $this->Class_material->table_ready(),
            'schedules'   => $this->class_schedule->get_all_active(),
            'materials'   => [],
            'max_size_mb' => (int) (self::MAX_SIZE_KB / 1024),
            'whitelist'   => self::UPLOAD_WHITELIST,
            // Shown in the upload card: our 50 MB cap is meaningless if php.ini
            // is still at the stock 2 MB, and that mismatch is invisible
            // otherwise (see the post_max_size tripwire in upload_material()).
            'php_upload_max' => ini_get('upload_max_filesize'),
            'php_post_max'   => ini_get('post_max_size'),
        ];

        if ($data['table_ready']) {
            $materials = $this->Class_material->get_all_for_admin();

            // Surface the orphan case: a row whose file has gone missing from
            // disk (hand-deleted, or a failed unlink on a previous release).
            foreach ($materials as &$m) {
                $m['rel_path'] = $this->Class_material->relative_path($m);
                $m['on_disk']  = file_exists(FCPATH . $m['rel_path']);
                $m['section_ids'] = $this->Class_material->get_section_ids($m['material_id']);
            }
            unset($m);

            $data['materials'] = $materials;
        }

        $this->load->view('admin/manage_materials', $data);
    }

    // ── Writes ──────────────────────────────────────────────────────────────

    public function upload_material()
    {
        if ($this->input->method() !== 'post') {
            redirect('admin/materials');
            return;
        }

        // Check this BEFORE reading any POST field. When the request body
        // exceeds post_max_size, PHP discards $_POST *and* $_FILES entirely and
        // sets no error flag anywhere — so the naive path reports "choose a
        // file" for a file that was chosen, and the real limit stays invisible.
        if (empty($_POST) && empty($_FILES) && (int) ($_SERVER['CONTENT_LENGTH'] ?? 0) > 0) {
            $this->_fail('The upload exceeded the server limit (post_max_size = '
                . ini_get('post_max_size') . ', upload_max_filesize = '
                . ini_get('upload_max_filesize') . '). Raise both in php.ini and restart Apache.');
            return;
        }

        if (!$this->Class_material->table_ready()) {
            $this->_fail('Class material tables are missing — run the installer first.');
            return;
        }

        $title       = trim((string) $this->input->post('title'));
        $description = trim((string) $this->input->post('description'));
        $category    = trim((string) $this->input->post('category'));
        $schedule_ids = $this->input->post('schedule_ids');
        $schedule_ids = is_array($schedule_ids) ? $schedule_ids : [];

        if ($title === '') {
            $this->_fail('A title is required.');
            return;
        }

        if (!$schedule_ids) {
            $this->_fail('Assign the material to at least one section — otherwise no student can see it.');
            return;
        }

        if (empty($_FILES['material_file']['name'])) {
            $this->_fail('Choose a file to upload.');
            return;
        }

        if (!isset($_FILES['material_file']['error']) || $_FILES['material_file']['error'] !== UPLOAD_ERR_OK) {
            $this->_fail('Upload failed: ' . $this->_upload_error_text($_FILES['material_file']['error'] ?? -1));
            return;
        }

        $original = $_FILES['material_file']['name'];
        $ext      = strtolower(pathinfo($original, PATHINFO_EXTENSION));

        if (!in_array($ext, self::UPLOAD_WHITELIST, true)) {
            $this->_fail('That file type (.' . htmlspecialchars($ext) . ') is not allowed. Allowed: '
                . implode(', ', self::UPLOAD_WHITELIST));
            return;
        }

        // Every dot-segment except the trailing extension, which the whitelist
        // above already vetted — blocks demo.php.pdf without blocking demo.php.
        $inner_parts = explode('.', strtolower($original));
        array_pop($inner_parts);

        foreach ($inner_parts as $part) {
            if (in_array($part, self::FORBIDDEN_PARTS, true)) {
                $this->_fail('That filename contains a disallowed extension (.' . htmlspecialchars($part) . ').');
                return;
            }
        }

        if ($_FILES['material_file']['size'] > self::MAX_SIZE_KB * 1024) {
            $this->_fail('File is too large. Maximum is ' . (int) (self::MAX_SIZE_KB / 1024) . ' MB.');
            return;
        }

        // The on-disk folder follows the class of the FIRST assigned section;
        // the file is shared by every assigned section, not copied per section.
        $class = $this->_class_for_schedule((int) $schedule_ids[0]);

        $dir = $this->Class_material->dir_for_class_code($class['class_code']);
        if (!$dir) {
            $this->_fail('Upload directory is missing or not writable: assets/materials/. '
                . 'Check permissions on the server.');
            return;
        }

        $stored = $this->_stored_name($title, $ext);

        $config = [
            'upload_path'   => $dir,
            'allowed_types' => implode('|', self::UPLOAD_WHITELIST),
            'max_size'      => self::MAX_SIZE_KB,
            // file_name carries the extension, so CI keeps it as-is instead of
            // re-deriving one from the original name.
            'file_name'     => $stored,
            'overwrite'     => false,
        ];

        $this->upload->initialize($config);

        if (!$this->upload->do_upload('material_file')) {
            $this->_fail($this->upload->display_errors('', ''));
            return;
        }

        $uploaded = $this->upload->data();

        $material_id = $this->Class_material->create([
            'title'         => $title,
            'description'   => $description !== '' ? $description : null,
            'category'      => $category !== '' ? $category : null,
            'class_id'      => $class['class_id'],
            'class_code'    => $class['class_code'],
            // Whatever CI actually wrote — it appends _1 etc. on collision.
            'stored_name'   => $uploaded['file_name'],
            'original_name' => $original,
            'extension'     => $ext,
            // NOT $uploaded['file_size'] — CI rounds that to KILOBYTES
            // (system/libraries/Upload.php:497). We store bytes.
            'file_size'     => (int) $_FILES['material_file']['size'],
            'is_active'     => 1,
            'created_by'    => $this->session->userdata('student_id'),
        ], $schedule_ids);

        if (!$material_id) {
            @unlink($uploaded['full_path']);
            $this->_fail('Could not save the material record — the file was not kept.');
            return;
        }

        $this->session->set_flashdata('success',
            'Uploaded "' . html_escape($title) . '" to ' . count($schedule_ids) . ' section(s).');
        redirect('admin/materials');
    }

    /** Metadata + section reassignment. Never touches the file on disk. */
    public function update_material()
    {
        if ($this->input->method() !== 'post') {
            redirect('admin/materials');
            return;
        }

        $material_id = (int) $this->input->post('material_id');
        $row = $material_id ? $this->Class_material->get_by_id($material_id) : null;

        if (!$row) {
            $this->_fail('That material no longer exists.');
            return;
        }

        $title = trim((string) $this->input->post('title'));
        if ($title === '') {
            $this->_fail('A title is required.');
            return;
        }

        $schedule_ids = $this->input->post('schedule_ids');
        $schedule_ids = is_array($schedule_ids) ? $schedule_ids : [];

        if (!$schedule_ids) {
            $this->_fail('Assign the material to at least one section — otherwise no student can see it.');
            return;
        }

        $description = trim((string) $this->input->post('description'));
        $category    = trim((string) $this->input->post('category'));

        $this->Class_material->update_meta($material_id, [
            'title'       => $title,
            'description' => $description !== '' ? $description : null,
            'category'    => $category !== '' ? $category : null,
            'is_active'   => $this->input->post('is_active') ? 1 : 0,
        ]);

        $this->Class_material->set_sections($material_id, $schedule_ids);

        $this->session->set_flashdata('success', 'Updated "' . html_escape($title) . '".');
        redirect('admin/materials');
    }

    public function delete_material($material_id = null)
    {
        if ($this->input->method() !== 'post') {
            redirect('admin/materials');
            return;
        }

        $material_id = (int) $material_id;
        $row = $material_id ? $this->Class_material->get_by_id($material_id) : null;

        if (!$row) {
            $this->_fail('That material no longer exists.');
            return;
        }

        $path = $this->Class_material->absolute_path($row);

        // Row first: an undeletable file is a tidy-up chore, but a row pointing
        // at a deleted file is a broken link on every student's screen.
        $this->Class_material->delete($material_id);

        $note = '';
        if (file_exists($path) && !@unlink($path)) {
            $note = ' The file could not be removed from disk — delete it by hand: '
                . html_escape($this->Class_material->relative_path($row));
            log_message('error', 'Class materials: failed to unlink ' . $path);
        }

        $this->session->set_flashdata($note ? 'error' : 'success',
            'Deleted "' . html_escape($row['title']) . '".' . $note);
        redirect('admin/materials');
    }

    // ── Setup ───────────────────────────────────────────────────────────────
    public function install()
    {
        $this->load->library('schema_guard');
        $tables = ['class_materials', 'class_material_sections'];

        if (!$this->schema_guard->confirmed('Class materials tables setup', 'admin/materials_install', $tables)) {
            return;
        }

        $backup   = $this->schema_guard->backup($tables, 'class_materials');
        $failures = $this->Class_material->install();

        if (!empty($failures)) {
            $this->session->set_flashdata('error',
                'Class material schema finished with ' . count($failures) . ' failed statement(s) — see application/logs/. '
                . 'Backup: ' . ($backup ?: 'NOT WRITTEN'));
        } elseif (!$this->Class_material->ensure_base_dir()) {
            $this->session->set_flashdata('error',
                'Tables are ready, but assets/materials/ is missing or not writable. '
                . 'Fix the directory permissions before uploading.');
        } else {
            $this->session->set_flashdata('success',
                'Class material tables ready.' . ($backup ? ' Backup written to ' . basename($backup) . '.' : ''));
        }

        redirect('admin/materials');
    }

    // ── Helpers ─────────────────────────────────────────────────────────────

    private function _fail($message)
    {
        $this->session->set_flashdata('error', $message);
        redirect('admin/materials');
    }

    /**
     * class_id + class_code for one schedule, used to pick the on-disk folder.
     * Falls back to a placeholder so an orphaned schedule row still uploads.
     */
    private function _class_for_schedule($schedule_id)
    {
        $row = $this->db->select('cs.class_id, cl.class_code')
                        ->from('class_schedule cs')
                        ->join('classes cl', 'cl.class_id = cs.class_id', 'left')
                        ->where('cs.schedule_id', (int) $schedule_id)
                        ->get()->row_array();

        return [
            'class_id'   => $row && $row['class_id'] ? (int) $row['class_id'] : null,
            'class_code' => $row && $row['class_code'] ? $row['class_code'] : '_unsorted',
        ];
    }

    /**
     * Collision-safe filename: a readable slug of the title plus a timestamp
     * and six random hex chars, so re-uploading the same title never clobbers
     * the previous file and the name is not guessable from the title alone.
     *
     * For NEUTRALIZED_EXTS the real extension is kept as an inner segment and
     * '.txt' becomes the trailing one, so the file on disk is inert. CI's
     * Upload::_prep_filename() then rewrites the inner dot to an underscore
     * (`demo.php.txt` → `demo_php.txt`) — harmless, and the caller stores
     * whatever CI actually wrote rather than this string, so the two can't
     * drift.
     */
    private function _stored_name($title, $ext)
    {
        $slug = preg_replace('/[^a-z0-9]+/', '_', strtolower($title));
        $slug = trim($slug, '_');
        $slug = substr($slug, 0, 60);

        if ($slug === '') {
            $slug = 'material';
        }

        $tail = in_array($ext, self::NEUTRALIZED_EXTS, true) ? $ext . '.txt' : $ext;

        return $slug . '-' . time() . '-' . substr(md5(uniqid('', true)), 0, 6) . '.' . $tail;
    }

    private function _upload_error_text($code)
    {
        switch ($code) {
            case UPLOAD_ERR_INI_SIZE:
            case UPLOAD_ERR_FORM_SIZE:
                return 'the file exceeds the server upload limit (upload_max_filesize / post_max_size in php.ini).';
            case UPLOAD_ERR_PARTIAL:
                return 'the file was only partially uploaded — try again.';
            case UPLOAD_ERR_NO_FILE:
                return 'no file was received.';
            case UPLOAD_ERR_NO_TMP_DIR:
                return 'the server has no temp directory configured.';
            case UPLOAD_ERR_CANT_WRITE:
                return 'the server could not write the file to disk.';
            case UPLOAD_ERR_EXTENSION:
                return 'a PHP extension blocked the upload.';
            default:
                return 'unknown error (code ' . (int) $code . ').';
        }
    }
}
