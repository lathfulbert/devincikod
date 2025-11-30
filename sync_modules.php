<?php

require_once __DIR__ . '/vendor/autoload.php';

use App\Core\Application;

$app = new Application(__DIR__);

// Force discover and sync modules
echo "Discovering modules...\n";
$app->moduleManager->discover();

$allModules = $app->moduleManager->getAllModules();
echo "Found " . count($allModules) . " modules:\n";
foreach ($allModules as $name => $module) {
    echo "  - $name\n";
}

echo "\nSyncing to registry...\n";
try {
    $app->moduleManager->syncToRegistry();
    echo "Sync completed!\n";
} catch (\Exception $e) {
    echo "Error during sync: " . $e->getMessage() . "\n";
}

echo "\nEnabling all modules...\n";
foreach ($allModules as $name => $module) {
    try {
        $app->moduleManager->getRegistry()->setEnabled($name, true);
        echo "  ✓ Enabled: $name\n";
    } catch (\Exception $e) {
        echo "  ✗ Failed to enable $name: " . $e->getMessage() . "\n";
    }
}

echo "\nDone!\n";
