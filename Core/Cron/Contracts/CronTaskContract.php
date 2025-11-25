<?php

namespace App\Core\Cron\Contracts;

/**
 * Interface CronTaskContract
 * 
 * Contract that all cron tasks must implement.
 * Defines the standard behavior for scheduled tasks.
 */
interface CronTaskContract
{
    /**
     * Execute the cron task.
     *
     * @return void
     */
    public function handle(): void;

    /**
     * Get the cron expression for this task.
     * Example: "0 0 * * *" (daily at midnight)
     *
     * @return string Cron expression
     */
    public function expression(): string;

    /**
     * Get a human-readable description of this task.
     *
     * @return string Task description
     */
    public function description(): string;

    /**
     * Determine if overlapping executions should be prevented.
     * If true, a mutex lock will be used to ensure only one instance runs at a time.
     *
     * @return bool
     */
    public function withoutOverlapping(): bool;

    /**
     * Get the maximum execution timeout in seconds.
     * The task will be killed if it exceeds this time.
     *
     * @return int Timeout in seconds
     */
    public function timeout(): int;

    /**
     * Get the timezone for this task.
     *
     * @return string Timezone (e.g., 'UTC', 'America/New_York')
     */
    public function timezone(): string;
}
