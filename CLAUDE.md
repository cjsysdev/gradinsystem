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

## Grading (read before touching anything grade-related)
**`Grade_calculator` (`application/models/Grade_calculator.php`) is the only
place a grade may be computed.** Never add grade arithmetic to a query, a
controller, or a view — that duplication is exactly what this model replaced
(four divergent SQL copies of the transmutation formula, eight copy-pasted
weighted-sum loops, and a helper that hardcoded the passing rate to 60).

Three layers, kept strictly separate:
1. **Data** — SQL returns raw sums only (`sum_score`, `sum_max`,
   `n_assessments`, `n_ungraded`). No transmutation, no weighting in SQL.
2. **Policy** — `transmute()` / `component()` / `term_grade()` /
   `final_grade()` are pure PHP and take every rule as an argument.
   All tunables live in `application/config/grading.php`.
3. **API** — `for_student()`, `for_schedule()`, `for_schedule_final()`,
   `for_all_schedules_final()`. Controllers call these and only map to views.

Rules that are easy to break:
- **Roster = `class_student.schedule_id` + `status='enrolled'` + active
  semester.** Never join `class_student.section = class_schedule.section` —
  that ignores semester and enrolment status (it rendered 90 students on a
  51-student section).
- **A NULL `classworks.score` counts as 0** and is reported separately as
  `pending_count`. Do not "fix" this without a decision — it changes grades.
- **A term is INC unless every `io_type` has at least one assessment.** Weights
  are never renormalized; a term missing its Major Exam does not scale the rest
  up. `'INC'` is a `status` field — never put the string into a numeric field.
- **All score writes go through `classworks::set_score()`**, which validates and
  clamps to `max_score`.
- `convertPercentageToGradePoint()` is a deprecated shim; call
  `Grade_calculator::transmute()` instead.

`GradeAuditController` (admin-only, also CLI-runnable) is the safety net:
`selftest` (policy unit checks), `diff` (compare against the frozen baseline in
`uploads/grade_audit/`), `integrity` (data drift report), `scoretest`
(guardrails, transactional + rolled back), `student/{id}` (spot-check).
**Run `selftest` and `diff` after any change to grading.**

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
