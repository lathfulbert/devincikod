<?php

namespace App\Core\Middleware;

/**
 * PermissionMiddleware
 * 
 * Vérifie si l'utilisateur a les permissions requises
 * Usage: ->middleware('can:edit-posts')
 * Usage: ->middleware('can:edit-posts,delete-posts')
 */
class PermissionMiddleware
{
    /**
     * Handle the request
     * 
     * @param mixed $request
     * @param callable $next
     * @param string ...$permissions
     * @return mixed
     */
    public function handle($request, callable $next, ...$permissions)
    {
        // Check if user is authenticated
        if (!function_exists('auth') || !auth()->check()) {
            return $this->unauthorized($request, 'Authentication required.');
        }

        $user = auth()->user();

        // Check each permission
        foreach ($permissions as $permission) {
            if (!$user->can($permission)) {
                return $this->unauthorized(
                    $request,
                    "You do not have permission to: {$permission}"
                );
            }
        }

        return $next($request);
    }

    /**
     * Handle unauthorized access
     * 
     * @param mixed $request
     * @param string $message
     * @return mixed
     */
    protected function unauthorized($request, string $message)
    {
        // Check if it's an API request
        if ($this->expectsJson($request)) {
            http_response_code(403);
            header('Content-Type: application/json');
            echo json_encode([
                'error' => 'Forbidden',
                'message' => $message,
                'code' => 403
            ]);
            exit;
        }

        // Web request - redirect with error
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $_SESSION['_error'] = $message;

        header('Location: ' . url('/403'));
        exit;
    }

    /**
     * Check if request expects JSON response
     * 
     * @param mixed $request
     * @return bool
     */
    protected function expectsJson($request): bool
    {
        // Check Accept header
        $accept = $_SERVER['HTTP_ACCEPT'] ?? '';
        if (strpos($accept, 'application/json') !== false) {
            return true;
        }

        // Check if it's an API route
        $uri = $_SERVER['REQUEST_URI'] ?? '';
        if (strpos($uri, '/api/') !== false) {
            return true;
        }

        // Check X-Requested-With header
        $requestedWith = $_SERVER['HTTP_X_REQUESTED_WITH'] ?? '';
        if (strtolower($requestedWith) === 'xmlhttprequest') {
            return true;
        }

        return false;
    }
}
