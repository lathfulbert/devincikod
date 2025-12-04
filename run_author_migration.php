<?php

/**
 * Manual execution of the Author Tracking global migration
 */

require_once __DIR__ . '/vendor/autoload.php';

use App\Core\Application;

// Initialize application
$app = new Application(__DIR__);
$app->boot();

echo "🚀 Running Author Tracking Global Migration\n";
echo str_repeat("=", 60) . "\n\n";

// Load and run the migration
$migration = require __DIR__ . '/Core/Database/Migrations/001_add_author_tracking_columns.php';

try {
    $migration->up();

    echo "\n" . str_repeat("=", 60) . "\n";
    echo "✅ Migration completed successfully!\n\n";

    // Record in migrations table
    $db = \App\Core\Database\Database::getInstance()->getPdo();
    $stmt = $db->prepare("INSERT INTO migrations (migration) VALUES (?)");
    $stmt->execute(['001_add_author_tracking_columns']);

    echo "✅ Migration recorded in database\n\n";

} catch (Exception $e) {
    echo "\n" . str_repeat("=", 60) . "\n";
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
    exit(1);
}

echo "🎉 All done! Run verify_author_tracking.php to confirm.\n";
