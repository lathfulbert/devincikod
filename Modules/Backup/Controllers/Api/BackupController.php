<?php

namespace Modules\Backup\Controllers\Api;

use App\Core\Http\Controller;
use App\Core\Http\Request;
use App\Core\Http\Response;
use Modules\Backup\Models\Backup;
use Modules\Backup\Services\BackupService;
use Modules\Backup\Services\RestoreService;
use Modules\Backup\Services\Storage\LocalDriver;

class BackupController extends Controller
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
        return Response::json(['status' => 'success', 'data' => $backups]);
    }

    public function run(Request $request)
    {
        $type = $request->input('type', 'full');

        try {
            $backup = $this->backupService->runBackup($type, 'api_user:' . auth()->id());
            return Response::json(['status' => 'success', 'message' => 'Backup completed', 'data' => $backup]);
        } catch (\Exception $e) {
            return Response::json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }

    public function show($id)
    {
        $backup = Backup::find($id);
        if (!$backup) {
            return Response::json(['status' => 'error', 'message' => 'Backup not found'], 404);
        }
        return Response::json(['status' => 'success', 'data' => $backup]);
    }

    public function delete($id)
    {
        $backup = Backup::find($id);
        if (!$backup) {
            return Response::json(['status' => 'error', 'message' => 'Backup not found'], 404);
        }

        $config = require __DIR__ . '/../../config/backup.php';
        $storage = new LocalDriver($config['storage']['drivers']['local']);
        $storage->delete($backup->path);
        $backup->delete();

        return Response::json(['status' => 'success', 'message' => 'Backup deleted']);
    }
}
