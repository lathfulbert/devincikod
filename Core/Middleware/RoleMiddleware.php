<?php

namespace App\Core\Middleware;

/**
 * RoleMiddleware
 * 
 * Vérifie si l'utilisateur a les rôles requis
 * Usage: ->middleware('role:admin')
 * Usage: ->middleware('role:admin,moderator')
 */
class RoleMiddleware
{
    /**
     * Handle the request
     * 
     * @param mixed $request
     * @param callable $next
     * @param string ...$roles
     * @return mixed
     */
    public function handle($request, callable $next, ...$roles)
    {
        // Check if user is authenticated
        if (!function_exists('auth') || !auth()->check()) {
            return $this->unauthorized($request, 'Authentication required.');
        }

        $user = auth()->user();

        // Check if user has any of the required roles
        $hasRole = false;
        foreach ($roles as $role) {
            if (method_exists($user, 'hasRole') && $user->hasRole($role)) {
                $hasRole = true;
                break;
            }
        }

        if (!$hasRole) {
            $rolesStr = implode(', ', $roles);
            return $this->unauthorized(
                $request,
                "You must have one of these roles: {$rolesStr}"
            );
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
        $accept = $_SERVER['HTTP_ACCEPT'] ?? '';
        if (strpos($accept, 'application/json') !== false) {
            return true;
        }

        $uri = $_SERVER['REQUEST_URI'] ?? '';
        if (strpos($uri, '/api/') !== false) {
            return true;
        }

        $requestedWith = $_SERVER['HTTP_X_REQUESTED_WITH'] ?? '';
        if (strtolower($requestedWith) === 'xmlhttprequest') {
            return true;
        }

        return false;
    }
}
