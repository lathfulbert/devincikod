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
                $menuItems = array_merge($menuItems, $items);
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
