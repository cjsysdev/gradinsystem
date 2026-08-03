<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Topic-file operations shared by the admin assessment screens and the
 * discussion manager: finding topic JSON under assets/json/, telling the two
 * topic widgets apart, counting their gradable items, and saving a pasted
 * topic file.
 *
 * These used to be private helpers on AdminController, called from five
 * different actions across two unrelated screens (assessments and
 * discussions). Structural validation of a topic file is NOT here — that
 * belongs to Iq_topic_model::validate_structure(), which this calls.
 */
class Iq_topic_helper
{
    private $CI;

    public function __construct()
    {
        $this->CI =& get_instance();
    }

    // Every topic JSON, both the legacy flat ones and the per-class folders —
    // see save_pasted_topic_json(). Topic slugs stay globally unique and
    // unaware of the folder, so callers just need every *.json under either.
    public function glob_topics()
    {
        $json_path = FCPATH . 'assets/json/';
        $root      = glob($json_path . '*.json') ?: [];
        $nested    = glob($json_path . '*/*.json') ?: [];
        return array_merge($root, $nested);
    }

    // Class code a topic file belongs to, derived from its parent folder
    // under assets/json/ (see save_pasted_topic_json(), which writes new
    // topics to assets/json/{CLASS_CODE}/). Returns '' for legacy/unfiled
    // files sitting directly in assets/json/, meaning "available to every class".
    public function class_code_from_path($file)
    {
        $json_path = rtrim(FCPATH . 'assets/json', '/\\');
        $parent    = rtrim(dirname($file), '/\\');
        return ($parent === $json_path) ? '' : basename($parent);
    }

    // Which renderer a topic-file belongs to. Any section carrying `chunks` is
    // the microlearning format (discussions/_interactive_micro_template.php);
    // everything else is the plain lesson+quiz format. Callers have already
    // excluded the legacy sections[].questions format before reaching here.
    public function format(array $topic_meta)
    {
        foreach (($topic_meta['sections'] ?? []) as $s) {
            if (!empty($s['chunks'])) {
                return 'micro';
            }
        }
        return 'discussion';
    }

    // Number of gradable questions in a discussion-format topic — one per
    // section that actually has a quiz (sections can be lesson-only). Shared
    // by manage_assessments() (for the JS auto-fill) and save_assessment()
    // (server-side max_score derivation, the authoritative source).
    public function count_questions(array $topic_meta)
    {
        $count = 0;
        foreach (($topic_meta['sections'] ?? []) as $s) {
            if (!empty($s['quiz'])) {
                $count++;
            }
        }
        return $count;
    }

    // Number of gradable items in a microlearning topic — 1 per chunk
    // micro-check plus 1 per section checkpoint, matching the scoring in
    // _interactive_micro_template.php exactly (objectives/recap screens are
    // not graded). Same role as count_questions() for the other format:
    // shared by the modal's Max Score auto-fill and the authoritative
    // server-side derivation in save_assessment().
    public function count_micro_items(array $topic_meta)
    {
        $count = 0;
        foreach (($topic_meta['sections'] ?? []) as $s) {
            $count += count($s['chunks'] ?? []);
            if (!empty($s['quiz'])) {
                $count++;
            }
        }
        return $count;
    }

    // Destination class for a pasted topic file (see resolve_paste()).
    // Both save_assessment() (which posts schedule_id in "section" apply_mode,
    // or class_id in "class"/"draft" mode) and update_class_assessment_master()
    // (which only ever posts class_id, no apply_mode/schedule_id) route through
    // here so the class-folder logic isn't duplicated a third time.
    public function class_id_from_post($post)
    {
        $mode = $post['apply_mode'] ?? 'section';
        if ($mode !== 'class' && $mode !== 'draft' && !empty($post['schedule_id'])) {
            $class_id = $this->CI->db->select('class_id')->where('schedule_id', $post['schedule_id'])
                ->get('class_schedule')->row('class_id');
            if ($class_id) {
                return (int) $class_id;
            }
        }
        return (int) ($post['class_id'] ?? $post['return_class_id'] ?? 0);
    }

    // Runs the assessment modal's "Paste new JSON" flow for a topic widget.
    // Returns null when the admin instead chose "Reuse existing topic" (the
    // caller's normal glob_topics() lookup handles that case unchanged),
    // false on validation/collision failure (flashdata already set by
    // save_pasted_topic_json()), or the newly-saved slug on success — callers
    // fold that slug into $master_fields['given'] before their existing
    // topic-lookup loop runs, so max_score derivation doesn't need to change.
    public function resolve_paste($post, $widget)
    {
        if (($post['iq_source'] ?? 'existing') !== 'new') {
            return null;
        }
        return $this->save_pasted_topic_json(
            $this->class_id_from_post($post),
            trim($post['iq_new_slug'] ?? ''),
            $post['iq_new_json'] ?? '',
            $widget['widget_key'] === 'iq_micro' ? 'micro' : 'discussion',
            false
        );
    }

    // Validates a pasted topic JSON template and writes it to
    // assets/json/{CLASS_CODE}/{slug}.json (falls back to assets/json/{slug}.json
    // if the class can't be resolved). Returns the slug on success, or false
    // (with a flashdata error already set) on failure.
    // $format: 'discussion' (default, manage_discussions' only caller today) or
    // 'micro' (the assessment-modal "Paste new JSON" flow — see resolve_paste()).
    // $allow_overwrite: manage_discussions has always silently overwritten an
    // existing slug; the assessment-modal flow passes false because a topic
    // file is shared by every assessment pointing at it, so clobbering one
    // could change an already-graded quiz out from under another section.
    public function save_pasted_topic_json($class_id, $slug, $json_text, $format = 'discussion', $allow_overwrite = true)
    {
        $slug = preg_replace('/[^a-z0-9_]/', '', strtolower($slug));
        if (!preg_match('/^[a-z0-9_]{1,100}$/', $slug)) {
            $this->CI->session->set_flashdata('error', 'Slug is required and may only contain lowercase letters, digits, and underscores.');
            return false;
        }

        $data = json_decode(trim($json_text), true);
        if (json_last_error() !== JSON_ERROR_NONE || !is_array($data)) {
            $this->CI->session->set_flashdata('error', 'Invalid JSON: ' . ($data === null ? json_last_error_msg() : 'must decode to an object.'));
            return false;
        }

        $this->CI->load->model('Iq_topic_model');
        $validation_error = $this->CI->Iq_topic_model->validate_structure($data, $format);
        if ($validation_error) {
            $this->CI->session->set_flashdata('error', $validation_error);
            return false;
        }

        $json_path = FCPATH . 'assets/json/';
        if (!is_writable($json_path)) {
            $this->CI->session->set_flashdata('error', 'assets/json/ is not writable. Contact your administrator.');
            return false;
        }

        if (!$allow_overwrite) {
            foreach ($this->glob_topics() as $existing) {
                if (basename($existing, '.json') === $slug) {
                    $where = $this->class_code_from_path($existing);
                    $this->CI->session->set_flashdata('error', 'Topic slug "' . $slug . '" already exists'
                        . ($where ? " (class {$where})" : ' (unfiled)')
                        . ' — pick another slug, or select that topic from the dropdown.');
                    return false;
                }
            }
        }

        $dest_dir = $json_path;
        if ($class_id) {
            $class = $this->CI->db->select('class_code')->where('class_id', $class_id)->get('classes')->row_array();
            if (!empty($class['class_code'])) {
                $folder = preg_replace('/[^A-Za-z0-9_-]/', '_', $class['class_code']);
                $candidate = $json_path . $folder . '/';
                if (is_dir($candidate) || @mkdir($candidate, 0775, true)) {
                    $dest_dir = $candidate;
                }
            }
        }

        $data['topic'] = $slug;
        $pretty = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

        $dest = $dest_dir . $slug . '.json';
        $overwrite = file_exists($dest);
        if (file_put_contents($dest, $pretty) === false) {
            $this->CI->session->set_flashdata('error', 'Failed to save JSON file. Check directory permissions.');
            return false;
        }

        $this->CI->session->set_flashdata('success', $overwrite
            ? "Topic file \"{$slug}.json\" overwritten."
            : "Topic file \"{$slug}.json\" created.");
        return $slug;
    }
}
