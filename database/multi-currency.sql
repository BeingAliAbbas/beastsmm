-- Multi-Currency Support Migration
-- Creates currencies table and seeds with default currencies

-- Create currencies table
CREATE TABLE IF NOT EXISTS `currencies` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `code` varchar(3) NOT NULL,
  `symbol` varchar(10) NOT NULL,
  `name` varchar(100) NOT NULL,
  `rate` decimal(15,6) NOT NULL DEFAULT 1.000000,
  `is_default` tinyint(1) NOT NULL DEFAULT 0,
  `enabled` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `code` (`code`),
  KEY `enabled` (`enabled`),
  KEY `is_default` (`is_default`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Insert default currencies
-- PKR is the base currency with rate = 1.0
INSERT INTO `currencies` (`code`, `symbol`, `name`, `rate`, `is_default`, `enabled`) VALUES
('PKR', 'Rs', 'Pakistani Rupee', 1.000000, 1, 1),
('USD', '$', 'US Dollar', 0.0036, 0, 1),
('EUR', '€', 'Euro', 0.0033, 0, 1),
('GBP', '£', 'British Pound', 0.0028, 0, 1),
('INR', '₹', 'Indian Rupee', 0.30, 0, 1),
('AUD', 'A$', 'Australian Dollar', 0.0055, 0, 1),
('CAD', 'C$', 'Canadian Dollar', 0.0049, 0, 1)
ON DUPLICATE KEY UPDATE 
  `symbol` = VALUES(`symbol`),
  `name` = VALUES(`name`),
  `rate` = VALUES(`rate`),
  `is_default` = VALUES(`is_default`),
  `enabled` = VALUES(`enabled`);
