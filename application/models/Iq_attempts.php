<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Iq_attempts extends CI_Model
{
    protected $table = 'iq_attempts';

    public function record($data)
    {
        $this->db->insert($this->table, $data);
        return $this->db->affected_rows() > 0;
    }

    // Overall accuracy stats for one topic
    public function topic_summary($topic)
    {
        $row = $this->db->query(
            "SELECT COUNT(*) as total,
                    COUNT(DISTINCT student_id) as students,
                    ROUND(SUM(is_correct) / COUNT(*) * 100, 1) as accuracy
             FROM {$this->table}
             WHERE topic = ?",
            [$topic]
        )->row_array();

        return $row ?: ['total' => 0, 'students' => 0, 'accuracy' => 0];
    }

    // Per-section accuracy (ordered by section_index)
    public function section_stats($topic)
    {
        return $this->db->query(
            "SELECT section_index,
                    section_title,
                    COUNT(*) as total,
                    SUM(is_correct) as correct,
                    ROUND(SUM(is_correct) / COUNT(*) * 100, 1) as accuracy
             FROM {$this->table}
             WHERE topic = ?
             GROUP BY section_index, section_title
             ORDER BY section_index ASC",
            [$topic]
        )->result_array();
    }

    // Top missed questions (highest miss rate first)
    public function missed_questions($topic, $limit = 10)
    {
        return $this->db->query(
            "SELECT section_title,
                    question_index,
                    question_text,
                    COUNT(*) as total,
                    SUM(is_correct) as correct,
                    ROUND((1 - SUM(is_correct) / COUNT(*)) * 100, 1) as miss_rate
             FROM {$this->table}
             WHERE topic = ?
             GROUP BY section_index, question_index, question_text
             ORDER BY miss_rate DESC
             LIMIT ?",
            [$topic, (int) $limit]
        )->result_array();
    }

    // List of distinct topics that have attempt data
    public function topics_with_data()
    {
        return $this->db->query(
            "SELECT topic, COUNT(*) as total, COUNT(DISTINCT student_id) as students
             FROM {$this->table}
             GROUP BY topic
             ORDER BY topic ASC"
        )->result_array();
    }

    // Per-section/question count of how many students chose each option text
    public function choice_distribution_by_topic($topic)
    {
        $rows = $this->db->query(
            "SELECT section_index, question_index, chosen_option, COUNT(*) as cnt
             FROM {$this->table}
             WHERE topic = ? AND chosen_option IS NOT NULL AND chosen_option != ''
             GROUP BY section_index, question_index, chosen_option",
            [$topic]
        )->result_array();

        $dist = [];
        foreach ($rows as $r) {
            $dist[(int)$r['section_index']][(int)$r['question_index']][$r['chosen_option']] = (int)$r['cnt'];
        }
        return $dist;
    }

    public function ensure_table()
    {
        $this->db->query("
            CREATE TABLE IF NOT EXISTS iq_attempts (
                id             INT AUTO_INCREMENT PRIMARY KEY,
                student_id     VARCHAR(50)  NOT NULL,
                topic          VARCHAR(100) NOT NULL,
                section_index  INT          NOT NULL,
                section_title  VARCHAR(255),
                question_index INT          NOT NULL,
                question_text  TEXT,
                is_correct     TINYINT(1)   NOT NULL DEFAULT 0,
                chosen_option  VARCHAR(500) NULL,
                created_at     TIMESTAMP    DEFAULT CURRENT_TIMESTAMP,
                INDEX idx_topic   (topic),
                INDEX idx_student (student_id),
                INDEX idx_ts      (topic, section_index)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
        ");

        // Add chosen_option to tables created before this column existed
        $this->db->query("
            ALTER TABLE {$this->table}
            ADD COLUMN IF NOT EXISTS chosen_option VARCHAR(500) NULL
        ");
    }
}
