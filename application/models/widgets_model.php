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

    private function _add_column_if_missing($table, $column, $definition)
    {
        $exists = $this->db->query(
            "SELECT 1 FROM information_schema.COLUMNS
             WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? AND COLUMN_NAME = ?",
            [$table, $column]
        )->num_rows() > 0;

        if (!$exists) {
            $this->db->query("ALTER TABLE `$table` ADD COLUMN `$column` $definition");
        }
    }
}
