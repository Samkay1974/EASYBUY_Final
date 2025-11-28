-- Collaboration Order Payments Table
-- Tracks individual member payments for collaboration orders

CREATE TABLE IF NOT EXISTS `collaboration_order_payments` (
  `payment_id` int(11) NOT NULL AUTO_INCREMENT,
  `order_id` int(11) NOT NULL COMMENT 'The main collaboration order',
  `collaboration_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL COMMENT 'The member who made this payment',
  `contribution_percent` decimal(5,2) NOT NULL COMMENT 'Member contribution percentage',
  `amount` decimal(10,2) NOT NULL COMMENT 'Amount paid by this member',
  `payment_status` enum('pending','paid','failed') NOT NULL DEFAULT 'pending',
  `paid_at` datetime DEFAULT NULL,
  `created_at` datetime NOT NULL,
  PRIMARY KEY (`payment_id`),
  UNIQUE KEY `unique_member_payment` (`order_id`, `user_id`),
  KEY `order_id` (`order_id`),
  KEY `collaboration_id` (`collaboration_id`),
  KEY `user_id` (`user_id`),
  KEY `payment_status` (`payment_status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

