<?php

require 'vendor/autoload.php';
require 'Core/Support/helpers.php';

$app = new \App\Core\Application(__DIR__);
$app->boot();

echo "=== Vérification du menu Configuration ===\n\n";

// 1. Vérifier les routes
echo "1. Routes Configuration ?\n";
$routes = $app->router->getRoutes();

$expectedRoutes = [
    '/admin/modules' => 'Modules',
    '/admin/cache' => 'Cache',
];

foreach ($expectedRoutes as $path => $name) {
    $found = array_filter($routes, function($r) use ($path) {
        return $r['path'] === $path && $r['method'] === 'GET';
    });

    if (!empty($found)) {
        $route = array_values($found)[0];
        echo "   ✓ $name ($path)\n";
        echo "     → Controller: {$route['handler'][0]}::{$route['handler'][1]}\n";
    } else {
        echo "   ✗ $name ($path) MANQUANTE\n";
    }
}
echo "\n";

// 2. Vérifier le contrôleur ModuleController
echo "2. ModuleController ?\n";
if (class_exists('Modules\Admin\Controllers\ModuleController')) {
    echo "   ✓ ModuleController existe\n";

    $controller = new \Modules\Admin\Controllers\ModuleController();
    if (method_exists($controller, 'index')) {
        echo "   ✓ Méthode index() existe\n";
    }
} else {
    echo "   ✗ ModuleController NOT FOUND\n";
}
echo "\n";

// 3. Vérifier les vues
echo "3. Vue admin/modules/index ?\n";
$view = new \App\Core\View\View(__DIR__ . '/resources/views');
\App\Core\View\View::clearCache();

$reflection = new ReflectionClass($view);
$method = $reflection->getMethod('resolveViewPath');
$method->setAccessible(true);
$resolved = $method->invoke($view, 'admin/modules/index');

if ($resolved && file_exists($resolved)) {
    echo "   ✓ Vue résolue : $resolved\n";
} else {
    echo "   ✗ Vue NON RÉSOLUE\n";

    // Chercher où elle pourrait être
    echo "\n   Recherche de vues modules...\n";
    $possiblePaths = [
        __DIR__ . '/resources/views/backend/modules/index.php',
        __DIR__ . '/Modules/Admin/Views/modules/index.php',
        __DIR__ . '/templates_backup_20251201/backend/modules/index.php',
    ];

    foreach ($possiblePaths as $path) {
        if (file_exists($path)) {
            echo "   → Trouvé : $path\n";
        }
    }
}
echo "\n";

// 4. Vérifier le contrôleur pour Cache
echo "4. CacheController ou méthode cache() ?\n";
if (class_exists('Modules\Admin\Controllers\CacheController')) {
    echo "   ✓ CacheController existe\n";
} elseif (class_exists('Modules\Admin\Controllers\AdminController')) {
    $controller = new \Modules\Admin\Controllers\AdminController();
    if (method_exists($controller, 'cache')) {
        echo "   ✓ AdminController::cache() existe\n";
    } else {
        echo "   ✗ Aucune méthode cache() trouvée\n";
    }
} else {
    echo "   ✗ Pas de contrôleur pour cache\n";
}
