<?php

require_once __DIR__ . '/../vendor/autoload.php';

use App\Core\Cache\CacheManager;
use App\Core\Config\Config;
use App\Core\Database\Database;

try {
    // Bootstrap the application properly
    echo "Bootstrapping application...\n";

    // Load environment variables using the framework's DotEnv class
    (new \App\Core\Support\DotEnv(dirname(__DIR__) . '/.env'))->load();

    // Load config
    $config = new Config();
    $config->load(dirname(__DIR__) . '/config/app.php');
    $dbConfig = $config->get('database', []);

    // Connect to database
    $db = Database::getInstance();
    $db->connect($dbConfig);

    echo "Database connected.\n";

    echo "Attempting to get CacheManager instance...\n";
    $cache = CacheManager::getInstance();
    echo "✓ CacheManager instance created successfully.\n";

    $driver = $cache->getDriverName();
    echo "✓ Cache Driver: " . $driver . "\n";

    echo "\n✓ Test passed! The TypeError has been fixed.\n";
} catch (\Throwable $e) {
    echo "✗ Test failed with error: " . $e->getMessage() . "\n";
    echo "\nStack trace:\n" . $e->getTraceAsString() . "\n";
    exit(1);
}
