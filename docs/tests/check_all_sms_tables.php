<?php

require_once __DIR__ . '/vendor/autoload.php';

use App\Core\Application;

$app = new Application(__DIR__);
$app->boot();

$db = \App\Core\Database\Database::getInstance()->getPdo();

echo "🔍 Vérification des tables SMS\n";
echo str_repeat("=", 60) . "\n\n";

$tables = ['sms_billing_logs', 'sms_campaigns', 'sms_gateways', 'sms_messages', 'sms_queue'];

foreach ($tables as $table) {
    echo "📋 Table: $table\n";

    // Get all columns
    $stmt = $db->query("SHOW COLUMNS FROM `$table`");
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $hasCreatedBy = false;
    $hasUpdatedBy = false;
    $hasCreatedAt = false;
    $hasUpdatedAt = false;

    foreach ($columns as $col) {
        if ($col['Field'] === 'created_by') $hasCreatedBy = true;
        if ($col['Field'] === 'updated_by') $hasUpdatedBy = true;
        if ($col['Field'] === 'created_at') $hasCreatedAt = true;
        if ($col['Field'] === 'updated_at') $hasUpdatedAt = true;
    }

    echo "   created_at: " . ($hasCreatedAt ? '✅' : '❌') . "\n";
    echo "   created_by: " . ($hasCreatedBy ? '✅' : '❌') . "\n";
    echo "   updated_at: " . ($hasUpdatedAt ? '✅' : '❌') . "\n";
    echo "   updated_by: " . ($hasUpdatedBy ? '✅' : '❌') . "\n";
    echo "\n";
}

// Check wallets table too (involved in SMS billing)
echo "📋 Table: wallets\n";
$stmt = $db->query("SHOW COLUMNS FROM `wallets`");
$columns = $stmt->fetchAll(PDO::FETCH_ASSOC);

$hasCreatedBy = false;
$hasUpdatedBy = false;
$hasCreatedAt = false;
$hasUpdatedAt = false;

foreach ($columns as $col) {
    if ($col['Field'] === 'created_by') $hasCreatedBy = true;
    if ($col['Field'] === 'updated_by') $hasUpdatedBy = true;
    if ($col['Field'] === 'created_at') $hasCreatedAt = true;
    if ($col['Field'] === 'updated_at') $hasUpdatedAt = true;
}

echo "   created_at: " . ($hasCreatedAt ? '✅' : '❌') . "\n";
echo "   created_by: " . ($hasCreatedBy ? '✅' : '❌') . "\n";
echo "   updated_at: " . ($hasUpdatedAt ? '✅' : '❌') . "\n";
echo "   updated_by: " . ($hasUpdatedBy ? '✅' : '❌') . "\n";
