<?php

require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/Core/Application.php';

use App\Core\Database\Database;

$app = new \App\Core\Application(__DIR__);
$db = Database::getInstance();
$db->connect([
    'host' => 'localhost',
    'dbname' => 'sunuframework2',
    'username' => 'root',
    'password' => '',
    'charset' => 'utf8mb4'
]);

echo "Checking 'modules' table...\n";

try {
    $modules = $db->query("SELECT * FROM modules")->fetchAll();

    if (empty($modules)) {
        echo "Table 'modules' is EMPTY.\n";
    } else {
        foreach ($modules as $m) {
            echo "Module: {$m['name']} | Enabled: {$m['is_enabled']} | Installed: {$m['is_installed']} | Version: {$m['version']}\n";
        }
    }
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
