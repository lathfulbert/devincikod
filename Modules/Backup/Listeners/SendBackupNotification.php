<?php

namespace Modules\Backup\Listeners;

use Modules\Backup\Events\BackupSuccessful;
use Modules\Backup\Events\BackupFailed;
use Modules\Notifications\Services\NotificationService;
use App\Core\Application;

class SendBackupNotification
{
    protected NotificationService $notificationService;

    public function __construct()
    {
        if (class_exists(NotificationService::class) && class_exists(Application::class)) {
            $this->notificationService = new NotificationService(Application::getInstance());
        }
    }

    public function onBackupSuccessful(BackupSuccessful $event): void
    {
        if (!isset($this->notificationService)) {
            return;
        }

        // Send to Admin (User ID 1)
        $this->notificationService->send([
            'event' => 'backup.successful',
            'user_id' => 1,
            'template' => 'backup_success_email', // Email template
            'channels' => ['email', 'database'],
            'data' => [
                'backup_id' => $event->backup->id,
                'filename' => $event->backup->filename,
                'size' => $event->backup->size,
                'date' => $event->backup->created_at,
            ]
        ]);
    }

    public function onBackupFailed(BackupFailed $event): void
    {
        if (!isset($this->notificationService)) {
            return;
        }

        $this->notificationService->send([
            'event' => 'backup.failed',
            'user_id' => 1,
            'template' => 'backup_failed_email',
            'channels' => ['email', 'database'],
            'data' => [
                'backup_id' => $event->backup->id,
                'error' => $event->exception->getMessage(),
                'date' => $event->backup->created_at,
            ]
        ]);
    }
}
