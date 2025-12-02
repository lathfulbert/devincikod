<?php

require 'vendor/autoload.php';
require 'Core/Support/helpers.php';

$app = new \App\Core\Application(__DIR__);
$app->boot();

echo "=== Vérification des routes API Keys ===\n\n";

$routes = $app->router->getRoutes();

// Routes attendues
$expectedRoutes = [
    ['GET', '/admin/api-keys', 'index'],
    ['POST', '/admin/api-keys/generate', 'generate'],
    ['POST', '/admin/api-keys/revoke', 'revoke'],
    ['GET', '/admin/api-keys/docs', 'docs'],
];

echo "Routes API Keys :\n";
foreach ($expectedRoutes as [$method, $path, $action]) {
    $found = array_filter($routes, function($r) use ($method, $path) {
        return $r['method'] === $method && $r['path'] === $path;
    });

    if (!empty($found)) {
        echo "   ✓ $method $path\n";
    } else {
        echo "   ✗ $method $path MANQUANTE\n";
    }
}

echo "\n=== Recherche du fichier de routes Auth ===\n";
$authRoutesFile = __DIR__ . '/Modules/Auth/Routes/web.php';
if (file_exists($authRoutesFile)) {
    echo "✓ Fichier trouvé : $authRoutesFile\n";
} else {
    echo "✗ Fichier NON TROUVÉ\n";
}
