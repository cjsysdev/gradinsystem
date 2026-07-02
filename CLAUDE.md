# CLAUDE.md

## Project Overview
`gradinsystem` — CodeIgniter 3 LMS built by CJ for CMC (Carmen Municipal
College) computer science courses. Handles attendance, grading, classwork
submission, and interactive JSON-driven discussions/quizzes.

## Stack
- PHP (CodeIgniter 3, MY_Model active-record style models)
- MySQL/MariaDB (MyISAM tables, no foreign key enforcement in most cases)
- Bootstrap 4.5.2, vanilla JS/jQuery, no build step (assets served directly)

## Key Data Model
- `classes` → `class_schedule` (LEC/LAB) → `class_student` (enrollment)
- `assessments` (io_type: 1=Activity, 2=Performance Task, 3=Major Exam,
  4=Quiz; term: midterm/tentative-final/final) → `classworks` (one row per
  student submission: `file_upload` OR `code` text, plus `score`)
- `discussions` (type: `static` link, or `interactive` → JSON file in
  `assets/json/{slug}.json`, rendered via `InteractiveQuizController`)

## Active Initiative: Paperless Midterm Integration
Full plan: **`docs/paperless-midterm-plan.md`** — read this before working on
anything related to classwork widgets, the IS Innovations course, or new
interactive assessment types.

Quick summary: building 6 reusable classwork widgets (Worksheet Form, Card
Sort, Brainstorm Board, Diagram/Flow Builder, Decision Matrix, Calculator) so
every hands-on activity in a course can be done natively in the LMS instead
of paper/photo-upload. Widgets are looked up via a `widgets` registry table
(`application/models/widgets_model.php`) — `assessments.widget_id` points at
a row there (`widget_key`, `name`, `input_view`) rather than a plain
`widget_type` string, so adding a new widget later is "add a row + drop a
view file," not editing a controller's if/else chain. `assessments.given`
(added by `Widgets_model::install()`, not pre-existing) stores the
instructor's widget config JSON; `classworks.code` (already text-capable)
stores the student's submission JSON. Build order and full JSON schemas for
each widget are in the plan doc — do not re-derive them from scratch, follow
what's specified there unless told otherwise. Widget B (Worksheet Form) is
built as the reference implementation (`application/views/widgets/worksheet.php`,
one file shared by both editable-input and readonly-display modes via a
`$readonly` flag) — wired into `AssessmentController::assessment_view_code()`
(input), `ClassworkController::student_submission()` (readonly review), and
the "Widget" dropdown + JSON config textarea in `manage_assessments.php`.
Run `WidgetsController/install` once as admin to create/upgrade the schema.
A second widget, **Multiple Choice Quiz** (`quiz` widget_key, not in the
original 6-widget plan — see plan doc §10), was added as an opt-in
alternative to the legacy `QuizController`/`json_file_path` flow; it's the
only widget that auto-grades server-side (`Widgets_model::grade_quiz()`).

## Conventions
- Follow existing controller patterns (see `AssessmentController.php`,
  `ClassworkController.php`) for any new widget submission endpoints.
- Store structured widget data as JSON strings in existing longtext columns
  where possible; only add new columns/tables when the plan doc says to.
- Session/auth check pattern: `if (!isset($_SESSION['online'])) redirect('login');`
  in constructors — replicate this for any new controller.
