<?php

require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/Core/Support/helpers.php';

use App\Core\Application;
use App\Core\Database\Database;

$app = new Application(__DIR__);
$app->boot();

$db = Database::getInstance();

echo "Describing modules table...\n";

try {
    $stmt = $db->query("DESCRIBE modules");
    $columns = $stmt->fetchAll();

    foreach ($columns as $col) {
        echo "{$col['Field']} ({$col['Type']})\n";
    }
} catch (\Throwable $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
