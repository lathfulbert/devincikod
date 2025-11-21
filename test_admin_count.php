<?php
require_once 'vendor/autoload.php';
require_once 'Modules/Admin/AdminModule.php';

$adminModule = new \Modules\Admin\AdminModule();
$routes = $adminModule->getRoutes();

echo "Admin module has " . count($routes) . " routes\n";
echo "First route: " . $routes[0][0] . " " . $routes[0][1] . "\n";
echo "Last route: " . $routes[count($routes) - 1][0] . " " . $routes[count($routes) - 1][1] . "\n";
