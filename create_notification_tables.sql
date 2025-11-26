-- Notification System Tables - SQL Script
-- Run this to create tables manually if migrations don't work

USE sunuframework2;

-- 1. Notifications table
CREATE TABLE IF NOT EXISTS `notifications` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `event_type` VARCHAR(100) NOT NULL,
  `data` JSON NOT NULL,
  `status` ENUM('pending', 'processing', 'completed', 'failed') DEFAULT 'pending',
  `priority` TINYINT DEFAULT 5,
  `scheduled_at` TIMESTAMP NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX `idx_status_scheduled` (`status`, `scheduled_at`),
  INDEX `idx_event_type` (`event_type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2. Notification recipients table
CREATE TABLE IF NOT EXISTS `notification_recipients` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `notification_id` BIGINT UNSIGNED NOT NULL,
  `user_id` BIGINT UNSIGNED NOT NULL,
  `channels`JSON NOT NULL,
  `status` ENUM('pending', 'sent', 'failed', 'cancelled') DEFAULT 'pending',
  `attempts` TINYINT DEFAULT 0,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX `idx_user_status` (`user_id`, `status`),
  INDEX `idx_notification_id` (`notification_id`),
  FOREIGN KEY (`notification_id`) REFERENCES `notifications`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 3. Delivery logs table
CREATE TABLE IF NOT EXISTS `delivery_logs` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `recipient_id` BIGINT UNSIGNED NOT NULL,
  `channel` VARCHAR(20) NOT NULL,
  `provider` VARCHAR(50) NOT NULL,
  `provider_message_id` VARCHAR(255),
  `status` ENUM('sent', 'delivered', 'failed', 'bounced', 'complained') DEFAULT 'sent',
  `error_message` TEXT,
  `metadata` JSON,
  `attempt` TINYINT DEFAULT 1,
  `sent_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX `idx_provider_status` (`provider`, `status`, `sent_at`),
  INDEX `idx_recipient_id` (`recipient_id`),
  FOREIGN KEY (`recipient_id`) REFERENCES `notification_recipients`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 4. Notification templates table
CREATE TABLE IF NOT EXISTS `notification_templates` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(100) NOT NULL UNIQUE,
  `channel` VARCHAR(20) NOT NULL,
  `version` INT DEFAULT 1,
  `subject` VARCHAR(255),
  `body_html` TEXT,
  `body_text` TEXT,
  `body_sms` VARCHAR(500),
  `push_title` VARCHAR(100),
  `push_body` VARCHAR(200),
  `variables` JSON,
  `active` BOOLEAN DEFAULT TRUE,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX `idx_name_channel` (`name`, `channel`),
  INDEX `idx_active` (`active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 5. User notification preferences table
CREATE TABLE IF NOT EXISTS `user_notification_preferences` (
  `user_id` BIGINT UNSIGNED NOT NULL PRIMARY KEY,
  `channels_enabled` JSON,
  `dnd_enabled` BOOLEAN DEFAULT FALSE,
  `dnd_from` TIME,
  `dnd_to` TIME,
  `timezone` VARCHAR(50) DEFAULT 'UTC',
  `email_opt_in` BOOLEAN DEFAULT TRUE,
  `sms_opt_in` BOOLEAN DEFAULT TRUE,
  `push_opt_in` BOOLEAN DEFAULT TRUE,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 6. Notification providers table
CREATE TABLE IF NOT EXISTS `notification_providers` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `type` VARCHAR(20) NOT NULL,
  `name` VARCHAR(50) NOT NULL,
  `driver` VARCHAR(50) NOT NULL,
  `config` JSON NOT NULL,
  `weight` TINYINT DEFAULT 10,
  `enabled` BOOLEAN DEFAULT TRUE,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY `uk_type_name` (`type`, `name`),
  INDEX `idx_enabled` (`enabled`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 7. Provider metrics table
CREATE TABLE IF NOT EXISTS `provider_metrics` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `provider_id` INT UNSIGNED NOT NULL,
  `success_count` INT DEFAULT 0,
  `fail_count` INT DEFAULT 0,
  `avg_latency_ms` INT DEFAULT 0,
  `last_success_at` TIMESTAMP NULL,
  `last_failure_at` TIMESTAMP NULL,
  `date` DATE NOT NULL,
  UNIQUE KEY `uk_provider_date` (`provider_id`, `date`),
  INDEX `idx_date` (`date`),
  FOREIGN KEY (`provider_id`) REFERENCES `notification_providers`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 8. In-app notifications table
CREATE TABLE IF NOT EXISTS `inapp_notifications` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `user_id` BIGINT UNSIGNED NOT NULL,
  `notification_id` BIGINT UNSIGNED,
  `title` VARCHAR(255) NOT NULL,
  `message` TEXT NOT NULL,
  `type` VARCHAR(50) DEFAULT 'info',
  `read_at` TIMESTAMP NULL,
  `clicked_at` TIMESTAMP NULL,
  `data` JSON,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX `idx_user_unread` (`user_id`, `read_at`),
  INDEX `idx_created_at` (`created_at`),
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`notification_id`) REFERENCES `notifications`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Verification
SELECT 'Tables created successfully!' AS STATUS;
SHOW TABLES LIKE 'notification%';
