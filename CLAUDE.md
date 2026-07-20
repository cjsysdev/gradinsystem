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
- `assessments` (master, content-only) → `assessment_section` (junction:
  due/status/is_groupings per `schedule_id`) → `classworks` (one row per
  student submission: `file_upload` OR `code` text, plus `score`). One
  assessment can be shared across multiple sections as a single master row.
  `classworks.assessment_id` and `assessment_groupings`/`assessment_live_state`'s
  `assessment_id` all point at `assessment_section.assessment_section_id`
  (a **section** id, not the master id) — preserved from the pre-normalization
  `assessment_id` values, so this column name is legacy but the value it
  holds changed meaning. `given` (widget config JSON), `title`, `description`,
  `max_score`, `term`, `widget_id` live on the **master** (`assessments`)
  and are shared by every section; editing them via
  `AdminController::save_assessment()` propagates to all sibling sections.
  `assessment_full` is a compat VIEW (`assessment_section` ⋈ `assessments`)
  reproducing the old one-row-per-section denormalized shape
  (`assessment_id` = section id, `master_id` = master id) — nearly all
  read-only query sites use this view; never write through it (see
  `Assessment_normalize_model`, `assessments::create_master()` /
  `update_master()` / `assign_to_schedule()` / `update_section()` /
  `delete_section()` for the real write paths). `io_type`: 1=Activity,
  2=Performance Task, 3=Major Exam, 4=Quiz; `term`:
  midterm/tentative-final/final.
- `discussions` (type: `static` link, or `interactive` → JSON file in
  `assets/json/{slug}.json`, rendered via `InteractiveQuizController`)

## Active Initiative: Paperless Midterm Integration
Full plan: **`root/docs/paperless-midterm-plan.md`** — read this before working
on anything related to classwork widgets, the IS Innovations course, or new
interactive assessment types. Widget architecture notes, build history, and
per-widget deviations from spec auto-load via `.claude/rules/paperless-widgets.md`
whenever you touch widget-related controllers/models/views or the plan doc.

## Conventions
- Follow existing controller patterns (see `AssessmentController.php`,
  `ClassworkController.php`) for any new widget submission endpoints.
- Widget config storage rule: per-assessment widget config is a JSON string
  in `assessments.given` (validated on save in `AdminController::save_assessment()`);
  the `assets/json/` topic-file library is ONLY for shared, reusable lesson
  content (`iq_discussion`, where `given` holds just `{"topic": slug}`). Never
  add new file-based widget config or `json_file_path`-style uploads — that
  legacy quiz flow is soft-deprecated (kept working for old data only).
- Store structured widget data as JSON strings in existing longtext columns
  where possible; only add new columns/tables when the plan doc says to.
- Session/auth check pattern: `if (!isset($_SESSION['online'])) redirect('login');`
  in constructors — replicate this for any new controller.
