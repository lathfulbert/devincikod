<?php

use App\Core\Files\Services\FileManager;

if (!function_exists('file_manager')) {
    /**
     * Get the FileManager instance.
     */
    function file_manager(): FileManager
    {
        static $instance = null;
        if ($instance === null) {
            $instance = new FileManager();
        }
        return $instance;
    }
}

if (!function_exists('upload_file')) {
    /**
     * Upload a file.
     */
    function upload_file(array $file, ?string $subfolder = null): array
    {
        return file_manager()->upload($file, $subfolder);
    }
}

if (!function_exists('file_url')) {
    /**
     * Get the URL for a stored file.
     */
    function file_url(string $path): string
    {
        return '/' . ltrim($path, '/');
    }
}

if (!function_exists('delete_file')) {
    /**
     * Delete a file.
     */
    function delete_file(string $path): bool
    {
        return file_manager()->delete($path);
    }
}
