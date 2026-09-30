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
