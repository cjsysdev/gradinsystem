# Paperless Midterm Integration Plan

**Status:** All 6 widgets (B–G) implemented, plus four added outside the
original scope: **Multiple Choice Quiz** (see §10) — replaces the
`json_file_path`-upload requirement of the old `QuizController` flow for any
assessment that opts into it; that old flow is untouched and still works for
assessments not using the widget — **Widget H — Lab Worksheet** (see §4)
for Predict/Observe/Explain-style programming lab activities — **Widget I —
Case Study Worksheet** (see §4) for narrative case-study activities (story
panel + heterogeneous question types) — and **Widget J — Case Dossier
Rating** (see §4) for comparative case-study activities rating multiple
parallel dossiers against a shared framework. Widget D (Brainstorm Board) is
a first pass — see its section below for what was simplified vs. the
original spec (no drag-to-cluster positioning; participation-only classwork
tracking).
**Scope:** IS Innovations & New Technologies course, Midterm (Weeks 1–8)
**Related:** `docs/week1-lms-seed-example/` (SQL + JSON pilot for Week 1)

## 1. The Goal

Today only ONE interactive material type exists: the Interactive Discussion
(JSON-driven lesson + multiple-choice quiz, `assets/json/{slug}.json`, rendered
by `InteractiveQuizController`). Everything else (worksheets, posters,
brainstorms, decision matrices, ROI calculations, storyboards) is still
paper-equivalent — students fill it on paper/Canva and upload a photo.

The goal: build a small family of reusable, configurable classwork **widgets**
so every hands-on activity in the Midterm can be done natively in the LMS —
no printing, no photographing worksheets.

## 2. Current State

- **Interactive Discussion** (existing): `discussions` table (type=`interactive`)
  → JSON file in `assets/json/` → rendered by `InteractiveQuizController`.
  Great for self-paced concept teaching, not for open-ended group work.
- **Everything else**: `assessments` (io_type: Activity/Performance
  Task/Quiz/Major Exam) → student submits via `classworks.file_upload` or
  `classworks.code` (plain text/code box only).

## 3. Activity Audit — 6 Repeating Patterns

Every hands-on activity across all 16 Midterm sessions falls into one of 6
patterns. Build 6 reusable widgets, not 16 custom interfaces.

| # | Pattern | Example Sessions | Widget |
|---|---|---|---|
| 1 | Fill-in structured worksheet | 1.1, 5.1, 6.2, 8.2 | **B — Worksheet Form** |
| 2 | Sort items into categories | 2.2, 3.1, 3.2, 6.1, 7.1 | **C — Card Sort Board** |
| 3 | Free brainstorm + cluster + vote | 1.2, 8.1 | **D — Brainstorm & Voting Board** |
| 4 | Build a diagram / labeled framework | 2.1, 4.1, 8.1 | **E — Diagram / Flow Builder** |
| 5 | Compare options across criteria | 4.2, 6.1, 7.1 | **F — Decision Matrix** (can merge into B) |
| 6 | Calculate a number from inputs | 5.2 | **G — Calculator Widget** |

## 4. Widget Specifications

### Widget B — Dynamic Worksheet Form
- **Replaces:** fill-in-the-blank paper worksheets
- **Sessions:** 1.1 (Innovation Hunt), 5.1 (Robot Task Audit), 6.2 (Use-case
  design), 8.2 (Concept Brief)
- **UI:** table-style form matching the worksheet's columns; student can
  add/remove rows; submit locks it in.
- **Config (`assessments.given`):**
  ```json
  {
    "widget": "worksheet",
    "columns": ["Technology", "Problem Solved", "Inventor", "Timeline", "Why It Succeeded"],
    "min_rows": 5,
    "allow_add_rows": true
  }
  ```
- **Submission (`classworks.code`):**
  ```json
  {
    "rows": [
      {"Technology":"GCash","Problem Solved":"...","Inventor":"Globe","Timeline":"2004","Why It Succeeded":"..."}
    ]
  }
  ```
- **Priority: HIGH** — build first. Establishes the config-in/JSON-out
  pattern every other widget reuses.

### Widget C — Card Sort / Classification Board
- **Replaces:** printed classification cards
- **Sessions:** 2.2, 3.1, 3.2, 6.1, 7.1
- **UI:** unsorted cards at top, 2–3 labeled drop zones below; drag (or
  tap-to-place on mobile); optional justification text per placed card.
- **Config:**
  ```json
  {
    "widget": "card_sort",
    "bins": ["Incremental", "Disruptive"],
    "items": ["Android OS", "Netflix", "ChatGPT", "LED Bulbs"],
    "require_justification": true
  }
  ```
- **Submission:**
  ```json
  {
    "placements": [
      {"item":"Android OS","bin":"Incremental","justification":"..."}
    ]
  }
  ```
- **Priority: HIGH** — build second. Second most common pattern (5 sessions).
- **Implemented:** `application/views/widgets/card_sort.php`. Config/submission
  shape matches this spec exactly. UI uses a per-item bin `<select>` instead
  of true drag-and-drop — more robust on mobile and needs no extra JS library
  in this no-build-step codebase; still satisfies "tap-to-place."

### Widget D — Brainstorm & Voting Board
- **Replaces:** poster paper + physical sticky notes
- **Sessions:** 1.2 (Ideation Mural), 8.1 (gallery-walk feedback)
- **UI:** blank canvas, "Add Idea" sticky notes, drag to cluster, limited
  votes per student (dot-voting).
- **Config:**
  ```json
  {
    "widget": "brainstorm_board",
    "prompt": "How could IS help Maria the farmer?",
    "max_votes_per_student": 3,
    "group_mode": true
  }
  ```
- **Submission (shared/class-wide, NOT per-student):**
  ```json
  {
    "notes": [
      {"id":1,"text":"Direct-to-buyer app","author_group":"Group A","x":120,"y":80,"votes":5}
    ]
  }
  ```
- **DB note:** This one is shared across the class section, closer to
  `PollController`'s pattern than `classworks`. Start by storing the whole
  board as one JSON blob in `assessments.given`, updated via polling. A
  dedicated `board_notes` table is a later upgrade if real-time feel is needed.
- **Priority: MEDIUM** — build third/fifth. Highest classroom value but most
  complex (shared/live state).
- **Implemented (first pass, deviates from spec):**
  `application/controllers/BrainstormController.php` +
  `application/views/brainstorm_board.php` + fallback
  `application/views/widgets/brainstorm.php` (used only by the generic
  per-student readonly views, which don't really apply here).
  - Storage: **not** `assessments.given` (that's the instructor's config —
    prompt + max votes — and stays read-only at runtime). Board state lives
    in the generic `assessment_live_state` table via `Live_state_model`,
    keyed `(assessment_id, group_id = NULL)` = section-wide, exactly the
    reuse the plan called for in §6.
  - Submission shape differs: `votes` is an array of student_ids (not a
    count) so per-student vote limits can be enforced and un-voting works;
    notes carry `author` (an individual student's name), not `author_group`
    — this build doesn't track group-authored notes, everyone posts as
    themselves.
  - **Dropped: drag-to-cluster (`x`/`y` positioning).** Notes render in a
    simple flex-wrap grid instead. Clustering is real UI complexity
    (drag state, collision, persistence) that wasn't essential to get
    brainstorm+voting working — candidate for a follow-up pass.
  - Grading: each interaction (post or vote) creates/keeps one participation
    `classworks` row (`status='submitted'`, no `code`, no `score`) so admin
    submission lists show who took part; the actual content is the shared
    board, not a per-student field.

### Widget E — Diagram / Flow Builder
- **Replaces:** hand-drawn diagrams
- **Sessions:** 2.1 (Innovation Triangle), 4.1 (IoT Pipeline), 8.1 (Full
  System Map — free-form, stretch goal), 7.2 (storyboard — stretch goal)
- **UI (fixed-flow mode):** pre-drawn node shape/sequence from config;
  student only fills in the TEXT inside each node.
- **Config (fixed pipeline example):**
  ```json
  {
    "widget": "diagram",
    "mode": "fixed_flow",
    "nodes": ["Sense", "Transmit", "Store", "Act"],
    "connections": "sequential"
  }
  ```
- **Submission:**
  ```json
  {
    "node_content": {
      "Sense": "Vibration + temperature sensor on the loom motor",
      "Transmit": "LoRa module to a nearby gateway",
      "Store": "Cloud database (Firebase)",
      "Act": "SMS alert to the factory owner's phone"
    }
  }
  ```
- **Priority: MEDIUM** — fixed-flow mode covers 2.1 and 4.1 well and is
  straightforward. Free-form canvas mode (Week 8.1, 7.2) is a later
  enhancement — defer.
- **Implemented:** `application/views/widgets/diagram.php`, fixed-flow mode
  only, matches spec exactly. Free-form canvas mode still deferred.

### Widget F — Decision Matrix Tool
- **Replaces:** printed decision-matrix worksheets
- **Sessions:** 4.2 (Connectivity Matrix), 6.1 (Latency Budget), 7.1
  (AR/VR/MR cost-tier matching)
- **Config:**
  ```json
  {
    "widget": "decision_matrix",
    "rows": ["Smart irrigation", "Fish tank monitor", "Offshore fishing boat"],
    "columns": [
      {"name":"Cost","type":"text"},
      {"name":"Best Fit","type":"select","options":["WiFi","Bluetooth","LoRa","Cellular","Satellite"]}
    ]
  }
  ```
- **Priority: LOW-MEDIUM** — essentially Widget B with typed columns
  (text vs. dropdown). Build as a **B enhancement**, not standalone.
- **Implemented:** `application/views/widgets/decision_matrix.php`, as its
  own widget file rather than a mode of Widget B — cleaner given rows are
  fixed (not add/removable like Worksheet's) and columns are typed.
  Submission shape (not specified in the original spec):
  `{"cells": {"<row label>": {"<column name>": "<value>", ...}, ...}}`.

### Widget G — Calculator Widget
- **Replaces:** manual math on paper
- **Sessions:** 5.2 (ROI / Payback Period)
- **Config:**
  ```json
  {
    "widget": "calculator",
    "inputs": [
      {"label":"Equipment Cost (₱)","key":"cost"},
      {"label":"Monthly Savings (₱)","key":"savings"}
    ],
    "formula": "cost / savings",
    "result_label": "Months to Break Even"
  }
  ```
- **Priority: LOW** — only 1 session, but simple and satisfying. Good
  quick win alongside Widget B.
- **Implemented:** `application/views/widgets/calculator.php`, config matches
  spec exactly. Formula evaluated by a small hand-written recursive-descent
  parser (+ - * / parentheses, variable substitution) — deliberately not
  `eval()`/`new Function()`, even though the formula string itself is
  admin-authored/trusted, since the *values* substituted into it are
  student-entered. Submission shape: `{"inputs": {"<key>": "<value>", ...}, "result": <number>}`.

### Widget H — Lab Worksheet (added outside original scope)
- **Why:** hands-on programming labs (e.g. CC104 "Introduction to Arrays")
  follow a Predict → Compile/Run → Observe → Explain loop per experiment,
  often with a deliberate "break it on purpose" step — not covered well by
  Worksheet Form's free-row table, since each experiment has fixed
  instructions/code plus a *different* small set of prompts, not repeatable
  columns.
- **Replaces:** paper/HTML lab worksheets like
  `CC104_Lab_Intro_to_Arrays_60min.html` — students previously would have
  had to fill this out on paper or in a separate doc and upload a photo/file.
- **UI:** fixed sequence of experiment cards (title, admin-authored
  instructions/code block, optional "breaking it on purpose" styling), each
  with a few free-text prompts (Predict/Observe/Explain, or custom tags);
  an optional exit question after the last experiment. A lightweight
  progress indicator (X/N answered) is computed client-side from filled
  fields — not a separate saved flag.
- **Config (`assessments.given`):**
  ```json
  {
    "widget": "lab_worksheet",
    "intro": "<p>optional HTML shown above the experiments</p>",
    "experiments": [
      {
        "title": "Experiment 1.1 — Declare an array and print the first element",
        "instructions": "<p>...</p><pre><code>...</code></pre>",
        "warning": false,
        "prompts": [
          {"tag": "predict", "label": "PREDICT", "text": "What number will print?"},
          {"tag": "observe", "label": "OBSERVE", "text": "What actually printed?"},
          {"tag": "explain", "label": "EXPLAIN", "text": "Why?"}
        ],
        "note": "Fix it back: ..."
      }
    ],
    "exit_question": "optional single free-text question shown after all experiments"
  }
  ```
- **Submission (`classworks.code`):**
  ```json
  {
    "answers": { "0": {"predict": "...", "observe": "...", "explain": "..."} },
    "exit_question": "..."
  }
  ```
  Keyed by experiment index (string, since JSON object keys are strings) —
  same indexing convention as Widget F's `cells` object.
- **Not auto-graded** — manual score entry, like Worksheet Form/Card Sort.
  `instructions`/`intro` are trusted admin-authored HTML rendered as-is
  (same trust model as `_interactive_quiz_template.php`'s `section.lesson`),
  not escaped like Worksheet Form's plain-text columns.
- **Implemented:** `application/views/widgets/lab_worksheet.php`. Renders
  inline via the standard `assessment_view_code.php` flow (no special-case
  redirect, unlike Brainstorm/Interactive Discussion) since it's a normal
  per-student form widget.

### Widget I — Case Study Worksheet (added outside original scope)
- **Why:** narrative case-study activities (e.g. Session 1.2's "Meet Maria
  the calamansi farmer" field notebook) combine a story panel (intro +
  stat cards) with a fixed sequence of questions of several *different*
  shapes — plain reflection text, a fixed list of short lines (brainstorm
  ideas, ranked picks), single-choice "vote" buttons that reveal a
  rationale note once picked, and multi-select toggle cards (e.g. an
  Innovation-Triangle strength check). None of the other widgets combine
  a narrative panel with this much question-type variety in one fixed
  flow — Worksheet Form is a repeatable-row table, and Lab Worksheet's
  items are code experiments with Predict/Observe/Explain prompts, not
  case-study questions.
- **Replaces:** one-off standalone HTML "field notebook" worksheets
  (e.g. `session1-2-calamansi-farmer.html`) that students would otherwise
  fill out separately from the LMS or, worse, on paper/photo-upload.
- **UI:** the story panel (eyebrow/title/intro HTML/stat cards) renders as
  read-only context at the top in both modes; below it, a fixed sequence
  of sections (each with a label + optional timing badge) holds the
  questions. A lightweight progress indicator (X/N answered) is computed
  client-side, same pattern as Lab Worksheet's.
- **Config (`assessments.given`):**
  ```json
  {
    "story": {
      "eyebrow": "Session 1.2 · Field Notebook",
      "title": "Innovation in Bohol: Maria's Calamansi Farm",
      "intro": "<p>optional trusted HTML shown above the stat cards</p>",
      "stats": [ {"label": "NO FERTILIZER CREDIT", "text": "..."} ]
    },
    "sections": [
      {
        "label": "Meet Maria",
        "timing": "3–15 min · Problem Intro",
        "questions": [
          {"type": "text", "badge": "core", "prompt": "...", "rows": 2, "placeholder": "..."},
          {"type": "list", "badge": "core", "prompt": "...", "lines": 3, "placeholders": ["1. ...", "2. ...", "3. ..."]},
          {"type": "choice", "badge": "core", "prompt": "...", "options": [{"text": "...", "note": "..."}]},
          {"type": "toggle_grid", "badge": "bonus", "prompt": "...", "items": [{"title": "TECH", "text": "..."}]}
        ]
      }
    ]
  }
  ```
  Four question `type`s cover every shape needed by the source worksheet:
  `text` (textarea), `list` (N fixed one-line inputs), `choice`
  (single-select buttons, each option's `note` revealed only once picked),
  `toggle_grid` (multi-select cards). `badge` (`core`/`bonus`) is purely
  informational, mirroring the source worksheet's own visual tagging — it
  does not affect grading.
- **Submission (`classworks.code`):** flat, running question index across
  all sections (same index-keyed-object convention as Lab
  Worksheet/Decision Matrix):
  ```json
  {
    "answers": {
      "0": "text answer",
      "1": ["line one", "line two", "line three"],
      "2": 1,
      "9": [0, 1]
    }
  }
  ```
  `text` → string, `list` → string[], `choice` → selected option index (or
  `null`), `toggle_grid` → array of toggled-on item indices.
- **Not auto-graded** — manual score entry, same as Worksheet Form/Card
  Sort/Lab Worksheet.
- **Implemented:** `application/views/widgets/case_study.php`. Renders
  inline via the standard `assessment_view_code.php` flow (no special-case
  redirect) since it's a normal per-student form widget, same as Lab
  Worksheet. The admin "Widget" dropdown's example JSON
  (`manage_assessments.php`'s `widgetExamples.case_study`) is the full
  Session 1.2 "Meet Maria" worksheet, ready to use as-is.

### Widget J — Case Dossier Rating (added outside original scope)
- **Why:** comparative case-study activities (e.g. Session 2.1's "Why
  Inventions Fail: The Innovation Triangle") combine a hook question, a
  read-only conceptual-framework explainer, and — the piece none of the
  other widgets cover — **multiple parallel case dossiers** (e.g. GCash /
  Kodak / Friendster), each rated 1–5 per shared factor with a required
  cited-evidence text field, followed by reflection questions. Widget I
  (Case Study Worksheet) has no concept of more than one case at a time or
  of a 1–5 rating+evidence interaction.
- **Replaces:** one-off standalone HTML worksheets built for this exact
  "rate several real cases against a framework, cite your evidence" pattern
  (e.g. `session2-1-innovation-triangle.html`).
- **UI:** a read-only meta header (eyebrow/title/sub), then in order: the
  hook section (reuses Widget I's `text`/`list`/`choice` question shapes
  verbatim), a read-only framework explainer (factor cards + an anchor
  quote — no input), one accent-colored card per case dossier (read-only
  facts/source, followed by a 1–5 button scale + evidence text input per
  factor), and a reflection section (same question shapes as the hook).
- **Config (`assessments.given`):**
  ```json
  {
    "meta": {"eyebrow": "Session 2.1 · Field Notebook", "title": "...", "sub": "..."},
    "hook": {"label": "...", "timing": "...", "intro": "<p>...</p>", "questions": [ {"type": "list", "badge": "core", "prompt": "...", "lines": 3, "placeholders": [...]} ]},
    "framework": {"label": "...", "timing": "...", "intro": "<p>...</p>", "factors": [{"title": "TECH", "text": "..."}], "anchor": "Tech alone is not enough."},
    "groups": [
      {
        "name": "GCash", "accent": "mango",
        "dossier": {"title": "Case Dossier — GCash", "facts": ["...", "..."], "source": "Sources: ..."},
        "factors": [{"title": "TECH", "question": "Did the technology work?"}]
      }
    ],
    "reflection": {"label": "Reflection", "timing": "...", "questions": [ {"type": "text", "badge": "core", "prompt": "...", "rows": 3, "placeholder": "..."} ]}
  }
  ```
  `hook`/`reflection` questions reuse Widget I's `text`/`list`/`choice`
  field names exactly. `framework` and `groups[].dossier` are pure
  admin-authored display content, no answer captured. `groups[].factors` is
  the new interaction: each renders a 1–5 rating scale + an evidence text
  input.
- **Submission (`classworks.code`):**
  ```json
  {
    "hook_answers": {"0": ["line one", "line two", "line three"]},
    "group_ratings": {"0": {"0": {"score": 4, "evidence": "cited number/fact"}}},
    "reflection_answers": {"0": "...", "1": 2}
  }
  ```
  `hook_answers`/`reflection_answers` are flat, index-keyed-object maps
  (same convention as every other widget). `group_ratings` is keyed
  `group index → factor index → {score, evidence}` (`score` 1–5 or `null`).
- **Not auto-graded** — manual score entry, same as every worksheet-style
  widget so far.
- **Implemented:** `application/views/widgets/case_dossier.php`. No shared
  base class with Widget I — the `text`/`list`/`choice` renderer is
  duplicated locally (as `cd_render_question()`), matching how every widget
  here is self-contained. Renders inline via the standard
  `assessment_view_code.php` flow, no special-case redirect. There's no
  mechanism to assign "your group only gets GCash" — every authored
  `groups[]` entry renders to every student, matching the source worksheet's
  own design (all three dossiers side by side, compared during debrief).
  The admin "Widget" dropdown's example JSON (`widgetExamples.case_dossier`)
  is the full Session 2.1 "Innovation Triangle" worksheet (all 3 dossiers),
  ready to use as-is.

### Widget K — Chapter Worksheet (added outside original scope)
- **Why:** the Feasibility Study Worksheet Pack (`uploads/Feasibility_Study_
  Worksheet_Pack_10x45min.docx`, IS Innovations & New Technologies — "Innovation
  Feasibility & Adoption Study") is ten 45-minute worksheets, each producing
  one chapter of a team's dossier, that all share one shape: a read-only
  timed-move table, a read-only "the model" worked-example callout (drawn
  from the pack's running "Should the Carmen Public Market Adopt QR-Based
  Digital Payments?" case), a fixed sequence of typed steps mixing free-text
  answers, fixed-row/typed-column grids (e.g. an evidence grid, a technology
  selection matrix), single-choice picks (e.g. Incremental/Disruptive), and
  checklists (e.g. a PT1 assembly check), a read-only "the trap" warning
  callout, a peer-check question, and a fixed team/date/filed/peer-checked-by
  sign-off. None of Widgets B–J combine a worked-model callout, a trap
  callout, and a sign-off block around a mixed-type step sequence, so this is
  a genuinely new shape rather than a config variant of an existing widget —
  per the widget-creation skill's escalation rule, it's built as one new
  widget rather than 10 one-off configs, since the shape recurs unchanged
  across all 10 worksheets in the pack.
- **UI:** read-only meta header (eyebrow/title/sub) → read-only timed-move
  table → read-only "Model" callout (blue accent) → each step in order
  (`text`: prefixed free-text answer; `grid`: fixed row labels × typed
  columns, mirrors Widget F/`decision_matrix`'s row/column shape; `choice`:
  button group with an optional rationale note, reuses Widget I/J's
  interaction; `checklist`: plain checkboxes) → read-only "Trap" callout
  (amber accent) → peer-check instruction + task + free-text answer → a
  fixed team/date/filed-checkbox/peer-checked-by sign-off row.
- **Config (`assessments.given`):**
  ```json
  {
    "meta": {"eyebrow": "WORKSHEET 1 · 45 MINUTES · PRODUCES DOSSIER CHAPTER 1", "title": "The Problem", "sub": "..."},
    "timeline": {"label": "How this session runs", "moves": [{"time": "0–5", "move": "Read the model", "detail": "..."}]},
    "model": {"label": "THE MODEL — how the Carmen Market study did it", "html": "<p>...</p>"},
    "steps": [
      {"type": "text", "label": "STEP 1 — ...", "instruction": "...", "prefix": "Our problem is:", "rows": 2, "placeholder": "..."},
      {"type": "grid", "label": "STEP 2 — ...", "instruction": "...", "columns": [{"name": "...", "type": "text|select|checkbox", "options": [...]}], "rows": [{"label": "Someone said it", "sub": "(a real quote)"}], "note": "CORE/BONUS grading note"},
      {"type": "choice", "label": "...", "instruction": "...", "options": [{"text": "...", "note": "..."}]},
      {"type": "checklist", "label": "...", "instruction": "...", "items": ["Ch. 1 The Problem", "Ch. 2 Innovation Triangle"]}
    ],
    "trap": {"label": "THE TRAP", "html": "<p>...</p>"},
    "peer_check": {"label": "PEER CHECK", "instruction": "...", "task": "...", "rows": 3},
    "file_it": {"label": "FILE IT", "instruction": "..."}
  }
  ```
- **Submission (`classworks.code`):**
  ```json
  {
    "steps": {
      "0": "free text answer",
      "1": {"Someone said it": {"0": "quote text", "1": "source"}, "A number": {"0": "...", "1": "..."}},
      "2": 1,
      "3": [true, false, true]
    },
    "peer_check": "free text answer",
    "file_it": {"team": "Team Alpha", "date": "2026-08-10", "filed": true, "peer_checked_by": "J.D."}
  }
  ```
  `steps` is a flat, index-keyed-object map (same convention as every other
  widget) whose value shape depends on that step's `type`: `text` → string;
  `grid` → row-label-keyed object of column-index-keyed values (same
  row-label-as-key convention as Widget F/`decision_matrix`'s `cells`);
  `choice` → selected option index or `null`; `checklist` → a bool array
  parallel to `items`. `file_it` is always the same 4 fixed fields
  regardless of config.
- **Not auto-graded** — manual score entry, same as every worksheet-style
  widget so far.
- **Implemented:** `application/views/widgets/chapter_worksheet.php`. Fully
  self-contained (no shared base class with Widget I/J), matching the rest
  of this widget family. Renders inline via the standard
  `assessment_view_code.php` flow, no special-case redirect. The admin
  "Widget" dropdown's example JSON (`widgetExamples.chapter_worksheet`) is
  the full Worksheet 1 "The Problem" chapter, ready to use as-is; Worksheets
  2–10 from the same pack reuse this widget unchanged, just with a different
  config JSON per worksheet. Worksheet 2 ("Why It's Unsolved — The
  Innovation Triangle") is also authored — config JSON handed to CJ directly
  (not stored in the repo, same as every other per-assessment widget config)
  — using only the `grid`/`text` step types already built for Worksheet 1,
  confirming no widget code changes are needed per worksheet. Worksheets
  3–10 remain unauthored — see the pack's docx for their content.

## 5. Full Session-to-Widget Mapping (Weeks 1–8)

| Session | Concept Portion | Hands-On Activity | Widget |
|---|---|---|---|
| 1.1 | Invention vs Innovation | Innovation Hunt Worksheet | Discussion + **B** |
| 1.2 | Innovation in Bohol | Ideation Mural (Maria) | Discussion + **D**¹ |
| 2.1 | Why Inventions Fail | Innovation Triangle | Discussion + **E**² |
| 2.2 | Sources of Innovation | Jigsaw case sort | Discussion + **C** |
| 3.1 | Disruptive vs Incremental | Classification cards + debate | Discussion + **C** |
| 3.2 | Innovation Patterns | Pattern matching + synthesis | Discussion + **C** |
| 4.1 | IoT Fundamentals | IoT Pipeline diagram | Discussion + **E** |
| 4.2 | Sensors & Connectivity | Connectivity Decision Matrix | Discussion + **F** |
| 5.1 | What Makes a Robot? | Robot Task Audit | Discussion + **B** |
| 5.2 | Business Case for Automation | ROI Calculator + debate | Discussion + **G** |
| 6.1 | Latency & Edge Computing | Latency Budget classification | Discussion + **C** |
| 6.2 | 5G as Enabler | Use-case design worksheet | Discussion + **B** |
| 7.1 | AR vs VR vs MR | Tech-to-scenario matching | Discussion + **F** |
| 7.2 | Spatial Computing | Tourism AR storyboard | Discussion + **E** (sequential, stretch) |
| 8.1 | Synthesis Workshop | Full System Map + gallery walk | **E** (free-form, stretch) + **D** |
| 8.2 | — | Midterm Exam + Concept Brief | Discussion (exam) + **B** (brief) |

¹ **Widget I** (Case Study Worksheet, §4) is also available for 1.2 as a
fuller alternative — the whole "Meet Maria" field notebook (story + 13
questions across the Mural/Gallery Walk/Reflection portions) as one
graded, per-student/group worksheet. This doesn't replace **D** — Brainstorm
Board's live shared board is still the intended tool for the in-class
Ideation Mural moment itself — the two can be used together or Widget I
can stand in on its own where a live shared board isn't needed.

² **Widget J** (Case Dossier Rating, §4) is also available for 2.1 as a
fuller alternative — the whole "Innovation Triangle" field notebook (hook +
framework explainer + all 3 case dossiers rated with cited evidence +
reflection) as one graded worksheet, instead of pairing the Discussion topic
with Widget E's fixed-flow diagram.

## 6. Database Integration

**Update (implemented):** rather than a plain `assessments.widget_type
VARCHAR`, widgets are looked up through a small registry table so adding a
widget later is "add a row + drop a view file," not editing a controller's
if/else chain:

```sql
CREATE TABLE `widgets` (
  `widget_id`         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `widget_key`        VARCHAR(32) NOT NULL UNIQUE,   -- 'worksheet', 'card_sort', etc. — matches the JSON config's "widget" field
  `name`              VARCHAR(64) NOT NULL,           -- shown in the admin "Widget" dropdown
  `input_view`        VARCHAR(128) NOT NULL,          -- e.g. 'widgets/worksheet' — one file, shared by input + readonly modes via a $readonly flag
  `admin_config_view` VARCHAR(128) DEFAULT NULL,      -- reserved for a future visual config builder; NULL today (instructor hand-writes JSON)
  `created_at`        DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
);
ALTER TABLE `assessments` ADD COLUMN `widget_id` INT UNSIGNED DEFAULT NULL;
```

- **`assessments.given`** → instructor's widget config JSON. **Correction:**
  earlier drafts of this doc assumed this column already existed
  ("currently unused"); it did not — `Widgets_model::install()` adds it
  alongside `widget_id`.
- **`classworks.code`** (already used for text submissions) → student's
  widget submission JSON. Unchanged — `AssessmentController::submit_classwork()`
  needed zero modifications; each widget's JS serializes its state into the
  existing hidden `code` field before the form submits.
- Run `WidgetsController/install` (admin-only) once to create/upgrade the
  schema — same pattern as `Groupings/install` and `poll/install`.
- All rows (`worksheet`, `quiz`, `card_sort`, `diagram`, `decision_matrix`,
  `calculator`, `lab_worksheet`, `brainstorm`, `iq_discussion`,
  `case_study`, `case_dossier`) are seeded now — an `input_view` file exists
  for every one before its row gets inserted.

**Config storage convention (settled Jul 2026):** per-assessment widget
config lives as a JSON string in `assessments.given` — that is the standard
for every widget. `AdminController::save_assessment()` validates the JSON
server-side (invalid/empty config is rejected with a flash error instead of
stored silently), the Add/Edit modal validates it client-side before submit,
and a "Load from .json file" button reads a locally authored file into the
textarea (authoring convenience; storage stays in the DB). The `assets/json/`
file library is reserved for shared, reusable lesson content only
(`iq_discussion` topics, where `given` holds just `{"topic": slug}`). The
legacy `json_file_path` upload flow (`QuizController`) is soft-deprecated:
existing assessments keep working, but the upload field was removed from the
legacy add-assessment form and `manage_json_files.php` carries a
legacy-only warning — new quizzes go through the `quiz` widget.

Widget D (Brainstorm Board) is the one exception, as planned — shared/live
state across a section rather than one row per student. It reuses the
generic `assessment_live_state` table (`application/models/live_state_model.php`,
keyed by `assessment_id` + optional `group_id`, `group_id = NULL` meaning
section-wide) that already existed from the group-assessment-submission
feature, via `BrainstormController` (`get_or_create($assessment_id, null)`)
— no new table was introduced.

## 7. Build Roadmap

| Phase | What to Build | Why This Order |
|---|---|---|
| 1 | ~~Add `widget_type` column.~~ Add `widgets` registry table + `widget_id` column. Build Widget B. **(Done)** | Establishes the pattern; unlocks 4 sessions immediately. |
| 2 | Build Widget C. **(Done)** | Second most-used pattern (5 sessions); same architecture as Phase 1. |
| 3 | Build Widget G, and Widget F (standalone rather than a B mode). **(Done)** | Small, low-risk quick wins. |
| 4 | Build Widget E — fixed-flow mode only. **(Done)** | Covers 2.1 and 4.1. |
| 5 | Build Widget D — simple polling version. **(Done, first pass — see §4 deviations)** | Most complex; covers 1.2 fully. |
| 6 | (Stretch) Free-form canvas mode for E; storyboard mode for 7.2. | Polish once core 6 widgets are proven. |

## 8. Concrete Example: Week 1 Under This Plan

**Session 1.1 — Innovation Hunt**
- Before: student fills paper/Canva worksheet, photographs it, uploads via file-upload.
- After (Widget B): student opens the assessment, sees a live 5-row table, types directly into each cell, submits. Instructor gets structured, searchable, gradable data instantly.

**Session 1.2 — Ideation Mural**
- Before: groups draw on poster paper, photograph it, upload.
- After (Widget D): groups post digital sticky notes to a shared board, drag to cluster, vote (max 3 per student). Instructor can export the final board state directly from the LMS.

## 9. Next Steps

1. Confirm Phase 1 scope (Widget B) → draft exact PHP controller changes +
   a simple front-end view for the Worksheet widget.
2. Add the `widget_type` column via one small migration SQL statement.
3. Re-do the Week 1 seed using Widget B instead of plain file-upload for the
   Innovation Hunt worksheet, as a working pilot.
4. Once Week 1 is validated with real student use, proceed to Weeks 2–8
   using the same widgets, then Widget C and beyond per the roadmap.

## 10. Widget — Multiple Choice Quiz (added outside original scope)

**Why:** the pre-existing quiz mechanism (`QuizController`, iotype 3/4
"Major Exam"/"Quiz") requires the admin to upload a `.json` file
(`assessments.json_file_path`) before a quiz can run. That flow is left
completely untouched — this widget is an **opt-in alternative** so a quiz
can instead be authored the same way as every other widget: config as JSON
in `assessments.given`, no file upload.

**Unlike every other widget, this one auto-grades.** Client never computes
or sends a score — `Widgets_model::grade_quiz($config, $answers)` is the
single source of truth, called from both `AssessmentController::submit_classwork()`
(solo) and `GroupWorkController::submit_group()` (group, one shared score
applied to every member). Mirrors `QuizController::submit()`'s exact
matching rules (case-insensitive trimmed match for free-text questions,
trimmed exact match for multiple-choice) so grading behaves identically to
the legacy flow.

- **Config (`assessments.given`):**
  ```json
  {
    "questions": [
      {"question": "2 + 2 = ?", "choices": ["3", "4", "5"], "answer": "4"},
      {"question": "Capital of France?", "choices": [], "answer": "Paris"}
    ]
  }
  ```
  Empty/omitted `choices` → free-text question. No question shuffling or
  random-subset selection (unlike `QuizController`) — kept deliberately
  simple; the full fixed list in `given` is what's shown, in that order.
- **Raw submission** (what the widget posts, pre-grading):
  `{"answers": {"0": "4", "1": "paris"}}` — index-keyed to `questions`.
- **Stored submission (`classworks.code`), post-grading:** an array of
  `{question, user_answer, correct_answer, is_correct}` — the exact same
  shape `QuizController::submit()` already stores, so the review UI
  (`widgets/quiz.php` in readonly mode) looks the same regardless of which
  flow produced it. `classworks.score` is set automatically.
- **View:** `application/views/widgets/quiz.php` — one file, three states:
  blank input, prefilled input (resuming a group draft via `Live_state_model`),
  and readonly graded review (correct/incorrect per question, shown
  immediately after submit per the "show results right away" decision).
- Student is redirected straight to `student_submission/{classwork_id}`
  after submitting (instead of the `classwork` list) specifically for this
  widget, so results are visible immediately — matches `quiz_result.php`'s
  existing behavior for the legacy flow.
