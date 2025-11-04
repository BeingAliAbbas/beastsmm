-- Multi-Currency Support Migration
-- Creates currencies table and seeds default currencies with PKR as base

-- Create currencies table
CREATE TABLE IF NOT EXISTS `currencies` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `code` varchar(10) NOT NULL,
  `symbol` varchar(10) NOT NULL,
  `name` varchar(100) NOT NULL,
  `rate` decimal(15,8) NOT NULL DEFAULT '1.00000000',
  `is_default` tinyint(1) NOT NULL DEFAULT '0',
  `enabled` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `code` (`code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Seed default currencies
-- PKR is the base currency with rate = 1.0
INSERT INTO `currencies` (`code`, `symbol`, `name`, `rate`, `is_default`, `enabled`) VALUES
('PKR', 'Rs', 'Pakistani Rupee', 1.00000000, 1, 1),
('USD', '$', 'US Dollar', 0.00357143, 0, 1),
('EUR', '€', 'Euro', 0.00326797, 0, 1),
('GBP', '£', 'British Pound', 0.00277778, 0, 1),
('INR', '₹', 'Indian Rupee', 0.29762, 0, 1),
('AUD', 'A$', 'Australian Dollar', 0.00555556, 0, 1),
('CAD', 'C$', 'Canadian Dollar', 0.00497512, 0, 1)
ON DUPLICATE KEY UPDATE 
  `symbol` = VALUES(`symbol`),
  `name` = VALUES(`name`),
  `rate` = VALUES(`rate`),
  `is_default` = VALUES(`is_default`),
  `enabled` = VALUES(`enabled`);
