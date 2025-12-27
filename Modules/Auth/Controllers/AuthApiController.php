<?php

namespace Modules\Auth\Controllers;

use App\Core\Application;
use Modules\RBAC\Services\RbacService;
use Modules\Users\Models\User;

/**
 * AuthApiController
 * 
 * API endpoints for authentication-related data
 */
class AuthApiController
{
    protected RbacService $rbacService;

    public function __construct()
    {
        $app = Application::getInstance();
        $this->rbacService = $app->make(RbacService::class);
    }

    /**
     * Get current user permissions
     * GET /api/auth/me/permissions
     * 
     * @return void
     */
    /**
     * Get current user permissions
     * GET /api/auth/me/permissions
     */
    public function getPermissions()
    {
        // Utiliser la fonction helper auth() ou charger depuis la session
        $user = null;
        if (function_exists('auth')) {
            $user = auth()->user();
        } else {
            // Fallback: charger depuis la session
            if (isset($_SESSION['user_id'])) {
                $user = User::find($_SESSION['user_id']);
            }
        }

        if (!$user) {
            return $this->jsonResponse([
                'success' => false,
                'error' => 'Unauthorized',
                'message' => 'User not authenticated'
            ], 401);
        }

        // Get all user permissions
        $permissions = $this->getUserPermissions($user);

        // Get accessible modules
        $moduleAccessMiddleware = new \App\Core\Module\Middleware\ModuleAccessMiddleware();
        $accessibleModules = $moduleAccessMiddleware->getAccessibleModules();

        return $this->jsonResponse([
            'success' => true,
            'data' => [
                'permissions' => $permissions,
                'accessible_modules' => $accessibleModules,
                'user' => [
                    'id' => $user->id,
                    'username' => $user->username,
                    'email' => $user->email
                ]
            ]
        ]);
    }

    /**
     * Get current user info
     * GET /api/auth/me
     */
    public function getMe()
    {
        // Utiliser la fonction helper auth() ou charger depuis la session
        $user = null;
        if (function_exists('auth')) {
            $user = auth()->user();
        } else {
            // Fallback: charger depuis la session
            if (isset($_SESSION['user_id'])) {
                $user = User::find($_SESSION['user_id']);
            }
        }

        if (!$user) {
            return $this->jsonResponse([
                'success' => false,
                'error' => 'Unauthorized',
                'message' => 'User not authenticated'
            ], 401);
        }

        return $this->jsonResponse([
            'success' => true,
            'data' => [
                'id' => $user->id,
                'username' => $user->username,
                'email' => $user->email,
                'first_name' => $user->first_name ?? null,
                'last_name' => $user->last_name ?? null,
                'is_active' => $user->is_active ?? true
            ]
        ]);
    }

    /**
     * Get all permissions for a user
     * 
     * @param User $user
     * @return array
     */
    protected function getUserPermissions(User $user): array
    {
        $db = \App\Core\Database\Database::getInstance();

        // Get user roles
        $userRoles = \Modules\RBAC\Models\UserRole::where('user_id', $user->id)->get();

        if (empty($userRoles)) {
            return [];
        }

        $roleIds = array_map(fn($ur) => $ur->role_id, $userRoles);

        // Get all permissions for these roles
        $sql = "SELECT DISTINCT p.slug, p.name, p.description, p.module_slug
                FROM permissions p
                INNER JOIN role_permissions rp ON p.id = rp.permission_id
                WHERE rp.role_id IN (" . implode(',', array_fill(0, count($roleIds), '?')) . ")
                ORDER BY p.module_slug, p.slug";

        $stmt = $db->query($sql, $roleIds);
        $permissions = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        // Return as simple array of slugs for frontend
        return array_map(fn($p) => $p['slug'], $permissions);
    }

    /**
     * Send JSON response
     * 
     * @param array $data
     * @param int $statusCode
     * @return string|false
     */
    protected function jsonResponse(array $data, int $statusCode = 200)
    {
        http_response_code($statusCode);
        header('Content-Type: application/json');
        return json_encode($data, JSON_PRETTY_PRINT);
    }
}
