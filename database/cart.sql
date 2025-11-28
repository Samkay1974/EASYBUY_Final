-- Cart Module Database Table
-- Run this SQL script to create the cart_items table for persistent cart storage

-- Table: cart_items
CREATE TABLE IF NOT EXISTS `cart_items` (
  `cart_item_id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL DEFAULT 1 COMMENT 'MOQ quantity (multiplier)',
  `created_at` datetime NOT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`cart_item_id`),
  UNIQUE KEY `unique_user_product` (`user_id`, `product_id`),
  KEY `user_id` (`user_id`),
  KEY `product_id` (`product_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Add foreign key constraints (optional, uncomment if your database supports them)
-- ALTER TABLE `cart_items`
--   ADD CONSTRAINT `fk_cart_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
--   ADD CONSTRAINT `fk_cart_product` FOREIGN KEY (`product_id`) REFERENCES `products` (`product_id`) ON DELETE CASCADE;

