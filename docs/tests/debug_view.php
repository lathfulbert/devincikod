<?php

require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/Core/Support/helpers.php';

use App\Core\Application;

// Mock for CLI
$_SERVER['HTTP_HOST'] = 'localhost';
$_SERVER['REQUEST_URI'] = '/';
$_SERVER['REQUEST_METHOD'] = 'GET';
$_SERVER['SCRIPT_NAME'] = '/index.php';

$app = new Application(__DIR__);
$app->boot();

echo "Base Path: " . $app->getBasePath() . "\n";
echo "Templates Path: " . $app->getBasePath() . '/templates' . "\n";

$viewName = 'backend/wallet/history';
echo "Attempting to render: $viewName\n";

try {
    // Try to locate the file manually first
    $expectedPath = $app->getBasePath() . '/templates/backend/wallet/history.php';
    echo "Expected File: $expectedPath\n";
    echo "File Exists: " . (file_exists($expectedPath) ? 'YES' : 'NO') . "\n";

    // Try rendering
    $app->view->render($viewName, ['title' => 'Test', 'transactions' => []]);
    echo "Render Success!\n";
} catch (\Throwable $e) {
    echo "Render Error: " . $e->getMessage() . "\n";
}
