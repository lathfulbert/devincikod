<?php

require 'vendor/autoload.php';
require 'Core/Support/helpers.php';

$app = new \App\Core\Application(__DIR__);
$app->boot();

echo "=== Vérification du menu SMS ===\n\n";

// 1. Vérifier les routes SMS
echo "1. Routes SMS ?\n";
$routes = $app->router->getRoutes();
$smsRoutes = array_filter($routes, function($r) {
    return stripos($r['path'], '/admin/sms') !== false || stripos($r['path'], '/admin/api-keys') !== false;
});

echo "Routes SMS/API trouvées : " . count($smsRoutes) . "\n";
foreach ($smsRoutes as $r) {
    if ($r['method'] === 'GET') {
        echo "  {$r['method']} {$r['path']} -> {$r['handler'][0]}::{$r['handler'][1]}\n";
    }
}

echo "\n2. Menu SMS dans la sidebar ?\n";
$menuItems = \App\Core\View\SidebarService::getItems();
$smsMenu = array_filter($menuItems, function($item) {
    return isset($item['title']) && stripos($item['title'], 'sms') !== false;
});

if (!empty($smsMenu)) {
    foreach ($smsMenu as $menu) {
        echo "   ✓ Menu '{$menu['title']}' trouvé\n";
        if (isset($menu['children'])) {
            foreach ($menu['children'] as $child) {
                echo "     → {$child['title']} : {$child['url']}\n";
            }
        }
    }
} else {
    echo "   ✗ Menu SMS NOT FOUND\n";
}

echo "\n3. Recherche de la route /admin/sms\n";
$smsRoute = array_filter($routes, function($r) {
    return $r['path'] === '/admin/sms' && $r['method'] === 'GET';
});

if (!empty($smsRoute)) {
    $route = array_values($smsRoute)[0];
    echo "   ✓ Route /admin/sms trouvée\n";
    echo "   - Controller: {$route['handler'][0]}::{$route['handler'][1]}\n";
} else {
    echo "   ✗ Route /admin/sms NOT FOUND\n";
}

echo "\n4. Vérification du module SmsCore\n";
if (class_exists('Modules\SmsCore\SmsCoreModule')) {
    echo "   ✓ SmsCoreModule existe\n";

    $app = \App\Core\Application::getInstance();
    $modules = $app->moduleManager->getModules();
    $smsModule = null;
    foreach ($modules as $module) {
        if (get_class($module) === 'Modules\SmsCore\SmsCoreModule') {
            $smsModule = $module;
            break;
        }
    }

    if ($smsModule) {
        echo "   ✓ Module SmsCore chargé\n";
        $routes = $smsModule->getRoutes();
        echo "   - Fichiers de routes : " . count($routes) . "\n";
        foreach ($routes as $routeFile) {
            echo "     → " . basename($routeFile) . "\n";
        }
    } else {
        echo "   ✗ Module SmsCore NON CHARGÉ\n";
    }
} else {
    echo "   ✗ SmsCoreModule NOT FOUND\n";
}
