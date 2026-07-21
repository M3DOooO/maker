-- Server database preparation
-- Import the original psxeqwgl_playstation (1).sql on hosting first, then run
-- this optional file so the server can keep a small audit table for sync events.

CREATE TABLE IF NOT EXISTS `sync_status_server` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `branch_id` varchar(100) NOT NULL,
  `device_id` varchar(100) NOT NULL,
  `last_received_at` datetime DEFAULT NULL,
  `last_payload_database` varchar(100) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `branch_device` (`branch_id`,`device_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;
