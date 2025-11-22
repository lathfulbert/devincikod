<?php

namespace App\Core\Files\Contracts;

interface FileManagerInterface
{
    /**
     * Upload a file or multiple files.
     * 
     * @param array $files The $_FILES array or a specific file entry
     * @param string|null $subfolder Optional subfolder
     * @return array Result with success status and file details
     */
    public function upload(array $files, ?string $subfolder = null): array;

    /**
     * Delete a file.
     * 
     * @param string $path Relative path to the file
     * @return bool
     */
    public function delete(string $path): bool;

    /**
     * List files in a directory.
     * 
     * @param string $directory Relative directory path
     * @return array List of files
     */
    public function listFiles(string $directory = ''): array;

    /**
     * Create a thumbnail for an image.
     * 
     * @param string $sourcePath Absolute path to source image
     * @param string $filename Filename
     * @return string|null Path to thumbnail or null on failure
     */
    public function createThumbnail(string $sourcePath, string $filename): ?string;
}
