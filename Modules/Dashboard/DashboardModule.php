<?php

namespace Modules\Dashboard;

use App\Core\Module\AbstractModule;

class DashboardModule extends AbstractModule
{
    protected function getModulePath(): string
    {
        return __DIR__;
    }
    public function boot(): void
    {
        // Enregistrer les widgets du module
        if (function_exists('register_widget')) {
            register_widget('dashboard', \Modules\Dashboard\Widgets\WelcomeWidget::class);
        }
    }

    public function getRoutes(): array
    {
        return [
            'web' => __DIR__ . '/Routes/web.php',
        ];
    }

    public function getMenuItems(): array
    {
        return [
            [
                'type' => 'link',
                'title' => 'Dashboard',
                'icon' => 'home',
                'url' => '/admin/dashboard',
                'class' => 'link-nav',
                'permission' => 'access.dashboard' // Permission requise pour voir le menu
            ]
        ];
    }
}