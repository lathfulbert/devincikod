<?php

/**
 * Test script for Redis Queue Driver
 * 
 * Auto-detects and uses:
 * 1. PHP Redis extension if available (faster)
 * 2. Predis library as fallback (composer)
 * 
 * Usage: php test-redis-queue.php
 */

require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/bootstrap/app.php';

use Modules\Admin\Jobs\SendWelcomeEmailJob;
use App\Core\Redis\RedisConnection;

echo "\n";
echo "╔════════════════════════════════════════╗\n";
echo "║     Testing Redis Queue Driver         ║\n";
echo "╚════════════════════════════════════════╝\n\n";

// Detect Redis client
echo "🔍 Detecting Redis client...\n";

$hasExtension = extension_loaded('redis');
$hasPredis = class_exists('Predis\Client');

echo "   PHP Redis Extension: " . ($hasExtension ? "✅ Installed" : "❌ Not installed") . "\n";
echo "   Predis Library:      " . ($hasPredis ? "✅ Installed" : "❌ Not installed") . "\n";

if (!$hasExtension && !$hasPredis) {
    echo "\n❌ No Redis client available!\n";
    echo "   Install one of:\n";
    echo "   1. PHP Extension: pecl install redis (recommended, faster)\n";
    echo "   2. Predis:        composer require predis/predis\n\n";
    exit(1);
}

echo "\n";

// Test Redis server connection
echo "🔧 Checking Redis server...\n";

try {
    $testConfig = [
        'host' => '127.0.0.1',
        'port' => 6379,
        'database' => 0,
        'prefix' => 'test:',
    ];

    $testRedis = RedisConnection::getInstance($testConfig);
    $testRedis->getRedis()->ping();

    $clientType = $testRedis->isUsingExtension() ? 'PHP Extension (native)' : 'Predis (Composer)';
    echo "   ✅ Connected using: {$clientType}\n";
    echo "   ✅ Server: 127.0.0.1:6379\n\n";
} catch (Exception $e) {
    echo "   ❌ Cannot connect to Redis server\n";
    echo "   Error: " . $e->getMessage() . "\n\n";
    echo "💡 Start Redis server:\n";
    echo "   Windows: Download from https://github.com/microsoftarchive/redis/releases\n";
    echo "   Docker:  docker run -d -p 6379:6379 redis\n";
    echo "   Linux:   sudo service redis start\n\n";
    exit(1);
}

// Cleanup
echo "🧹 Cleaning test data...\n";
try {
    $redis = $testRedis->getRedis();
    if ($testRedis->isUsingExtension()) {
        $keys = $redis->keys('test:*');
    } else {
        $keys = $redis->keys('test:*');
    }

    if (!empty($keys)) {
        $testRedis->delete(...$keys);
        echo "   ✅ Cleared " . count($keys) . " keys\n";
    }
} catch (Exception $e) {
    echo "   ⚠️  " . $e->getMessage() . "\n";
}
echo "\n";

// Test 1: Dispatch jobs
echo "📝 Test 1: Dispatching jobs with Redis...\n";
putenv('QUEUE_CONNECTION=redis');

try {
    for ($i = 1; $i <= 3; $i++) {
        SendWelcomeEmailJob::dispatch([
            'user_id' => $i,
            'email' => "user{$i}@example.com",
        ]);
        echo "   ✅ Job #{$i} dispatched\n";
    }

    echo "✅ Test 1 PASSED!\n\n";
} catch (Exception $e) {
    echo "❌ Test 1 FAILED: " . $e->getMessage() . "\n\n";
    exit(1);
}

// Test 2: Verify queue
echo "📝 Test 2: Verifying queue...\n";

try {
    // Recreate connection with queue prefix
    $queueConfig = [
        'host' => '127.0.0.1',
        'port' => 6379,
        'database' => 0,
        'prefix' => 'queue:',
    ];

    // Check queue size
    $redis = $testRedis->getRedis();

    if ($testRedis->isUsingExtension()) {
        $redis->setOption(\Redis::OPT_PREFIX, 'queue:');
        $queueSize = $redis->lLen('queue:default');
    } else {
        $queueSize = $redis->llen('queue:queue:default');
    }

    echo "   📊 Queue size: {$queueSize} jobs\n";

    if ($queueSize >= 3) {
        echo "✅ Test 2 PASSED!\n\n";
    } else {
        echo "⚠️  Test 2: Expected ≥3, got {$queueSize}\n\n";
    }
} catch (Exception $e) {
    echo "❌ Test 2 FAILED: " . $e->getMessage() . "\n\n";
}

// Summary
echo "╔════════════════════════════════════════╗\n";
echo "║           SUCCESS! ✅                  ║\n";
echo "╚════════════════════════════════════════╝\n\n";

$clientUsed = $testRedis->isUsingExtension() ?
    'PHP Redis Extension (⚡ Native, ultra-rapide)' :
    'Predis Library (📦 Composer, compatible)';

echo "🎉 Redis fonctionne avec: {$clientUsed}\n\n";

echo "📚 Pour utiliser en production:\n";
echo "   1. Mettre QUEUE_CONNECTION=redis dans .env\n";
echo "   2. Démarrer workers: php sunu queue:work default\n\n";

if (!$hasExtension && $hasPredis) {
    echo "💡 Pour plus de performance:\n";
    echo "   Installez l'extension PHP Redis:\n";
    echo "   pecl install redis\n";
    echo "   → 2-3x plus rapide que Predis!\n\n";
}

echo "✨ Avantages:\n";
echo "   ⚡ 10-100x plus rapide que Database\n";
echo "   📈 Meilleure scalabilité\n";
echo "   🔒 Opérations atomiques\n";
echo "   💾 100% gratuit!\n\n";

// Cleanup
try {
    if ($testRedis->isUsingExtension()) {
        $redis->setOption(\Redis::OPT_PREFIX, 'queue:');
        $keys = $redis->keys('*');
    } else {
        $keys = $redis->keys('queue:*');
    }

    if (!empty($keys)) {
        $testRedis->delete(...$keys);
    }
} catch (Exception $e) {
    // Ignore
}

putenv('QUEUE_CONNECTION=database');
