<?php
require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/Modules/Admin/AdminModule.php';
$module = new Modules\Admin\AdminModule();
$routes = $module->getRoutes();
echo "Total routes from AdminModule: " . count($routes) . "\n";
foreach ($routes as $r) {
    // Indexed array format: [method, path, handler, middleware]
    echo "[{$r[0]}] {$r[1]}\n";
}
