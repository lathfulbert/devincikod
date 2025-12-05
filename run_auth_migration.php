<?php

/**
 * Migration runner for Auth module
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

// Run migration
$migration = new \Modules\Auth\Database\Migrations\CreateAuthTables();

echo "Running Auth module migrations...\n";

try {
    $migration->up();
    echo "✅ Migration completed successfully!\n";
} catch (Exception $e) {
    echo "❌ Migration failed: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}
