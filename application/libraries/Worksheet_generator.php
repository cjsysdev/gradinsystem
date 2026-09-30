<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Prompt builders and response validators for the admin Worksheet Generator
 * (AdminContentController::worksheet_generate(), route admin/worksheet_generate).
 *
 * Pure logic: no database, no session, no CI dependencies — it turns a request
 * type plus a topic into an [system, user] prompt pair for Anthropic_client,
 * and checks the shape of whatever JSON comes back. The controller keeps the
 * HTTP concerns (reading input, calling the client, rendering the preview).
 *
 * The four supported types map to a prompt builder and a validator each:
 *   lab_worksheet        → the "lab_worksheet" classwork widget config
 *   worksheet_table      → the "worksheet" classwork widget config
 *   discussion           → an interactive-discussion topic file (assets/json/)
 *   quiz_from_worksheet  → a Bloom's Taxonomy quiz derived from source content
 */
class Worksheet_generator
{
    /**
     * [$system, $user] prompt pair for $type, or null if $type is unknown
     * (the caller reports that as a user-facing error).
     *
     * $params: ['course' => string, 'count' => int, 'duration' => string]
     * $source is only used by quiz_from_worksheet.
     */
    public function prompt($type, $topic, array $params, $source = '')
    {
        switch ($type) {
            case 'lab_worksheet':
                return $this->prompt_lab_worksheet($topic, $params);
            case 'worksheet_table':
                return $this->prompt_worksheet_table($topic, $params);
            case 'discussion':
                return $this->prompt_discussion($topic, $params);
            case 'quiz_from_worksheet':
                return $this->prompt_quiz_from_worksheet($topic, $source, $params);
        }
        return null;
    }

    /**
     * Null when $data has a usable shape for $type, otherwise a short reason
     * the caller shows next to the raw JSON. An unknown $type validates as OK
     * — prompt() has already rejected it before we ever get here.
     */
    public function validate($type, $data)
    {
        switch ($type) {
            case 'lab_worksheet':
                return $this->validate_lab_worksheet($data);
            case 'worksheet_table':
                return $this->validate_worksheet_table($data);
            case 'discussion':
                return $this->validate_discussion($data);
            case 'quiz_from_worksheet':
                return $this->validate_quiz($data);
        }
        return null;
    }

    public function strip_fences($text)
    {
        $text = trim($text);
        $text = preg_replace('/^```(?:json)?\s*/i', '', $text);
        $text = preg_replace('/\s*```$/', '', $text);
        return trim($text);
    }

    public function slug($topic)
    {
        $slug = strtolower(trim($topic));
        $slug = preg_replace('/[^a-z0-9]+/', '_', $slug);
        return trim($slug, '_') ?: 'topic';
    }

    private function prompt_lab_worksheet($topic, $params)
    {
        $count = $params['count'];
        $system = <<<SYS
You generate config JSON for the "lab_worksheet" classwork widget in a CodeIgniter LMS (Predict/Observe/Explain lab activities). Output ONLY raw JSON, no markdown fences, no commentary, matching EXACTLY this shape:

{
  "intro": "<p>optional HTML shown above the experiments (objectives, timeline, etc.)</p>",
  "experiments": [
    {
      "title": "Experiment 1.1 — short descriptive title",
      "instructions": "<p>...</p><pre><code>...</code></pre>",
      "warning": false,
      "hint": "optional nudge for the whole experiment",
      "prompts": [
        {"tag": "predict", "label": "PREDICT", "text": "What do you think will happen?", "hint": "optional nudge for this prompt"},
        {"tag": "observe", "label": "OBSERVE", "text": "What actually happened?"},
        {"tag": "explain", "label": "EXPLAIN", "text": "Why did that happen?"}
      ],
      "note": "optional short note shown after the prompts"
    }
  ],
  "exit_question": "optional single free-text question shown after all experiments",
  "exit_question_hint": "optional nudge for the exit question"
}

Rules:
- "instructions" is trusted HTML — use <p>, <pre><code>...</code></pre> for code snippets, <ul>/<li> as needed. Escape HTML entities inside <code> blocks (&lt; &gt; &amp;).
- Allowed "tag" values: predict, observe, explain, bonus. Most experiments use predict+observe+explain; use "bonus" sparingly for an optional stretch prompt.
- Set "warning": true only for an experiment that deliberately breaks something to illustrate a concept ("breaking it on purpose").
- "note" and "exit_question" are optional — omit the key entirely if not needed, do not use null.
- Every "hint" is optional and PLAIN TEXT (no HTML — it is escaped). It renders collapsed behind a small (?) the student taps, so it must nudge toward the reasoning, never state the answer. Add one only where a student can realistically get stuck; most prompts need none. Omit the key entirely otherwise.
- Order experiments so difficulty ramps up gradually.
SYS;
        $user = "Generate a lab worksheet (Predict/Observe/Explain, {$count} experiments) about: {$topic}."
            . ($params['course'] !== '' ? " Course context: {$params['course']}." : '')
            . ($params['duration'] !== '' ? " Target duration: {$params['duration']}." : '')
            . " Output raw JSON only.";
        return [$system, $user];
    }

    private function prompt_worksheet_table($topic, $params)
    {
        $count = $params['count'];
        $system = <<<SYS
You generate config JSON for the "worksheet" classwork widget in a CodeIgniter LMS — a repeatable-row table activity. Output ONLY raw JSON, no markdown fences, no commentary, matching EXACTLY this shape:

{
  "widget": "worksheet",
  "columns": ["Column A", "Column B", "Column C"],
  "min_rows": 5,
  "allow_add_rows": true
}

Rules:
- "columns" is a PLAIN ARRAY OF STRINGS (column headers) — NOT an array of objects with key/label/type.
- "min_rows" is an integer — how many empty rows the student starts with.
- "allow_add_rows" is a boolean — whether the student may add more rows beyond min_rows.
- Choose columns that fit a table-style activity for the given topic (comparison, categorization, timeline, etc.).
SYS;
        $user = "Generate a worksheet table (around {$count} suggested min_rows, 3-6 columns) about: {$topic}."
            . ($params['course'] !== '' ? " Course context: {$params['course']}." : '')
            . " Output raw JSON only.";
        return [$system, $user];
    }

    private function prompt_discussion($topic, $params)
    {
        $slug = $this->slug($topic);
        $count = max(3, $params['count']) ?: 10;
        $system = <<<SYS
You generate interactive-discussion topic JSON for a CodeIgniter LMS. Output ONLY raw JSON, no markdown fences, no commentary, matching EXACTLY this shape:

{
  "topic": "snake_case_slug",
  "title": "Human-readable title",
  "description": "One sentence description.",
  "congratsText": "Encouraging completion message.",
  "sections": [
    {
      "id": 0,
      "title": "Objectives",
      "quiz": null,
      "lesson": "<div class=\\"lesson-title\\">...</div><div class=\\"lesson-text\\">...</div>"
    },
    {
      "id": 1,
      "title": "Section title",
      "quiz": {
        "question": "A question about this section's content.",
        "code": "optional code snippet, HTML-entity-encoded",
        "options": ["Option A", "Option B", "Option C", "Option D"],
        "correct": 0
      },
      "lesson": "<div class=\\"lesson-title\\">...</div><div class=\\"lesson-text\\">...</div>"
    }
  ]
}

Rules:
- "id" is 0-based and increments by 1 per section.
- Section 0 is always "Objectives" (3 bullet points in the lesson HTML) with "quiz": null.
- The LAST section is always "Recap" with "quiz": null.
- All other (middle) sections MUST have a "quiz" object — never null for them.
- "correct" is a ZERO-BASED index into "options" for the correct choice.
- "code" inside "quiz" is optional — omit the key entirely if there is no code snippet; when present, HTML-encode angle brackets and quotes (&lt; &gt; &amp; &quot;).
- "lesson" is trusted HTML using ONLY these CSS classes where relevant: lesson-title, lesson-text, highlight, code-block, comparison, comparison-col, comparison-label.
- Generate exactly {$count} sections total (including Objectives and Recap).
SYS;
        $user = "Generate an interactive discussion topic (slug: {$slug}, {$count} sections) about: {$topic}."
            . ($params['course'] !== '' ? " Course context: {$params['course']}." : '')
            . " Output raw JSON only.";
        return [$system, $user];
    }

    private function prompt_quiz_from_worksheet($topic, $source, $params)
    {
        $slug = $this->slug($topic !== '' ? $topic : 'worksheet');
        $count = max(5, $params['count']) ?: 15;
        $system = <<<SYS
You generate a Bloom's Taxonomy multiple-choice quiz JSON derived from source content, for a CodeIgniter LMS. The source content may be an instructor's worksheet/activity config JSON, a plain description of an assessment, and/or anonymized excerpts of real student submissions for that assessment. Output ONLY raw JSON, no markdown fences, no commentary, matching EXACTLY this shape:

{
  "topic": "snake_case_slug",
  "title": "Quiz Title — Bloom's Taxonomy Quiz",
  "questions": [
    {
      "id": 1,
      "bloomLevel": "Remember",
      "question": "Question text.",
      "code": "optional code snippet",
      "choices": ["Choice A", "Choice B", "Choice C", "Choice D"],
      "answer": "Choice A",
      "topic": "snake_case_slug",
      "type": "multiple_choice"
    }
  ]
}

Rules:
- "id" starts at 1 and increments by 1.
- "bloomLevel" is one of: Remember, Understand, Apply, Analyze, Evaluate, Create. Distribute across levels, weighted toward Remember/Understand/Apply.
- "answer" MUST be the EXACT STRING of the correct choice (not an index).
- "code" is optional — omit the key entirely when not needed.
- Every question's content must be grounded in the source content given below — do not introduce unrelated topics.
- When the source includes "Sample student submissions", prefer questions that build on the concepts, patterns, or common mistakes actually visible in those submissions, so students recognize their own classwork in the quiz. Never reference or imply any specific student's identity — the submissions are anonymized and must stay that way in your output.
- Generate exactly {$count} questions.
SYS;
        $user = "Source content (base the quiz on this):\n\n{$source}\n\n"
            . "Generate a {$count}-question Bloom's Taxonomy quiz (slug: {$slug}) derived from the above."
            . ($topic !== '' ? " Focus/title hint: {$topic}." : '')
            . " Output raw JSON only.";
        return [$system, $user];
    }

    private function validate_lab_worksheet($data)
    {
        if (!is_array($data)) return 'not a JSON object';
        if (!isset($data['experiments']) || !is_array($data['experiments'])) return 'missing "experiments" array';
        foreach ($data['experiments'] as $i => $exp) {
            if (!is_array($exp)) return "experiments[{$i}] is not an object";
            if (empty($exp['title'])) return "experiments[{$i}] missing \"title\"";
            if (isset($exp['prompts']) && !is_array($exp['prompts'])) return "experiments[{$i}].prompts must be an array";
        }
        return null;
    }

    private function validate_worksheet_table($data)
    {
        if (!is_array($data)) return 'not a JSON object';
        if (!isset($data['columns']) || !is_array($data['columns'])) return 'missing "columns" array';
        foreach ($data['columns'] as $col) {
            if (!is_string($col)) return '"columns" must be an array of plain strings';
        }
        return null;
    }

    private function validate_discussion($data)
    {
        if (!is_array($data)) return 'not a JSON object';
        if (empty($data['topic']) || !is_string($data['topic'])) return 'missing "topic" slug';
        if (!isset($data['sections']) || !is_array($data['sections']) || empty($data['sections'])) return 'missing "sections" array';
        foreach ($data['sections'] as $i => $s) {
            if (!is_array($s)) return "sections[{$i}] is not an object";
            if (!array_key_exists('lesson', $s)) return "sections[{$i}] missing \"lesson\"";
            if (!array_key_exists('quiz', $s)) return "sections[{$i}] missing \"quiz\" key (use null if none)";
        }
        return null;
    }

    private function validate_quiz($data)
    {
        if (!is_array($data)) return 'not a JSON object';
        if (empty($data['topic']) || !is_string($data['topic'])) return 'missing "topic" slug';
        if (!isset($data['questions']) || !is_array($data['questions']) || empty($data['questions'])) return 'missing "questions" array';
        foreach ($data['questions'] as $i => $q) {
            if (!is_array($q)) return "questions[{$i}] is not an object";
            if (empty($q['question'])) return "questions[{$i}] missing \"question\"";
            if (!isset($q['choices']) || !is_array($q['choices'])) return "questions[{$i}] missing \"choices\" array";
            if (!array_key_exists('answer', $q)) return "questions[{$i}] missing \"answer\"";
            if (!in_array($q['answer'], $q['choices'], true)) return "questions[{$i}].answer does not exactly match one of its choices";
        }
        return null;
    }
}
