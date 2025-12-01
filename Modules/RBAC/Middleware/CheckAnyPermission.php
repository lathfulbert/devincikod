<?php

namespace Modules\RBAC\Middleware;

use App\Core\Container\Container;
use Modules\RBAC\Services\RbacService;

class CheckAnyPermission
{
    protected $rbacService;

    public function __construct()
    {
        $this->rbacService = Container::getInstance()->make(RbacService::class);
    }

    public function handle($request, $next, ...$permissions)
    {
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
            echo "403 Unauthorized";
            exit;
        }

        // Vérifier si l'utilisateur a au moins une des permissions
        $hasPermission = false;
        foreach ($permissions as $permission) {
            if ($this->rbacService->userHasPermission($user, $permission)) {
                $hasPermission = true;
                break;
            }
        }

        if (!$hasPermission) {
            http_response_code(403);
            echo "403 Forbidden - Missing permissions: " . implode(', ', $permissions);
            exit;
        }

        return $next($request);
    }
}
