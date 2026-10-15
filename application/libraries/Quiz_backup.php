<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * The Timed/Secure Quiz local backup file — the ONE place its format is read.
 *
 * secure_quiz_view.php downloads an encrypted copy of the student's answers
 * just before it posts, so a network failure mid-submit doesn't lose the
 * attempt. SecureQuizController::backup() turns that file back into a
 * submission while the instructor has the section's backup window open
 * (assessment_section.backup_upload_until, toggled from admin/all_submission).
 *
 * The encryption is CryptoJS's passphrase mode (OpenSSL-compatible
 * "Salted__" + EVP_BytesToKey/MD5 + AES-256-CBC). The passphrase ships in the
 * quiz page's JS, so it is obfuscation, not authentication: anyone can forge
 * a file. That is why nothing in the file is trusted except the question text
 * (to find the item) and the student's answer — is_correct, correct_answer and
 * any score are ignored and the attempt is re-graded by
 * Widgets_model::grade_quiz(), same as a live submit.
 *
 * Two payload shapes:
 *   legacy: [ {question, user_answer, correct_answer, is_correct, code}, ... ]
 *   v2:     {v: 2, assessment_id, student_id, switch_count, results: [ ...same... ]}
 */
class Quiz_backup
{
    // Must match the passphrase secure_quiz_view.php encrypts with (it echoes
    // this constant) and the one assets/decrypt/dcrpyt.html defaults to.
    const PASSPHRASE = 'teacherSecret';

    const MAX_BYTES = 1048576;

    /**
     * assessment_section.backup_upload_until for one section, or NULL when it
     * is unset or the column doesn't exist yet (WidgetsController/install not
     * re-run) — both read as "closed".
     */
    public function window_until($section_id)
    {
        $CI =& get_instance();
        if (!$CI->db->field_exists('backup_upload_until', 'assessment_section')) {
            return null;
        }
        $until = $CI->db->select('backup_upload_until')
            ->where('assessment_section_id', (int) $section_id)
            ->get('assessment_section')
            ->row('backup_upload_until');
        return $until ?: null;
    }

    /** TRUE while the instructor's backup-upload window is open (server clock). */
    public function window_open($section_id)
    {
        $until = $this->window_until($section_id);
        return $until !== null && strtotime($until) > time();
    }

    /** CryptoJS.AES.encrypt(text, passphrase).toString() in, plaintext out (NULL on failure). */
    public function decrypt($text, $passphrase = self::PASSPHRASE)
    {
        if (!function_exists('openssl_decrypt')) {
            log_message('error', 'Quiz_backup::decrypt(): the openssl PHP extension is not enabled.');
            return null;
        }

        $raw = base64_decode(preg_replace('/\s+/', '', (string) $text), true);
        if ($raw === false || strlen($raw) < 32 || substr($raw, 0, 8) !== 'Salted__') {
            return null;
        }

        $salt = substr($raw, 8, 8);
        $ciphertext = substr($raw, 16);

        // EVP_BytesToKey with MD5, one iteration: 32-byte key + 16-byte IV.
        $derived = '';
        $block = '';
        while (strlen($derived) < 48) {
            $block = md5($block . $passphrase . $salt, true);
            $derived .= $block;
        }

        $plain = openssl_decrypt($ciphertext, 'aes-256-cbc', substr($derived, 0, 32), OPENSSL_RAW_DATA, substr($derived, 32, 16));
        return $plain === false ? null : $plain;
    }

    /**
     * Decrypted JSON in, normalized payload out:
     * ['version' => 1|2, 'student_id' => ?int, 'assessment_id' => ?int,
     *  'switch_count' => ?int, 'items' => [ {question, user_answer}, ... ]]
     * or NULL when it isn't a quiz backup at all.
     */
    public function parse($plaintext)
    {
        $data = json_decode((string) $plaintext, true);
        if (!is_array($data)) {
            return null;
        }

        if ($data === array_values($data)) {
            $version = 1;
            $results = $data;
            $meta = [];
        } elseif (isset($data['results']) && is_array($data['results'])) {
            $version = (int) ($data['v'] ?? 2);
            $results = $data['results'];
            $meta = $data;
        } else {
            return null;
        }

        $items = [];
        foreach ($results as $r) {
            if (!is_array($r) || !isset($r['question']) || !is_scalar($r['question'])) {
                return null;
            }
            $items[] = [
                'question'    => (string) $r['question'],
                'user_answer' => isset($r['user_answer']) && is_scalar($r['user_answer']) ? (string) $r['user_answer'] : 'No answer',
            ];
        }

        return [
            'version'       => $version,
            'student_id'    => isset($meta['student_id']) && is_numeric($meta['student_id']) ? (int) $meta['student_id'] : null,
            'assessment_id' => isset($meta['assessment_id']) && is_numeric($meta['assessment_id']) ? (int) $meta['assessment_id'] : null,
            'switch_count'  => isset($meta['switch_count']) && is_numeric($meta['switch_count']) ? (int) $meta['switch_count'] : null,
            'items'         => $items,
        ];
    }

    /**
     * Matches the file's items to real questions and returns what
     * Widgets_model::grade_quiz() takes: [$questions, $answers] (index-aligned).
     *
     * $pool is the student's own drawn set (the session's shuffled_questions_*)
     * when it survived, otherwise the full bank. Items are keyed on the TRIMMED
     * QUESTION TEXT — the config carries no ids and the drawn order is random
     * per student (same reason Widgets_model::quiz_item_stats() keys on it).
     *
     * Returns a string error message instead when the file doesn't fit.
     */
    public function rebuild(array $items, array $pool, $max_items)
    {
        if (empty($items)) {
            return 'The backup file has no answers in it.';
        }
        if ($max_items > 0 && count($items) > $max_items) {
            return 'The backup file has more questions than this quiz allows.';
        }

        $by_text = [];
        foreach ($pool as $q) {
            if (is_array($q) && isset($q['question'])) {
                $key = trim((string) $q['question']);
                if (!isset($by_text[$key])) $by_text[$key] = $q;
            }
        }

        $questions = [];
        $answers = [];
        $seen = [];
        foreach ($items as $item) {
            $key = trim($item['question']);
            if (isset($seen[$key])) {
                return 'The backup file lists the same question twice.';
            }
            if (!isset($by_text[$key])) {
                return 'The backup file does not match this quiz\'s questions. Make sure you uploaded the file for this quiz.';
            }
            $seen[$key] = true;
            $answers[count($questions)] = $item['user_answer'];
            $questions[] = $by_text[$key];
        }

        return [$questions, $answers];
    }
}
