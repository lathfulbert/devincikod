<?php

namespace App\Core\Files\Middleware;

class SecureUploadMiddleware
{
    public function handle($request, $next)
    {
        // Check if user is authenticated (optional, depends on route protection)
        if (function_exists('auth') && !auth()->check()) {
            http_response_code(401);
            echo json_encode(['error' => 'Unauthorized']);
            return false;
        }

        // Check if storage directory is writable
        $config = require dirname(__DIR__) . '/Config/files.php';
        $uploadPath = dirname(dirname(dirname(__DIR__))) . '/' . $config['uploads']['path'];

        if (!is_dir($uploadPath)) {
            mkdir($uploadPath, 0755, true);
        }

        if (!is_writable($uploadPath)) {
            http_response_code(500);
            echo json_encode(['error' => 'Upload directory is not writable.']);
            return false;
        }

        return $next($request);
    }
}
