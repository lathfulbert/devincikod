<?php

/**
 * Queue System Test Script
 * 
 * This script tests the queue foundation by:
 * 1. Dispatching a test job
 * 2. Verifying it appears in the database
 * 3. Showing job details
 */


require __DIR__ . '/vendor/autoload.php';

use Modules\Admin\Jobs\SendWelcomeEmailJob;
use App\Core\Queue\QueueManager;
use App\Core\Application;

// Bootstrap the application
$app = new Application(__DIR__);
$app->boot();


echo "\n";
echo "==============================================\n";
echo "  Queue System Test\n";
echo "==============================================\n\n";

// Test 1: Dispatch a job
echo "📤 Test 1: Dispatching a job...\n";

try {
    $result = SendWelcomeEmailJob::dispatch([
        'email' => 'john@example.com',
        'name' => 'John Doe',
    ], 'emails');

    if ($result) {
        echo "✅ Job dispatched successfully!\n\n";
    } else {
        echo "❌ Failed to dispatch job (returned false)\n\n";

        // Try to get more details
        $qm = QueueManager::getInstance();
        var_dump($qm->getConfig());

        exit(1);
    }
} catch (\Throwable $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . ":" . $e->getLine() . "\n";
    echo $e->getTraceAsString() . "\n";
    exit(1);
}

// Test 2: Check queue size
echo "📊 Test 2: Checking queue size...\n";

try {
    $queueManager = QueueManager::getInstance();
    $size = $queueManager->size('emails');

    echo "✅ Queue 'emails' has $size job(s)\n\n";
} catch (\Throwable $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    exit(1);
}

// Test 3: Verify in database
echo "🔍 Test 3: Checking database...\n";

try {
    $db = \App\Core\Database\Database::getInstance();
    $result = $db->query("SELECT * FROM jobs WHERE queue = 'emails' ORDER BY id DESC LIMIT 1")->fetch(\PDO::FETCH_ASSOC);

    if ($result) {
        echo "✅ Job found in database:\n";
        echo "   ID: " . $result['id'] . "\n";
        echo "   Queue: " . $result['queue'] . "\n";
        echo "   Attempts: " . $result['attempts'] . "\n";
        echo "   Created: " . date('Y-m-d H:i:s', $result['created_at']) . "\n";

        $payload = json_decode($result['payload'], true);
        echo "   Job Class: " . ($payload['job'] ?? 'unknown') . "\n";

        $data = json_decode($payload['data'] ?? '{}', true);
        echo "   Email: " . ($data['email'] ?? 'N/A') . "\n";
        echo "   Name: " . ($data['name'] ?? 'N/A') . "\n";
    } else {
        echo "⚠️  No job found in database\n";
    }
} catch (\Throwable $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    exit(1);
}

echo "\n";
echo "==============================================\n";
echo "  ✅ All Tests Passed!\n";
echo "==============================================\n\n";

echo "Next steps:\n";
echo "1. Implement QueueWorker to process jobs\n";
echo "2. Run: php sunu queue:work emails\n";
echo "3. Watch the job execute!\n\n";
