---
paths:
  - "application/views/widgets/**"
  - "application/models/widgets_model.php"
  - "application/controllers/AssessmentController.php"
  - "application/controllers/BrainstormController.php"
  - "application/controllers/WidgetsController.php"
  - "application/views/admin/manage_assessments.php"
  - "root/docs/paperless-midterm-plan.md"
---

## Active Initiative: Paperless Midterm Integration
Full plan: **`root/docs/paperless-midterm-plan.md`** — read this before working on
anything related to classwork widgets, the IS Innovations course, or new
interactive assessment types.

Quick summary: 6 reusable classwork widgets (Worksheet Form, Card Sort,
Brainstorm Board, Diagram/Flow Builder, Decision Matrix, Calculator) so
every hands-on activity in a course can be done natively in the LMS instead
of paper/photo-upload — all 6 are now built (plan doc §4 has an
"Implemented" note per widget with file paths and any deviations from the
original spec). Widgets are looked up via a `widgets` registry table
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
**Brainstorm Board** (`brainstorm` widget_key) is architecturally different
from every other widget — it's a shared, section-wide live board rather than
a per-student form, so it doesn't render inline via `assessment_view_code.php`
like the others; it gets its own full-page flow via `BrainstormController.php`
(`AssessmentController::assessment_view_code()` redirects there when it
detects this widget_key, before any of the per-student/grouping logic runs).
A third widget, **Lab Worksheet** (`lab_worksheet` widget_key, not in the
original 6-widget plan — see plan doc §4 "Widget H"), covers Predict/Observe/
Explain-style lab activities: a fixed sequence of admin-authored experiments
(instructions + code snippets + a few free-text prompts each), plus an
optional exit question. Not auto-graded — same manual-score-entry pattern
as Worksheet Form/Card Sort. Renders inline via the standard
`assessment_view_code.php` flow like Worksheet Form (no special-case
redirect needed).
A fourth widget, **Case Study Worksheet** (`case_study` widget_key, not in
the original 6-widget plan — see plan doc §4 "Widget I"), covers narrative
case-study activities (e.g. "Meet Maria the calamansi farmer," Session 1.2):
a read-only story panel (intro + stat cards) followed by a fixed sequence of
sections holding heterogeneous questions — free text, a fixed list of short
lines, single-choice buttons that reveal a rationale note once picked, and
multi-select toggle cards. Not auto-graded — same manual-score-entry pattern
as Worksheet Form/Lab Worksheet. Renders inline via the standard
`assessment_view_code.php` flow like Worksheet Form/Lab Worksheet (no
special-case redirect needed). The admin "Widget" dropdown's example JSON
for this widget (`manage_assessments.php`'s `widgetExamples.case_study`) is
the full Session 1.2 "Meet Maria" worksheet, ready to save as-is.
A fifth widget, **Case Dossier Rating** (`case_dossier` widget_key, not in
the original 6-widget plan — see plan doc §4 "Widget J"), covers comparative
case-study activities (e.g. "Why Inventions Fail: The Innovation Triangle,"
Session 2.1): a hook question, a read-only conceptual-framework explainer,
then multiple parallel case dossiers (e.g. GCash/Kodak/Friendster) each
rated 1-5 per factor with a required cited-evidence text field, then
reflection questions. Every authored dossier renders to every student —
there's no per-student/group case assignment. Not auto-graded — same
manual-score-entry pattern as the other worksheet-style widgets. Renders
inline via the standard `assessment_view_code.php` flow (no special-case
redirect). Reuses Case Study Worksheet's `text`/`list`/`choice` question
shapes for its hook/reflection sections, but duplicated locally in its own
view file rather than shared — every widget here is self-contained.
A sixth widget, **Chapter Worksheet** (`chapter_worksheet` widget_key, not
in the original 6-widget plan — see plan doc §4 "Widget K"), covers the
Feasibility Study Worksheet Pack (`uploads/Feasibility_Study_Worksheet_Pack_
10x45min.docx`, IS Innovations — ten 45-minute worksheets, each producing
one chapter of a team's "Innovation Feasibility & Adoption Study" dossier):
a read-only timed-move table, a read-only "the model" worked-example
callout, a fixed sequence of typed steps (`text` free-answer, `grid`
fixed-row/typed-column tables reusing Widget F's row-label-keyed cell
convention, `choice` button picks reusing Widget I/J's interaction,
`checklist` checkboxes), a read-only "the trap" warning callout, a
peer-check question, and a fixed team/date/filed/peer-checked-by sign-off.
Not auto-graded, same manual-score-entry pattern as the other
worksheet-style widgets. Renders inline via the standard
`assessment_view_code.php` flow (no special-case redirect). Fully
self-contained, no shared base class with other widgets. The admin "Widget"
dropdown's example JSON (`widgetExamples.chapter_worksheet`) is the full
Worksheet 1 "The Problem" chapter, ready to use as-is — Worksheets 2–10
from the same pack reuse this same widget with a different config JSON
each (not yet authored).
