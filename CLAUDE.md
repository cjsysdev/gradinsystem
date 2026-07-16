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
Full plan: **`root/docs/paperless-midterm-plan.md`** — read this before working
on anything related to classwork widgets, the IS Innovations course, or new
interactive assessment types. Widget architecture notes, build history, and
per-widget deviations from spec auto-load via `.claude/rules/paperless-widgets.md`
whenever you touch widget-related controllers/models/views or the plan doc.

## Conventions
- Follow existing controller patterns (see `AssessmentController.php`,
  `ClassworkController.php`) for any new widget submission endpoints.
- Store structured widget data as JSON strings in existing longtext columns
  where possible; only add new columns/tables when the plan doc says to.
- Session/auth check pattern: `if (!isset($_SESSION['online'])) redirect('login');`
  in constructors — replicate this for any new controller.
