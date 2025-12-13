<?php

use App\Core\Application;
use App\Core\Module\Middleware\ModuleAccessMiddleware;

if (!function_exists('can_access_module')) {
    /**
     * Vérifie si l'utilisateur peut accéder à un module
     *
     * @param string $moduleKey Clé du module (ex: 'crm', 'sms_marketing')
     * @return bool
     */
    function can_access_module(string $moduleKey): bool
    {
        try {
            $middleware = new ModuleAccessMiddleware();
            return $middleware->canAccessModule($moduleKey);
        } catch (\Exception $e) {
            error_log("can_access_module error: " . $e->getMessage());
            return false;
        }
    }
}

if (!function_exists('require_module_access')) {
    /**
     * Requiert l'accès à un module, sinon bloque avec 403
     *
     * @param string $moduleKey Clé du module
     * @return void
     * @throws \Exception
     */
    function require_module_access(string $moduleKey): void
    {
        $middleware = new ModuleAccessMiddleware();
        $middleware->handle($moduleKey);
    }
}

if (!function_exists('get_accessible_modules')) {
    /**
     * Récupère la liste des modules accessibles pour l'utilisateur
     *
     * @return array
     */
    function get_accessible_modules(): array
    {
        try {
            $middleware = new ModuleAccessMiddleware();
            return $middleware->getAccessibleModules();
        } catch (\Exception $e) {
            error_log("get_accessible_modules error: " . $e->getMessage());
            return [];
        }
    }
}

if (!function_exists('filter_accessible_modules')) {
    /**
     * Filtre une liste de modules selon les permissions
     *
     * @param array $modules
     * @return array
     */
    function filter_accessible_modules(array $modules): array
    {
        try {
            $middleware = new ModuleAccessMiddleware();
            return $middleware->filterAccessibleModules($modules);
        } catch (\Exception $e) {
            error_log("filter_accessible_modules error: " . $e->getMessage());
            return [];
        }
    }
}

if (!function_exists('module_permission')) {
    /**
     * Génère le nom de permission d'accès à un module
     *
     * @param string $moduleKey
     * @return string
     */
    function module_permission(string $moduleKey): string
    {
        return 'access.' . $moduleKey;
    }
}
