<?php
// Simulate HTTP Request
$_SERVER['REQUEST_URI'] = '/admin/email-marketing/templates';
$_SERVER['REQUEST_METHOD'] = 'GET';
$_SERVER['SCRIPT_NAME'] = '/index.php';
$_SERVER['HTTP_HOST'] = 'localhost';

// Capture output
ob_start();

try {
    require __DIR__ . '/public/index.php';
} catch (Throwable $e) {
    echo "Exception: " . $e->getMessage();
}

$output = ob_get_clean();
echo "HTTP Response Code: " . http_response_code() . "\n";
echo "Output length: " . strlen($output) . "\n";
echo substr($output, 0, 500); // Show first 500 chars

$logFile = __DIR__ . '/storage/logs/debug_routes.log';
if (file_exists($logFile)) {
    echo "\n\nLog file exists! Content:\n";
    echo file_get_contents($logFile);
} else {
    echo "\n\nLog file DOES NOT exist.\n";
}
