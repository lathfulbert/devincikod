<?php

namespace Modules\Backup\Controllers\Api;

use Modules\Backup\Models\Backup;
use Modules\Backup\Services\BackupService;
use Modules\Backup\Services\RestoreService;
use Modules\Backup\Services\Storage\LocalDriver;

class BackupController
{
    protected BackupService $backupService;
    protected RestoreService $restoreService;

    public function __construct()
    {
        $this->backupService = new BackupService();
        $this->restoreService = new RestoreService();
    }

    public function index()
    {
        $backups = Backup::query()->orderBy('created_at', 'DESC')->get();

        header('Content-Type: application/json');
        return json_encode(['status' => 'success', 'data' => $backups]);
        exit;
    }

    public function run()
    {
        $type = $_POST['type'] ?? 'full';

        try {
            $backup = $this->backupService->runBackup($type, 'api_user:1');
            header('Content-Type: application/json');
            return json_encode(['status' => 'success', 'message' => 'Backup completed', 'data' => $backup]);
        } catch (\Exception $e) {
            http_response_code(500);
            header('Content-Type: application/json');
            return json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }
        exit;
    }

    public function show($params)
    {
        $id = $params['id'] ?? null;
        $backup = Backup::find($id);

        if (!$backup) {
            http_response_code(404);
            header('Content-Type: application/json');
            return json_encode(['status' => 'error', 'message' => 'Backup not found']);
            exit;
        }

        header('Content-Type: application/json');
        return json_encode(['status' => 'success', 'data' => $backup]);
        exit;
    }

    public function delete($params)
    {
        $id = $params['id'] ?? null;
        $backup = Backup::find($id);

        if (!$backup) {
            http_response_code(404);
            header('Content-Type: application/json');
            return json_encode(['status' => 'error', 'message' => 'Backup not found']);
            exit;
        }

        $config = require __DIR__ . '/../../config/backup.php';
        $storage = new LocalDriver($config['storage']['drivers']['local']);
        $storage->delete($backup->path);
        $backup->delete();

        header('Content-Type: application/json');
        return json_encode(['status' => 'success', 'message' => 'Backup deleted']);
        exit;
    }
}
