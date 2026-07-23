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

    public function save_content($state_id, $content, $student_id)
    {
        $this->db->where('state_id', $state_id)->update($this->table, [
            'content'        => $content,
            'last_edited_by' => $student_id,
        ]);
        // Ready reflects agreement with the current content, not stale content.
        // affected_rows is 0 when the row didn't actually change (identical
        // content re-saved by the same editor), so a no-op echo save neither
        // wipes ready flags nor bumps updated_at.
        if ($this->db->affected_rows() > 0) {
            $this->db->where('state_id', $state_id)->delete('assessment_live_state_ready');
        }
    }

    // ── Field-level merge (LWW-Map) ──────────────────────────────────────────
    //
    // Instead of replacing the whole shared blob on every save, a client sends
    // only the leaves it changed as a flat { "answers.0": "text", ... } patch.
    // We apply each leaf onto the stored structure and stamp it with a fresh
    // `rev`, so two members editing DIFFERENT parts never clobber each other,
    // and same-part edits resolve last-writer-wins by rev. Content is still
    // materialized back into the same nested JSON `content` blob every read
    // site (page render, submit fan-out) already expects — nothing downstream
    // changes shape.

    // Sets a dotted leaf path (e.g. "rows.2.Observation") on a nested array,
    // creating intermediate containers as needed. Numeric segments become int
    // keys so a run of them re-encodes as a JSON array, not an object.
    private function set_by_path(&$arr, $path, $value)
    {
        $segments = explode('.', $path);
        $ref = &$arr;
        foreach ($segments as $i => $seg) {
            $key = ctype_digit($seg) ? (int) $seg : $seg;
            if ($i === count($segments) - 1) {
                $ref[$key] = $value;
            } else {
                if (!isset($ref[$key]) || !is_array($ref[$key])) {
                    $ref[$key] = [];
                }
                $ref = &$ref[$key];
            }
        }
        unset($ref);
    }

    // Merge a sparse patch of changed leaves into the group's shared blob.
    // Serialized per group by a MySQL advisory lock (the DB is MyISAM in most
    // installs — no row locks/transactions), so two members' concurrent merges
    // to the same row can't lose an update in the read-modify-write window;
    // different groups (different state_id) never contend.
    public function merge_patch($state_id, array $patch, $student_id)
    {
        $lock = 'live_state_' . $state_id;
        $this->db->query('SELECT GET_LOCK(?, 3)', [$lock]);
        try {
            $state   = $this->db->where('state_id', $state_id)->get($this->table)->row_array();
            $content = json_decode($state['content'] ?? '', true);
            if (!is_array($content)) {
                $content = [];
            }
            $versions = json_decode($state['field_versions'] ?? '', true);
            if (!is_array($versions)) {
                $versions = [];
            }

            $rev     = (int) ($state['rev'] ?? 0) + 1;
            $changed = false;
            foreach ($patch as $path => $value) {
                $this->set_by_path($content, $path, $value);
                $versions[$path] = $rev;
                $changed = true;
            }

            if ($changed) {
                $this->db->where('state_id', $state_id)->update($this->table, [
                    'content'        => json_encode($content),
                    'field_versions' => json_encode($versions),
                    'rev'            => $rev,
                    'last_edited_by' => $student_id,
                ]);
                // Ready reflects agreement with the current content; a real
                // edit invalidates prior "ready" signals.
                $this->db->where('state_id', $state_id)->delete('assessment_live_state_ready');
            }
        } finally {
            $this->db->query('SELECT RELEASE_LOCK(?)', [$lock]);
        }

        return $this->db->where('state_id', $state_id)->get($this->table)->row_array();
    }

    // ── Presence ("who's editing which field") ───────────────────────────────

    public function set_presence($state_id, $student_id, $field_path)
    {
        $existing = $this->db->where(['state_id' => $state_id, 'student_id' => $student_id])
            ->get('assessment_live_state_presence')->row_array();

        if ($existing) {
            $this->db->where('id', $existing['id'])->update('assessment_live_state_presence', [
                'field_path' => $field_path,
                // Force updated_at to advance even when the path is unchanged,
                // so a steady heartbeat on the same field keeps presence fresh.
                'updated_at' => date('Y-m-d H:i:s'),
            ]);
        } else {
            $this->db->insert('assessment_live_state_presence', [
                'state_id'   => $state_id,
                'student_id' => $student_id,
                'field_path' => $field_path,
            ]);
        }

        // Opportunistic sweep so the table can't grow unbounded — cheap, keyed
        // on the indexed updated_at, no cron needed.
        $this->db->where('updated_at <', date('Y-m-d H:i:s', time() - 900))
            ->delete('assessment_live_state_presence');
    }

    // student_id => field_path for members who heartbeated within the window.
    public function get_presence_map($state_id, $fresh_seconds = 6)
    {
        $rows = $this->db->where('state_id', $state_id)
            ->where('updated_at >=', date('Y-m-d H:i:s', time() - $fresh_seconds))
            ->get('assessment_live_state_presence')->result_array();
        $map = [];
        foreach ($rows as $row) {
            $map[$row['student_id']] = $row['field_path'];
        }
        return $map;
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
