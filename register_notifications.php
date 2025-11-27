<?php

require_once __DIR__ . '/vendor/autoload.php';

use App\Core\Application;

$app = new Application(__DIR__);
$app->boot();

// Get the module registry
$registry = $app->moduleManager->getRegistry();

// Check if Notifications module exists
if (!$registry->isInstalled('Notifications')) {
    echo "Registering Notifications module...\n";

    // Register the module
    $module = $app->moduleManager->getModule('Notifications');
    if ($module) {
        $manifestPath = __DIR__ . '/Modules/Notifications/module.json';
        $manifest = \App\Core\Module\ModuleManifest::fromFile($manifestPath);

        if ($manifest) {
            $registry->register($module, $manifest);
            echo "Module registered successfully!\n";

            // Mark as installed
            $db = \App\Core\Database\Database::getInstance();
            $db->query("UPDATE modules SET installed = 1 WHERE name = ?", ['Notifications']);
            echo "Module marked as installed!\n";
        }
    }
} else {
    echo "Notifications module already registered.\n";
}

echo "Done!\n";
