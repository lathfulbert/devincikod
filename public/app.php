<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/../vendor/autoload.php';

use App\Core\Application;

try {
    $app = new Application(dirname(__DIR__));
    $app->boot();
    $app->run();
} catch (\Throwable $e) {
    echo "<h1>Application Error</h1>";
    echo "<pre>";
    echo "Message: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . "\n";
    echo "Line: " . $e->getLine() . "\n\n";
    echo "Trace:\n" . $e->getTraceAsString();
    echo "</pre>";
    exit(1);
}
