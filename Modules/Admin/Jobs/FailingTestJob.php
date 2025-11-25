<?php

namespace Modules\Admin\Jobs;

use App\Core\Queue\Job;

/**
 * FailingTestJob
 * 
 * Job that fails intentionally to test retry mechanism.
 */
class FailingTestJob extends Job
{
    protected int $tries = 3;
    protected int $retryAfter = 5; // 5 seconds base delay

    public function handle(): void
    {
        $data = $this->getData();
        $attempt = $data['attempt'] ?? 1;

        echo "🔥 FailingTestJob executing (attempt $attempt)...\n";

        // Always fail for testing
        throw new \RuntimeException("This job is designed to fail for testing retry logic");
    }

    public function failed(\Throwable $exception): void
    {
        echo "💀 FailingTestJob failed permanently: " . $exception->getMessage() . "\n";
        error_log("FailingTestJob failed: " . $exception->getMessage());
    }
}
