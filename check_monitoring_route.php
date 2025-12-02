<?php

require 'vendor/autoload.php';
require 'Core/Support/helpers.php';

$app = new \App\Core\Application(__DIR__);
$app->boot();

echo "=== Recherche de la route Monitoring ===\n\n";

$routes = $app->router->getRoutes();

// Chercher toutes les routes contenant "monitoring"
$monitoringRoutes = array_filter($routes, function($r) {
    return stripos($r['path'], 'monitoring') !== false;
});

echo "Routes Monitoring trouvées : " . count($monitoringRoutes) . "\n\n";
foreach ($monitoringRoutes as $r) {
    echo "{$r['method']} {$r['path']}\n";
}

// Chercher la route exacte /admin/monitoring
echo "\n=== Recherche de /admin/monitoring ===\n";
$adminMonitoring = array_filter($routes, function($r) {
    return $r['path'] === '/admin/monitoring';
});

if (empty($adminMonitoring)) {
    echo "❌ Route /admin/monitoring NON TROUVÉE !\n\n";

    echo "Routes Admin disponibles :\n";
    $adminRoutes = array_filter($routes, function($r) {
        return strpos($r['path'], '/admin') === 0;
    });

    foreach ($adminRoutes as $r) {
        echo "  {$r['method']} {$r['path']}\n";
    }
} else {
    echo "✓ Route /admin/monitoring trouvée\n";
    foreach ($adminMonitoring as $r) {
        print_r($r);
    }
}

echo "\n=== Vérification du contrôleur MonitoringController ===\n";
if (class_exists('Modules\Admin\Controllers\MonitoringController')) {
    echo "✓ MonitoringController existe\n";
} else {
    echo "❌ MonitoringController N'EXISTE PAS\n";
}
