-- Student Polling Module (Mentimeter-like)
-- Run once against gradingsystem2025a

CREATE TABLE IF NOT EXISTS `polls` (
  `poll_id`            INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `title`              VARCHAR(255) NOT NULL,
  `pin`                VARCHAR(8)   NOT NULL UNIQUE,
  `status`             ENUM('draft','active','closed') NOT NULL DEFAULT 'draft',
  `active_question_id` INT UNSIGNED DEFAULT NULL,
  `created_by`         VARCHAR(100) DEFAULT NULL,
  `created_at`         DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `poll_questions` (
  `question_id`   INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `poll_id`       INT UNSIGNED NOT NULL,
  `question_text` TEXT NOT NULL,
  `sort_order`    TINYINT UNSIGNED NOT NULL DEFAULT 0,
  `show_results`  TINYINT(1) NOT NULL DEFAULT 0,
  FOREIGN KEY (`poll_id`) REFERENCES `polls`(`poll_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `poll_options` (
  `option_id`    INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `question_id`  INT UNSIGNED NOT NULL,
  `option_text`  VARCHAR(255) NOT NULL,
  `sort_order`   TINYINT UNSIGNED NOT NULL DEFAULT 0,
  FOREIGN KEY (`question_id`) REFERENCES `poll_questions`(`question_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `poll_responses` (
  `response_id`  INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `question_id`  INT UNSIGNED NOT NULL,
  `option_id`    INT UNSIGNED NOT NULL,
  `student_id`   VARCHAR(50) DEFAULT NULL,
  `answered_at`  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY `uq_student_question` (`question_id`, `student_id`),
  FOREIGN KEY (`question_id`) REFERENCES `poll_questions`(`question_id`) ON DELETE CASCADE,
  FOREIGN KEY (`option_id`)   REFERENCES `poll_options`(`option_id`)    ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
