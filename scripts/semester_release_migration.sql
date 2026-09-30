-- Lets an admin control which past semesters students can view.
-- Active semester is always visible to its students regardless of this flag.
ALTER TABLE semester_master
  ADD COLUMN grades_released TINYINT(1) NOT NULL DEFAULT 0;
