<?php

namespace App\Core\Files\Controllers;

use App\Core\Files\Services\FileManager;

class FileController
{
    protected FileManager $fileManager;

    public function __construct()
    {
        $this->fileManager = new FileManager();
    }

    /**
     * Handle file upload
     */
    public function upload()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->json(['error' => 'Method not allowed'], 405);
            return;
        }

        if (!isset($_FILES['files'])) {
            $this->json(['error' => 'No files uploaded'], 400);
            return;
        }

        $subfolder = $_POST['subfolder'] ?? null;
        $results = $this->fileManager->upload($_FILES['files'], $subfolder);

        $this->json(['results' => $results]);
    }

    /**
     * List files
     */
    public function list()
    {
        $directory = $_GET['dir'] ?? '';
        // Security check to prevent directory traversal
        if (strpos($directory, '..') !== false) {
            $this->json(['error' => 'Invalid directory'], 403);
            return;
        }

        $files = $this->fileManager->listFiles($directory);
        $this->json(['files' => $files]);
    }

    /**
     * Delete file
     */
    public function delete()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'DELETE') {
            $this->json(['error' => 'Method not allowed'], 405);
            return;
        }

        // Get data from raw input for DELETE request
        $input = json_decode(file_get_contents('php://input'), true);
        $path = $input['path'] ?? null;

        if (!$path) {
            $this->json(['error' => 'Path required'], 400);
            return;
        }

        // Security check
        if (strpos($path, '..') !== false) {
            $this->json(['error' => 'Invalid path'], 403);
            return;
        }

        if ($this->fileManager->delete($path)) {
            $this->json(['success' => true, 'message' => 'File deleted']);
        } else {
            $this->json(['error' => 'Failed to delete file'], 500);
        }
    }

    /**
     * Helper for JSON response
     */
    protected function json(array $data, int $status = 200)
    {
        http_response_code($status);
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }
}
