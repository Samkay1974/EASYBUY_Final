-- Reports Module Database Table
-- Run this SQL script to create the necessary table for the report system

-- Table: user_reports
CREATE TABLE IF NOT EXISTS `user_reports` (
  `report_id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL COMMENT 'User who submitted the report',
  `report_type` enum('bug','feature','complaint','other') NOT NULL DEFAULT 'other',
  `subject` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `status` enum('pending','reviewed','resolved','dismissed') NOT NULL DEFAULT 'pending',
  `admin_notes` text DEFAULT NULL COMMENT 'Notes from superadmin',
  `created_at` datetime NOT NULL,
  `updated_at` datetime DEFAULT NULL,
  `reviewed_at` datetime DEFAULT NULL,
  `reviewed_by` int(11) DEFAULT NULL COMMENT 'Superadmin user_id who reviewed',
  PRIMARY KEY (`report_id`),
  KEY `user_id` (`user_id`),
  KEY `status` (`status`),
  KEY `created_at` (`created_at`),
  KEY `reviewed_by` (`reviewed_by`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

