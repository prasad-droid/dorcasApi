-- Run this SQL in your database to support push notifications

ALTER TABLE `customers` ADD COLUMN `device_token` TEXT NULL AFTER `phone_verified`;
ALTER TABLE `vendors` ADD COLUMN `device_token` TEXT NULL AFTER `referred_by`;

-- Optional: Ensure notifications table is ready (if not already)
CREATE TABLE IF NOT EXISTS `notifications` (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_type` enum('customer','vendor') NOT NULL,
  `user_id` int(10) UNSIGNED NOT NULL,
  `type` varchar(60) NOT NULL,
  `title` varchar(200) NOT NULL,
  `body` text NOT NULL,
  `icon` varchar(10) NOT NULL DEFAULT '?',
  `color` varchar(20) NOT NULL DEFAULT '#2e85fd',
  `link` varchar(300) DEFAULT NULL,
  `booking_id` int(10) UNSIGNED DEFAULT NULL,
  `is_read` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_user` (`user_type`,`user_id`,`is_read`),
  KEY `idx_booking` (`booking_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
