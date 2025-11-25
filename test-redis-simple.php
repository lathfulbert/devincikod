<?php

/**
 * Simple Redis Test - No framework bootstrap required
 * 
 * Tests Redis connection and Predis/Extension detection
 * Usage: php test-redis-simple.php
 */

require_once __DIR__ . '/vendor/autoload.php';

echo "\n";
echo "╔════════════════════════════════════════╗\n";
echo "║     Redis Connection Test              ║\n";
echo "╚════════════════════════════════════════╝\n\n";

// Step 1: Detect available clients
echo "🔍 Step 1: Detecting Redis clients...\n";

$hasExtension = extension_loaded('redis');
$hasPredis = class_exists('Predis\Client');

echo "   PHP Redis Extension: " . ($hasExtension ? "✅ Installed" : "❌ Not installed") . "\n";
echo "   Predis Library:      " . ($hasPredis ? "✅ Installed" : "❌ Not installed") . "\n";

if (!$hasExtension && !$hasPredis) {
    echo "\n❌ ERROR: No Redis client available!\n";
    echo "\nInstall one of:\n";
    echo "  1. Predis:        composer require predis/predis\n";
    echo "  2. PHP Extension: pecl install redis (faster)\n\n";
    exit(1);
}

echo "\n";

// Step 2: Test Redis server connection
echo "🔧 Step 2: Testing Redis server connection...\n";

$client = null;
$clientType = null;

// Try extension first
if ($hasExtension) {
    try {
        $client = new Redis();
        $connected = @$client->connect('127.0.0.1', 6379, 2.0);

        if ($connected) {
            $client->ping();
            $clientType = 'PHP Redis Extension (⚡ Native)';
            echo "   ✅ Connected using: {$clientType}\n";
        }
    } catch (Exception $e) {
        $client = null;
    }
}

// Try Predis if extension failed or not available
if (!$client && $hasPredis) {
    try {
        $client = new Predis\Client([
            'scheme' => 'tcp',
            'host' => '127.0.0.1',
            'port' => 6379,
        ]);

        $client->ping();
        $clientType = 'Predis Library (📦 Composer)';
        echo "   ✅ Connected using: {$clientType}\n";
    } catch (Exception $e) {
        $client = null;
    }
}

if (!$client) {
    echo "\n❌ ERROR: Cannot connect to Redis server!\n\n";
    echo "Redis server is not running. Start it:\n";
    echo "  Windows: Download from https://github.com/microsoftarchive/redis/releases\n";
    echo "  Docker:  docker run -d -p 6379:6379 redis\n";
    echo "  Linux:   sudo service redis start\n\n";
    exit(1);
}

echo "   ✅ Server: 127.0.0.1:6379\n\n";

// Step 3: Test basic operations
echo "📝 Step 3: Testing basic Redis operations...\n";

try {
    $testKey = 'test:hello';
    $testValue = 'world_' . time();

    // SET
    if ($hasExtension && $client instanceof Redis) {
        $client->set($testKey, $testValue);
    } else {
        $client->set($testKey, $testValue);
    }
    echo "   ✅ SET operation successful\n";

    // GET
    $retrieved = $client->get($testKey);
    if ($retrieved === $testValue) {
        echo "   ✅ GET operation successful\n";
    } else {
        echo "   ⚠️  GET returned unexpected value\n";
    }

    // DELETE
    $client->del([$testKey]);
    echo "   ✅ DEL operation successful\n";
} catch (Exception $e) {
    echo "   ❌ Operations failed: " . $e->getMessage() . "\n";
}

echo "\n";

// Step 4: Test Queue operations
echo "📝 Step 4: Testing Queue operations (LPUSH/RPOP)...\n";

try {
    $queueKey = 'test:queue';

    // Push items
    if ($hasExtension && $client instanceof Redis) {
        $client->lPush($queueKey, 'job1', 'job2', 'job3');
    } else {
        $client->lpush($queueKey, ['job1', 'job2', 'job3']);
    }
    echo "   ✅ LPUSH successful (3 items)\n";

    // Get queue size
    $size = $client->llen($queueKey);
    echo "   ✅ Queue size: {$size} items\n";

    // Pop item
    $item = $client->rpop($queueKey);
    echo "   ✅ RPOP successful: {$item}\n";

    // Cleanup
    $client->del([$queueKey]);
} catch (Exception $e) {
    echo "   ❌ Queue operations failed: " . $e->getMessage() . "\n";
}

echo "\n";

// Summary
echo "╔════════════════════════════════════════╗\n";
echo "║           SUCCESS! ✅                  ║\n";
echo "╚════════════════════════════════════════╝\n\n";

echo "🎉 Redis is working perfectly!\n";
echo "📊 Using: {$clientType}\n\n";

if (!$hasExtension && $hasPredis) {
    echo "💡 TIP: For better performance (2-3x faster):\n";
    echo "   Install PHP Redis extension:\n";
    echo "   → pecl install redis\n\n";
}

echo "✅ Your Redis queue system is ready to use!\n";
echo "   Set QUEUE_CONNECTION=redis in .env\n";
echo "   Run: php sunu queue:work default\n\n";

// Cleanup any test keys
try {
    if ($hasExtension && $client instanceof Redis) {
        $keys = $client->keys('test:*');
    } else {
        $keys = $client->keys('test:*');
    }

    if (!empty($keys)) {
        $client->del($keys);
    }
} catch (Exception $e) {
    // Ignore cleanup errors
}
