-- Remove duplicate UNIQUE KEY & convert to InnoDB
CREATE TABLE `accounts` (
  `account_id` int NOT NULL AUTO_INCREMENT,
  `student_id` int NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `created_at` date NOT NULL,
  `role` varchar(32) DEFAULT NULL,
  PRIMARY KEY (`account_id`),
  UNIQUE KEY `username` (`username`),  -- Removed duplicate
  KEY `student_id` (`student_id`)
) ENGINE=InnoDB AUTO_INCREMENT=489 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Convert to InnoDB + consistent collation
CREATE TABLE `assessments` (
  `assessment_id` int NOT NULL AUTO_INCREMENT,
  `iotype_id` int DEFAULT NULL,
  `schedule_id` int DEFAULT NULL,
  `title` varchar(64) DEFAULT NULL,
  `description` longtext,
  `given` longtext,
  `max_score` int DEFAULT NULL,
  `status` varchar(32) DEFAULT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `due` datetime DEFAULT CURRENT_TIMESTAMP,
  `term` enum('midterm','tentative-final','final') DEFAULT 'final',
  `pdf_file_path` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`assessment_id`),
  KEY `iotype_id` (`iotype_id`),
  KEY `schedule_id` (`schedule_id`)
) ENGINE=InnoDB AUTO_INCREMENT=131 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Added FKs + engine conversion
CREATE TABLE `attendance` (
  `attendance_id` int NOT NULL AUTO_INCREMENT,
  `schedule_id` int NOT NULL,
  `student_id` int NOT NULL,
  `date` datetime DEFAULT CURRENT_TIMESTAMP,
  `status` enum('present','absent','late','excuse','others') NOT NULL,
  `ip_address` varchar(32) DEFAULT NULL,
  PRIMARY KEY (`attendance_id`),
  KEY `schedule_id` (`schedule_id`),
  CONSTRAINT `fk_attendance_schedule` 
    FOREIGN KEY (`schedule_id`) REFERENCES `class_schedule` (`schedule_id`)
) ENGINE=InnoDB AUTO_INCREMENT=9204 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Added FK + engine conversion
CREATE TABLE `class_schedule` (
  `schedule_id` int NOT NULL AUTO_INCREMENT,
  `class_id` int DEFAULT NULL,
  `section` varchar(32) NOT NULL,
  `type` enum('LEC','LAB') NOT NULL,
  `time_start` time DEFAULT NULL,
  `time_end` time DEFAULT NULL,
  `day` varchar(125) DEFAULT NULL,
  PRIMARY KEY (`schedule_id`),
  KEY `class_id` (`class_id`),
  CONSTRAINT `fk_schedule_class` 
    FOREIGN KEY (`class_id`) REFERENCES `classes` (`class_id`)
) ENGINE=InnoDB AUTO_INCREMENT=19 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Added FK + engine conversion
CREATE TABLE `class_student` (
  `id` int NOT NULL AUTO_INCREMENT,
  `student_id` int DEFAULT NULL,
  `class_id` int DEFAULT NULL,
  `section` varchar(64) DEFAULT NULL,
  `is_cleared` tinyint(1) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `student_id` (`student_id`),
  KEY `class_id` (`class_id`),
  CONSTRAINT `fk_classstudent_class` 
    FOREIGN KEY (`class_id`) REFERENCES `classes` (`class_id`)
) ENGINE=InnoDB AUTO_INCREMENT=243 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Base table (no changes needed)
CREATE TABLE `classes` (
  `class_id` int NOT NULL AUTO_INCREMENT,
  `class_code` varchar(32) NOT NULL,
  `class_name` varchar(100) NOT NULL,
  `instructor` varchar(100) NOT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`class_id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Added FK + engine conversion
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
  PRIMARY KEY (`classwork_id`),
  KEY `assessment_id` (`assessment_id`),
  CONSTRAINT `fk_classworks_assessment` 
    FOREIGN KEY (`assessment_id`) REFERENCES `assessments` (`assessment_id`)
) ENGINE=InnoDB AUTO_INCREMENT=4871 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Already InnoDB (no change)
CREATE TABLE `global_settings` (
  `id` int NOT NULL AUTO_INCREMENT,
  `setting_key` varchar(255) NOT NULL,
  `setting_value` varchar(255) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `setting_key` (`setting_key`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Base table (no FKs needed)
CREATE TABLE `io_type` (
  `iotype_id` int NOT NULL AUTO_INCREMENT,
  `type` varchar(50) NOT NULL,
  `percentage` varchar(50) NOT NULL,
  PRIMARY KEY (`iotype_id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Already InnoDB (no change)
CREATE TABLE `semester_master` (
  `trans_no` int NOT NULL AUTO_INCREMENT,
  `semcode` char(5) NOT NULL DEFAULT '',
  `description` varchar(75) DEFAULT NULL,
  `semtype` char(1) DEFAULT NULL,
  `semyear` int DEFAULT NULL,
  PRIMARY KEY (`trans_no`),
  UNIQUE KEY `semcode` (`semcode`)
) ENGINE=InnoDB AUTO_INCREMENT=15 DEFAULT CHARSET=ascii;

-- Engine conversion only
CREATE TABLE `temp_csv_import` (
  `lastname` varchar(100) DEFAULT NULL,
  `firstname` varchar(100) DEFAULT NULL,
  `score` decimal(10,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Add remaining foreign keys (executed separately)
ALTER TABLE `assessments`
  ADD CONSTRAINT `fk_assessments_iotype` 
    FOREIGN KEY (`iotype_id`) REFERENCES `io_type` (`iotype_id`),
  ADD CONSTRAINT `fk_assessments_schedule` 
    FOREIGN KEY (`schedule_id`) REFERENCES `class_schedule` (`schedule_id`);