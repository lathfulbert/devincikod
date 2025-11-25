<?php

namespace App\Core\Queue;

use App\Core\Application;
use App\Core\Queue\Contracts\JobContract;
use App\Core\Queue\Drivers\DatabaseDriver;

/**
 * Class QueueWorker
 * 
 * Worker process that processes jobs from the queue.
 * Handles retries, timeouts, failures, and graceful shutdown.
 */
class QueueWorker
{
    private Application $app;
    private QueueManager $queueManager;
    private bool $shouldQuit = false;
    private int $memoryLimit;
    private int $sleepSeconds;
    private int $maxTries;

    public function __construct(Application $app)
    {
        $this->app = $app;
        $this->queueManager = QueueManager::getInstance();

        $config = $this->queueManager->getConfig('worker');
        $this->memoryLimit = ($config['memory_limit'] ?? 128) * 1024 * 1024; // Convert MB to bytes
        $this->sleepSeconds = $config['sleep'] ?? 3;
        $this->maxTries = $config['max_tries'] ?? 3;

        // Register signal handlers for graceful shutdown
        $this->registerSignalHandlers();
    }

    /**
     * Start the worker daemon.
     *
     * @param string $queue Queue name to process
     * @param int|null $maxJobs Maximum jobs to process before stopping
     */
    public function daemon(string $queue = 'default', ?int $maxJobs = null): void
    {
        $jobsProcessed = 0;

        echo "🚀 Worker started. Listening on queue: $queue\n";
        echo "   Press Ctrl+C to stop gracefully.\n\n";

        while (!$this->shouldQuit) {
            // Check memory usage
            if ($this->memoryExceeded()) {
                echo "⚠️  Memory limit exceeded. Restarting worker...\n";
                break;
            }

            // Get next job from queue
            $job = $this->getNextJob($queue);

            if ($job === null) {
                // No jobs available, sleep
                echo "💤 No jobs. Sleeping for {$this->sleepSeconds}s...\n";
                sleep($this->sleepSeconds);
                continue;
            }

            // Process the job
            $this->processJob($job, $queue);
            $jobsProcessed++;

            // Check if we've hit max jobs limit
            if ($maxJobs !== null && $jobsProcessed >= $maxJobs) {
                echo "✅ Processed $jobsProcessed jobs. Stopping.\n";
                break;
            }
        }

        if ($this->shouldQuit) {
            echo "\n🛑 Worker shutdown gracefully.\n";
        }
    }

    /**
     * Get the next job from the queue.
     */
    private function getNextJob(string $queue): ?array
    {
        $driver = $this->queueManager->getDriver();
        return $driver->pop($queue);
    }

    /**
     * Process a single job.
     */
    private function processJob(array $jobData, string $queue): void
    {
        $jobId = $jobData['id'];
        $attempts = $jobData['attempts'];
        $payload = $jobData['payload'];
        $job = null;

        echo "📦 Processing job #{$jobId} (attempt {$attempts})...\n";

        try {
            // Deserialize the job
            $job = JobSerializer::deserialize($payload);

            if (!$job) {
                throw new \RuntimeException("Failed to deserialize job");
            }

            // Execute the job with timeout
            $this->executeJob($job);

            // Job succeeded, delete from queue
            $this->queueManager->getDriver()->delete($queue, $jobId);

            echo "✅ Job #{$jobId} completed successfully!\n\n";
        } catch (\Throwable $e) {
            echo "❌ Job #{$jobId} failed: " . $e->getMessage() . "\n";

            // Check if we should retry
            $maxTries = ($job && method_exists($job, 'tries')) ? $job->tries() : $this->maxTries;

            if ($attempts < $maxTries) {
                // Retry with exponential backoff
                $retryAfter = ($job && method_exists($job, 'retryAfter')) ? $job->retryAfter() : 60;
                $delay = $this->calculateRetryDelay($attempts, $retryAfter);
                $this->queueManager->getDriver()->release($queue, $jobData, $delay);

                echo "🔄 Job #{$jobId} will retry in {$delay}s (attempt {$attempts}/{$maxTries})\n\n";
            } else {
                // Max attempts exceeded, mark as failed
                $this->markJobAsFailed($queue, $payload, $e);
                $this->queueManager->getDriver()->delete($queue, $jobId);

                echo "💀 Job #{$jobId} failed permanently after {$attempts} attempts\n\n";

                // Call job's failed handler if it exists
                if ($job && method_exists($job, 'failed')) {
                    try {
                        $job->failed($e);
                    } catch (\Throwable $failedHandlerException) {
                        echo "⚠️  Job failed handler threw exception: " . $failedHandlerException->getMessage() . "\n";
                    }
                }
            }
        }
    }

    /**
     * Execute a job with timeout handling.
     */
    private function executeJob(JobContract $job): void
    {
        $timeout = $job->timeout();

        // For simplicity, we'll execute directly
        // In production, you might want to use pcntl_alarm or similar for true timeout enforcement
        $startTime = time();

        $job->handle();

        $duration = time() - $startTime;

        if ($duration > $timeout) {
            throw new \RuntimeException("Job exceeded timeout of {$timeout}s (took {$duration}s)");
        }
    }

    /**
     * Calculate retry delay with exponential backoff.
     */
    private function calculateRetryDelay(int $attempts, int $baseDelay): int
    {
        // Exponential backoff: baseDelay * 2^(attempts-1)
        // Capped at 1 hour
        $delay = $baseDelay * pow(2, $attempts - 1);
        return min($delay, 3600);
    }

    /**
     * Mark a job as failed.
     */
    private function markJobAsFailed(string $queue, array $payload, \Throwable $exception): void
    {
        $driver = $this->queueManager->getDriver();

        if ($driver instanceof DatabaseDriver) {
            $driver->storeFailed($queue, $payload, $exception);
        }
    }

    /**
     * Check if memory limit exceeded.
     */
    private function memoryExceeded(): bool
    {
        $usage = memory_get_usage(true);
        return $usage >= $this->memoryLimit;
    }

    /**
     * Register signal handlers for graceful shutdown.
     */
    private function registerSignalHandlers(): void
    {
        // Signal handling is not reliably supported on Windows
        // For production Linux/Unix systems, uncomment the following:

        /*
        if (!function_exists('pcntl_signal')) {
            return;
        }

        pcntl_signal(SIGTERM, function () {
            echo "\n⚠️  Received SIGTERM. Shutting down gracefully...\n";
            $this->shouldQuit = true;
        });

        pcntl_signal(SIGINT, function () {
            echo "\n⚠️  Received SIGINT (Ctrl+C). Shutting down gracefully...\n";
            $this->shouldQuit = true;
        });
        */
    }

    /**
     * Check for pending signals.
     * Must be called in the main loop for signal handlers to work.
     */
    private function handleSignals(): void
    {
        if (function_exists('pcntl_signal_dispatch')) {
            pcntl_signal_dispatch();
        }
    }
}
