<?php

namespace Modules\Backup\Controllers\Admin;

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
        return $this->view('Backup::admin.index', ['backups' => $backups]);
    }

    public function create(Request $request)
    {
        $type = $request->input('type', 'full');

        try {
            $this->backupService->runBackup($type, 'user:' . auth()->id());
            return Response::redirect(route('admin.backups.index'))->with('success', 'Backup started successfully.');
        } catch (\Exception $e) {
            return Response::redirect(route('admin.backups.index'))->with('error', 'Backup failed: ' . $e->getMessage());
        }
    }

    public function download($id)
    {
        $backup = Backup::find($id);
        if (!$backup || !$backup->isSuccessful()) {
            return Response::redirect(route('admin.backups.index'))->with('error', 'Backup not found.');
        }

        $config = require __DIR__ . '/../../config/backup.php';
        $storage = new LocalDriver($config['storage']['drivers']['local']);

        if (!$storage->exists($backup->path)) {
            return Response::redirect(route('admin.backups.index'))->with('error', 'File not found.');
        }

        $path = $storage->getFullPath($backup->path); // This is a hack, should use stream response

        header('Content-Description: File Transfer');
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="' . basename($path) . '"');
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        header('Content-Length: ' . filesize($path));
        readfile($path);
        exit;
    }

    public function restore($id)
    {
        try {
            $this->restoreService->restore($id);
            return Response::redirect(route('admin.backups.index'))->with('success', 'System restored successfully.');
        } catch (\Exception $e) {
            return Response::redirect(route('admin.backups.index'))->with('error', 'Restore failed: ' . $e->getMessage());
        }
    }

    public function delete($id)
    {
        $backup = Backup::find($id);
        if ($backup) {
            $config = require __DIR__ . '/../../config/backup.php';
            $storage = new LocalDriver($config['storage']['drivers']['local']);
            $storage->delete($backup->path);
            $backup->delete();
        }

        return Response::redirect(route('admin.backups.index'))->with('success', 'Backup deleted.');
    }
}
