# Paperless Midterm Integration Plan

**Status:** Phase 1 implemented — widgets registry + Widget B (Worksheet Form) live; Widgets C–G not yet built
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

## 5. Full Session-to-Widget Mapping (Weeks 1–8)

| Session | Concept Portion | Hands-On Activity | Widget |
|---|---|---|---|
| 1.1 | Invention vs Innovation | Innovation Hunt Worksheet | Discussion + **B** |
| 1.2 | Innovation in Bohol | Ideation Mural (Maria) | Discussion + **D** |
| 2.1 | Why Inventions Fail | Innovation Triangle | Discussion + **E** |
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
- Only insert a `widgets` row for a widget once its `input_view` file
  actually exists, so the admin dropdown never offers a widget with no view
  behind it. Widget B (`worksheet`) is the only row today.

Widget D (Brainstorm Board) is still the exception — shared/live state
across a group/section rather than one row per student. A generic
`assessment_live_state` table (`application/models/live_state_model.php`,
keyed by `assessment_id` + optional `group_id`, `group_id = NULL` meaning
section-wide) already exists from the group-assessment-submission feature —
reuse it (`get_or_create($assessment_id, null)`) instead of introducing
another polling table when Widget D is built.

## 7. Build Roadmap

| Phase | What to Build | Why This Order |
|---|---|---|
| 1 | ~~Add `widget_type` column.~~ Add `widgets` registry table + `widget_id` column. Build Widget B. **(Done)** | Establishes the pattern; unlocks 4 sessions immediately. |
| 2 | Build Widget C. | Second most-used pattern (5 sessions); same architecture as Phase 1. |
| 3 | Build Widget G, and Widget F as a B enhancement. | Small, low-risk quick wins. |
| 4 | Build Widget E — fixed-flow mode only. | Covers 2.1 and 4.1. |
| 5 | Build Widget D — simple polling version. | Most complex; covers 1.2 fully. |
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
