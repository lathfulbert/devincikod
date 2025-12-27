<?php

/**
 * Simple verification script for Author Tracking System
 */

require_once __DIR__ . '/vendor/autoload.php';

use App\Core\Application;

// Initialize application
$app = new Application(__DIR__);
$app->boot();

echo "🔍 Author Tracking System - Verification\n";
echo str_repeat("=", 60) . "\n\n";

// Check database connection
try {
    $db = \App\Core\Database\Database::getInstance()->getPdo();
    echo "✅ Database connection: OK\n\n";
} catch (Exception $e) {
    echo "❌ Database connection failed: " . $e->getMessage() . "\n";
    exit(1);
}

// Tables to check
$tables = [
    'sms_messages' => ['created_by', 'updated_by'],
    'sms_campaigns' => ['created_by', 'updated_by'],
    'sender_names' => ['created_by', 'updated_by', 'deleted_by'],
    'wallets' => ['created_by', 'updated_by'],
    'wallet_transactions' => ['created_by']
];

echo "📋 Checking Author Tracking Columns:\n";
echo str_repeat("-", 60) . "\n";

foreach ($tables as $table => $expectedColumns) {
    echo "\n🔹 Table: $table\n";

    // Check if table exists
    try {
        $stmt = $db->query("SHOW TABLES LIKE '$table'");
        $tableExists = $stmt->fetch();

        if (!$tableExists) {
            echo "   ⚠️  Table not found\n";
            continue;
        }

        // Check columns
        $stmt = $db->query("SHOW COLUMNS FROM $table LIKE '%_by'");
        $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (empty($columns)) {
            echo "   ❌ No author tracking columns found\n";
        } else {
            foreach ($columns as $col) {
                $field = $col['Field'];
                $type = $col['Type'];
                $null = $col['Null'] === 'YES' ? 'nullable' : 'not null';

                if (in_array($field, $expectedColumns)) {
                    echo "   ✅ $field ($type, $null)\n";
                } else {
                    echo "   ℹ️  $field ($type, $null) - unexpected column\n";
                }
            }
        }

    } catch (Exception $e) {
        echo "   ❌ Error: " . $e->getMessage() . "\n";
    }
}

echo "\n" . str_repeat("=", 60) . "\n";
echo "📊 Summary\n";
echo str_repeat("=", 60) . "\n\n";

// Check if models have HasAuthor trait
$modelsToCheck = [
    'SmsCore' => ['SmsMessage', 'SmsCampaign', 'SenderName'],
    'Wallet' => ['Wallet', 'WalletTransaction'],
];

echo "🔹 Checking Models for HasAuthor Trait:\n";
echo str_repeat("-", 60) . "\n\n";

foreach ($modelsToCheck as $module => $models) {
    echo "Module: $module\n";
    foreach ($models as $model) {
        $modelClass = "App\\Modules\\$module\\Models\\$model";

        if (class_exists($modelClass)) {
            $traits = class_uses($modelClass);
            if ($traits && in_array('App\\Core\\Database\\Traits\\HasAuthor', $traits)) {
                echo "   ✅ $model - HasAuthor trait active\n";
            } else {
                echo "   ❌ $model - HasAuthor trait missing\n";
            }
        } else {
            echo "   ⚠️  $model - Class not found\n";
        }
    }
    echo "\n";
}

echo str_repeat("=", 60) . "\n";
echo "\n✅ Verification Complete!\n\n";

echo "📚 Next Steps:\n";
echo "   1. Use the author-info component in your views\n";
echo "   2. Implement access controls based on authorship\n";
echo "   3. Test creating/updating records as different users\n\n";

echo "📖 Documentation:\n";
echo "   - FINAL_SUMMARY_AUTHOR_TRACKING.md\n";
echo "   - Core/Database/AUTHOR_TRACKING.md\n";
echo "   - QUICK_COMMANDS_AUTHOR_TRACKING.md\n\n";
