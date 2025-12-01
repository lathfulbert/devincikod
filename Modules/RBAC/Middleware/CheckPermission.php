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
        // Récupérer l'utilisateur depuis la session
        if (!isset($_SESSION['user_id'])) {
            http_response_code(403);
            echo "403 Unauthorized - Not authenticated";
            exit;
        }

        // Charger le modèle User complet
        $userId = $_SESSION['user_id'];
        $user = \Modules\Users\Models\User::find($userId);

        if (!$user || !$this->rbacService->userHasPermission($user, $permission)) {
            http_response_code(403);
            echo "403 Forbidden - Missing permission: {$permission}";
            exit;
        }

        return $next($request);
    }
}
