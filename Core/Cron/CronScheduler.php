<?php

namespace App\Core\Cron;

use App\Core\Cron\Contracts\CronTaskContract;
use App\Core\Database\Database;

/**
 * Class CronScheduler
 * 
 * Manages the registration and scheduling of cron tasks.
 */
class CronScheduler
{
    private static ?CronScheduler $instance = null;
    private array $tasks = [];
    private array $config;

    public static function getInstance(): CronScheduler
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct()
    {
        $this->loadConfig();
    }

    private function loadConfig(): void
    {
        $configPath = __DIR__ . '/../../config/cron.php';
        if (file_exists($configPath)) {
            $this->config = require $configPath;
        } else {
            $this->config = [
                'timezone' => 'UTC',
                'logging' => ['enabled' => true, 'table' => 'cron_logs'],
            ];
        }
    }

    /**
     * Register a cron task.
     */
    public function register(CronTaskContract $task): void
    {
        $this->tasks[] = $task;
    }

    /**
     * Get all registered tasks.
     *
     * @return CronTaskContract[]
     */
    public function getTasks(): array
    {
        return $this->tasks;
    }

    /**
     * Get tasks that are due to run now.
     *
     * @return CronTaskContract[]
     */
    public function getDueTasks(): array
    {
        $dueTasks = [];
        $timezone = $this->config['timezone'] ?? 'UTC';

        foreach ($this->tasks as $task) {
            // Use task's timezone if specified, otherwise global default
            $taskTimezone = method_exists($task, 'timezone') ? $task->timezone() : $timezone;

            if (CronExpression::isDue($task->expression(), $taskTimezone)) {
                $dueTasks[] = $task;
            }
        }

        return $dueTasks;
    }

    /**
     * Check if a task is currently running (mutex).
     */
    public function isRunning(CronTaskContract $task): bool
    {
        if (!$task->withoutOverlapping()) {
            return false;
        }

        $mutexFile = $this->getMutexPath($task);

        if (!file_exists($mutexFile)) {
            return false;
        }

        // Check if process is still alive (Linux only)
        // For Windows/simple implementation, we check file age vs timeout
        $pid = (int)file_get_contents($mutexFile);

        // Simple timeout check: if lock file is older than task timeout, assume it crashed
        $timeout = $task->timeout();
        if (time() - filemtime($mutexFile) > $timeout) {
            $this->releaseLock($task);
            return false;
        }

        return true;
    }

    /**
     * Acquire a lock for the task.
     */
    public function acquireLock(CronTaskContract $task): bool
    {
        if (!$task->withoutOverlapping()) {
            return true;
        }

        if ($this->isRunning($task)) {
            return false;
        }

        $mutexFile = $this->getMutexPath($task);
        $dir = dirname($mutexFile);

        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        file_put_contents($mutexFile, getmypid());
        return true;
    }

    /**
     * Release the lock for the task.
     */
    public function releaseLock(CronTaskContract $task): void
    {
        if (!$task->withoutOverlapping()) {
            return;
        }

        $mutexFile = $this->getMutexPath($task);
        if (file_exists($mutexFile)) {
            unlink($mutexFile);
        }
    }

    private function getMutexPath(CronTaskContract $task): string
    {
        $hash = md5(get_class($task));
        return __DIR__ . '/../../storage/cron/mutex/' . $hash . '.lock';
    }
}
