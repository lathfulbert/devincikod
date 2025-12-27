<?php
require __DIR__ . '/vendor/autoload.php';

use App\Core\Application;
use App\Core\Database\Database;

$app = new Application(__DIR__);
$app->boot();

$db = Database::getInstance();
$modules = $db->query("SELECT * FROM modules WHERE name LIKE '%mail%'")->fetchAll(PDO::FETCH_ASSOC);

echo "Modules found:\n";
foreach ($modules as $module) {
    echo "ID: " . $module['id'] . " | Name: " . $module['name'] . " | Active: " . $module['is_active'] . " | Installed: " . $module['is_installed'] . "\n";
}
