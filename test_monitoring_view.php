<?php

require 'vendor/autoload.php';
require 'Core/Support/helpers.php';

$app = new \App\Core\Application(__DIR__);
$app->boot();

echo "=== Test de la vue Monitoring ===\n\n";

// Test de résolution de vue
$view = new \App\Core\View\View(__DIR__ . '/resources/views');
\App\Core\View\View::clearCache();

// Supprimer le log existant
@unlink(__DIR__ . '/storage/logs/debug_view_render.log');

echo "Test de résolution pour 'admin.monitoring.index':\n";
$reflection = new ReflectionClass($view);
$method = $reflection->getMethod('resolveViewPath');
$method->setAccessible(true);
$resolved = $method->invoke($view, 'admin.monitoring.index');

if ($resolved) {
    echo "✓ Vue résolue : $resolved\n";
    if (file_exists($resolved)) {
        echo "✓ Fichier existe\n";
    } else {
        echo "✗ Fichier n'existe pas !\n";
    }
} else {
    echo "✗ Vue NON RÉSOLUE\n\n";

    // Chercher manuellement
    echo "Recherche manuelle de la vue...\n";
    $possiblePaths = [
        __DIR__ . '/resources/views/backend/monitoring/index.php',
        __DIR__ . '/Modules/Admin/Views/monitoring/index.php',
    ];

    foreach ($possiblePaths as $path) {
        if (file_exists($path)) {
            echo "✓ Trouvé : $path\n";
        } else {
            echo "✗ Pas trouvé : $path\n";
        }
    }
}

// Vérifier le log de résolution
echo "\n=== Log de résolution ===\n";
if (file_exists(__DIR__ . '/storage/logs/debug_view_render.log')) {
    echo file_get_contents(__DIR__ . '/storage/logs/debug_view_render.log');
}
