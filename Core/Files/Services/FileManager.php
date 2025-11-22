<?php

namespace App\Core\Files\Services;

use App\Core\Files\Contracts\FileManagerInterface;

class FileManager implements FileManagerInterface
{
    protected array $config;
    protected string $basePath;

    public function __construct()
    {
        $this->config = require dirname(__DIR__) . '/Config/files.php';
        $this->basePath = dirname(dirname(dirname(__DIR__))); // Root path
    }

    /**
     * Upload files
     */
    public function upload(array $files, ?string $subfolder = null): array
    {
        $results = [];
        $normalizedFiles = $this->normalizeFiles($files);

        foreach ($normalizedFiles as $file) {
            if ($file['error'] !== UPLOAD_ERR_OK) {
                $results[] = [
                    'success' => false,
                    'name' => $file['name'],
                    'error' => $this->getUploadErrorMessage($file['error'])
                ];
                continue;
            }

            if (!$this->validateFile($file)) {
                $results[] = [
                    'success' => false,
                    'name' => $file['name'],
                    'error' => 'Invalid file type or size.'
                ];
                continue;
            }

            $results[] = $this->processUpload($file, $subfolder);
        }

        return $results;
    }

    /**
     * Process a single file upload
     */
    protected function processUpload(array $file, ?string $subfolder = null): array
    {
        // Generate path
        $uploadPath = $this->getUploadPath($subfolder);
        $filename = $this->generateUniqueName($file['name']);
        $destination = $uploadPath . '/' . $filename;

        // Move file
        if (move_uploaded_file($file['tmp_name'], $destination)) {
            $relativePath = str_replace($this->basePath . '/', '', $destination);
            $thumbnail = null;

            // Create thumbnail if image
            if ($this->config['images']['create_thumbnails'] && $this->isImage($file['type'])) {
                $thumbnail = $this->createThumbnail($destination, $filename);
            }

            return [
                'success' => true,
                'name' => $file['name'],
                'filename' => $filename,
                'path' => $relativePath,
                'url' => $this->getFileUrl($relativePath),
                'thumbnail' => $thumbnail ? $this->getFileUrl($thumbnail) : null,
                'size' => $file['size'],
                'type' => $file['type']
            ];
        }

        return [
            'success' => false,
            'name' => $file['name'],
            'error' => 'Failed to move uploaded file.'
        ];
    }

    /**
     * Delete a file
     */
    public function delete(string $path): bool
    {
        $fullPath = $this->basePath . '/' . ltrim($path, '/');

        if (file_exists($fullPath)) {
            // Delete thumbnail if exists
            $thumbPath = str_replace(
                $this->config['uploads']['path'],
                $this->config['images']['thumbnail_path'],
                $fullPath
            );

            if (file_exists($thumbPath)) {
                unlink($thumbPath);
            }

            return unlink($fullPath);
        }

        return false;
    }

    /**
     * List files
     */
    public function listFiles(string $directory = ''): array
    {
        $dirPath = $this->basePath . '/' . $this->config['uploads']['path'] . '/' . $directory;
        $files = [];

        if (is_dir($dirPath)) {
            $items = scandir($dirPath);
            foreach ($items as $item) {
                if ($item === '.' || $item === '..') continue;

                $fullPath = $dirPath . '/' . $item;
                if (is_file($fullPath)) {
                    $relativePath = str_replace($this->basePath . '/', '', $fullPath);
                    $files[] = [
                        'name' => $item,
                        'path' => $relativePath,
                        'url' => $this->getFileUrl($relativePath),
                        'size' => filesize($fullPath),
                        'modified' => filemtime($fullPath)
                    ];
                }
            }
        }

        return $files;
    }

    /**
     * Create thumbnail
     */
    public function createThumbnail(string $sourcePath, string $filename): ?string
    {
        $thumbDir = $this->basePath . '/' . $this->config['images']['thumbnail_path'];

        // Replicate subfolder structure in thumbs
        $relativePath = dirname(str_replace($this->basePath . '/' . $this->config['uploads']['path'], '', $sourcePath));
        $targetDir = $thumbDir . $relativePath;

        if (!is_dir($targetDir)) {
            mkdir($targetDir, 0755, true);
        }

        $targetPath = $targetDir . '/' . $filename;
        $width = $this->config['images']['thumbnail_dimensions']['width'];
        $height = $this->config['images']['thumbnail_dimensions']['height'];

        // Get image info
        list($origWidth, $origHeight, $type) = getimagesize($sourcePath);

        // Calculate ratio
        $ratio = $origWidth / $origHeight;
        if ($width / $height > $ratio) {
            $width = $height * $ratio;
        } else {
            $height = $width / $ratio;
        }

        // Create image resource
        $source = match ($type) {
            IMAGETYPE_JPEG => imagecreatefromjpeg($sourcePath),
            IMAGETYPE_PNG => imagecreatefrompng($sourcePath),
            IMAGETYPE_GIF => imagecreatefromgif($sourcePath),
            IMAGETYPE_WEBP => imagecreatefromwebp($sourcePath),
            default => null
        };

        if (!$source) return null;

        $thumb = imagecreatetruecolor($width, $height);

        // Preserve transparency
        if ($type == IMAGETYPE_PNG || $type == IMAGETYPE_WEBP) {
            imagecolortransparent($thumb, imagecolorallocatealpha($thumb, 0, 0, 0, 127));
            imagealphablending($thumb, false);
            imagesavealpha($thumb, true);
        }

        imagecopyresampled($thumb, $source, 0, 0, 0, 0, $width, $height, $origWidth, $origHeight);

        // Save thumbnail
        $success = match ($type) {
            IMAGETYPE_JPEG => imagejpeg($thumb, $targetPath, $this->config['images']['quality']),
            IMAGETYPE_PNG => imagepng($thumb, $targetPath),
            IMAGETYPE_GIF => imagegif($thumb, $targetPath),
            IMAGETYPE_WEBP => imagewebp($thumb, $targetPath),
            default => false
        };

        imagedestroy($thumb);
        imagedestroy($source);

        return $success ? str_replace($this->basePath . '/', '', $targetPath) : null;
    }

    // --- Helpers ---

    protected function normalizeFiles(array $files): array
    {
        $normalized = [];
        if (isset($files['name']) && is_array($files['name'])) {
            foreach ($files['name'] as $idx => $name) {
                $normalized[] = [
                    'name' => $name,
                    'type' => $files['type'][$idx],
                    'tmp_name' => $files['tmp_name'][$idx],
                    'error' => $files['error'][$idx],
                    'size' => $files['size'][$idx]
                ];
            }
        } elseif (isset($files['name'])) {
            $normalized[] = $files;
        }
        return $normalized;
    }

    protected function validateFile(array $file): bool
    {
        if ($file['size'] > $this->config['uploads']['max_size']) {
            return false;
        }

        if (!in_array($file['type'], $this->config['uploads']['allowed_types'])) {
            return false;
        }

        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (!in_array($ext, $this->config['uploads']['allowed_extensions'])) {
            return false;
        }

        return true;
    }

    protected function getUploadPath(?string $subfolder): string
    {
        $path = $this->basePath . '/' . $this->config['uploads']['path'];

        if ($this->config['organize_by_date']) {
            $path .= '/' . date('Y/m/d');
        }

        if ($this->config['organize_by_user'] && function_exists('auth') && auth()->check()) {
            $path .= '/' . auth()->user()['id'];
        }

        if ($subfolder) {
            $path .= '/' . trim($subfolder, '/');
        }

        if (!is_dir($path)) {
            mkdir($path, 0755, true);
        }

        return $path;
    }

    protected function generateUniqueName(string $filename): string
    {
        $ext = pathinfo($filename, PATHINFO_EXTENSION);
        return uniqid() . '_' . time() . '.' . $ext;
    }

    protected function isImage(string $mime): bool
    {
        return strpos($mime, 'image/') === 0;
    }

    protected function getFileUrl(string $relativePath): string
    {
        // Assuming 'storage' is linked to 'public/storage' or served directly
        // Adjust based on your framework's public path logic
        return '/' . $relativePath;
    }

    protected function getUploadErrorMessage(int $code): string
    {
        return match ($code) {
            UPLOAD_ERR_INI_SIZE => 'The uploaded file exceeds the upload_max_filesize directive in php.ini',
            UPLOAD_ERR_FORM_SIZE => 'The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form',
            UPLOAD_ERR_PARTIAL => 'The uploaded file was only partially uploaded',
            UPLOAD_ERR_NO_FILE => 'No file was uploaded',
            UPLOAD_ERR_NO_TMP_DIR => 'Missing a temporary folder',
            UPLOAD_ERR_CANT_WRITE => 'Failed to write file to disk',
            UPLOAD_ERR_EXTENSION => 'A PHP extension stopped the file upload',
            default => 'Unknown upload error',
        };
    }
}
