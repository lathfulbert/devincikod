<?php

require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/Core/Support/helpers.php';

use App\Core\Application;

// Initialize application
$app = new Application(__DIR__);

// Test rendering the dashboard
echo "Testing template rendering...\n\n";

try {
    $html = view('backend.dashboard', [
        'title' => 'Test Dashboard',
        'products' => []
    ]);
    
    echo "✓ Template rendered successfully!\n";
    echo "Length: " . strlen($html) . " bytes\n";
    
    // Check for includes
    if (strpos($html, 'page-header') !== false) {
        echo "✓ Header included successfully\n";
    } else {
        echo "✗ Header NOT included\n";
    }
    
    if (strpos($html, 'sidebar-wrapper') !== false) {
        echo "✓ Sidebar included successfully\n";
    } else {
        echo "✗ Sidebar NOT included\n";
    }
    
    if (strpos($html, 'footer') !== false) {
        echo "✓ Footer included successfully\n";
    } else {
        echo "✗ Footer NOT included\n";
    }
    
    // Check compiled files
    echo "\nCompiled template files:\n";
    $files = glob(__DIR__ . '/storage/cache/views/*.php');
    echo "Found " . count($files) . " compiled templates\n";
    
} catch (Exception $e) {
    echo "✗ Error: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}
