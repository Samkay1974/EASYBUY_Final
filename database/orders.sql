-- Orders Module Database Tables
-- Run this SQL script to create the necessary tables for the order management system

-- Table: orders
CREATE TABLE IF NOT EXISTS `orders` (
  `order_id` int(11) NOT NULL AUTO_INCREMENT,
  `customer_id` int(11) NOT NULL,
  `collaboration_id` int(11) DEFAULT NULL COMMENT 'NULL for regular orders, set for collaboration orders',
  `total_amount` decimal(10,2) NOT NULL COMMENT 'Total amount before transaction fee',
  `transaction_fee` decimal(10,2) NOT NULL DEFAULT 0.00 COMMENT '5% transaction fee',
  `final_amount` decimal(10,2) NOT NULL COMMENT 'Total amount + transaction fee',
  `status` enum('pending','processing','completed','cancelled') NOT NULL DEFAULT 'pending',
  `payment_status` enum('pending','paid','failed','refunded') NOT NULL DEFAULT 'pending',
  `created_at` datetime NOT NULL,
  `updated_at` datetime DEFAULT NULL,
  `cancelled_at` datetime DEFAULT NULL,
  PRIMARY KEY (`order_id`),
  KEY `customer_id` (`customer_id`),
  KEY `collaboration_id` (`collaboration_id`),
  KEY `status` (`status`),
  KEY `payment_status` (`payment_status`),
  KEY `created_at` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Table: order_details
CREATE TABLE IF NOT EXISTS `order_details` (
  `order_detail_id` int(11) NOT NULL AUTO_INCREMENT,
  `order_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `wholesaler_id` int(11) NOT NULL COMMENT 'The user_id of the wholesaler who created the product',
  `quantity` int(11) NOT NULL COMMENT 'Actual quantity ordered',
  `unit_price` decimal(10,2) NOT NULL COMMENT 'Price per unit at time of order',
  `subtotal` decimal(10,2) NOT NULL COMMENT 'quantity * unit_price',
  `created_at` datetime NOT NULL,
  PRIMARY KEY (`order_detail_id`),
  KEY `order_id` (`order_id`),
  KEY `product_id` (`product_id`),
  KEY `wholesaler_id` (`wholesaler_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Add foreign key constraints (optional, uncomment if your database supports them)
-- ALTER TABLE `orders`
--   ADD CONSTRAINT `fk_order_customer` FOREIGN KEY (`customer_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
--   ADD CONSTRAINT `fk_order_collaboration` FOREIGN KEY (`collaboration_id`) REFERENCES `collaborations` (`collaboration_id`) ON DELETE SET NULL;

-- ALTER TABLE `order_details`
--   ADD CONSTRAINT `fk_order_detail_order` FOREIGN KEY (`order_id`) REFERENCES `orders` (`order_id`) ON DELETE CASCADE,
--   ADD CONSTRAINT `fk_order_detail_product` FOREIGN KEY (`product_id`) REFERENCES `products` (`product_id`) ON DELETE CASCADE,
--   ADD CONSTRAINT `fk_order_detail_wholesaler` FOREIGN KEY (`wholesaler_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

