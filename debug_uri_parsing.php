<?php
// Simulating what Application::run() does with URI parsing
$_SERVER['REQUEST_URI'] = '/sunuframework2/admin/users/edit/1';
$_SERVER['SCRIPT_NAME'] = '/sunuframework2/public/index.php';
$_SERVER['REQUEST_METHOD'] = 'GET';

$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
echo "Original URI: {$uri}\n";

$scriptName = str_replace('\\', '/', $_SERVER['SCRIPT_NAME']);
$scriptDir = str_replace('\\', '/', dirname($scriptName));

echo "Script name: {$scriptName}\n";
echo "Script dir: {$scriptDir}\n";

// Ensure scriptDir ends with / for consistent matching
if (substr($scriptDir, -1) !== '/') {
    $scriptDir .= '/';
}

echo "Script dir (normalized): {$scriptDir}\n";

// Case 1: Request includes the full path (e.g. /sunuframework2/public/login)
if (strpos($uri, $scriptDir) === 0) {
    $uri = substr($uri, strlen($scriptDir));
    echo "Case 1 matched - URI after strip: {$uri}\n";
} else {
    echo "Case 1 NOT matched\n";
    // Case 2: Request is rewritten (e.g. /sunuframework2/login -> /sunuframework2/public/index.php)
    $publicSegment = '/public/';
    if (substr($scriptDir, -strlen($publicSegment)) === $publicSegment) {
        $baseDir = substr($scriptDir, 0, -strlen($publicSegment) + 1); // Keep trailing slash
        echo "  Base dir: {$baseDir}\n";
        if (strpos($uri, $baseDir) === 0) {
            $uri = substr($uri, strlen($baseDir));
            echo "  Case 2 matched - URI after strip: {$uri}\n";
        } else {
            echo "  Case 2 NOT matched\n";
        }
    }
}

// Cleanup
$uri = '/' . ltrim($uri, '/');

echo "\nFinal URI for router: {$uri}\n";
