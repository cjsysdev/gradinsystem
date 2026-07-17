<?php
defined('BASEPATH') or exit('No direct script access allowed');

// Generic, reusable "live collaborative state" storage: one polled/edited
// text-or-JSON blob per (assessment, optional group). Used today by group
// assessment submission (group_id set); a future shared/section-wide widget
// (e.g. a brainstorm board) can reuse this with group_id = null instead of
// introducing its own storage.
class Live_state_model extends CI_Model
{
    protected $table = 'assessment_live_state';

    public function get_or_create($assessment_id, $group_id = null)
    {
        $state = $this->get_state($assessment_id, $group_id);
        if ($state) {
            return $state;
        }

        $this->db->insert($this->table, [
            'assessment_id' => $assessment_id,
            'group_id'      => $group_id,
            'content'       => '',
        ]);
        return $this->get_state($assessment_id, $group_id);
    }

    public function get_state($assessment_id, $group_id = null)
    {
        $builder = $this->db->where('assessment_id', $assessment_id);
        $builder = $group_id === null ? $builder->where('group_id', null) : $builder->where('group_id', $group_id);
        return $builder->get($this->table)->row_array();
    }

    // Persists the shared draft and bumps the monotonic revision. Returns the
    // new revision so the caller can hand it back to the saving client as its
    // fresh sync marker.
    //
    // When $merge is true the incoming $content is field-merged into whatever
    // is already stored (see merge_json) instead of replacing it wholesale —
    // this is what stops one member's autosave, serialized from a screen where
    // a teammate's fields are still blank, from erasing those teammate answers.
    // Callers that own the whole blob in lockstep (the interactive-quiz group
    // sync) leave $merge false and keep straight last-write-wins.
    public function save_content($state_id, $content, $student_id, $merge = false)
    {
        if ($merge) {
            // Read-modify-write: the merge logic lives in PHP, so it can't be a
            // single atomic UPDATE. Two saves landing in the exact same instant
            // can still lose one side's edit, degrading to plain last-write —
            // acceptable at group scale (a few students), and never worse than
            // the pre-merge behaviour.
            $current = $this->db->select('content')->where('state_id', $state_id)
                ->get($this->table)->row_array();
            if ($current && $current['content'] !== null && $current['content'] !== '') {
                $content = $this->merge_json($current['content'], $content);
            }
        }

        $this->db->set('content', $content)
            ->set('last_edited_by', $student_id)
            ->set('revision', 'revision + 1', false)
            ->where('state_id', $state_id)
            ->update($this->table);

        // Ready reflects agreement with the current content, not stale content.
        $this->db->where('state_id', $state_id)->delete('assessment_live_state_ready');

        $row = $this->db->select('revision')->where('state_id', $state_id)
            ->get($this->table)->row_array();
        return $row ? (int) $row['revision'] : 0;
    }

    // Field-merges two shared-draft snapshots (JSON strings) into one, keeping
    // every non-blank value from either side so neither student's answers are
    // lost. $overlay wins on a genuine same-field conflict; $base backfills any
    // field $overlay left blank. Non-structural input (a plain-text draft or
    // malformed JSON) can't be field-merged, so it falls back to last-write,
    // preferring a non-empty overlay.
    public function merge_json($base_json, $overlay_json)
    {
        $base    = json_decode((string) $base_json, true);
        $overlay = json_decode((string) $overlay_json, true);

        if (!is_array($base) || !is_array($overlay)) {
            $overlay_str = (string) $overlay_json;
            return $overlay_str !== '' ? $overlay_str : (string) $base_json;
        }

        return json_encode($this->_merge_fill($base, $overlay));
    }

    private function _merge_fill($base, $overlay)
    {
        if (is_array($base) && is_array($overlay)) {
            $merged = $base;
            foreach ($overlay as $k => $v) {
                $merged[$k] = array_key_exists($k, $base)
                    ? $this->_merge_fill($base[$k], $v)
                    : $v;
            }
            return $merged;
        }

        // Leaf value: take the overlay unless it's blank and the base isn't.
        return $this->_is_blank($overlay) ? $base : $overlay;
    }

    // Blank = nothing a student actually entered. Numeric 0 and false are real
    // answers (e.g. a picked choice at index 0, a decision-matrix rating), so
    // only null / whitespace-only strings / empty arrays count as blank.
    private function _is_blank($v)
    {
        if ($v === null) return true;
        if (is_string($v)) return trim($v) === '';
        if (is_array($v)) return count($v) === 0;
        return false;
    }

    // ── Ready / soft consensus ───────────────────────────────────────────────

    public function set_ready($state_id, $student_id, $ready)
    {
        $existing = $this->db->where(['state_id' => $state_id, 'student_id' => $student_id])
            ->get('assessment_live_state_ready')->row_array();

        if ($existing) {
            $this->db->where('id', $existing['id'])->update('assessment_live_state_ready', ['ready' => $ready ? 1 : 0]);
        } else {
            $this->db->insert('assessment_live_state_ready', [
                'state_id'   => $state_id,
                'student_id' => $student_id,
                'ready'      => $ready ? 1 : 0,
            ]);
        }
    }

    public function get_ready_map($state_id)
    {
        $rows = $this->db->where('state_id', $state_id)->get('assessment_live_state_ready')->result_array();
        $map = [];
        foreach ($rows as $row) {
            $map[$row['student_id']] = (bool) $row['ready'];
        }
        return $map;
    }
}
