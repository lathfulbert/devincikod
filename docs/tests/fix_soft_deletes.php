<?php

/**
 * Fix: Add deleted_at column to tables using SoftDeletes
 */

require_once __DIR__ . '/vendor/autoload.php';

use App\Core\Application;
use App\Core\Database\Database;

$app = new Application(__DIR__);
$app->boot();

$db = Database::getInstance()->getPdo();

echo "🔧 Fixing Soft Deletes - Adding deleted_at columns\n";
echo str_repeat("=", 60) . "\n\n";

// Tables that use SoftDeletes but are missing deleted_at
$tables = [
    'sender_names',
    'roles',
    'contacts',
    'api_keys',
    'email_templates'
];

foreach ($tables as $table) {
    // Check if table exists
    $stmt = $db->query("SHOW TABLES LIKE '$table'");
    if ($stmt->rowCount() === 0) {
        echo "⚠️  Table '$table' n'existe pas, ignorée.\n\n";
        continue;
    }

    echo "📋 Table: $table\n";

    // Check if deleted_at already exists
    $stmt = $db->query("SHOW COLUMNS FROM `$table` LIKE 'deleted_at'");
    if ($stmt->rowCount() > 0) {
        echo "   ✓ deleted_at existe déjà\n\n";
        continue;
    }

    // Check if deleted_by exists (indicator that table should have soft deletes)
    $stmt = $db->query("SHOW COLUMNS FROM `$table` LIKE 'deleted_by'");
    if ($stmt->rowCount() === 0) {
        echo "   ℹ️  deleted_by n'existe pas, probablement pas de soft delete nécessaire\n\n";
        continue;
    }

    try {
        // Add deleted_at column before deleted_by
        $sql = "ALTER TABLE `$table` ADD COLUMN `deleted_at` TIMESTAMP NULL AFTER `updated_at`";
        $db->exec($sql);
        echo "   ✅ Colonne deleted_at ajoutée\n";

        // Add index
        $indexName = "idx_{$table}_deleted_at";
        $db->exec("ALTER TABLE `$table` ADD INDEX `$indexName` (`deleted_at`)");
        echo "   ✅ Index créé\n\n";

    } catch (\Exception $e) {
        echo "   ❌ Erreur: " . $e->getMessage() . "\n\n";
    }
}

echo str_repeat("=", 60) . "\n";
echo "✅ Fix terminé!\n\n";

echo "🔍 Vérification:\n";
foreach ($tables as $table) {
    $stmt = $db->query("SHOW TABLES LIKE '$table'");
    if ($stmt->rowCount() === 0) {
        continue;
    }

    $stmt = $db->query("SHOW COLUMNS FROM `$table` WHERE Field IN ('deleted_at', 'deleted_by')");
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (!empty($columns)) {
        echo "  ✅ $table: ";
        $cols = array_column($columns, 'Field');
        echo implode(', ', $cols) . "\n";
    }
}
