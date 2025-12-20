<?php

namespace App\Core\View;

use App\Core\Application;
use App\Core\Module\Middleware\ModuleAccessMiddleware;

class SidebarService
{
    /**
     * Get aggregated menu items from all enabled modules.
     */
    public static function getItems(): array
    {
        $app = Application::getInstance();
        $modules = $app->moduleManager->getModules(); // Only loaded (enabled) modules

        // Obtenir les modules accessibles pour l'utilisateur actuel
        $accessMiddleware = new ModuleAccessMiddleware();
        $accessibleModules = $accessMiddleware->getAccessibleModules();

        $menuItems = [];

        foreach ($modules as $module) {
            // Vérifier si le module est accessible
            $moduleKey = self::getModuleKey($module->getName());
            if (!in_array($moduleKey, $accessibleModules)) {
                continue; // Sauter ce module si pas accessible
            }

            $items = $module->getMenuItems();
            if (!empty($items)) {
                // Filtrer les items selon les permissions individuelles
                $filteredItems = [];
                foreach ($items as $item) {
                    if (isset($item['permission'])) {
                        // Vérifier la permission spécifique à l'item
                        if (!self::hasPermission($item['permission'])) {
                            continue; // Sauter cet item si permission manquante
                        }
                    }
                    $filteredItems[] = $item;
                }
                $menuItems = array_merge($menuItems, $filteredItems);
            }
        }

        return $menuItems;
    }

    /**
     * Render the sidebar HTML.
     */
    public static function render(): void
    {
        $items = self::getItems();

        foreach ($items as $item) {
            self::renderItem($item);
        }
    }

    /**
     * Vérifie si l'utilisateur a une permission spécifique
     *
     * @param string $permission
     * @return bool
     */
    protected static function hasPermission(string $permission): bool
    {
        $app = Application::getInstance();
        $rbacService = $app->make(\Modules\RBAC\Services\RbacService::class);

        // Obtenir l'utilisateur actuel
        $user = null;
        if (function_exists('auth')) {
            $user = auth()->user();
        } else {
            if (isset($_SESSION['user_id'])) {
                $user = \Modules\Users\Models\User::find($_SESSION['user_id']);
            }
        }

        if (!$user) {
            return false;
        }

        // Admin a toutes les permissions
        if (is_object($user) && method_exists($user, 'hasRole')) {
            if ($user->hasRole('Administrateur') || $user->hasRole('admin')) {
                return true;
            }
        } elseif (is_array($user) && isset($user['role'])) {
            if ($user['role'] === 'Administrateur' || $user['role'] === 'admin') {
                return true;
            }
        }

        return $rbacService->userHasPermission($user, $permission);
    }

    /**
     * Convertit un nom de module en clé de permission
     *
     * @param string $moduleName
     * @return string
     */
    protected static function getModuleKey(string $moduleName): string
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

    protected static function renderItem(array $item): void
    {
        $type = $item['type'] ?? 'link';

        if ($type === 'separator') {
            echo '<li class="sidebar-list">
                    <label class="badge ' . ($item['class'] ?? 'badge-light-primary') . '">' . htmlspecialchars($item['label']) . '</label>
                  </li>';
            return;
        }

        if ($type === 'link') {
            if (isset($item['route'])) {
                $url = isset($item['route_params']) ? route($item['route'], $item['route_params']) : route($item['route']);
            } elseif (isset($item['url'])) {
                $url = url($item['url']);
            } else {
                $url = 'javascript:void(0)';
            }
            $activeClass = (current_url() == $url) ? 'active' : '';
            $extraClass = $item['class'] ?? '';

            echo '<li class="sidebar-list">
                    <a class="sidebar-link sidebar-title ' . $extraClass . ' ' . $activeClass . '" href="' . $url . '">
                        <i data-feather="' . ($item['icon'] ?? 'circle') . '"></i>
                        <span>' . htmlspecialchars($item['title']) . '</span>
                    </a>
                  </li>';
            return;
        }

        if ($type === 'dropdown') {
            // Filtrer les enfants selon les permissions
            $filteredChildren = [];
            if (isset($item['children']) && is_array($item['children'])) {
                foreach ($item['children'] as $child) {
                    if (isset($child['permission'])) {
                        if (!self::hasPermission($child['permission'])) {
                            continue; // Sauter cet enfant si permission manquante
                        }
                    }
                    $filteredChildren[] = $child;
                }
            }

            // N'afficher le dropdown que s'il y a des enfants visibles
            if (!empty($filteredChildren)) {
                echo '<li class="sidebar-list">
                        <a class="sidebar-link sidebar-title" href="javascript:void(0)">
                            <i data-feather="' . ($item['icon'] ?? 'circle') . '"></i>
                            <span>' . htmlspecialchars($item['title']) . '</span>
                        </a>
                        <ul class="sidebar-submenu">';

                foreach ($filteredChildren as $child) {
                    if (isset($child['route'])) {
                        $childUrl = isset($child['route_params']) ? route($child['route'], $child['route_params']) : route($child['route']);
                    } elseif (isset($child['url'])) {
                        $childUrl = url($child['url']);
                    } else {
                        $childUrl = '#';
                    }
                    echo '<li><a href="' . $childUrl . '">' . htmlspecialchars($child['title']) . '</a></li>';
                }

                echo '  </ul>
                      </li>';
            }
        }
    }
}
