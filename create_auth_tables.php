<?php

/**
 * Create missing Auth tables manually
 */

require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/Core/Support/helpers.php';

use App\Core\Support\DotEnv;
use App\Core\Config\Config;
use App\Core\Database\Database;

// Load environment
(new DotEnv(__DIR__ . '/.env'))->load();

// Load config
$config = new Config();
$config->load(__DIR__ . '/config/database.php', 'database');

// Connect to database
$defaultConnection = $config->get('database.default', 'mysql');
$dbConfig = $config->get("database.connections.{$defaultConnection}", []);
Database::getInstance()->connect($dbConfig);

$db = Database::getInstance();

echo "Creating Auth module tables...\n\n";

// Check and create user_mfa_setup
try {
    $db->query("SELECT 1 FROM user_mfa_setup LIMIT 1");
    echo "✅ user_mfa_setup already exists\n";
} catch (Exception $e) {
    echo "Creating user_mfa_setup...\n";
    $db->query("
        CREATE TABLE user_mfa_setup (
            id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            user_id BIGINT UNSIGNED NOT NULL,
            method_type VARCHAR(255) NOT NULL,
            secret TEXT NULL,
            is_verified TINYINT(1) DEFAULT 0,
            last_used_at TIMESTAMP NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            UNIQUE KEY user_method (user_id, method_type),
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
    echo "✅ user_mfa_setup created\n";
}

// Check and create oauth_accounts
try {
    $db->query("SELECT 1 FROM oauth_accounts LIMIT 1");
    echo "✅ oauth_accounts already exists\n";
} catch (Exception $e) {
    echo "Creating oauth_accounts...\n";
    $db->query("
        CREATE TABLE oauth_accounts (
            id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            user_id BIGINT UNSIGNED NOT NULL,
            provider VARCHAR(255) NOT NULL,
            provider_user_id VARCHAR(255) NOT NULL,
            access_token TEXT NOT NULL,
            refresh_token TEXT NULL,
            expires_at TIMESTAMP NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            UNIQUE KEY provider_user (provider, provider_user_id),
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
    echo "✅ oauth_accounts created\n";
}

// Check and create auth_logs
try {
    $db->query("SELECT 1 FROM auth_logs LIMIT 1");
    echo "✅ auth_logs already exists\n";
} catch (Exception $e) {
    echo "Creating auth_logs...\n";
    $db->query("
        CREATE TABLE auth_logs (
            id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            user_id BIGINT UNSIGNED NULL,
            event_type VARCHAR(255) NOT NULL,
            ip_address VARCHAR(255) NULL,
            user_agent VARCHAR(255) NULL,
            details JSON NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
    echo "✅ auth_logs created\n";
}

// Check if users table needs updates
echo "\nChecking users table...\n";
try {
    $db->query("SELECT status FROM users LIMIT 1");
    echo "✅ users.status column already exists\n";
} catch (Exception $e) {
    echo "Adding columns to users table...\n";
    $db->query("
        ALTER TABLE users 
        ADD COLUMN status VARCHAR(255) DEFAULT 'active' AFTER password,
        ADD COLUMN last_login_ip VARCHAR(255) NULL,
        ADD COLUMN last_login_at TIMESTAMP NULL,
        ADD COLUMN device_fingerprint VARCHAR(255) NULL
    ");
    echo "✅ users table updated\n";
}

echo "\n✅ All Auth tables created successfully!\n";
