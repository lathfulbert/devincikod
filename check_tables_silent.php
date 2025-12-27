<?php
require_once __DIR__ . '/bootstrap.php';

use App\Core\Database\Database;

$db = Database::getInstance();
$tables = ['whatsapp_gateways', 'whatsapp_templates', 'whatsapp_campaigns', 'whatsapp_messages'];
$missing = [];
foreach ($tables as $table) {
    try {
        $db->query("SELECT 1 FROM $table LIMIT 1");
    } catch (\Exception $e) {
        $missing[] = $table;
    }
}
file_put_contents('migration_status.txt', empty($missing) ? "SUCCESS" : "MISSING: " . implode(', ', $missing));
