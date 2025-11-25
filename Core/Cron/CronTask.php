<?php

namespace App\Core\Cron;

use App\Core\Cron\Contracts\CronTaskContract;

/**
 * Class CronTask
 * 
 * Abstract base class for all cron tasks.
 * Provides default implementations for common cron task behaviors.
 */
abstract class CronTask implements CronTaskContract
{
    /**
     * Whether to prevent overlapping executions.
     */
    protected bool $withoutOverlapping = true;

    /**
     * The maximum execution timeout in seconds.
     */
    protected int $timeout = 300; // 5 minutes

    /**
     * Timezone for this task.
     */
    protected string $timezone = 'UTC';

    /**
     * Execute the cron task.
     * This method must be implemented by subclasses.
     */
    abstract public function handle(): void;

    /**
     * Get the cron expression.
     * This method must be implemented by subclasses.
     * 
     * Examples:
     * - "* * * * *"      Every minute
     * - "0 * * * *"      Hourly
     * - "0 0 * * *"      Daily at midnight
     * - "0 0 * * 0"      Weekly on Sunday
     * - "0 0 1 * *"      Monthly on the 1st
     * - "* /5 * * * *"   Every 5 minutes (remove space)
     */
    abstract public function expression(): string;

    /**
     * Get task description.
     * This method must be implemented by subclasses.
     */
    abstract public function description(): string;

    /**
     * Determine if overlapping executions should be prevented.
     */
    public function withoutOverlapping(): bool
    {
        return $this->withoutOverlapping;
    }

    /**
     * Get the timeout in seconds.
     */
    public function timeout(): int
    {
        return $this->timeout;
    }

    /**
     * Get the timezone.
     */
    public function timezone(): string
    {
        return $this->timezone;
    }

    /**
     * Hook called before the task runs.
     * Override in subclasses if needed.
     */
    protected function before(): void
    {
        // Override in subclasses
    }

    /**
     * Hook called after the task completes successfully.
     * Override in subclasses if needed.
     */
    protected function after(): void
    {
        // Override in subclasses
    }

    /**
     * Hook called if the task fails.
     * Override in subclasses if needed.
     */
    protected function onFailure(\Throwable $exception): void
    {
        // Override in subclasses
    }
}
