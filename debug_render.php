<?php
require_once __DIR__ . '/vendor/autoload.php';

use App\Core\Application;

$app = new Application(__DIR__);
$app->boot();

try {
    echo "Starting render...\n";
    echo $app->view->render('backend.dashboard');
    echo "\nRender complete.\n";
} catch (Throwable $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString();
}
