<?php
// Debug simple
error_reporting(E_ALL);
ini_set('display_errors', '1');

echo "<h1>Test Debug</h1>";
echo "<p>PHP Works!</p>";

try {
    require_once __DIR__ . '/../vendor/autoload.php';
    echo "<p>✓ Autoload Works!</p>";
    
    use App\Core\Application;
    
    $app = new Application(dirname(__DIR__));
    echo "<p>✓ Application Created!</p>";
    
    $app->boot();
    echo "<p>✓ Application Booted!</p>";
    
    echo "<p>Routes loaded: " . count($app->router->getRoutes()) . "</p>";
    
    echo "<h2>Server Variables:</h2>";
    echo "<pre>";
    echo "REQUEST_URI: " . ($_SERVER['REQUEST_URI'] ?? 'N/A') . "\n";
    echo "SCRIPT_NAME: " . ($_SERVER['SCRIPT_NAME'] ?? 'N/A') . "\n";
    echo "REQUEST_METHOD: " . ($_SERVER['REQUEST_METHOD'] ?? 'N/A') . "\n";
    echo "</pre>";
    
    // Now dispatch
    echo "<h2>Attempting dispatch...</h2>";
    $app->run();
    
} catch (\Throwable $e) {
    echo "<p style='color:red'>ERROR: " . $e->getMessage() . "</p>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
}
?>
