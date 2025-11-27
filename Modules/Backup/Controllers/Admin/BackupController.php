<?php

namespace Modules\Backup\Controllers\Admin;

use App\Core\Application;
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
        $app = Application::getInstance();
        $backups = Backup::query()->orderBy('created_at', 'DESC')->get();

        echo $app->view->render('backup/admin/index', [
            'title' => 'Backups',
            'backups' => $backups
        ]);
    }

    public function create()
    {
        $type = $_POST['type'] ?? 'full';

        try {
            // TODO: Implement auth system - for now use user ID 1
            $this->backupService->runBackup($type, 'user:1');
            $_SESSION['flash_success'] = 'Backup démarré avec succès.';
        } catch (\Exception $e) {
            $_SESSION['flash_error'] = 'Échec du backup: ' . $e->getMessage();
        }

        redirect('/admin/backups');
    }

    public function download($params)
    {
        $id = $params['id'] ?? null;
        $backup = Backup::find($id);

        if (!$backup || $backup->status !== 'completed') {
            $_SESSION['flash_error'] = 'Backup introuvable.';
            redirect('/admin/backups');
            return;
        }

        $config = require __DIR__ . '/../../Config/backup.php';
        $storage = new LocalDriver($config['storage']['drivers']['local']);

        if (!$storage->exists($backup->path)) {
            $_SESSION['flash_error'] = 'Fichier introuvable.';
            redirect('/admin/backups');
            return;
        }

        $stream = $storage->getStream($backup->path);
        if (!$stream) {
            $_SESSION['flash_error'] = 'Impossible de lire le fichier.';
            redirect('/admin/backups');
            return;
        }

        header('Content-Description: File Transfer');
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="' . $backup->filename . '"');
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        header('Content-Length: ' . $backup->size);
        fpassthru($stream);
        fclose($stream);
        exit;
    }

    public function restore($params)
    {
        $id = $params['id'] ?? null;

        try {
            $this->restoreService->restore($id);
            $_SESSION['flash_success'] = 'Système restauré avec succès.';
        } catch (\Exception $e) {
            $_SESSION['flash_error'] = 'Échec de la restauration: ' . $e->getMessage();
        }

        redirect('/admin/backups');
    }

    public function delete($params)
    {
        $id = $params['id'] ?? null;
        $backup = Backup::find($id);

        if ($backup) {
            $config = require __DIR__ . '/../../Config/backup.php';
            $storage = new LocalDriver($config['storage']['drivers']['local']);
            $storage->delete($backup->path);
            $backup->delete();
            $_SESSION['flash_success'] = 'Backup supprimé.';
        } else {
            $_SESSION['flash_error'] = 'Backup introuvable.';
        }

        redirect('/admin/backups');
    }
}
