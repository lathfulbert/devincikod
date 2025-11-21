<?php

namespace App\Core\Cache\Drivers;

use App\Core\Cache\CacheInterface;

/**
 * FilesystemDriver
 * 
 * Driver de cache basé sur le système de fichiers.
 * Idéal pour environnements sans extensions spécifiques.
 */
class FilesystemDriver implements CacheInterface
{
    private string $cachePath;
    private string $prefix;

    public function __construct(array $config, string $prefix = '')
    {
        $this->prefix = $prefix;

        // Déterminer le chemin du cache
        $basePath = dirname(__DIR__, 3); // Remonte à la racine du framework
        $configPath = $config['filesystem_path'] ?? 'storage/cache';
        $this->cachePath = rtrim($basePath . '/' . $configPath, '/');

        // Créer le répertoire si nécessaire
        if (!is_dir($this->cachePath)) {
            mkdir($this->cachePath, 0755, true);
        }
    }

    /**
     * Construit le chemin complet d'un fichier de cache
     */
    private function getFilePath(string $key): string
    {
        $key = $this->prefix . $key;
        $hash = md5($key);

        // Structure hiérarchique : aa/bb/aabbccdd...
        $dir = $this->cachePath . '/' . substr($hash, 0, 2) . '/' . substr($hash, 2, 2);

        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        return $dir . '/' . $hash;
    }

    /**
     * Lit et décode un fichier de cache
     */
    private function readFile(string $filePath): mixed
    {
        if (!file_exists($filePath)) {
            return null;
        }

        $content = file_get_contents($filePath);
        if ($content === false) {
            return null;
        }

        $data = unserialize($content);

        // Vérifier l'expiration
        if (isset($data['expires_at']) && $data['expires_at'] !== null) {
            if (time() > $data['expires_at']) {
                // Expiré, supprimer le fichier
                @unlink($filePath);
                return null;
            }
        }

        return $data['value'] ?? null;
    }

    /**
     * Écrit et encode dans un fichier de cache
     */
    private function writeFile(string $filePath, mixed $value, ?int $ttl): bool
    {
        $expiresAt = $ttl !== null ? time() + $ttl : null;

        $data = [
            'value' => $value,
            'expires_at' => $expiresAt,
            'created_at' => time()
        ];

        $content = serialize($data);

        // Utiliser un fichier temporaire et rename atomique
        $tempFile = $filePath . '.tmp';

        if (file_put_contents($tempFile, $content, LOCK_EX) === false) {
            return false;
        }

        return rename($tempFile, $filePath);
    }

    public function get(string $key, mixed $default = null): mixed
    {
        $value = $this->readFile($this->getFilePath($key));
        return $value !== null ? $value : $default;
    }

    public function set(string $key, mixed $value, ?int $ttl = null): bool
    {
        return $this->writeFile($this->getFilePath($key), $value, $ttl);
    }

    public function has(string $key): bool
    {
        return $this->readFile($this->getFilePath($key)) !== null;
    }

    public function delete(string $key): bool
    {
        $filePath = $this->getFilePath($key);
        if (file_exists($filePath)) {
            return @unlink($filePath);
        }
        return true;
    }

    public function clear(): bool
    {
        return $this->deleteDirectory($this->cachePath);
    }

    /**
     * Supprime récursivement un répertoire
     */
    private function deleteDirectory(string $dir): bool
    {
        if (!is_dir($dir)) {
            return true;
        }

        $items = scandir($dir);
        foreach ($items as $item) {
            if ($item === '.' || $item === '..') {
                continue;
            }

            $path = $dir . '/' . $item;

            if (is_dir($path)) {
                $this->deleteDirectory($path);
            } else {
                @unlink($path);
            }
        }

        // Ne pas supprimer le répertoire racine du cache
        if ($dir !== $this->cachePath) {
            @rmdir($dir);
        }

        return true;
    }

    public function getMultiple(array $keys, mixed $default = null): array
    {
        $result = [];
        foreach ($keys as $key) {
            $result[$key] = $this->get($key, $default);
        }
        return $result;
    }

    public function setMultiple(array $values, ?int $ttl = null): bool
    {
        $success = true;
        foreach ($values as $key => $value) {
            if (!$this->set($key, $value, $ttl)) {
                $success = false;
            }
        }
        return $success;
    }

    public function deleteMultiple(array $keys): bool
    {
        $success = true;
        foreach ($keys as $key) {
            if (!$this->delete($key)) {
                $success = false;
            }
        }
        return $success;
    }

    public function increment(string $key, int $value = 1): int|false
    {
        $filePath = $this->getFilePath($key);
        $current = $this->get($key, 0);

        if (!is_numeric($current)) {
            return false;
        }

        $newValue = (int)$current + $value;

        if ($this->set($key, $newValue)) {
            return $newValue;
        }

        return false;
    }

    public function decrement(string $key, int $value = 1): int|false
    {
        return $this->increment($key, -$value);
    }

    public function remember(string $key, callable $callback, ?int $ttl = null): mixed
    {
        $value = $this->get($key);

        if ($value !== null) {
            return $value;
        }

        $value = $callback();
        $this->set($key, $value, $ttl);

        return $value;
    }

    public function getStats(): array
    {
        $totalFiles = 0;
        $totalSize = 0;

        $this->calculateStats($this->cachePath, $totalFiles, $totalSize);

        return [
            'total_files' => $totalFiles,
            'total_size_bytes' => $totalSize,
            'total_size_readable' => $this->formatBytes($totalSize),
            'cache_path' => $this->cachePath
        ];
    }

    /**
     * Calcule récursivement les statistiques
     */
    private function calculateStats(string $dir, int &$files, int &$size): void
    {
        if (!is_dir($dir)) {
            return;
        }

        $items = scandir($dir);
        foreach ($items as $item) {
            if ($item === '.' || $item === '..') {
                continue;
            }

            $path = $dir . '/' . $item;

            if (is_dir($path)) {
                $this->calculateStats($path, $files, $size);
            } else {
                $files++;
                $size += filesize($path);
            }
        }
    }

    /**
     * Formate les octets en format lisible
     */
    private function formatBytes(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $i = 0;

        while ($bytes >= 1024 && $i < count($units) - 1) {
            $bytes /= 1024;
            $i++;
        }

        return round($bytes, 2) . ' ' . $units[$i];
    }
}
