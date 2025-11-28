-- Collaboration Module Database Tables
-- Run this SQL script to create the necessary tables

-- Table: collaborations
CREATE TABLE IF NOT EXISTS `collaborations` (
  `collaboration_id` int(11) NOT NULL AUTO_INCREMENT,
  `product_id` int(11) NOT NULL,
  `creator_id` int(11) NOT NULL,
  `min_contribution_percent` decimal(5,2) NOT NULL DEFAULT 30.00,
  `status` enum('open','completed','expired','cancelled') NOT NULL DEFAULT 'open',
  `created_at` datetime NOT NULL,
  `expires_at` datetime NOT NULL,
  `completed_at` datetime DEFAULT NULL,
  PRIMARY KEY (`collaboration_id`),
  KEY `product_id` (`product_id`),
  KEY `creator_id` (`creator_id`),
  KEY `status` (`status`),
  KEY `expires_at` (`expires_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Table: collaboration_members
CREATE TABLE IF NOT EXISTS `collaboration_members` (
  `member_id` int(11) NOT NULL AUTO_INCREMENT,
  `collaboration_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `contribution_percent` decimal(5,2) NOT NULL,
  `joined_at` datetime NOT NULL,
  PRIMARY KEY (`member_id`),
  UNIQUE KEY `unique_member` (`collaboration_id`,`user_id`),
  KEY `collaboration_id` (`collaboration_id`),
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Add foreign key constraints (optional, uncomment if your database supports them)
-- ALTER TABLE `collaborations`
--   ADD CONSTRAINT `fk_collab_product` FOREIGN KEY (`product_id`) REFERENCES `products` (`product_id`) ON DELETE CASCADE,
--   ADD CONSTRAINT `fk_collab_creator` FOREIGN KEY (`creator_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

-- ALTER TABLE `collaboration_members`
--   ADD CONSTRAINT `fk_member_collab` FOREIGN KEY (`collaboration_id`) REFERENCES `collaborations` (`collaboration_id`) ON DELETE CASCADE,
--   ADD CONSTRAINT `fk_member_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

