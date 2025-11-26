<?php

namespace Modules\Backup\Cron;

use App\Core\Cron\Contracts\CronTaskContract;
use Modules\Backup\Services\BackupService;

class BackupTask implements CronTaskContract
{
    protected BackupService $backupService;

    public function __construct()
    {
        $this->backupService = new BackupService();
    }

    public function handle(): void
    {
        // Run full backup
        $this->backupService->runBackup('full', 'cron');
    }

    public function expression(): string
    {
        // Daily at 02:00 AM
        return '0 2 * * *';
    }

    public function description(): string
    {
        return 'Daily System Backup (Database + Files)';
    }

    public function withoutOverlapping(): bool
    {
        return true;
    }

    public function timeout(): int
    {
        return 3600; // 1 hour
    }

    public function timezone(): string
    {
        return 'UTC';
    }
}
