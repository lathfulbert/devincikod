<?php

require 'vendor/autoload.php';
require 'Core/Support/helpers.php';

$app = new \App\Core\Application(__DIR__);
$app->boot();

echo "=== Vérification des routes Queue ===\n\n";

$routes = $app->router->getRoutes();

// Chercher toutes les routes contenant "queue"
$queueRoutes = array_filter($routes, function($r) {
    return stripos($r['path'], 'queue') !== false;
});

echo "Routes Queue trouvées : " . count($queueRoutes) . "\n\n";
foreach ($queueRoutes as $r) {
    echo "{$r['method']} {$r['path']}\n";
}

echo "\n=== Routes attendues d'après le menu ===\n";
$expectedRoutes = [
    '/admin/queue',           // Dashboard
    '/admin/queue/jobs',      // Active Jobs
    '/admin/queue/failed',    // Failed Jobs
    '/admin/queue/stats',     // Statistics
];

foreach ($expectedRoutes as $path) {
    $found = array_filter($queueRoutes, function($r) use ($path) {
        return $r['path'] === $path;
    });

    if (empty($found)) {
        echo "✗ MANQUANTE : $path\n";
    } else {
        echo "✓ Existe : $path\n";
    }
}

echo "\n=== Vérification du QueueController ===\n";
if (class_exists('Modules\Admin\Controllers\QueueController')) {
    echo "✓ QueueController existe\n";

    $controller = new \Modules\Admin\Controllers\QueueController();
    $methods = get_class_methods($controller);

    echo "\nMéthodes disponibles :\n";
    foreach ($methods as $method) {
        if (!in_array($method, ['__construct', '__destruct'])) {
            echo "  - $method()\n";
        }
    }
} else {
    echo "✗ QueueController N'EXISTE PAS\n";
}
