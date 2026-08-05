<?php
defined('BASEPATH') or exit('No direct script access allowed');

// Registry of reusable classwork "widgets" (Worksheet Form, Card Sort, etc. —
// see root/docs/paperless-midterm-plan.md). Adding a new widget later means
// "add a row + drop a view file", not editing a controller's if/else chain.
class Widgets_model extends CI_Model
{
    public function install()
    {
        $this->db->query("CREATE TABLE IF NOT EXISTS `widgets` (
            `widget_id`         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            `widget_key`        VARCHAR(32) NOT NULL,
            `name`              VARCHAR(64) NOT NULL,
            `input_view`        VARCHAR(128) NOT NULL,
            `admin_config_view` VARCHAR(128) DEFAULT NULL,
            `created_at`        DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            UNIQUE KEY `uq_widget_key` (`widget_key`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

        // assessments.given is referenced by CLAUDE.md/the paperless-midterm plan
        // as "currently unused" — but it does not actually exist in the live
        // schema yet (verified against the current DB dump), so it's added here
        // alongside widget_id rather than assumed to pre-exist.
        // Note: "ADD COLUMN IF NOT EXISTS" is a MariaDB-only extension — real
        // MySQL rejects it as a syntax error (silently, since db_debug=FALSE),
        // so existence is checked via information_schema instead, which works
        // on both.
        $this->_add_column_if_missing('assessments', 'given', 'LONGTEXT DEFAULT NULL');
        $this->_add_column_if_missing('assessments', 'widget_id', 'INT UNSIGNED DEFAULT NULL');

        // Tab-switch count for the Timed/Secure Quiz widget. secure_quiz_view.php
        // has always counted window blurs and posted them as `blur_count`, but no
        // controller read it, so the number was discarded at submit — this column
        // is where SecureQuizController::submit() now parks it.
        // Nullable with no default on purpose: NULL means "not recorded" (every
        // pre-existing row, and every non-secure-quiz widget), which is a
        // different fact from 0 = "recorded, never left the tab". Adding a
        // nullable column touches no existing row's data; readers must still
        // tolerate the column being absent until an admin runs
        // WidgetsController/install (see classworks::has_switch_count()).
        $this->_add_column_if_missing('classworks', 'switch_count', 'SMALLINT UNSIGNED DEFAULT NULL');

        // widget_key rows get added when their input_view actually exists, so
        // the admin dropdown never offers a widget with no view behind it.
        $this->db->query("INSERT IGNORE INTO widgets (widget_key, name, input_view, admin_config_view)
            VALUES ('worksheet', 'Worksheet Form', 'widgets/worksheet', NULL)");
        $this->db->query("INSERT IGNORE INTO widgets (widget_key, name, input_view, admin_config_view)
            VALUES ('quiz', 'Multiple Choice Quiz', 'widgets/quiz', NULL)");
        $this->db->query("INSERT IGNORE INTO widgets (widget_key, name, input_view, admin_config_view)
            VALUES ('card_sort', 'Card Sort Board', 'widgets/card_sort', NULL)");
        $this->db->query("INSERT IGNORE INTO widgets (widget_key, name, input_view, admin_config_view)
            VALUES ('diagram', 'Diagram / Flow Builder', 'widgets/diagram', NULL)");
        $this->db->query("INSERT IGNORE INTO widgets (widget_key, name, input_view, admin_config_view)
            VALUES ('decision_matrix', 'Decision Matrix', 'widgets/decision_matrix', NULL)");
        $this->db->query("INSERT IGNORE INTO widgets (widget_key, name, input_view, admin_config_view)
            VALUES ('calculator', 'Calculator', 'widgets/calculator', NULL)");
        // Lab Worksheet: fixed sequence of experiments, each with admin-authored
        // instructions and Predict/Observe/Explain-style prompts. Not auto-graded
        // (manual score entry, like Worksheet Form/Card Sort).
        $this->db->query("INSERT IGNORE INTO widgets (widget_key, name, input_view, admin_config_view)
            VALUES ('lab_worksheet', 'Lab Worksheet', 'widgets/lab_worksheet', NULL)");
        // Brainstorm Board is not a per-student submission — see BrainstormController.
        $this->db->query("INSERT IGNORE INTO widgets (widget_key, name, input_view, admin_config_view)
            VALUES ('brainstorm', 'Brainstorm & Voting Board', 'widgets/brainstorm', NULL)");
        // Interactive Discussion/Quiz wraps an existing assets/json/{topic}.json
        // lesson+quiz topic (see InteractiveQuizController) as a gradable
        // assessment. Like Brainstorm, it's not a per-student form — see the
        // redirect in AssessmentController::assessment_view_code().
        $this->db->query("INSERT IGNORE INTO widgets (widget_key, name, input_view, admin_config_view)
            VALUES ('iq_discussion', 'Interactive Discussion/Quiz', 'widgets/iq_discussion', NULL)");
        // Microlearning Quiz: same "wrap an assets/json topic file" idea as
        // iq_discussion, but for the denser Sololearn-style schema — each
        // section is a run of 1-2 sentence chunks with a 2-option micro-check
        // after every one, closed by a checkpoint that rotates between
        // mcq/arrange/type, plus objectives and recap screens. Rendered by
        // discussions/_interactive_micro_template.php via
        // InteractiveQuizController::micro(); like iq_discussion it's not a
        // per-student form — see the redirect in
        // AssessmentController::assessment_view_code(). Scored 1 point per
        // micro-check + 1 per checkpoint (max_score derived server-side).
        $this->db->query("INSERT IGNORE INTO widgets (widget_key, name, input_view, admin_config_view)
            VALUES ('iq_micro', 'Microlearning Quiz', 'widgets/iq_micro', NULL)");
        // Case Study Worksheet: narrative "story" panel (stat cards) + a fixed
        // sequence of sections holding heterogeneous questions (text/list/
        // choice-with-rationale/toggle-grid) — for case-study-driven activities
        // like "Meet Maria the calamansi farmer". Not auto-graded, same
        // manual-score-entry pattern as Worksheet Form/Lab Worksheet.
        $this->db->query("INSERT IGNORE INTO widgets (widget_key, name, input_view, admin_config_view)
            VALUES ('case_study', 'Case Study Worksheet', 'widgets/case_study', NULL)");
        // Case Dossier Rating: hook question -> read-only framework explainer
        // -> multiple parallel case dossiers, each rated 1-5 per factor with a
        // cited-evidence text field -> reflection questions. Not auto-graded,
        // same manual-score-entry pattern as the other worksheet-style widgets.
        $this->db->query("INSERT IGNORE INTO widgets (widget_key, name, input_view, admin_config_view)
            VALUES ('case_dossier', 'Case Dossier Rating', 'widgets/case_dossier', NULL)");
        // Timed/Secure Quiz: same {question, choices, answer} config/grading as
        // the 'quiz' widget above, but students take it in a dedicated
        // fullscreen/timer/tab-switch-lockdown page (SecureQuizController)
        // instead of an inline card form — see the redirect in
        // AssessmentController::assessment_view_code().
        $this->db->query("INSERT IGNORE INTO widgets (widget_key, name, input_view, admin_config_view)
            VALUES ('secure_quiz', 'Timed/Secure Quiz', 'widgets/secure_quiz', NULL)");
        // Chapter Worksheet: read-only timed-move table + "the model" worked
        // example -> a fixed sequence of typed steps (text/grid/choice/
        // checklist) -> read-only "the trap" warning -> peer-check question
        // -> team/date/filed/peer-checked-by sign-off. Built for the
        // Feasibility Study Worksheet Pack (10x45min dossier-chapter
        // worksheets) but reusable for any worked-model-then-steps worksheet.
        // Not auto-graded, same manual-score-entry pattern as the other
        // worksheet-style widgets.
        $this->db->query("INSERT IGNORE INTO widgets (widget_key, name, input_view, admin_config_view)
            VALUES ('chapter_worksheet', 'Chapter Worksheet', 'widgets/chapter_worksheet', NULL)");
        // Project Proposal: title/type/client/problem header fields + a
        // repeatable features table where each planned feature is tagged
        // with the CRUD operation it implements. Built for term-project
        // proposals (a CRUD system in C or Web). Not auto-graded, same
        // manual-score-entry pattern as Worksheet Form/Case Study Worksheet.
        $this->db->query("INSERT IGNORE INTO widgets (widget_key, name, input_view, admin_config_view)
            VALUES ('project_proposal', 'Project Proposal', 'widgets/project_proposal', NULL)");
    }

    public function get_all()
    {
        return $this->db->order_by('name')->get('widgets')->result_array();
    }

    public function get($widget_id)
    {
        return $this->db->where('widget_id', $widget_id)->get('widgets')->row_array();
    }

    // Server-side grading for the quiz widget — never trust a client-computed
    // score. $answers is index-keyed to $config['questions'] (same shape the
    // widget's getWidgetState() produces: {"answers": {"0": "...", ...}}).
    // Mirrors QuizController::submit()'s comparison logic so results look the
    // same whether an assessment uses the old json_file_path quiz or this widget.
    // Accepts either the canonical {"questions":[...]} object or a bare list of
    // question objects [ {...}, {...} ] (the legacy QuizController json-file
    // shape, and the shape people naturally paste), so a config given in either
    // form grades/renders identically. Used by grade_quiz(), SecureQuizController,
    // and the quiz/secure_quiz widget views.
    public function quiz_questions($config)
    {
        if (!is_array($config)) return [];
        if (array_key_exists('questions', $config)) {
            return is_array($config['questions']) ? $config['questions'] : [];
        }
        return $config === array_values($config) ? $config : []; // bare list only
    }

    // "Progress" readout (answered/total) for worksheet-style widgets, mirroring
    // each widget's own client-side updateProgress() bar exactly — same unit,
    // same total — so an instructor sees the same figure the student saw while
    // filling it in. Only implemented for widgets that actually have a progress
    // bar in their own view (lab_worksheet, case_study, case_dossier,
    // chapter_worksheet); other widget_keys (including worksheet/decision_matrix,
    // which have no "answered" concept of their own, and quiz/secure_quiz, which
    // store graded results rather than raw answers) return null so callers
    // render no indicator. $config = decoded assessments.given; $answers =
    // decoded classworks.code, a live_draft array, or [] / null (never opened).
    public function submission_progress($widget_key, $config, $answers)
    {
        $config  = is_array($config) ? $config : [];
        $answers = is_array($answers) ? $answers : [];

        switch ($widget_key) {
            case 'lab_worksheet':
                return $this->_progress_lab_worksheet($config, $answers);
            case 'case_study':
                return $this->_progress_case_study($config, $answers);
            case 'case_dossier':
                return $this->_progress_case_dossier($config, $answers);
            case 'chapter_worksheet':
                return $this->_progress_chapter_worksheet($config, $answers);
            default:
                return null;
        }
    }

    // Mirrors widgets/lab_worksheet.php's updateProgress(): one unit per
    // experiment (answered only when every one of its prompt fields is
    // non-blank, and it has at least one prompt) plus one unit for the exit
    // question if the config defines one.
    private function _progress_lab_worksheet($config, $answers)
    {
        $experiments = $config['experiments'] ?? [];
        $exit_q      = $config['exit_question'] ?? '';
        $exp_answers = $answers['answers'] ?? [];

        $total = 0;
        $done  = 0;

        foreach ($experiments as $i => $exp) {
            $total++;
            $prompts = $exp['prompts'] ?? [];
            if (empty($prompts)) continue;

            $exp_ans     = $exp_answers[$i] ?? [];
            $all_filled  = true;
            foreach ($prompts as $p) {
                $tag = $p['tag'] ?? 'predict';
                $val = $exp_ans[$tag] ?? '';
                if (trim((string) $val) === '') { $all_filled = false; break; }
            }
            if ($all_filled) $done++;
        }

        if ($exit_q !== '') {
            $total++;
            if (trim((string) ($answers['exit_question'] ?? '')) !== '') $done++;
        }

        return ['total' => $total, 'answered' => $done, 'empty' => $total - $done];
    }

    // Mirrors widgets/case_study.php's updateProgress(): one unit per question
    // across every section, tested per the question's own type.
    private function _progress_case_study($config, $answers)
    {
        $sections = $config['sections'] ?? [];
        $qa       = $answers['answers'] ?? [];

        $total = 0;
        $done  = 0;
        $idx   = 0;

        foreach ($sections as $section) {
            foreach ($section['questions'] ?? [] as $q) {
                $total++;
                if ($this->_question_answered($q['type'] ?? 'text', $qa[$idx] ?? null)) $done++;
                $idx++;
            }
        }

        return ['total' => $total, 'answered' => $done, 'empty' => $total - $done];
    }

    // Mirrors widgets/case_dossier.php's updateProgress(): hook questions +
    // each group's per-factor rating (answered when a 1-5 score is picked,
    // matching the bar's .cd-rate-btn.picked check) + reflection questions.
    private function _progress_case_dossier($config, $answers)
    {
        $hook       = $config['hook'] ?? [];
        $groups     = $config['groups'] ?? [];
        $reflection = $config['reflection'] ?? [];

        $hook_answers       = $answers['hook_answers'] ?? [];
        $group_ratings      = $answers['group_ratings'] ?? [];
        $reflection_answers = $answers['reflection_answers'] ?? [];

        $total = 0;
        $done  = 0;

        foreach ($hook['questions'] ?? [] as $qi => $q) {
            $total++;
            if ($this->_question_answered($q['type'] ?? 'text', $hook_answers[$qi] ?? null)) $done++;
        }

        foreach ($groups as $gi => $group) {
            $ratings = $group_ratings[$gi] ?? [];
            foreach ($group['factors'] ?? [] as $fi => $factor) {
                $total++;
                $score = $ratings[$fi]['score'] ?? null;
                if (is_numeric($score)) $done++;
            }
        }

        foreach ($reflection['questions'] ?? [] as $qi => $q) {
            $total++;
            if ($this->_question_answered($q['type'] ?? 'text', $reflection_answers[$qi] ?? null)) $done++;
        }

        return ['total' => $total, 'answered' => $done, 'empty' => $total - $done];
    }

    // Shared text/list/choice "is this answered?" test, matching the identical
    // logic duplicated in case_study.php's and case_dossier.php's updateProgress().
    private function _question_answered($type, $value)
    {
        if ($type === 'text') {
            return is_string($value) && trim($value) !== '';
        }
        if ($type === 'list') {
            if (!is_array($value)) return false;
            foreach ($value as $line) {
                if (trim((string) $line) !== '') return true;
            }
            return false;
        }
        if ($type === 'choice') {
            return is_numeric($value);
        }
        if ($type === 'toggle_grid') {
            return is_array($value) && count($value) > 0;
        }
        return false;
    }

    // Mirrors widgets/chapter_worksheet.php's updateProgress(): one unit per
    // text/choice/checklist step, one unit per grid row, plus one unit for
    // peer_check if the config defines it. file_it is intentionally excluded —
    // the widget's own progress bar doesn't count it either.
    private function _progress_chapter_worksheet($config, $answers)
    {
        $steps      = $config['steps'] ?? [];
        $peer_check = $config['peer_check'] ?? [];
        $step_ans   = $answers['steps'] ?? [];

        $total = 0;
        $done  = 0;

        foreach ($steps as $si => $step) {
            $type   = $step['type'] ?? 'text';
            $answer = $step_ans[$si] ?? null;

            if ($type === 'text') {
                $total++;
                if (is_string($answer) && trim($answer) !== '') $done++;
            } elseif ($type === 'grid') {
                $columns   = $step['columns'] ?? [];
                $grid_rows = $step['rows'] ?? [];
                $grid_val  = is_array($answer) ? $answer : [];
                foreach ($grid_rows as $row) {
                    $total++;
                    $row_label = $row['label'] ?? '';
                    $row_vals  = $grid_val[$row_label] ?? [];
                    $filled    = false;
                    foreach ($columns as $ci => $col) {
                        $cval  = $row_vals[$ci] ?? null;
                        $ctype = $col['type'] ?? 'text';
                        $cell_filled = $ctype === 'checkbox' ? !empty($cval) : trim((string) $cval) !== '';
                        if ($cell_filled) { $filled = true; break; }
                    }
                    if ($filled) $done++;
                }
            } elseif ($type === 'choice') {
                $total++;
                if (is_numeric($answer)) $done++;
            } elseif ($type === 'checklist') {
                $total++;
                if (is_array($answer)) {
                    foreach ($answer as $checked) {
                        if (!empty($checked)) { $done++; break; }
                    }
                }
            }
        }

        if (!empty($peer_check)) {
            $total++;
            if (trim((string) ($answers['peer_check'] ?? '')) !== '') $done++;
        }

        return ['total' => $total, 'answered' => $done, 'empty' => $total - $done];
    }

    public function grade_quiz($config, $answers)
    {
        $questions = $this->quiz_questions($config);
        $score = 0;
        $results = [];

        foreach ($questions as $i => $q) {
            $user_answer = $answers[$i] ?? 'No answer';
            $choices = array_filter($q['choices'] ?? [], function ($c) { return trim($c) !== ''; });

            if (!empty($choices)) {
                $is_correct = trim((string) $user_answer) === trim((string) $q['answer']);
            } else {
                $is_correct = mb_strtolower(trim((string) $user_answer)) === mb_strtolower(trim((string) $q['answer']));
            }

            if ($is_correct) $score++;

            $results[] = [
                'question'       => $q['question'],
                'user_answer'    => $user_answer,
                'correct_answer' => $q['answer'],
                'is_correct'     => $is_correct,
            ];
        }

        return ['score' => $score, 'results' => $results];
    }

    // Class-wide item analysis over a set of quiz submissions — "which questions
    // is everyone getting wrong, and what are they picking instead". Lives here
    // next to grade_quiz() because this is the model that owns the quiz result
    // shape; keeping the reader beside the writer is what stops the two drifting.
    // Serves both quiz widgets (`quiz` and `secure_quiz`) — grade_quiz() is the
    // single writer for both, so the stored blobs are byte-identical in shape.
    //
    // $result_lists: array of decoded classworks.code blobs, each a grade_quiz()
    //                results array [{question,user_answer,correct_answer,is_correct}].
    // $config:       decoded assessments.given, used only to order items by their
    //                position in the current bank and to spot bank drift.
    //
    // Aggregation is keyed on the TRIMMED QUESTION TEXT, which looks crude but is
    // the only stable identifier available: the config carries no question ids,
    // and SecureQuizController::index() shuffles the bank and slices it to
    // max_score per student, then destroys the drawn set at submit. Array
    // position therefore means nothing across two submissions, and an item's
    // denominator is "how many submissions contained it", never "how many
    // students sat the quiz".
    //
    // The stored is_correct is trusted as-is and never recomputed against the
    // current config: it was graded server-side at submit time, and re-deriving
    // it would silently rewrite history whenever an instructor edits the bank
    // afterwards, as well as disagree with the already-recorded classworks.score.
    //
    // Note this is descriptive statistics over booleans, not grading — no
    // transmutation, no weighting, no score is produced or written. Grade
    // arithmetic stays in Grade_calculator (see CLAUDE.md).
    public function quiz_item_stats(array $result_lists, $config = [])
    {
        $bank = $this->quiz_questions($config);

        // Bank order, so items an instructor recognises stay findable, and so
        // questions never drawn can be reported separately.
        $bank_index = [];
        foreach ($bank as $i => $q) {
            $key = trim((string) ($q['question'] ?? ''));
            // Two bank entries with identical text collapse into one row; first
            // occurrence wins the index.
            if ($key !== '' && !isset($bank_index[$key])) $bank_index[$key] = $i;
        }

        $items       = [];
        $submissions = 0;
        $score_dist  = [];

        foreach ($result_lists as $results) {
            if (!is_array($results)) continue; // null / malformed code column
            $submissions++;
            $student_correct = 0;

            foreach ($results as $r) {
                if (!is_array($r)) continue;
                $key = trim((string) ($r['question'] ?? ''));
                if ($key === '') continue;

                if (!isset($items[$key])) {
                    $items[$key] = [
                        'question'       => $key,
                        'correct_answer' => (string) ($r['correct_answer'] ?? ''),
                        'bank_index'     => $bank_index[$key] ?? null,
                        'shown'          => 0,
                        'correct'        => 0,
                        'wrong'          => 0,
                        'no_answer'      => 0,
                        'answers'        => [],
                    ];
                }

                $answer     = (string) ($r['user_answer'] ?? '');
                $is_correct = !empty($r['is_correct']);
                // 'No answer' is the literal sentinel grade_quiz() writes for an
                // item the student never touched. On a timed quiz "ran out of
                // time" and "picked the wrong option" are different diagnoses,
                // so it counts as a miss but is kept out of the wrong/distractor
                // tallies.
                $skipped = ($answer === 'No answer');

                $items[$key]['shown']++;
                if ($is_correct)      $items[$key]['correct']++;
                elseif ($skipped)     $items[$key]['no_answer']++;
                else                  $items[$key]['wrong']++;

                if (!$skipped) {
                    if (!isset($items[$key]['answers'][$answer])) {
                        $items[$key]['answers'][$answer] = ['answer' => $answer, 'count' => 0, 'is_correct' => $is_correct];
                    }
                    $items[$key]['answers'][$answer]['count']++;
                }

                if ($is_correct) $student_correct++;
            }

            $score_dist[$student_correct] = ($score_dist[$student_correct] ?? 0) + 1;
        }

        $total_answers = 0;
        $total_correct = 0;

        foreach ($items as $key => $item) {
            $shown = $item['shown'];
            $items[$key]['miss_rate'] = $shown > 0
                ? round((($shown - $item['correct']) / $shown) * 100, 1)
                : 0.0;

            // Distractors, most-picked first — this is the "what are they
            // choosing instead" readout.
            $answers = array_values($item['answers']);
            usort($answers, function ($a, $b) { return $b['count'] <=> $a['count']; });
            foreach ($answers as $i => $a) {
                $answers[$i]['pct'] = $shown > 0 ? round(($a['count'] / $shown) * 100, 1) : 0.0;
            }
            $items[$key]['answers'] = $answers;

            $total_answers += $shown;
            $total_correct += $item['correct'];
        }

        // Worst first — the whole point of the page.
        $items = array_values($items);
        usort($items, function ($a, $b) {
            if ($a['miss_rate'] === $b['miss_rate']) return $b['shown'] <=> $a['shown'];
            return $b['miss_rate'] <=> $a['miss_rate'];
        });

        // Bank questions that no submission ever contained. Expected and normal
        // when the bank is bigger than max_score, since each student is served a
        // random slice — but worth showing, because an item with no data is not
        // the same as an item everybody got right.
        $seen        = array_column($items, 'question');
        $never_shown = [];
        foreach ($bank as $i => $q) {
            $key = trim((string) ($q['question'] ?? ''));
            if ($key !== '' && !in_array($key, $seen, true)) {
                $never_shown[] = ['bank_index' => $i, 'question' => $key];
            }
        }

        ksort($score_dist);

        return [
            'submission_count' => $submissions,
            'bank_count'       => count($bank),
            'items'            => $items,
            'never_shown'      => $never_shown,
            'score_dist'       => $score_dist,
            'totals'           => [
                'answers'  => $total_answers,
                'correct'  => $total_correct,
                'accuracy' => $total_answers > 0 ? round(($total_correct / $total_answers) * 100, 1) : 0.0,
            ],
        ];
    }

    // Per-student score ranking over the same submissions quiz_item_stats()
    // aggregates anonymously — "who topped this quiz, and who needs a second
    // look". Sits beside it (and grade_quiz()) for the same reason: this reads
    // the result shape that model writes, and split files drift.
    //
    // $submissions: rows from classworks::get_all_submissions(), optionally with
    //               a 'section' label added by the caller when several sections
    //               of one master are pooled.
    //
    // Returns the WHOLE cohort ranked, not a top-N slice — the instructor wants
    // to find one named student as often as to see who topped the quiz, and a
    // 51-row table is cheap.
    //
    // Ranking is on the RECORDED classworks.score, not on a recount of the
    // stored results: the score is what the student's grade is actually built
    // from, and an instructor may have adjusted it. Only when the score is NULL
    // (never graded — shouldn't happen for the auto-graded quiz widgets, but
    // possible on a hand-made row) does it fall back to counting is_correct,
    // and that entry is flagged so the view can say so.
    //
    // Descriptive only — no transmutation, no weighting, nothing written. Grade
    // arithmetic stays in Grade_calculator (see CLAUDE.md).
    public function quiz_score_ranking(array $submissions)
    {
        $entries = [];

        foreach ($submissions as $row) {
            $results = json_decode($row['code'] ?? '', true);
            if (!is_array($results)) $results = [];

            $correct    = 0;
            $unanswered = 0;
            foreach ($results as $r) {
                if (!is_array($r)) continue;
                if (!empty($r['is_correct']))                            $correct++;
                elseif (($r['user_answer'] ?? '') === 'No answer')       $unanswered++;
            }

            $graded = isset($row['score']) && $row['score'] !== null && $row['score'] !== '';
            $score  = $graded ? (float) $row['score'] : (float) $correct;
            $max    = (float) ($row['max_score'] ?? 0);

            $entries[] = [
                'name'         => trim(($row['lastname'] ?? '') . ', ' . ($row['firstname'] ?? '')),
                'section'      => $row['section'] ?? null,
                'score'        => $score,
                'max_score'    => $max,
                'percent'      => $max > 0 ? round(($score / $max) * 100, 1) : null,
                'correct'      => $correct,
                'items'        => count($results),
                'unanswered'   => $unanswered,
                'graded'       => $graded,
                'switch_count' => isset($row['switch_count']) && $row['switch_count'] !== null
                    ? (int) $row['switch_count'] : null,
            ];
        }

        if (!$entries) {
            return [
                'count'     => 0,
                'students'  => [],
                'average'   => 0.0, 'median' => 0.0, 'highest' => 0.0, 'lowest' => 0.0,
                'max_score' => 0.0,
            ];
        }

        // Highest first. Ties break on fewer unanswered items then name, so the
        // order is stable between page loads instead of following row order.
        usort($entries, function ($a, $b) {
            if ($a['score'] != $b['score'])           return $b['score'] <=> $a['score'];
            if ($a['unanswered'] != $b['unanswered']) return $a['unanswered'] <=> $b['unanswered'];
            return strcasecmp($a['name'], $b['name']);
        });

        $count = count($entries);

        // Competition ranking: equal scores share a rank and the next one skips
        // (1, 2, 2, 4). Sequential numbering would claim an order between two
        // students who scored exactly the same, which the data doesn't support.
        // 'tied' lets the view mark a shared rank instead of looking like a bug.
        $rank = 0;
        foreach ($entries as $i => $e) {
            $same = $i > 0 && $e['score'] == $entries[$i - 1]['score'];
            if (!$same) $rank = $i + 1;
            $entries[$i]['rank'] = $rank;
            $entries[$i]['tied'] = $same
                || ($i + 1 < $count && $e['score'] == $entries[$i + 1]['score']);
        }

        $scores = array_column($entries, 'score');
        sort($scores);
        $mid = (int) floor($count / 2);

        return [
            'count'     => $count,
            'students'  => $entries,
            'average'   => round(array_sum($scores) / $count, 2),
            'median'    => $count % 2 ? $scores[$mid] : round(($scores[$mid - 1] + $scores[$mid]) / 2, 2),
            'highest'   => $scores[$count - 1],
            'lowest'    => $scores[0],
            'max_score' => (float) $entries[0]['max_score'],
        ];
    }

    private function _add_column_if_missing($table, $column, $definition)
    {
        $exists = $this->db->query(
            "SELECT 1 FROM information_schema.COLUMNS
             WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? AND COLUMN_NAME = ?",
            [$table, $column]
        )->num_rows() > 0;

        if ($exists) {
            return true;
        }

        // db_debug is FALSE, so a failed ALTER returns quietly and install()
        // would still report success — the exact failure mode that lost 464
        // group memberships and led to Schema_guard (see that library's header).
        // Log it loudly instead.
        $result = $this->db->query("ALTER TABLE `$table` ADD COLUMN `$column` $definition");
        $error  = $this->db->error();

        if ($result === false || !empty($error['code'])) {
            log_message('error', sprintf(
                'Widgets_model::install() could not add `%s`.`%s` [%s] %s',
                $table,
                $column,
                isset($error['code']) ? $error['code'] : '?',
                isset($error['message']) ? $error['message'] : 'unknown error'
            ));
            return false;
        }

        return true;
    }
}
