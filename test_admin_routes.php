<?php
require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/Modules/Admin/AdminModule.php';
$module = new Modules\Admin\AdminModule();
$routes = $module->getRoutes();
foreach ($routes as $r) {
    echo "[{$r['method']}] {$r['path']}\n";
}
