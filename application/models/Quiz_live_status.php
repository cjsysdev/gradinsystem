<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Quiz_live_status extends CI_Model
{
    protected $table = 'quiz_live_status';

    public function ensure_table()
    {
        $this->db->query("
            CREATE TABLE IF NOT EXISTS {$this->table} (
                id             INT AUTO_INCREMENT PRIMARY KEY,
                assessment_id  INT NOT NULL,
                student_id     INT NOT NULL,
                items_answered INT NOT NULL DEFAULT 0,
                total_items    INT NOT NULL DEFAULT 0,
                blur_count     INT NOT NULL DEFAULT 0,
                status         ENUM('not_started','answering','submitted') NOT NULL DEFAULT 'not_started',
                score          DECIMAL(10,2) NULL,
                started_at     DATETIME NULL,
                last_heartbeat TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                submitted_at   DATETIME NULL,
                UNIQUE KEY uq_student_assessment (assessment_id, student_id),
                INDEX idx_assessment (assessment_id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
        ");
    }

    public function init_student($assessment_id, $student_id, $total_items)
    {
        $this->db->query("
            INSERT INTO {$this->table}
                (assessment_id, student_id, total_items, status, started_at)
            VALUES (?, ?, ?, 'answering', NOW())
            ON DUPLICATE KEY UPDATE
                total_items    = VALUES(total_items),
                status         = IF(status = 'submitted', 'submitted', 'answering'),
                started_at     = IF(started_at IS NULL, NOW(), started_at),
                last_heartbeat = CURRENT_TIMESTAMP
        ", [(int)$assessment_id, (int)$student_id, (int)$total_items]);
    }

    public function heartbeat($assessment_id, $student_id, $items_answered, $blur_count, $total_items = 0)
    {
        $this->db->query("
            INSERT INTO {$this->table}
                (assessment_id, student_id, items_answered, total_items, blur_count, status, started_at)
            VALUES (?, ?, ?, ?, ?, 'answering', NOW())
            ON DUPLICATE KEY UPDATE
                items_answered = VALUES(items_answered),
                blur_count     = VALUES(blur_count),
                total_items    = IF(total_items = 0, VALUES(total_items), total_items),
                status         = IF(status = 'submitted', 'submitted', 'answering'),
                started_at     = IF(started_at IS NULL, NOW(), started_at),
                last_heartbeat = CURRENT_TIMESTAMP
        ", [(int)$assessment_id, (int)$student_id, (int)$items_answered, (int)$total_items, (int)$blur_count]);
    }

    public function mark_submitted($assessment_id, $student_id, $score)
    {
        $this->db->query("
            INSERT INTO {$this->table}
                (assessment_id, student_id, status, score, submitted_at, started_at)
            VALUES (?, ?, 'submitted', ?, NOW(), NOW())
            ON DUPLICATE KEY UPDATE
                status         = 'submitted',
                score          = VALUES(score),
                submitted_at   = NOW(),
                last_heartbeat = CURRENT_TIMESTAMP
        ", [(int)$assessment_id, (int)$student_id, (float)$score]);
    }

    public function get_for_assessment($assessment_id)
    {
        return $this->db->query("
            SELECT qls.student_id, qls.items_answered, qls.total_items,
                   qls.blur_count, qls.status, qls.score,
                   qls.started_at, qls.submitted_at, qls.last_heartbeat
            FROM {$this->table} qls
            WHERE qls.assessment_id = ?
        ", [(int)$assessment_id])->result_array();
    }
}
