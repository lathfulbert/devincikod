<?php

require_once __DIR__ . '/vendor/autoload.php';

use App\Core\Application;

$app = new Application(__DIR__);
$app->boot();

echo "=== Modules découverts (tous) ===\n\n";

$allModules = $app->moduleManager->getAllModules();
foreach ($allModules as $name => $module) {
    echo "- [$name] " . get_class($module) . "\n";
}

echo "\n=== Modules chargés (activés) ===\n\n";

$modules = $app->moduleManager->getModules();

foreach ($modules as $name => $module) {
    $class = get_class($module);
    echo "- $class\n";

    // Check if it has menu items
    $menuItems = $module->getMenuItems();
    if (!empty($menuItems)) {
        echo "  ✓ Menu items: " . count($menuItems) . "\n";
    }
}

echo "\n=== Modules en BD ===\n\n";
$db = \App\Core\Database\Database::getInstance();
$dbModules = $db->query("SELECT name, slug, is_active FROM modules ORDER BY name")->fetchAll();
foreach ($dbModules as $mod) {
    $active = $mod['is_active'] ? '✓' : '✗';
    echo "$active [{$mod['slug']}] {$mod['name']}\n";
}
