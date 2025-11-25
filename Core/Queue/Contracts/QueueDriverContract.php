<?php

namespace App\Core\Queue\Contracts;

/**
 * Interface QueueDriverContract
 * 
 * Contract for queue driver implementations (Database, Redis, File, etc.).
 * All drivers must implement these methods to be compatible with the QueueManager.
 */
interface QueueDriverContract
{
    /**
     * Push a new job onto the queue.
     *
     * @param string $queue Queue name
     * @param array $payload Serialized job data
     * @return bool Success status
     */
    public function push(string $queue, array $payload): bool;

    /**
     * Pop the next job off of the queue.
     *
     * @param string $queue Queue name
     * @return array|null Job data or null if queue is empty
     */
    public function pop(string $queue): ?array;

    /**
     * Release a reserved job back onto the queue.
     *
     * @param string $queue Queue name
     * @param array $job Job data
     * @param int $delay Delay in seconds before the job is available again
     * @return bool Success status
     */
    public function release(string $queue, array $job, int $delay = 0): bool;

    /**
     * Delete a job from the queue.
     *
     * @param string $queue Queue name
     * @param string $id Job ID
     * @return bool Success status
     */
    public function delete(string $queue, string $id): bool;

    /**
     * Get the size of the queue.
     *
     * @param string $queue Queue name
     * @return int Number of jobs in queue
     */
    public function size(string $queue): int;

    /**
     * Clear all jobs from the queue.
     *
     * @param string $queue Queue name
     * @return bool Success status
     */
    public function clear(string $queue): bool;
}
