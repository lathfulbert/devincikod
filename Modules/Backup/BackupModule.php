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
        return [
            'admin' => __DIR__ . '/Routes/admin.php',
            'api' => __DIR__ . '/Routes/api_v1.php',
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
                'title' => 'Backups',
                'icon' => 'fas fa-database',
                'route' => 'admin.backups.index',
                'permission' => 'backup.view',
                'children' => [
                    [
                        'title' => 'Liste des backups',
                        'route' => 'admin.backups.index',
                        'permission' => 'backup.view',
                    ],
                    [
                        'title' => 'Santé du système',
                        'route' => 'admin.backups.health',
                        'permission' => 'backup.view_health',
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
