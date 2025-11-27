<?php
require_once __DIR__ . '/../vendor/autoload.php';

use App\Core\Application;

$app = new Application(dirname(__DIR__));
$app->boot();

echo "Discovering modules...\n";
$app->moduleManager->discover();
$modules = $app->moduleManager->getAllModules();
echo "Found " . count($modules) . " modules: " . implode(', ', array_keys($modules)) . "\n";

echo "Syncing to registry...\n";
try {
    $app->moduleManager->syncToRegistry();
    echo "Sync complete.\n";
} catch (Throwable $e) {
    echo "Error syncing: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString();
}

echo "Checking DB...\n";
$db = \App\Core\Database\Database::getInstance();
$auth = $db->query("SELECT * FROM modules WHERE name = 'Auth'")->fetch();
if ($auth) {
    echo "Auth module is in DB. Enabled: " . $auth['is_enabled'] . "\n";
    if (!$auth['is_enabled']) {
        echo "Enabling Auth module...\n";
        $db->query("UPDATE modules SET is_enabled = 1 WHERE name = 'Auth'");
        echo "Enabled.\n";
    }
} else {
    echo "Auth module NOT found in DB after sync.\n";
}
