<?php

namespace Modules\Backup\Services;

use Modules\Backup\Models\Backup;
use Modules\Backup\Services\Storage\LocalDriver;
use Modules\Backup\Services\Storage\StorageDriverInterface;

class RestoreService
{
    protected StorageDriverInterface $storage;
    protected array $config;

    public function __construct()
    {
        $this->config = require __DIR__ . '/../../config/backup.php';
        $this->storage = new LocalDriver($this->config['storage']['drivers']['local']);
    }

    public function restore(int $backupId): bool
    {
        $backup = Backup::find($backupId);
        if (!$backup || !$backup->isSuccessful()) {
            throw new \Exception("Backup not found or failed");
        }

        $path = $backup->path;
        if (!$this->storage->exists($path)) {
            throw new \Exception("Backup file not found in storage");
        }

        // Download to temp
        $tempPath = sys_get_temp_dir() . '/' . $backup->filename;
        file_put_contents($tempPath, $this->storage->get($path));

        try {
            if ($backup->type === 'database') {
                $this->restoreDatabase($tempPath);
            } elseif ($backup->type === 'files') {
                $this->restoreFiles($tempPath);
            } elseif ($backup->type === 'full') {
                $this->restoreFull($tempPath);
            }
        } finally {
            if (file_exists($tempPath)) {
                unlink($tempPath);
            }
        }

        return true;
    }

    protected function restoreDatabase(string $path): void
    {
        // Decompress if needed
        if (str_ends_with($path, '.gz')) {
            $newPath = substr($path, 0, -3);
            $fp = gzopen($path, 'r');
            $out = fopen($newPath, 'w');
            while (!gzeof($fp)) {
                fwrite($out, gzread($fp, 4096));
            }
            fclose($out);
            gzclose($fp);
            $path = $newPath;
        }

        $dbConfig = require __DIR__ . '/../../../../config/database.php';
        $host = $dbConfig['connections']['mysql']['host'] ?? '127.0.0.1';
        $port = $dbConfig['connections']['mysql']['port'] ?? '3306';
        $user = $dbConfig['connections']['mysql']['username'] ?? 'root';
        $pass = $dbConfig['connections']['mysql']['password'] ?? '';
        $db   = $dbConfig['connections']['mysql']['database'] ?? 'sunuframework';

        // Construct mysql command
        $cmd = sprintf(
            'mysql --host=%s --port=%s --user=%s %s %s < %s',
            escapeshellarg($host),
            escapeshellarg($port),
            escapeshellarg($user),
            $pass ? '--password=' . escapeshellarg($pass) : '',
            escapeshellarg($db),
            escapeshellarg($path)
        );

        $output = [];
        $returnVar = 0;
        exec($cmd, $output, $returnVar);

        if ($returnVar !== 0) {
            throw new \Exception("Database restore failed with exit code $returnVar");
        }
    }

    protected function restoreFiles(string $path): void
    {
        $zip = new \ZipArchive();
        if ($zip->open($path) !== true) {
            throw new \Exception("Cannot open zip file");
        }

        $baseDir = __DIR__ . '/../../../../'; // Root of project
        $zip->extractTo($baseDir);
        $zip->close();
    }

    protected function restoreFull(string $path): void
    {
        // Unzip outer package
        $zip = new \ZipArchive();
        if ($zip->open($path) !== true) {
            throw new \Exception("Cannot open full backup zip");
        }

        $extractPath = sys_get_temp_dir() . '/restore_' . uniqid();
        mkdir($extractPath);
        $zip->extractTo($extractPath);
        $zip->close();

        // Look for db and files inside
        $files = scandir($extractPath);
        foreach ($files as $file) {
            if ($file === '.' || $file === '..') continue;

            $fullPath = $extractPath . '/' . $file;
            if (str_starts_with($file, 'db_')) {
                $this->restoreDatabase($fullPath);
            } elseif (str_starts_with($file, 'files_')) {
                $this->restoreFiles($fullPath);
            }
        }

        // Cleanup
        $this->recursiveDelete($extractPath);
    }

    protected function recursiveDelete($dir)
    {
        if (is_dir($dir)) {
            $objects = scandir($dir);
            foreach ($objects as $object) {
                if ($object != "." && $object != "..") {
                    if (is_dir($dir . "/" . $object))
                        $this->recursiveDelete($dir . "/" . $object);
                    else
                        unlink($dir . "/" . $object);
                }
            }
            rmdir($dir);
        }
    }
}
