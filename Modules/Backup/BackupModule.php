<?php

namespace Modules\Backup;

use App\Core\Module\AbstractModule;

class BackupModule extends AbstractModule
{
    protected function getModulePath(): string
    {
        return __DIR__;
    }

    public function getRoutes(): array
    {
        $authMiddleware = [new \App\Core\Middleware\AuthMiddleware(), 'handle'];

        return [
            // Admin Backup Routes
            ['GET', '/admin/backups', [\Modules\Backup\Controllers\Admin\BackupController::class, 'index'], [$authMiddleware]],
            ['POST', '/admin/backups/create', [\Modules\Backup\Controllers\Admin\BackupController::class, 'create'], [$authMiddleware]],
            ['GET', '/admin/backups/download/{id}', [\Modules\Backup\Controllers\Admin\BackupController::class, 'download'], [$authMiddleware]],
            ['POST', '/admin/backups/restore/{id}', [\Modules\Backup\Controllers\Admin\BackupController::class, 'restore'], [$authMiddleware]],
            ['DELETE', '/admin/backups/delete/{id}', [\Modules\Backup\Controllers\Admin\BackupController::class, 'delete'], [$authMiddleware]],

            // Monitoring Routes
            ['GET', '/admin/backups/health', [\Modules\Backup\Controllers\Admin\MonitoringController::class, 'index'], [$authMiddleware]],
            ['GET', '/admin/backups/stats', [\Modules\Backup\Controllers\Admin\MonitoringController::class, 'stats'], [$authMiddleware]],
        ];
    }

    public function getServices(): array
    {
        return [
            \Modules\Backup\Services\BackupService::class,
            \Modules\Backup\Services\RestoreService::class,
            \Modules\Backup\Services\MonitoringService::class,
        ];
    }

    public function getMenuItems(): array
    {
        return [
            [
                'type' => 'dropdown',
                'title' => 'Backups',
                'icon' => 'database',
                'badge' => null,
                'children' => [
                    [
                        'title' => 'Liste des backups',
                        'url' => '/admin/backups',
                        'icon' => 'list',
                    ],
                    [
                        'title' => 'Santé du système',
                        'url' => '/admin/backups/health',
                        'icon' => 'activity',
                    ],
                ]
            ]
        ];
    }

    public function getPermissions(): array
    {
        return [
            'backup.view',
            'backup.create',
            'backup.restore',
            'backup.delete',
            'backup.view_health',
        ];
    }
    public function boot(): void
    {
        // Register Cron Task
        $cron = \App\Core\Cron\CronScheduler::getInstance();
        $cron->register(new \Modules\Backup\Cron\BackupTask());

        // Register Event Listeners
        $events = \App\Core\Events\EventDispatcher::getInstance();
        $events->listen(\Modules\Backup\Events\BackupSuccessful::class, [\Modules\Backup\Listeners\SendBackupNotification::class, 'onBackupSuccessful']);
        $events->listen(\Modules\Backup\Events\BackupFailed::class, [\Modules\Backup\Listeners\SendBackupNotification::class, 'onBackupFailed']);
    }
}
