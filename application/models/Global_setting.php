<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Key/value reads and writes on `global_settings`.
 *
 * Also owns the lists of nav-bar links the admin can switch on and off from
 * admin/settings — one list for the student nav bar (views/nav_bar.php) and
 * one for the admin nav bar (views/admin/nav_bar.php). Each link is a row
 * whose value is '1' (shown) or '0' (hidden). A missing row counts as shown,
 * so a link never disappears just because its row hasn't been seeded
 * (scripts/nav_links_settings.sql).
 *
 * Dashboard and Settings are deliberately NOT in the admin list: they are
 * always shown, so hiding links can never lock the admin out of this page.
 */
class Global_setting extends CI_Model
{
    /** setting_key => label. Order = nav order. */
    const NAV_LINKS = [
        'student' => [
            'nav_attendance'  => 'Attendance',
            'nav_classwork'   => 'Classwork',
            'nav_my_records'  => 'My Records',
            'nav_project_log' => 'Project Log',
            'nav_materials'   => 'Materials',
        ],
        'admin' => [
            'admin_nav_assessments'         => 'Assessments',
            'admin_nav_class_assessments'   => 'Class Assessments',
            'admin_nav_classwork'           => 'Classwork',
            'admin_nav_section_monitoring'  => 'Section Monitoring',
            'admin_nav_emergency_contacts'  => 'Emergency Contacts',
            'admin_nav_student_violations'  => 'Student Violations',
            'admin_nav_students_by_section' => 'Students by Section',
            'admin_nav_semesters'           => 'Semesters',
            'admin_nav_student_requests'    => 'Student Requests',
            'admin_nav_password_resets'     => 'Password Resets',
            'admin_nav_polls'               => 'Polls',
            'admin_nav_groupings'           => 'Groupings',
            'admin_nav_project_logs'        => 'Project Logs',
            'admin_nav_worksheet_generator' => 'Worksheet Generator',
            'admin_nav_sms_announcements'   => 'SMS Announcements',
            'admin_nav_class_materials'     => 'Class Materials',
            'admin_nav_uncleared_students'  => 'Uncleared Students',
        ],
    ];

    public function get($key, $default = null)
    {
        $row = $this->db->get_where('global_settings', ['setting_key' => $key])->row();
        return $row ? $row->setting_value : $default;
    }

    public function set($key, $value)
    {
        $exists = $this->db->where('setting_key', $key)->count_all_results('global_settings') > 0;
        if ($exists) {
            $this->db->where('setting_key', $key)->update('global_settings', ['setting_value' => (string) $value]);
        } else {
            $this->db->insert('global_settings', ['setting_key' => $key, 'setting_value' => (string) $value]);
        }
    }

    /** One nav bar's links with their state: key => ['label' => ..., 'enabled' => bool]. */
    public function nav_links($group)
    {
        $links = self::NAV_LINKS[$group] ?? [];
        if (!$links) {
            return [];
        }

        $values = [];
        $rows = $this->db->where_in('setting_key', array_keys($links))
                         ->get('global_settings')->result_array();
        foreach ($rows as $r) {
            $values[$r['setting_key']] = $r['setting_value'];
        }

        $out = [];
        foreach ($links as $key => $label) {
            $out[$key] = [
                'label'   => $label,
                'enabled' => ($values[$key] ?? '1') === '1',
            ];
        }
        return $out;
    }

    /** key => bool, for a nav-bar view to test with $nav['nav_x']. */
    public function nav_flags($group)
    {
        return array_map(function ($l) { return $l['enabled']; }, $this->nav_links($group));
    }

    /**
     * Exam mode: replaces the student nav bar with a single Exam button and
     * hides submitted-work/answer review (classwork.php, student_submission.php,
     * quiz_result.php). '1' = on; a missing row = off. Read per request through
     * exam_term_on() (exam_mode_helper), not the session, so a toggle reaches
     * students who are already logged in.
     */
    const EXAM_TERM_KEY = 'exam_term';

    public function exam_term()
    {
        return $this->get(self::EXAM_TERM_KEY, '0') === '1';
    }

    public function set_exam_term($on)
    {
        $this->set(self::EXAM_TERM_KEY, $on ? '1' : '0');
    }

    const PAST_DUE_TYPES_KEY = 'past_due_classwork_types';

    /**
     * Which io_types keep showing on the student Classwork page once they are
     * past due. Stored as a CSV of iotype_ids under PAST_DUE_TYPES_KEY. A
     * missing row means every type is shown (the behaviour before this
     * setting existed); an empty string means none are.
     *
     * @return int[]|null  iotype_ids, or NULL for "all types"
     */
    public function past_due_types()
    {
        $value = $this->get(self::PAST_DUE_TYPES_KEY);
        if ($value === null) {
            return null;
        }
        return array_values(array_filter(array_map('intval', explode(',', $value))));
    }

    /** io_type rows with an 'enabled' flag, for the settings form. */
    public function past_due_type_options()
    {
        $enabled = $this->past_due_types();
        $types   = $this->db->order_by('iotype_id')->get('io_type')->result_array();
        foreach ($types as &$t) {
            $t['enabled'] = $enabled === null || in_array((int) $t['iotype_id'], $enabled, true);
        }
        unset($t);
        return $types;
    }

    /** Save the shown io_types. Ids that aren't real io_types are dropped. */
    public function save_past_due_types(array $iotype_ids)
    {
        $valid = array_map('intval', array_column(
            $this->db->select('iotype_id')->get('io_type')->result_array(), 'iotype_id'
        ));
        $ids = array_values(array_intersect($valid, array_map('intval', $iotype_ids)));
        $this->set(self::PAST_DUE_TYPES_KEY, implode(',', $ids));
    }

    /**
     * Save one nav bar: every link in $enabled_keys is shown, every other link
     * in that group is hidden. Keys outside the group are ignored.
     */
    public function save_nav_links($group, array $enabled_keys)
    {
        foreach (array_keys(self::NAV_LINKS[$group] ?? []) as $key) {
            $this->set($key, in_array($key, $enabled_keys, true) ? '1' : '0');
        }
    }
}
