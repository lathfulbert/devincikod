<?php

/**
 * Script to register and activate the EmailMarketing module
 */

require_once __DIR__ . '/vendor/autoload.php';

use App\Core\Application;

$app = new Application(__DIR__);
$app->boot();

try {
    // Get module manager instance
    $moduleManager = $app->moduleManager;

    echo "=== EmailMarketing Module Registration ===\n\n";

    // Step 1: Discover all modules
    echo "1. Discovering modules...\n";
    $moduleManager->discover();

    // Step 2: Sync to registry (this will register EmailMarketing if it has module.json)
    echo "2. Syncing modules to registry...\n";
    $moduleManager->syncToRegistry();

    // Step 3: Check if EmailMarketing is now registered
    $registry = $moduleManager->getRegistry();
    $moduleData = $registry->find('EmailMarketing');

    if ($moduleData) {
        echo "✓ EmailMarketing module found in registry!\n";
        echo "  - Name: {$moduleData['name']}\n";
        echo "  - Version: {$moduleData['version']}\n";
        echo "  - Installed: " . ($moduleData['is_installed'] ? 'Yes' : 'No') . "\n";
        echo "  - Active: " . ($moduleData['is_active'] ? 'Yes' : 'No') . "\n\n";

        // Step 4: Activate if not already active
        if (!$moduleData['is_active']) {
            echo "3. Activating EmailMarketing module...\n";
            $registry->setEnabled('EmailMarketing', true);
            echo "✓ Module activated successfully!\n\n";
        } else {
            echo "3. Module is already active.\n\n";
        }

        // Step 5: Verify final state
        echo "=== Final Status ===\n";
        $finalData = $registry->find('EmailMarketing');
        echo "Module: {$finalData['name']}\n";
        echo "Status: " . ($finalData['is_active'] ? 'ENABLED ✓' : 'DISABLED ✗') . "\n";
        echo "Installed: " . ($finalData['is_installed'] ? 'YES ✓' : 'NO ✗') . "\n";
    } else {
        echo "✗ EmailMarketing module NOT found in registry.\n";
        echo "Please check if module.json exists and is valid.\n";
        exit(1);
    }

    echo "\n=== SUCCESS ===\n";
    echo "EmailMarketing module is now registered and enabled!\n";
    echo "The sidebar menu and routes should now be accessible.\n";
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
    exit(1);
}
