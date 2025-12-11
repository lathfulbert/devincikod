-- Migration: Create wallet_topup_requests table
-- Description: Stores wallet top-up requests with gateway integration and admin approval workflow

CREATE TABLE IF NOT EXISTS `wallet_topup_requests` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `user_id` BIGINT UNSIGNED NOT NULL,
    `wallet_id` BIGINT UNSIGNED NOT NULL,
    `amount` DECIMAL(15,2) NOT NULL,
    `currency` VARCHAR(3) NOT NULL DEFAULT 'XOF',

    -- Payment method
    `payment_method` ENUM('gateway', 'cash', 'mobile_money', 'bank_transfer', 'other') NOT NULL DEFAULT 'gateway',
    `gateway_id` BIGINT UNSIGNED NULL COMMENT 'Reference to wallet_gateways table if payment_method = gateway',

    -- Gateway integration
    `gateway_transaction_id` VARCHAR(255) NULL COMMENT 'Transaction ID from payment gateway',
    `gateway_response` JSON NULL COMMENT 'Full response from payment gateway',
    `gateway_status` VARCHAR(50) NULL COMMENT 'Status from gateway: SUCCESS, PENDING, FAILED',

    -- Request status
    `status` ENUM('pending', 'processing', 'approved', 'rejected', 'completed', 'failed', 'cancelled') NOT NULL DEFAULT 'pending',
    `notes` TEXT NULL COMMENT 'User notes or payment reference',
    `proof_of_payment` VARCHAR(255) NULL COMMENT 'File path to payment receipt/proof (for offline payments)',

    -- Admin review
    `reviewed_by` BIGINT UNSIGNED NULL COMMENT 'Admin user ID who reviewed the request',
    `reviewed_at` TIMESTAMP NULL,
    `admin_notes` TEXT NULL COMMENT 'Admin notes/reason for approval or rejection',

    -- Metadata
    `ip_address` VARCHAR(45) NULL,
    `user_agent` VARCHAR(255) NULL,

    -- Timestamps
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    `completed_at` TIMESTAMP NULL COMMENT 'When the credit was actually added to wallet',

    -- Indexes
    INDEX `idx_user_id` (`user_id`),
    INDEX `idx_wallet_id` (`wallet_id`),
    INDEX `idx_status` (`status`),
    INDEX `idx_payment_method` (`payment_method`),
    INDEX `idx_gateway_id` (`gateway_id`),
    INDEX `idx_reviewed_by` (`reviewed_by`),
    INDEX `idx_created_at` (`created_at`),

    -- Foreign keys
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`wallet_id`) REFERENCES `wallets`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`gateway_id`) REFERENCES `wallet_gateways`(`id`) ON DELETE SET NULL,
    FOREIGN KEY (`reviewed_by`) REFERENCES `users`(`id`) ON DELETE SET NULL

) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Add comment to table
ALTER TABLE `wallet_topup_requests` COMMENT = 'Wallet top-up requests with gateway integration and admin approval workflow';
