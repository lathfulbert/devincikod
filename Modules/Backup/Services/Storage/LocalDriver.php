<?php

declare(strict_types=1);

namespace Modules\Backup\Services\Storage;

class LocalDriver implements StorageDriverInterface
{
    protected readonly string $root;

    public function __construct(array $config)
    {
        $this->root = rtrim($config['path'] ?? 'storage/backups', '/');

        // Ensure directory exists
        if (!is_dir($this->root)) {
            mkdir($this->root, 0755, true);
        }
    }

    protected function getFullPath(string $path): string
    {
        return $this->root . '/' . ltrim($path, '/');
    }

    public function put(string $path, string $content): bool
    {
        $fullPath = $this->getFullPath($path);
        $dir = dirname($fullPath);

        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        return file_put_contents($fullPath, $content) !== false;
    }

    public function putStream(string $path, $resource): bool
    {
        $fullPath = $this->getFullPath($path);
        $dir = dirname($fullPath);

        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        $dest = fopen($fullPath, 'w');
        if (!$dest) {
            return false;
        }

        $result = stream_copy_to_stream($resource, $dest);
        fclose($dest);

        return $result !== false;
    }

    public function get(string $path): ?string
    {
        $fullPath = $this->getFullPath($path);
        if (!$this->exists($path)) {
            return null;
        }
        return file_get_contents($fullPath);
    }

    public function getStream(string $path)
    {
        $fullPath = $this->getFullPath($path);
        if (!$this->exists($path)) {
            return null;
        }
        return fopen($fullPath, 'r');
    }

    public function delete(string $path): bool
    {
        $fullPath = $this->getFullPath($path);
        if (!$this->exists($path)) {
            return true;
        }
        return unlink($fullPath);
    }

    public function exists(string $path): bool
    {
        return file_exists($this->getFullPath($path));
    }

    public function allFiles(string $directory): array
    {
        $fullPath = $this->getFullPath($directory);
        if (!is_dir($fullPath)) {
            return [];
        }

        $files = [];
        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($fullPath, \RecursiveDirectoryIterator::SKIP_DOTS)
        );

        foreach ($iterator as $file) {
            if ($file->isFile()) {
                $files[] = str_replace($this->root . '/', '', $file->getPathname());
            }
        }

        return $files;
    }

    public function size(string $path): int
    {
        return filesize($this->getFullPath($path));
    }

    public function lastModified(string $path): int
    {
        return filemtime($this->getFullPath($path));
    }
}
