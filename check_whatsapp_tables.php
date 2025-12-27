<?php

require_once __DIR__ . '/bootstrap.php';

use App\Core\Database\Database;

$db = Database::getInstance();
$tables = ['whatsapp_gateways', 'whatsapp_templates', 'whatsapp_campaigns', 'whatsapp_messages'];
$missing = [];

foreach ($tables as $table) {
    try {
        $db->query("SELECT 1 FROM $table LIMIT 1");
        echo "Table '$table' exists.\n";
    } catch (\Exception $e) {
        $missing[] = $table;
        echo "Table '$table' MISSING.\n";
    }
}

if (empty($missing)) {
    echo "SUCCESS: All tables found.\n";
} else {
    echo "FAILURE: Missing tables: " . implode(', ', $missing) . "\n";
}
