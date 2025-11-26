<?php

namespace Modules\Backup\Services\Storage;

interface StorageDriverInterface
{
    /**
     * Save content to storage.
     *
     * @param string $path
     * @param string $content
     * @return bool
     */
    public function put(string $path, string $content): bool;

    /**
     * Save stream to storage.
     *
     * @param string $path
     * @param resource $resource
     * @return bool
     */
    public function putStream(string $path, $resource): bool;

    /**
     * Get content from storage.
     *
     * @param string $path
     * @return string|null
     */
    public function get(string $path): ?string;

    /**
     * Get stream from storage.
     *
     * @param string $path
     * @return resource|null
     */
    public function getStream(string $path);

    /**
     * Delete file from storage.
     *
     * @param string $path
     * @return bool
     */
    public function delete(string $path): bool;

    /**
     * Check if file exists.
     *
     * @param string $path
     * @return bool
     */
    public function exists(string $path): bool;

    /**
     * Get all files in directory.
     *
     * @param string $directory
     * @return array
     */
    public function allFiles(string $directory): array;

    /**
     * Get file size.
     *
     * @param string $path
     * @return int
     */
    public function size(string $path): int;

    /**
     * Get file last modified time.
     *
     * @param string $path
     * @return int
     */
    public function lastModified(string $path): int;
}
