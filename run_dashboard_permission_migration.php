<?php
// Script pour exécuter la migration Dashboard
require_once __DIR__ . '/bootstrap.php';

echo "Dashboard Permission Migration Runner\n";
echo "===================================\n\n";

try {
    $db = \App\Core\Database\Database::getInstance();
    $action = $argv[1] ?? 'up';
    if (!in_array($action, ['up', 'down'])) {
        echo "Usage: php run_dashboard_permission_migration.php [up|down]\n";
        exit(1);
    }

    require_once __DIR__ . '/database/migrations/add_dashboard_permission.php';
    $migration = new AddDashboardPermissionMigration();

    if ($action === 'down') {
        $migration->down();
    } else {
        $migration->up();
    }

    echo "\nMigration exécutée avec succès!\n";

} catch (Exception $e) {
    echo "Erreur lors de l'exécution de la migration: " . $e->getMessage() . "\n";
    exit(1);
}
