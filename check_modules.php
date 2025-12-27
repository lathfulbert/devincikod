<?php
require __DIR__ . '/vendor/autoload.php';

use App\Core\Application;
use App\Core\Database\Database;

$app = new Application(__DIR__);
// Boot to load config/DB
$app->boot();

$db = Database::getInstance();
$modules = $db->query("SELECT * FROM modules")->fetchAll(PDO::FETCH_ASSOC);

echo "Modules in DB (Count: " . count($modules) . "):\n";
if (count($modules) > 0) {
    print_r($modules[0]);
}
foreach ($modules as $module) {
    // Try to guess keys
    echo "- " . json_encode($module) . "\n";
}
