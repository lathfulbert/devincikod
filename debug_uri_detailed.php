<?php
// Exact simulation of web request
$_SERVER['REQUEST_URI'] = '/sunuframework2/admin/users/edit/1';
$_SERVER['SCRIPT_NAME'] = '/sunuframework2/public/index.php';

$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
echo "1. Original URI: {$uri}\n";

$scriptName = str_replace('\\', '/', $_SERVER['SCRIPT_NAME']);
$scriptDir = str_replace('\\', '/', dirname($scriptName));

echo "2. Script name: {$scriptName}\n";
echo "3. Script dir: {$scriptDir}\n";

// Ensure scriptDir ends with / for consistent matching
if (substr($scriptDir, -1) !== '/') {
    $scriptDir .= '/';
}

echo "4. Script dir (normalized): {$scriptDir}\n";
echo "5. Check if URI starts with scriptDir: " . (strpos($uri, $scriptDir) === 0 ? 'YES' : 'NO') . "\n";

// Case 1: Request includes the full path (e.g. /sunuframework2/public/login)
if (strpos($uri, $scriptDir) === 0) {
    $uri = substr($uri, strlen($scriptDir));
    echo "6. Case 1 MATCHED - URI after strip: {$uri}\n";
} else {
    echo "6. Case 1 NOT matched\n";
    // Case 2: Request is rewritten (e.g. /sunuframework2/login -> /sunuframework2/public/index.php)
    $publicSegment = '/public/';
    echo "7. Check if scriptDir ends with '/public/': " . (substr($scriptDir, -strlen($publicSegment)) === $publicSegment ? 'YES' : 'NO') .  "\n";

    if (substr($scriptDir, -strlen($publicSegment)) === $publicSegment) {
        $baseDir = substr($scriptDir, 0, -strlen($publicSegment) + 1); // Keep trailing slash
        echo "8. Base dir calculated: {$baseDir}\n";
        echo "9. Check if URI starts with baseDir: " . (strpos($uri, $baseDir) === 0 ? 'YES' : 'NO') . "\n";

        if (strpos($uri, $baseDir) === 0) {
            $uri = substr($uri, strlen($baseDir));
            echo "10. Case 2 MATCHED - URI after strip: {$uri}\n";
        } else {
            echo "10. Case 2 NOT matched\n";
        }
    }
}

// Cleanup
$uri = '/' . ltrim($uri, '/');

echo "\n===== FINAL URI for router: {$uri} =====\n";
