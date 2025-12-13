<?php

namespace App\Core\Module\Middleware;

use App\Core\Application;
use Modules\RBAC\Services\RbacService;

/**
 * Middleware ModuleAccessMiddleware
 *
 * Vérifie si l'utilisateur a la permission d'accéder à un module spécifique.
 * Format de permission: access.<module_key>
 *
 * Si l'utilisateur n'a pas la permission:
 * - Bloque l'accès aux routes du module (403 Forbidden)
 * - Masque le module dans les menus
 * - Empêche le chargement des widgets/API du module
 */
class ModuleAccessMiddleware
{
    private RbacService $rbacService;

    public function __construct()
    {
        $app = Application::getInstance();
        $this->rbacService = $app->make(RbacService::class);
    }

    /**
     * Vérifie l'accès à un module
     *
     * @param string $moduleKey Clé du module (ex: 'crm', 'sms_marketing', 'wallet')
     * @return bool
     */
    public function canAccessModule(string $moduleKey): bool
    {
        // Modules exemptés du contrôle d'accès (essentiels pour l'authentification)
        $exemptModules = ['auth'];
        
        if (in_array($moduleKey, $exemptModules)) {
            return true; // Toujours permettre l'accès aux modules essentiels
        }

        // Utiliser la fonction helper auth() ou charger depuis la session
        $user = null;
        
        if (function_exists('auth')) {
            $user = auth()->user();
        } else {
            // Fallback: charger depuis la session
            if (isset($_SESSION['user_id'])) {
                $user = \Modules\Users\Models\User::find($_SESSION['user_id']);
            }
        }

        // Si pas d'utilisateur connecté, refuser l'accès
        if (!$user) {
            return false;
        }

        // Vérifier si l'utilisateur est admin (a tous les droits)
        $isAdmin = false;
        if (is_object($user) && method_exists($user, 'hasRole')) {
            $isAdmin = $user->hasRole('admin');
        } elseif (is_array($user) && isset($user['role']) && $user['role'] === 'admin') {
            $isAdmin = true;
        }
        
        if ($isAdmin) {
            return true; // Admin a accès à tout
        }

        // Construire la permission d'accès au module
        $permission = 'access.' . $moduleKey;

        // Vérifier via RBAC
        return $this->rbacService->userHasPermission($user, $permission);
    }

    /**
     * Gère la requête et vérifie l'accès au module
     * Compatible avec le système de middleware du Router.
     *
     * @param mixed $request
     * @param callable $next
     * @param string $moduleKey
     * @return mixed
     */
    public function handle($request, $next, string $moduleKey)
    {
        if (!$this->canAccessModule($moduleKey)) {
            http_response_code(403);

            // Si requête AJAX/API, retourner JSON
            if ($this->isAjaxRequest()) {
                header('Content-Type: application/json');
                echo json_encode([
                    'success' => false,
                    'error' => 'Module access denied',
                    'message' => "You don't have permission to access this module",
                    'required_permission' => 'access.' . $moduleKey
                ]);
                exit;
            }

            // Sinon, afficher une page d'erreur
            if (function_exists('view')) {
                echo view('errors.403', [
                    'message' => "Vous n'avez pas la permission d'accéder à ce module.",
                    'required_permission' => 'access.' . $moduleKey
                ]);
            } else {
                echo "403 Forbidden - Module access denied. Required permission: access.{$moduleKey}";
            }
            exit;
        }

        return $next($request);
    }

    /**
     * Vérifie si la requête est une requête AJAX/API
     *
     * @return bool
     */
    private function isAjaxRequest(): bool
    {
        return !empty($_SERVER['HTTP_X_REQUESTED_WITH'])
            && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest'
            || strpos($_SERVER['REQUEST_URI'] ?? '', '/api/') !== false;
    }

    /**
     * Récupère tous les modules accessibles pour l'utilisateur connecté
     *
     * @return array Liste des clés de modules accessibles
     */
    public function getAccessibleModules(): array
    {
        // Utiliser la fonction helper auth() ou charger depuis la session
        $user = null;
        
        if (function_exists('auth')) {
            $user = auth()->user();
        } else {
            // Fallback: charger depuis la session
            if (isset($_SESSION['user_id'])) {
                $user = \Modules\Users\Models\User::find($_SESSION['user_id']);
            }
        }

        if (!$user) {
            return [];
        }
        
        $app = Application::getInstance();

        // Récupérer tous les modules enregistrés
        $moduleManager = $app->moduleManager;
        $allModules = $moduleManager->getRegistry()->getAll();

        $accessibleModules = [];

        foreach ($allModules as $moduleData) {
            $moduleName = $moduleData['name'];
            $moduleKey = $this->getModuleKey($moduleName);
            $permission = 'access.' . $moduleKey;

            if ($this->rbacService->userHasPermission($user, $permission)) {
                $accessibleModules[] = $moduleKey;
            }
        }

        return $accessibleModules;
    }

    /**
     * Convertit un nom de module en clé de permission
     *
     * @param string $moduleName
     * @return string
     */
    protected function getModuleKey(string $moduleName): string
    {
        // Gérer les acronymes courants (AI, RBAC, API, etc.)
        $acronyms = ['AI', 'RBAC', 'API', 'SMS', 'MFA', 'OTP', 'CRM', 'ERP'];
        $normalized = $moduleName;
        
        foreach ($acronyms as $acronym) {
            if (strpos($normalized, $acronym) !== false) {
                // Remplacer l'acronyme par sa version minuscule
                $normalized = str_replace($acronym, strtolower($acronym), $normalized);
            }
        }
        
        // Convert PascalCase to snake_case (mais préserver les acronymes déjà en minuscule)
        $key = strtolower(preg_replace('/(?<!^)[A-Z]/', '_$0', $normalized));
        // Replace spaces and hyphens with underscores
        $key = str_replace([' ', '-'], '_', $key);
        // Nettoyer les underscores multiples
        $key = preg_replace('/_+/', '_', $key);
        $key = trim($key, '_');
        
        return $key;
    }

    /**
     * Filtre une liste de modules selon les permissions de l'utilisateur
     *
     * @param array $modules Liste des modules à filtrer
     * @return array Modules accessibles
     */
    public function filterAccessibleModules(array $modules): array
    {
        $accessibleModules = $this->getAccessibleModules();

        return array_filter($modules, function ($module) use ($accessibleModules) {
            $moduleKey = is_array($module) ? ($module['key'] ?? $module['name'] ?? '') : $module;
            return in_array($moduleKey, $accessibleModules);
        });
    }
}
