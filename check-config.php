<?php

declare(strict_types=1);

/**
 * PHP Configuration Checker
 * 
 * Checks PHP 8.3 configuration and recommends optimizations
 */

echo "=== PHP 8.3 Configuration Check ===\n\n";

// PHP Version
echo "PHP Version: " . PHP_VERSION . "\n";
if (version_compare(PHP_VERSION, '8.3.0', '<')) {
    echo "⚠️  Warning: PHP 8.3+ recommended\n";
} else {
    echo "✅ PHP version OK\n";
}
echo "\n";

// OPcache
echo "--- OPcache ---\n";
if (function_exists('opcache_get_status')) {
    $opcache = opcache_get_status();
    if ($opcache === false) {
        echo "❌ OPcache is disabled\n";
    } else {
        echo "✅ OPcache is enabled\n";
        echo "  Memory Used: " . round($opcache['memory_usage']['used_memory'] / 1024 / 1024, 2) . " MB\n";
        echo "  Memory Free: " . round($opcache['memory_usage']['free_memory'] / 1024 / 1024, 2) . " MB\n";
        echo "  Hit Rate: " . round($opcache['opcache_statistics']['opcache_hit_rate'], 2) . "%\n";
        echo "  Cached Scripts: " . $opcache['opcache_statistics']['num_cached_scripts'] . "\n";

        // JIT
        if (isset($opcache['jit'])) {
            echo "\n--- JIT ---\n";
            echo "✅ JIT is available\n";
            echo "  Mode: " . ($opcache['jit']['enabled'] ? 'Enabled' : 'Disabled') . "\n";
            if ($opcache['jit']['enabled']) {
                echo "  Buffer Size: " . round($opcache['jit']['buffer_size'] / 1024 / 1024, 2) . " MB\n";
            }
        } else {
            echo "\n--- JIT ---\n";
            echo "⚠️  JIT not configured\n";
        }
    }
} else {
    echo "❌ OPcache extension not loaded\n";
}
echo "\n";

// Strict Types
echo "--- Code Quality ---\n";
echo "declare(strict_types=1): Manually check files\n";
echo "Union Types: PHP 8.0+ ✅\n";
echo "Enums: PHP 8.1+ ✅\n";
echo "Readonly: PHP 8.1+ ✅\n";
echo "\n";

// Security
echo "--- Security ---\n";
echo "expose_php: " . (ini_get('expose_php') ? '❌ On (should be Off)' : '✅ Off') . "\n";
echo "display_errors: " . (ini_get('display_errors') ? '⚠️  On (turn Off in production)' : '✅ Off') . "\n";
echo "\n";

// Performance Settings
echo "--- Performance Settings ---\n";
echo "memory_limit: " . ini_get('memory_limit') . "\n";
echo "max_execution_time: " . ini_get('max_execution_time') . "s\n";
echo "upload_max_filesize: " . ini_get('upload_max_filesize') . "\n";
echo "post_max_size: " . ini_get('post_max_size') . "\n";
echo "\n";

// Recommendations
echo "=== Recommendations ===\n";
$recommendations = [];

if (!function_exists('opcache_get_status') || opcache_get_status() === false) {
    $recommendations[] = "Enable OPcache for 2-3x performance boost";
}

if (function_exists('opcache_get_status') && !isset(opcache_get_status()['jit']['enabled'])) {
    $recommendations[] = "Configure JIT for additional performance (opcache.jit=tracing)";
}

if (ini_get('expose_php')) {
    $recommendations[] = "Set expose_php=Off for security";
}

if (empty($recommendations)) {
    echo "✅ Configuration looks good!\n";
} else {
    foreach ($recommendations as $i => $rec) {
        echo ($i + 1) . ". " . $rec . "\n";
    }
}

echo "\n=== Check Complete ===\n";
