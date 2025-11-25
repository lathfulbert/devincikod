<?php

namespace App\Core\Queue\Drivers;

use App\Core\Queue\Contracts\QueueDriverContract;
use App\Core\Redis\RedisConnection;

/**
 * Class RedisDriver
 * 
 * Redis-based queue driver for high-performance job processing.
 * Uses Redis lists for queues and hashes for job data.
 */
class RedisDriver implements QueueDriverContract
{
    private RedisConnection $redis;
    private string $prefix;

    public function __construct(array $config)
    {
        $this->redis = RedisConnection::getInstance($config);
        $this->prefix = $config['prefix'] ?? 'queue:';
    }

    /**
     * Push a job onto the queue.
     */
    public function push(string $queue, array $payload): bool
    {
        // Generate unique job ID
        $jobId = $this->redis->incr("{$this->prefix}job_id");

        // Serialize payload
        $payloadJson = json_encode($payload);

        // Store job data
        $jobKey = "{$this->prefix}job:{$jobId}";
        $this->redis->hmset($jobKey, [
            'id' => (string)$jobId,
            'queue' => $queue,
            'payload' => $payloadJson,
            'attempts' => '0',
            'reserved_at' => '',
            'available_at' => (string)time(),
            'created_at' => (string)time(),
        ]);

        // Push job ID to queue
        $queueKey = "{$this->prefix}queue:{$queue}";
        $this->redis->lpush($queueKey, (string)$jobId);

        return true;
    }

    /**
     * Pop a job from the queue.
     */
    public function pop(string $queue): ?array
    {
        $queueKey = "{$this->prefix}queue:{$queue}";

        // Pop job ID from queue
        $jobId = $this->redis->rpop($queueKey);

        if ($jobId === null) {
            return null;
        }

        // Get job data
        $jobKey = "{$this->prefix}job:{$jobId}";
        $jobData = $this->redis->hgetall($jobKey);

        if (empty($jobData)) {
            return null;
        }

        // Mark as reserved
        $now = time();
        $this->redis->hset($jobKey, 'reserved_at', (string)$now);
        $attempts = (int)($jobData['attempts'] ?? 0) + 1;
        $this->redis->hset($jobKey, 'attempts', (string)$attempts);

        // Add to reserved set (for timeout tracking)
        $this->redis->zadd("{$this->prefix}reserved:{$queue}", $now, (string)$jobId);

        // Unserialize payload
        $payload = json_decode($jobData['payload'] ?? '{}', true);

        return [
            'id' => (int)$jobData['id'],
            'queue' => $jobData['queue'],
            'payload' => $payload,
            'attempts' => $attempts,
            'reserved_at' => $now,
        ];
    }

    /**
     * Release a reserved job back onto the queue.
     */
    public function release(string $queue, array $job, int $delay = 0): bool
    {
        $jobId = $job['id'] ?? null;

        if (!$jobId) {
            return false;
        }

        $jobKey = "{$this->prefix}job:{$jobId}";

        // Check if job exists
        if (!$this->redis->exists($jobKey)) {
            return false;
        }

        // Remove from reserved set
        $this->redis->zrem("{$this->prefix}reserved:{$queue}", (string)$jobId);

        // Update job data
        $this->redis->hset($jobKey, 'reserved_at', '');
        $this->redis->hset($jobKey, 'available_at', (string)(time() + $delay));

        if ($delay > 0) {
            // Add to delayed queue
            $this->redis->zadd("{$this->prefix}delayed:{$queue}", time() + $delay, (string)$jobId);
        } else {
            // Push back to queue immediately
            $this->redis->lpush("{$this->prefix}queue:{$queue}", (string)$jobId);
        }

        return true;
    }

    /**
     * Delete a job from the queue.
     */
    public function delete(string $queue, string $id): bool
    {
        $jobKey = "{$this->prefix}job:{$id}";

        // Remove from reserved set
        $this->redis->zrem("{$this->prefix}reserved:{$queue}", $id);

        // Delete job data
        $this->redis->delete($jobKey);

        return true;
    }

    /**
     * Get queue size.
     */
    public function size(string $queue): int
    {
        $queueKey = "{$this->prefix}queue:{$queue}";
        return $this->redis->llen($queueKey);
    }

    /**
     * Clear all jobs from a queue.
     */
    public function clear(string $queue): bool
    {
        $queueKey = "{$this->prefix}queue:{$queue}";

        // Get all job IDs from queue
        $jobIds = $this->redis->getRedis()->lRange($queueKey, 0, -1);

        // Delete all job data
        foreach ($jobIds as $jobId) {
            $this->redis->delete("{$this->prefix}job:{$jobId}");
        }

        // Delete queue
        $this->redis->delete($queueKey);

        // Delete reserved and delayed sets
        $this->redis->delete("{$this->prefix}reserved:{$queue}");
        $this->redis->delete("{$this->prefix}delayed:{$queue}");

        return true;
    }

    // Additional Redis-specific methods

    /**
     * Store a failed job.
     */
    public function failed(array $jobData, \Throwable $exception): void
    {
        $failedJobId = $this->redis->incr("{$this->prefix}failed_job_id");

        // Serialize payload
        $payload = isset($jobData['payload']) ? json_encode($jobData['payload']) : '{}';

        $failedKey = "{$this->prefix}failed_job:{$failedJobId}";
        $this->redis->hmset($failedKey, [
            'id' => (string)$failedJobId,
            'queue' => $jobData['queue'] ?? 'default',
            'payload' => $payload,
            'exception' => $exception->getMessage(),
            'failed_at' => (string)time(),
        ]);

        // Add to failed jobs sorted set
        $this->redis->zadd("{$this->prefix}failed_jobs", time(), (string)$failedJobId);
    }

    /**
     * Get all failed jobs.
     */
    public function getFailedJobs(): array
    {
        // Get all failed job IDs
        $failedJobIds = $this->redis->zrange("{$this->prefix}failed_jobs", 0, -1);

        $failedJobs = [];
        foreach ($failedJobIds as $failedJobId) {
            $failedKey = "{$this->prefix}failed_job:{$failedJobId}";
            $jobData = $this->redis->hgetall($failedKey);

            if (!empty($jobData)) {
                $payload = json_decode($jobData['payload'] ?? '{}', true);

                $failedJobs[] = [
                    'id' => (int)$jobData['id'],
                    'queue' => $jobData['queue'],
                    'payload' => $payload,
                    'exception' => $jobData['exception'],
                    'failed_at' => date('Y-m-d H:i:s', (int)$jobData['failed_at']),
                ];
            }
        }

        return $failedJobs;
    }

    /**
     * Retry a failed job.
     */
    public function retryFailedJob(int $failedJobId): void
    {
        $failedKey = "{$this->prefix}failed_job:{$failedJobId}";
        $jobData = $this->redis->hgetall($failedKey);

        if (empty($jobData)) {
            throw new \Exception("Failed job #{$failedJobId} not found");
        }

        // Unserialize payload
        $payload = json_decode($jobData['payload'] ?? '{}', true);

        // Re-push to queue
        $this->push($jobData['queue'], $payload);

        // Delete failed job
        $this->deleteFailedJob($failedJobId);
    }

    /**
     * Delete a failed job.
     */
    public function deleteFailedJob(int $failedJobId): void
    {
        $failedKey = "{$this->prefix}failed_job:{$failedJobId}";

        // Remove from failed jobs set
        $this->redis->zrem("{$this->prefix}failed_jobs", (string)$failedJobId);

        // Delete job data
        $this->redis->delete($failedKey);
    }

    /**
     * Process delayed jobs (move them to main queue if ready).
     */
    public function processDelayedJobs(string $queue): void
    {
        $delayedKey = "{$this->prefix}delayed:{$queue}";
        $now = time();

        // Get jobs ready to be processed
        $readyJobs = $this->redis->getRedis()->zRangeByScore($delayedKey, 0, $now);

        foreach ($readyJobs as $jobId) {
            // Remove from delayed
            $this->redis->zrem($delayedKey, $jobId);

            // Push to main queue
            $this->redis->lpush("{$this->prefix}queue:{$queue}", $jobId);
        }
    }
}
