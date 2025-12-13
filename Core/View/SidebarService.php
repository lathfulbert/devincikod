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
                        if (!$this->hasPermission($item['permission'])) {
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
            if ($user->hasRole('admin')) {
                return true;
            }
        } elseif (is_array($user) && isset($user['role']) && $user['role'] === 'admin') {
            return true;
        }

        return $rbacService->userHasPermission($user, $permission);
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
            $url = isset($item['url']) ? url($item['url']) : 'javascript:void(0)';
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
            echo '<li class="sidebar-list">
                    <a class="sidebar-link sidebar-title" href="javascript:void(0)">
                        <i data-feather="' . ($item['icon'] ?? 'circle') . '"></i>
                        <span>' . htmlspecialchars($item['title']) . '</span>
                    </a>
                    <ul class="sidebar-submenu">';

            if (isset($item['children']) && is_array($item['children'])) {
                foreach ($item['children'] as $child) {
                    $childUrl = isset($child['url']) ? url($child['url']) : '#';
                    echo '<li><a href="' . $childUrl . '">' . htmlspecialchars($child['title']) . '</a></li>';
                }
            }

            echo '  </ul>
                  </li>';
        }
    }
}
