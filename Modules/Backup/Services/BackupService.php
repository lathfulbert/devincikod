<?php

declare(strict_types=1);

namespace Modules\Backup\Services;

use Modules\Backup\Models\Backup;
use Modules\Backup\Services\Storage\LocalDriver;
use Modules\Backup\Services\Storage\StorageDriverInterface;
use Modules\Backup\Enums\BackupType;
use Modules\Backup\Enums\BackupStatus;

class BackupService
{
    protected StorageDriverInterface $storage;
    protected array $config;

    public function __construct()
    {
        $this->config = require __DIR__ . '/../config/backup.php';
        // For now, we only support local driver or we need a factory
        // In a real app, we would use a StorageManager to get the driver
        $this->storage = new LocalDriver($this->config['storage']['drivers']['local']);
    }

    public function runBackup(BackupType|string $type = 'full', string $initiatedBy = 'system'): Backup
    {
        // Convert string to enum if needed
        $backupType = $type instanceof BackupType ? $type : BackupType::from($type);

        $backup = new Backup([
            'type' => $backupType->value,
            'status' => BackupStatus::Processing->value,
            'initiated_by' => $initiatedBy,
            'disk' => 'local',
            'created_at' => date('Y-m-d H:i:s'),
        ]);
        $backup->save();

        try {
            $files = [];

            if ($backupType->includesDatabase()) {
                $files['database'] = $this->backupDatabase();
            }

            if ($backupType->includesFiles()) {
                $files['files'] = $this->backupFiles();
            }

            // If full backup, maybe zip everything together?
            // For now, let's keep them as separate files or zip them into one archive

            $finalPath = $this->packageBackup($files, $backupType->value);

            $backup->status = BackupStatus::Completed->value;
            $backup->path = $finalPath;
            $backup->filename = basename($finalPath);
            $backup->size = $this->storage->size($finalPath);
            $backup->completed_at = date('Y-m-d H:i:s');
            $backup->save();

            \App\Core\Events\EventDispatcher::getInstance()->dispatch(new \Modules\Backup\Events\BackupSuccessful($backup));

            return $backup;
        } catch (\Exception $e) {
            $backup->status = BackupStatus::Failed->value;
            $backup->error_message = $e->getMessage();
            $backup->save();

            \App\Core\Events\EventDispatcher::getInstance()->dispatch(new \Modules\Backup\Events\BackupFailed($backup, $e));

            throw $e;
        }
    }

    protected function backupDatabase(): string
    {
        $dbConfig = require __DIR__ . '/../../../../config/database.php'; // Adjust path to core config
        // Assuming MySQL for now
        $host = $dbConfig['connections']['mysql']['host'] ?? '127.0.0.1';
        $port = $dbConfig['connections']['mysql']['port'] ?? '3306';
        $user = $dbConfig['connections']['mysql']['username'] ?? 'root';
        $pass = $dbConfig['connections']['mysql']['password'] ?? '';
        $db   = $dbConfig['connections']['mysql']['database'] ?? 'sunuframework';

        $filename = 'db_' . date('Y-m-d_H-i-s') . '.sql';
        $tempPath = sys_get_temp_dir() . '/' . $filename;

        // Construct mysqldump command
        // WARNING: Putting password in command line is insecure in shared envs, but common in simple scripts
        // Better to use .my.cnf
        $cmd = sprintf(
            'mysqldump --host=%s --port=%s --user=%s %s %s > %s',
            escapeshellarg($host),
            escapeshellarg($port),
            escapeshellarg($user),
            $pass ? '--password=' . escapeshellarg($pass) : '',
            escapeshellarg($db),
            escapeshellarg($tempPath)
        );

        // Execute
        $output = [];
        $returnVar = 0;
        exec($cmd, $output, $returnVar);

        if ($returnVar !== 0) {
            throw new \Exception("Database backup failed with exit code $returnVar");
        }

        // Compress if needed
        if ($this->config['database']['compression']) {
            $gzPath = $tempPath . '.gz';
            $fp = gzopen($gzPath, 'w9');
            gzwrite($fp, file_get_contents($tempPath));
            gzclose($fp);
            unlink($tempPath);
            $tempPath = $gzPath;
            $filename .= '.gz';
        }

        // Move to storage
        $this->storage->put($filename, file_get_contents($tempPath));
        unlink($tempPath);

        return $filename;
    }

    protected function backupFiles(): string
    {
        $filename = 'files_' . date('Y-m-d_H-i-s') . '.zip';
        $tempPath = sys_get_temp_dir() . '/' . $filename;

        $zip = new \ZipArchive();
        if ($zip->open($tempPath, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) !== true) {
            throw new \Exception("Cannot create zip file");
        }

        $paths = $this->config['files']['paths'] ?? [];
        $excludes = $this->config['files']['exclude'] ?? [];
        $baseDir = __DIR__ . '/../../../../'; // Root of project

        foreach ($paths as $path) {
            $fullPath = realpath($baseDir . $path);
            if ($fullPath && is_dir($fullPath)) {
                $this->addDirToZip($zip, $fullPath, $excludes, $baseDir);
            }
        }

        $zip->close();

        // Move to storage
        $this->storage->put($filename, file_get_contents($tempPath));
        unlink($tempPath);

        return $filename;
    }

    protected function addDirToZip(\ZipArchive $zip, string $path, array $excludes, string $baseDir)
    {
        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($path, \RecursiveDirectoryIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::SELF_FIRST
        );

        foreach ($iterator as $file) {
            $filePath = $file->getPathname();
            $relativePath = str_replace($baseDir, '', $filePath);

            // Check excludes
            foreach ($excludes as $exclude) {
                if (strpos($relativePath, $exclude) === 0) {
                    continue 2;
                }
            }

            if ($file->isDir()) {
                $zip->addEmptyDir($relativePath);
            } else {
                $zip->addFile($filePath, $relativePath);
            }
        }
    }

    protected function packageBackup(array $files, string $type): string
    {
        // If only one file, return it
        if (count($files) === 1) {
            return reset($files);
        }

        // If multiple files (full backup), zip them together
        $filename = 'backup_' . $type . '_' . date('Y-m-d_H-i-s') . '.zip';
        $tempPath = sys_get_temp_dir() . '/' . $filename;

        $zip = new \ZipArchive();
        if ($zip->open($tempPath, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) !== true) {
            throw new \Exception("Cannot create final zip file");
        }

        foreach ($files as $key => $file) {
            // Retrieve file from storage to add to zip
            // This is inefficient for large files, but simple for now
            // Better: stream copy
            $content = $this->storage->get($file);
            $zip->addFromString($file, $content);
            // We might want to delete the individual files from storage if we are packaging them
            // But for now let's keep them or maybe we shouldn't have stored them yet?
            // Refactor: backupDatabase/backupFiles should return temp path, not store immediately
        }

        $zip->close();

        $this->storage->put($filename, file_get_contents($tempPath));
        unlink($tempPath);

        // Cleanup individual files from storage if we packaged them
        foreach ($files as $file) {
            $this->storage->delete($file);
        }

        return $filename;
    }
}
