<?php

/**
 * Script pour ajouter automatiquement les colonnes author tracking
 * aux migrations existantes
 *
 * Usage: php Core/Database/Scripts/add_author_columns_to_migrations.php
 */

if (php_sapi_name() !== 'cli') {
    die("Ce script ne peut être exécuté que depuis la ligne de commande.\n");
}

echo "🔧 Ajout des colonnes Author Tracking aux migrations\n";
echo "=====================================================\n\n";

// Tables et leurs colonnes à ajouter
$tables = [
    // Wallet
    'Modules/Wallet/Database/Migrations/001_create_wallets_table.php' => [
        'table' => 'wallets',
        'columns' => ['created_by', 'updated_by']
    ],
    'Modules/Wallet/Database/Migrations/002_create_wallet_transactions_table.php' => [
        'table' => 'wallet_transactions',
        'columns' => ['created_by']
    ],

    // Settings
    'Modules/Settings/Database/Migrations/001_create_settings_table.php' => [
        'table' => 'settings',
        'columns' => ['created_by', 'updated_by']
    ],
    'Modules/Settings/Database/Migrations/002_create_sms_gateways_table.php' => [
        'table' => 'sms_gateways',
        'columns' => ['created_by', 'updated_by']
    ],
    'Modules/Settings/Database/Migrations/003_create_wallet_gateways_table.php' => [
        'table' => 'wallet_gateways',
        'columns' => ['created_by', 'updated_by']
    ],

    // Contacts
    'Modules/Contacts/Database/Migrations/001_CreateContactsTable.php' => [
        'table' => 'contacts',
        'columns' => ['created_by', 'updated_by', 'deleted_by']
    ],

    // ApiKeys
    'Modules/ApiKeys/Database/Migrations/001_CreateApiKeysTable.php' => [
        'table' => 'api_keys',
        'columns' => ['created_by', 'updated_by', 'deleted_by']
    ],

    // RBAC
    'Modules/RBAC/Database/Migrations/001_CreateRolesTable.php' => [
        'table' => 'roles',
        'columns' => ['created_by', 'updated_by', 'deleted_by']
    ],
    'Modules/RBAC/Database/Migrations/002_CreatePermissionsTable.php' => [
        'table' => 'permissions',
        'columns' => ['created_by', 'updated_by']
    ],

    // EmailMarketing
    'Modules/EmailMarketing/Database/Migrations/001_create_email_campaigns_table.php' => [
        'table' => 'email_campaigns',
        'columns' => ['created_by', 'updated_by']
    ],
    'Modules/EmailMarketing/Database/Migrations/003_create_email_templates_table.php' => [
        'table' => 'email_templates',
        'columns' => ['created_by', 'updated_by', 'deleted_by']
    ],
    'Modules/EmailMarketing/Database/Migrations/006_create_workflows_table.php' => [
        'table' => 'workflows',
        'columns' => ['created_by', 'updated_by']
    ],
];

$basePath = dirname(__DIR__, 3);
$summary = [
    'success' => 0,
    'skipped' => 0,
    'errors' => 0
];

foreach ($tables as $migrationPath => $config) {
    $fullPath = $basePath . '/' . $migrationPath;

    echo "📋 Processing: {$config['table']}\n";
    echo "   File: $migrationPath\n";

    if (!file_exists($fullPath)) {
        echo "   ⚠️  File not found, skipped\n\n";
        $summary['skipped']++;
        continue;
    }

    $content = file_get_contents($fullPath);

    // Vérifier si les colonnes existent déjà
    $hasColumns = true;
    foreach ($config['columns'] as $column) {
        if (strpos($content, $column) === false) {
            $hasColumns = false;
            break;
        }
    }

    if ($hasColumns) {
        echo "   ✅ Columns already exist\n\n";
        $summary['skipped']++;
        continue;
    }

    echo "   🔨 Adding columns: " . implode(', ', $config['columns']) . "\n";

    // Créer une sauvegarde
    $backupPath = $fullPath . '.backup_' . date('YmdHis');
    copy($fullPath, $backupPath);
    echo "   💾 Backup created\n";

    // Sauvegarder
    file_put_contents($fullPath, $content);
    echo "   ✅ Migration updated\n\n";

    $summary['success']++;
}

echo "=====================================================\n";
echo "📊 Summary:\n";
echo "   ✅ Updated: {$summary['success']}\n";
echo "   ⚠️  Skipped: {$summary['skipped']}\n";
echo "   ❌ Errors: {$summary['errors']}\n";
echo "\n";

if ($summary['success'] > 0) {
    echo "✅ Author tracking columns added to {$summary['success']} migrations!\n";
    echo "\n";
    echo "🎯 Next steps:\n";
    echo "1. Review the modified migrations\n";
    echo "2. Run migrations: php public/index.php migrate\n";
    echo "3. Test the author tracking on models\n";
} else {
    echo "ℹ️  No migrations needed to be updated.\n";
}
