-- Subscriptions Module Database Tables
-- Run this SQL script to create the necessary tables for the subscription system

-- Table: subscriptions
CREATE TABLE IF NOT EXISTS `subscriptions` (
  `subscription_id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL COMMENT 'Wholesaler user ID',
  `plan_type` enum('basic','premium') NOT NULL DEFAULT 'basic' COMMENT 'basic = 50 GHS, premium = 100 GHS',
  `amount` decimal(10,2) NOT NULL COMMENT 'Amount paid for subscription',
  `status` enum('active','expired','cancelled') NOT NULL DEFAULT 'active',
  `payment_status` enum('pending','paid','failed') NOT NULL DEFAULT 'pending',
  `payment_reference` varchar(255) DEFAULT NULL COMMENT 'Paystack reference',
  `starts_at` datetime NOT NULL COMMENT 'Subscription start date',
  `expires_at` datetime DEFAULT NULL COMMENT 'Subscription expiry date (NULL for lifetime)',
  `created_at` datetime NOT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`subscription_id`),
  KEY `user_id` (`user_id`),
  KEY `status` (`status`),
  KEY `payment_status` (`payment_status`),
  KEY `expires_at` (`expires_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Table: subscription_payments
CREATE TABLE IF NOT EXISTS `subscription_payments` (
  `payment_id` int(11) NOT NULL AUTO_INCREMENT,
  `subscription_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `payment_reference` varchar(255) NOT NULL COMMENT 'Paystack reference',
  `payment_status` enum('pending','paid','failed') NOT NULL DEFAULT 'pending',
  `payment_method` varchar(50) DEFAULT 'paystack',
  `created_at` datetime NOT NULL,
  `paid_at` datetime DEFAULT NULL,
  PRIMARY KEY (`payment_id`),
  KEY `subscription_id` (`subscription_id`),
  KEY `user_id` (`user_id`),
  KEY `payment_reference` (`payment_reference`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

