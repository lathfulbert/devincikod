<?php

require_once __DIR__ . '/../vendor/autoload.php';

use App\Core\Application;
use App\Core\Module\ModuleManager;

// Initialize Application to setup container and DB
$app = new Application(dirname(__DIR__));

// Boot the application to connect to DB and load config
$app->boot();

// Get ModuleManager
$moduleManager = $app->moduleManager;

echo "Discovering modules...\n";
$moduleManager->discover();

echo "Syncing to registry...\n";
$moduleManager->syncToRegistry();

echo "Checking AI module status...\n";
$registry = $moduleManager->getRegistry();
$aiModule = $registry->find('AI');

if ($aiModule) {
    echo "AI Module found in registry.\n";
    if (!$aiModule['is_enabled']) {
        echo "AI Module is disabled. Enabling...\n";
        $registry->setEnabled('AI', true);
        echo "AI Module enabled.\n";
    } else {
        echo "AI Module is already enabled.\n";
    }
} else {
    echo "AI Module NOT found in registry even after sync!\n";
}

echo "Done.\n";
