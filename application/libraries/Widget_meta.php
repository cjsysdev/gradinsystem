<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Reads the self-describing metadata out of a widget config or topic JSON so
 * the assessment form can fill itself in.
 *
 * Pure logic, no CI dependencies. Used by the two assessment save paths
 * (save_assessment() and update_class_assessment_master()) as the server-side
 * backstop for configs that never went through the modal — the modal fills the
 * same fields live via autofillMetaFromWidgetConfig() in
 * views/admin/manage_assessments.php and views/admin/class_assessments.php.
 * Keep the two in step: if the key paths below change, change them there too.
 */
class Widget_meta
{
    // A config/topic JSON authored outside the modal (the generator skills, a
    // pasted worksheet or quiz file) almost always names itself, so there's no
    // reason to make the admin retype that into the form. Pulls whichever of
    // title/description/max_score the JSON actually carries, from the key
    // paths the existing configs use — top level for most widgets and every
    // topic file, 'meta' for chapter_worksheet/case_dossier, 'story' for
    // case_study. Returns ONLY the keys it found; the caller fills blank form
    // fields with them via fill_blank_fields() and never overwrites anything
    // the admin typed.
    public function from_config(array $config)
    {
        $pick = function (array $paths) use ($config) {
            foreach ($paths as $path) {
                $value = $config;
                foreach (explode('.', $path) as $key) {
                    if (!is_array($value) || !isset($value[$key])) {
                        continue 2;
                    }
                    $value = $value[$key];
                }
                if (!is_string($value) && !is_numeric($value)) {
                    continue;
                }
                // 'intro'-style values may hold admin-authored HTML; the form
                // fields are plain text, so flatten before using them.
                $text = trim(preg_replace('/\s+/', ' ', strip_tags((string) $value)));
                if ($text !== '') {
                    return $text;
                }
            }
            return null;
        };

        $meta = [];

        $title = $pick(['title', 'meta.title', 'story.title']);
        if ($title !== null) {
            // Matches the modal's maxlength="64" on the Title field.
            $meta['title'] = mb_substr($title, 0, 64);
        }

        $description = $pick(['description', 'subtitle', 'meta.sub', 'prompt']);
        if ($description !== null) {
            $meta['description'] = $description;
        }

        $max_score = $pick(['max_score', 'total_points', 'points']);
        if ($max_score !== null && (int) $max_score > 0) {
            $meta['max_score'] = (int) $max_score;
        }

        return $meta;
    }

    // Fills only the fields the admin left blank (a 0/negative Max Score counts
    // as blank — it's never a valid score). Used with from_config().
    public function fill_blank_fields(array &$fields, array $meta)
    {
        foreach ($meta as $field => $value) {
            $current = trim((string) ($fields[$field] ?? ''));
            $is_blank = $current === '' || ($field === 'max_score' && (int) $current <= 0);
            if ($is_blank) {
                $fields[$field] = $value;
            }
        }
    }
}
