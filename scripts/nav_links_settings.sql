-- Admin-toggleable nav-bar links (admin/settings). '1' = shown, '0' = hidden.
-- A missing row counts as shown (Global_setting::nav_links()), so this seed
-- is optional; it just makes the rows visible in the table.
INSERT IGNORE INTO global_settings (setting_key, setting_value) VALUES
  -- student nav bar
  ('nav_attendance',  '1'),
  ('nav_classwork',   '1'),
  ('nav_my_records',  '1'),
  ('nav_project_log', '1'),
  ('nav_materials',   '1'),
  -- admin nav bar (Dashboard and Settings are always shown)
  ('admin_nav_assessments',         '1'),
  ('admin_nav_class_assessments',   '1'),
  ('admin_nav_classwork',           '1'),
  ('admin_nav_section_monitoring',  '1'),
  ('admin_nav_emergency_contacts',  '1'),
  ('admin_nav_student_violations',  '1'),
  ('admin_nav_students_by_section', '1'),
  ('admin_nav_semesters',           '1'),
  ('admin_nav_student_requests',    '1'),
  ('admin_nav_password_resets',     '1'),
  ('admin_nav_polls',               '1'),
  ('admin_nav_groupings',           '1'),
  ('admin_nav_project_logs',        '1'),
  ('admin_nav_worksheet_generator', '1'),
  ('admin_nav_sms_announcements',   '1'),
  ('admin_nav_class_materials',     '1'),
  ('admin_nav_uncleared_students',  '1');
