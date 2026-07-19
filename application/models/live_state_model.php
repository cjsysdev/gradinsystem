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
