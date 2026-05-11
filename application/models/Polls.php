<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Polls extends CI_Model
{
    // ── Schema bootstrap ────────────────────────────────────────────────────

    public function install()
    {
        $sqls = [
            "CREATE TABLE IF NOT EXISTS `polls` (
              `poll_id`            INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
              `title`              VARCHAR(255) NOT NULL,
              `pin`                VARCHAR(8)   NOT NULL UNIQUE,
              `status`             ENUM('draft','active','closed') NOT NULL DEFAULT 'draft',
              `active_question_id` INT UNSIGNED DEFAULT NULL,
              `created_by`         VARCHAR(100) DEFAULT NULL,
              `created_at`         DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8",

            "CREATE TABLE IF NOT EXISTS `poll_questions` (
              `question_id`   INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
              `poll_id`       INT UNSIGNED NOT NULL,
              `question_text` TEXT NOT NULL,
              `sort_order`    TINYINT UNSIGNED NOT NULL DEFAULT 0,
              `show_results`  TINYINT(1) NOT NULL DEFAULT 0,
              FOREIGN KEY (`poll_id`) REFERENCES `polls`(`poll_id`) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8",

            "CREATE TABLE IF NOT EXISTS `poll_options` (
              `option_id`    INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
              `question_id`  INT UNSIGNED NOT NULL,
              `option_text`  VARCHAR(255) NOT NULL,
              `sort_order`   TINYINT UNSIGNED NOT NULL DEFAULT 0,
              FOREIGN KEY (`question_id`) REFERENCES `poll_questions`(`question_id`) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8",

            "CREATE TABLE IF NOT EXISTS `poll_responses` (
              `response_id`  INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
              `question_id`  INT UNSIGNED NOT NULL,
              `option_id`    INT UNSIGNED NOT NULL,
              `student_id`   VARCHAR(50) DEFAULT NULL,
              `answered_at`  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
              UNIQUE KEY `uq_student_question` (`question_id`, `student_id`),
              FOREIGN KEY (`question_id`) REFERENCES `poll_questions`(`question_id`) ON DELETE CASCADE,
              FOREIGN KEY (`option_id`)   REFERENCES `poll_options`(`option_id`)    ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8",
        ];

        foreach ($sqls as $sql) {
            $this->db->query($sql);
        }
    }

    // ── Poll CRUD ────────────────────────────────────────────────────────────

    public function create_poll($title, $created_by = null)
    {
        $pin = $this->_generate_pin();
        $this->db->insert('polls', [
            'title'      => $title,
            'pin'        => $pin,
            'created_by' => $created_by,
        ]);
        return ['poll_id' => $this->db->insert_id(), 'pin' => $pin];
    }

    public function get_all_polls()
    {
        return $this->db->order_by('created_at', 'DESC')->get('polls')->result_array();
    }

    public function get_poll($poll_id)
    {
        return $this->db->get_where('polls', ['poll_id' => $poll_id])->row_array();
    }

    public function get_poll_by_pin($pin)
    {
        return $this->db->get_where('polls', ['pin' => strtoupper($pin)])->row_array();
    }

    public function set_status($poll_id, $status)
    {
        $this->db->where('poll_id', $poll_id)->update('polls', ['status' => $status]);
    }

    public function delete_poll($poll_id)
    {
        $this->db->delete('polls', ['poll_id' => $poll_id]);
    }

    // ── Question management ──────────────────────────────────────────────────

    public function add_question($poll_id, $text, $sort_order = 0)
    {
        $this->db->insert('poll_questions', [
            'poll_id'       => $poll_id,
            'question_text' => $text,
            'sort_order'    => $sort_order,
        ]);
        return $this->db->insert_id();
    }

    public function get_questions($poll_id)
    {
        return $this->db->order_by('sort_order', 'ASC')
            ->get_where('poll_questions', ['poll_id' => $poll_id])
            ->result_array();
    }

    public function get_question($question_id)
    {
        return $this->db->get_where('poll_questions', ['question_id' => $question_id])->row_array();
    }

    public function toggle_show_results($question_id)
    {
        $q = $this->get_question($question_id);
        $new = $q['show_results'] ? 0 : 1;
        $this->db->where('question_id', $question_id)->update('poll_questions', ['show_results' => $new]);
        return $new;
    }

    // ── Option management ────────────────────────────────────────────────────

    public function add_option($question_id, $text, $sort_order = 0)
    {
        $this->db->insert('poll_options', [
            'question_id' => $question_id,
            'option_text' => $text,
            'sort_order'  => $sort_order,
        ]);
        return $this->db->insert_id();
    }

    public function get_options($question_id)
    {
        return $this->db->order_by('sort_order', 'ASC')
            ->get_where('poll_options', ['question_id' => $question_id])
            ->result_array();
    }

    // ── Active question ──────────────────────────────────────────────────────

    public function set_active_question($poll_id, $question_id)
    {
        $this->db->where('poll_id', $poll_id)->update('polls', [
            'active_question_id' => $question_id,
            'status'             => 'active',
        ]);
    }

    public function clear_active_question($poll_id)
    {
        $this->db->where('poll_id', $poll_id)->update('polls', ['active_question_id' => null]);
    }

    // ── Responses ────────────────────────────────────────────────────────────

    public function submit_response($question_id, $option_id, $student_id)
    {
        // INSERT IGNORE respects the unique key — no double votes
        $sql = "INSERT IGNORE INTO poll_responses (question_id, option_id, student_id)
                VALUES (?, ?, ?)";
        $this->db->query($sql, [$question_id, $option_id, $student_id]);
        return $this->db->affected_rows() > 0;
    }

    public function has_answered($question_id, $student_id)
    {
        return $this->db->get_where('poll_responses', [
            'question_id' => $question_id,
            'student_id'  => $student_id,
        ])->num_rows() > 0;
    }

    public function get_results($question_id)
    {
        $sql = "SELECT o.option_id, o.option_text, COUNT(r.response_id) AS votes
                FROM poll_options o
                LEFT JOIN poll_responses r
                       ON r.option_id = o.option_id AND r.question_id = o.question_id
                WHERE o.question_id = ?
                GROUP BY o.option_id
                ORDER BY o.sort_order ASC";
        return $this->db->query($sql, [$question_id])->result_array();
    }

    public function get_total_responses($question_id)
    {
        return $this->db->where('question_id', $question_id)->count_all_results('poll_responses');
    }

    // ── Helpers ──────────────────────────────────────────────────────────────

    private function _generate_pin()
    {
        do {
            $pin = strtoupper(substr(str_shuffle('ABCDEFGHJKLMNPQRSTUVWXYZ23456789'), 0, 6));
            $exists = $this->db->get_where('polls', ['pin' => $pin])->num_rows();
        } while ($exists);
        return $pin;
    }
}
