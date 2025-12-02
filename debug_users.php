<?php

require 'vendor/autoload.php';
require 'Core/Support/helpers.php';

$app = new \App\Core\Application(__DIR__);
$app->boot();

echo "=== Modules Chargés ===\n";
$modules = $app->moduleManager->getModules();
echo "Total: " . count($modules) . "\n\n";

$usersFound = false;
foreach ($modules as $module) {
    $className = get_class($module);
    echo "- " . $className;

    if (strpos($className, 'Users') !== false) {
        $usersFound = true;
        echo " ✓ USERS MODULE FOUND!\n";

        if (method_exists($module, 'getMenuItems')) {
            $menuItems = $module->getMenuItems();
            echo "  Menu Items: " . count($menuItems) . "\n";
            print_r($menuItems);
        }

        if (method_exists($module, 'getRoutes')) {
            $routes = $module->getRoutes();
            echo "  Route Files: " . count($routes) . "\n";
            print_r($routes);
        }
    } else {
        echo "\n";
    }
}

if (!$usersFound) {
    echo "\n❌ USERS MODULE NOT LOADED!\n";
    echo "\nChecking config/modules.php:\n";
    $configModules = require 'config/modules.php';
    foreach ($configModules as $moduleClass) {
        if (strpos($moduleClass, 'Users') !== false) {
            echo "  Found in config: $moduleClass\n";

            if (class_exists($moduleClass)) {
                echo "  ✓ Class exists\n";
            } else {
                echo "  ❌ Class does NOT exist\n";
            }
        }
    }
}

echo "\n=== Routes Users ===\n";
$allRoutes = $app->router->getRoutes();
$usersRoutes = array_filter($allRoutes, function ($route) {
    return strpos($route['path'], '/admin/users') !== false;
});

echo "Total Users Routes: " . count($usersRoutes) . "\n";
foreach ($usersRoutes as $route) {
    echo "  {$route['method']} {$route['path']}\n";
}
