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
**Microlearning Quiz** (`iq_micro` widget_key) is the second topic-file
widget, sitting alongside `iq_discussion`: same `assessments.given` =
`{"topic": slug}` config and the same full-page redirect out of
`AssessmentController::assessment_view_code()`, but it wraps the denser
Sololearn-style topic schema instead of lesson+quiz — each section is a run
of 1-2 sentence `chunks`, every chunk followed by a 2-option micro-check,
closed by a `quiz` checkpoint whose `type` rotates between `mcq`, `arrange`
(tap tokens into order) and `type` (free-typed short answer), plus
`objectives` and `recap` screens and `callback`/`refSection` tags on
sections that re-test an earlier one. Rendered by
`InteractiveQuizController::micro()` →
`views/discussions/_interactive_micro_template.php`, styled by
`assets/interactive-micro-style.css` layered on top of the shared
`interactive-quiz-style.css`. Scored 1 point per micro-check + 1 per
checkpoint, with `max_score` derived server-side by
`AdminController::_count_micro_topic_items()` (the discussion widget's
one-point-per-section-quiz counter doesn't apply). Unlike the sibling
template the score is recomputed from a per-screen results map rather than
incremented, so Back-nav can't double-count and the Back button is always
available. Topic files for both widgets live in the same
`assets/json/{CLASS}/` library and share one admin Topic dropdown, filtered
by `AdminController::_iq_topic_format()` ('micro' when any section has
`chunks`) so neither widget can be pointed at a topic its renderer would
reject. Group play is supported on a grouping assessment (same
`AssessmentController::assessment_view_code()` fall-through to
`GroupWorkController::workspace()` as `iq_discussion`, and
`GroupWorkController::_render_group_iq()` picks this template by
`widget_key`), but with a deliberately different sync model than
`iq_discussion`'s free-for-all: only one member (the "driver", chosen via
an in-template picker modal, transferable anytime via "Pass to...") may
answer or navigate — everyone else watches read-only. This sidesteps
syncing the `type` checkpoint's free-typed input keystroke-by-keystroke;
the driver just types, and it syncs once on Submit like every other answer
shape. Answers sync through the same whole-blob
`assessment_live_state`/`save_draft()` path as `iq_discussion`, keyed
`"{sectionIndex}:{chunkIndex}"` (micro-check) / `"{sectionIndex}:q"`
(checkpoint) instead of by section index, and are graded server-side by
`Iq_topic_model::grade_micro()` (never the client) on finish via
`GroupWorkController::submit_group_iq()`. That model (`grade_micro()` /
`grade_discussion()` / `resolve_file()` / `load_topic()`) is the ONE place a
topic-file blob turns into the `{question, chosen, correct_answer, is_correct,
answered}` results list `classworks.code` stores — both the group submit and
the admin group-submission page's live-draft panel go through it, so a scoring
rule can't drift between what a submit records and what the instructor sees
while the group is still working. The live blob shape
(`{v, driver, answers:{...}}` / `{v, sections:{...}}`) is NOT that results
list: `AdminController::group_submissions()` grades the draft before handing it
to the widget view, which renders drafts and submissions identically apart from
an `is_draft` wording flag. JSON topic files for this format are authored by
the `interactive-quiz-microlearning` skill.
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
