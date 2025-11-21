<?php

namespace App\Core\View;

use App\Core\Application;

class SidebarService
{
    /**
     * Get aggregated menu items from all enabled modules.
     */
    public static function getItems(): array
    {
        $app = Application::getInstance();
        $modules = $app->moduleManager->getModules(); // Only loaded (enabled) modules

        $menuItems = [];

        foreach ($modules as $module) {
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
