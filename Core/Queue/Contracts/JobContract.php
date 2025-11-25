<?php

namespace App\Core\Queue\Contracts;

/**
 * Interface JobContract
 * 
 * Contract that all jobs must implement.
 * Defines the standard behavior for asynchronous jobs.
 */
interface JobContract
{
    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(): void;

    /**
     * Get the number of times to attempt the job.
     *
     * @return int
     */
    public function tries(): int;

    /**
     * Get the number of seconds to wait before retrying after a failure.
     *
     * @return int
     */
    public function retryAfter(): int;

    /**
     * Get the maximum timeout for the job in seconds.
     *
     * @return int
     */
    public function timeout(): int;

    /**
     * Handle a job failure.
     *
     * @param \Throwable $exception
     * @return void
     */
    public function failed(\Throwable $exception): void;
}
