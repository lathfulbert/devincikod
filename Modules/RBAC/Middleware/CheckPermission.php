<?php

namespace Modules\RBAC\Middleware;

use App\Core\Container\Container;
use Modules\RBAC\Services\RbacService;

class CheckPermission
{
    protected $rbacService;

    public function __construct()
    {
        $this->rbacService = Container::getInstance()->make(RbacService::class);
    }

    public function handle($request, $next, $permission)
    {
        // Ensure session is started
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        // Récupérer l'utilisateur depuis la session
        if (!isset($_SESSION['user_id'])) {
            http_response_code(403);
            echo "403 Unauthorized - Not authenticated";
            exit;
        }

        // Charger le modèle User complet
        $userId = $_SESSION['user_id'];
        $user = \Modules\Users\Models\User::find($userId);

        if (!$user) {
            http_response_code(403);
            echo "403 Forbidden - User not found (ID: {$userId})";
            exit;
        }

        $hasPermission = $this->rbacService->userHasPermission($user, $permission);

        if (!$hasPermission) {
            // Debug info
            error_log("Permission check failed for user {$userId}, permission: {$permission}");
            error_log("User ID from model: " . $user->id);

            http_response_code(403);
            echo "403 Forbidden - Missing permission: {$permission}";
            exit;
        }

        return $next($request);
    }
}
