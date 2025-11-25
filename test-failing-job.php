<?php

/**
 * Test Failing Job & Retry Logic
 */

require __DIR__ . '/vendor/autoload.php';

use Modules\Admin\Jobs\FailingTestJob;
use App\Core\Application;

$app = new Application(__DIR__);
$app->boot();

echo "\n";
echo "Testing Failed Job & Retry Logic\n";
echo "=================================\n\n";

// Dispatch a job that will fail
FailingTestJob::dispatch(['attempt' => 1], 'test');

echo "✅ Failing job dispatched to 'test' queue\n";
echo "\nNow run: php sunu queue:work test --max-jobs=1\n";
echo "The job will fail and be retried automatically.\n\n";
