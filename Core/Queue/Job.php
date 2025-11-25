<?php

namespace App\Core\Queue;

use App\Core\Queue\Contracts\JobContract;
use App\Core\Queue\Contracts\ShouldQueue;

/**
 * Class Job
 * 
 * Abstract base class for all queued jobs.
 * Provides default implementations and a convenient dispatch() method.
 */
abstract class Job implements JobContract, ShouldQueue
{
    /**
     * The maximum number of times the job may be attempted.
     */
    protected int $tries = 3;

    /**
     * The number of seconds to wait before retrying the job.
     */
    protected int $retryAfter = 60;

    /**
     * The maximum number of seconds the job can run.
     */
    protected int $timeout = 60;

    /**
     * The job data payload.
     */
    protected array $data = [];

    /**
     * Execute the job.
     * This method must be implemented by subclasses.
     */
    abstract public function handle(): void;

    /**
     * Get the number of times to attempt the job.
     */
    public function tries(): int
    {
        return $this->tries;
    }

    /**
     * Get the number of seconds to wait before retrying.
     */
    public function retryAfter(): int
    {
        return $this->retryAfter;
    }

    /**
     * Get the maximum timeout for the job.
     */
    public function timeout(): int
    {
        return $this->timeout;
    }

    /**
     * Handle a job failure.
     * Override this method to implement custom failure logic (e.g., logging, notifications).
     */
    public function failed(\Throwable $exception): void
    {
        // Default: do nothing
        // Subclasses can override to add custom failure handling
    }

    /**
     * Set the job data.
     */
    public function setData(array $data): self
    {
        $this->data = $data;
        return $this;
    }

    /**
     * Get the job data.
     */
    public function getData(): array
    {
        return $this->data;
    }

    /**
     * Dispatch this job to the queue.
     *
     * @param array $data Job payload data
     * @param string $queue Queue name
     * @return bool Success status
     */
    public static function dispatch(array $data = [], string $queue = 'default'): bool
    {
        return QueueManager::getInstance()->push(static::class, $data, $queue);
    }

    /**
     * Dispatch this job to run after a delay.
     *
     * @param int $delay Delay in seconds
     * @param array $data Job payload data
     * @param string $queue Queue name
     * @return bool Success status
     */
    public static function dispatchAfter(int $delay, array $data = [], string $queue = 'default'): bool
    {
        return QueueManager::getInstance()->pushDelayed(static::class, $data, $queue, $delay);
    }
}
