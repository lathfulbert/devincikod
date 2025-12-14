<?php

/**
 * Wallet Permissions Migration Runner
 *
 * Usage:
 * php run_wallet_permissions_migration.php          # Run migration up
 * php run_wallet_permissions_migration.php down     # Rollback migration
 */

require_once __DIR__ . '/bootstrap.php';

echo "Wallet Permissions Migration Runner\n";
echo "===================================\n\n";

try {
    // Check current permissions before migration
    $db = \App\Core\Database\Database::getInstance();
    $currentPermissions = $db->query('SELECT * FROM permissions WHERE slug LIKE ? ORDER BY slug', ['%wallet%'])->fetchAll();

    echo "Permissions Wallet actuelles :\n";
    foreach ($currentPermissions as $perm) {
        echo "- {$perm['name']} (slug: {$perm['slug']})\n";
    }
    echo "\n";

    // Determine action
    $action = $argv[1] ?? 'up';
    if (!in_array($action, ['up', 'down'])) {
        echo "Usage: php run_wallet_permissions_migration.php [up|down]\n";
        exit(1);
    }

    echo "Action: $action\n\n";

    // Include and run migration
    require_once __DIR__ . '/database/migrations/harmonize_wallet_permissions.php';

    $migration = new HarmonizeWalletPermissionsMigration();

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