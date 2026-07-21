-- Computer database preparation
-- Import the original psxeqwgl_playstation (1).sql locally first, then run this
-- optional file if you want a local marker showing sync is enabled.

CREATE TABLE IF NOT EXISTS `sync_status_computer` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `branch_id` varchar(100) NOT NULL,
  `device_id` varchar(100) NOT NULL,
  `last_success_at` datetime DEFAULT NULL,
  `last_error` text DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `branch_device` (`branch_id`,`device_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

INSERT IGNORE INTO `sync_status_computer` (`id`, `branch_id`, `device_id`) VALUES (1, 'main', 'cashier-1');
