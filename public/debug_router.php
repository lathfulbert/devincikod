<?php

require_once __DIR__ . '/../vendor/autoload.php';

use App\Core\Application;

// Mock Application to expose protected logic if needed, or just dump vars
echo "<pre>";
echo "REQUEST_URI: " . $_SERVER['REQUEST_URI'] . "\n";
echo "SCRIPT_NAME: " . $_SERVER['SCRIPT_NAME'] . "\n";
echo "REQUEST_METHOD: " . $_SERVER['REQUEST_METHOD'] . "\n";

$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$scriptName = str_replace('\\', '/', $_SERVER['SCRIPT_NAME']);
$scriptDir = str_replace('\\', '/', dirname($scriptName));

if (substr($scriptDir, -1) !== '/') {
    $scriptDir .= '/';
}

echo "Script Dir: " . $scriptDir . "\n";

if (strpos($uri, $scriptDir) === 0) {
    $uri = substr($uri, strlen($scriptDir));
    echo "Case 1 matched. URI: " . $uri . "\n";
} else {
    $publicSegment = '/public/';
    if (substr($scriptDir, -strlen($publicSegment)) === $publicSegment) {
        $baseDir = substr($scriptDir, 0, -strlen($publicSegment) + 1);
        echo "Base Dir: " . $baseDir . "\n";
        if (strpos($uri, $baseDir) === 0) {
            $uri = substr($uri, strlen($baseDir));
            echo "Case 2 matched. URI: " . $uri . "\n";
        } else {
            echo "Case 2 NOT matched.\n";
        }
    } else {
        echo "Public segment not found in script dir.\n";
    }
}

$uri = '/' . ltrim($uri, '/');
echo "Final URI: " . $uri . "\n";

echo "\n--- Registered Routes ---\n";
$app = new Application(dirname(__DIR__));
$app->boot();
$routes = $app->router->getRoutes();
foreach ($routes as $route) {
    echo $route['method'] . " " . $route['path'] . "\n";
}
echo "</pre>";
