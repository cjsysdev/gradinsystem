CREATE TABLE `accounts` (
  `account_id` int NOT NULL AUTO_INCREMENT,
  `student_id` int NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `created_at` date NOT NULL,
  `role` varchar(32) DEFAULT NULL,
  PRIMARY KEY (`account_id`),
  UNIQUE KEY `username` (`username`),
  UNIQUE KEY `username_2` (`username`),
  KEY `student_id` (`student_id`)
) ENGINE=MyISAM AUTO_INCREMENT=489 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

CREATE TABLE `assessments` (
  `assessment_id` int NOT NULL AUTO_INCREMENT,
  `iotype_id` int DEFAULT NULL,
  `schedule_id` int DEFAULT NULL,
  `title` varchar(64) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `description` longtext COLLATE utf8mb4_general_ci,
  `given` longtext COLLATE utf8mb4_general_ci,
  `max_score` int DEFAULT NULL,
  `status` varchar(32) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `due` datetime DEFAULT CURRENT_TIMESTAMP,
  `term` enum('midterm','tentative-final','final') COLLATE utf8mb4_general_ci DEFAULT 'final',
  `pdf_file_path` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  PRIMARY KEY (`assessment_id`)
) ENGINE=MyISAM AUTO_INCREMENT=131 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `attendance` (
  `attendance_id` int NOT NULL AUTO_INCREMENT,
  `schedule_id` int NOT NULL,
  `student_id` int NOT NULL,
  `date` datetime DEFAULT CURRENT_TIMESTAMP,
  `status` enum('present','absent','late','excuse','others') NOT NULL,
  `ip_address` varchar(32) DEFAULT NULL,
  PRIMARY KEY (`attendance_id`)
) ENGINE=MyISAM AUTO_INCREMENT=9204 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

CREATE TABLE `class_schedule` (
  `schedule_id` int NOT NULL AUTO_INCREMENT,
  `class_id` int DEFAULT NULL,
  `section` varchar(32) NOT NULL,
  `type` enum('LEC','LAB') NOT NULL,
  `time_start` time DEFAULT NULL,
  `time_end` time DEFAULT NULL,
  `day` varchar(125) DEFAULT NULL,
  PRIMARY KEY (`schedule_id`),
  KEY `class_id` (`class_id`)
) ENGINE=MyISAM AUTO_INCREMENT=19 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

CREATE TABLE `class_student` (
  `id` int NOT NULL AUTO_INCREMENT,
  `student_id` int DEFAULT NULL,
  `class_id` int DEFAULT NULL,
  `section` varchar(64) DEFAULT NULL,
  `is_cleared` tinyint(1) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `student_id` (`student_id`),
  KEY `class_id` (`class_id`)
) ENGINE=MyISAM AUTO_INCREMENT=243 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

CREATE TABLE `classes` (
  `class_id` int NOT NULL AUTO_INCREMENT,
  `class_code` varchar(32) NOT NULL,
  `class_name` varchar(100) NOT NULL,
  `instructor` varchar(100) NOT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`class_id`)
) ENGINE=MyISAM AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

CREATE TABLE `classworks` (
  `classwork_id` int NOT NULL AUTO_INCREMENT,
  `student_id` int DEFAULT NULL,
  `assessment_id` int DEFAULT NULL,
  `score` decimal(10,2) DEFAULT NULL,
  `code` longtext,
  `file_upload` varchar(512) DEFAULT NULL,
  `status` varchar(24) DEFAULT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `submitted_at` datetime DEFAULT NULL,
  PRIMARY KEY (`classwork_id`)
) ENGINE=MyISAM AUTO_INCREMENT=4871 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

CREATE TABLE `global_settings` (
  `id` int NOT NULL AUTO_INCREMENT,
  `setting_key` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `setting_value` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `setting_key` (`setting_key`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `io_type` (
  `iotype_id` int NOT NULL AUTO_INCREMENT,
  `type` varchar(50) NOT NULL,
  `percentage` varchar(50) NOT NULL,
  PRIMARY KEY (`iotype_id`)
) ENGINE=MyISAM AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

CREATE TABLE `semester_master` (
  `trans_no` int NOT NULL AUTO_INCREMENT,
  `semcode` char(5) CHARACTER SET ascii COLLATE ascii_general_ci NOT NULL DEFAULT '',
  `description` varchar(75) CHARACTER SET ascii COLLATE ascii_general_ci DEFAULT NULL,
  `semtype` char(1) CHARACTER SET ascii COLLATE ascii_general_ci DEFAULT NULL,
  `semyear` int DEFAULT NULL,
  PRIMARY KEY (`trans_no`),
  UNIQUE KEY `trans_no` (`trans_no`),
  UNIQUE KEY `semcode` (`semcode`)
) ENGINE=InnoDB AUTO_INCREMENT=15 DEFAULT CHARSET=ascii;

CREATE TABLE `temp_csv_import` (
  `lastname` varchar(100) DEFAULT NULL,
  `firstname` varchar(100) DEFAULT NULL,
  `score` decimal(10,2) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;