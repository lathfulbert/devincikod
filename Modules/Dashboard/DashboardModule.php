<?php
namespace Modules\Dashboard;
require_once __DIR__ . '/../../Core/Widget/helpers.php';

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
            register_widget('dashboard', \Modules\Dashboard\Widgets\SmsStatsWidget::class);
            register_widget('dashboard', \Modules\Dashboard\Widgets\WalletCreditWidget::class);
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
                'type' => 'submenu',
                'title' => 'Dashboard',
                'icon' => 'home',
                'url' => '/admin/dashboard',
                'class' => 'link-nav',
                'permission' => 'access.dashboard',
                'children' => [
                    [
                        'type' => 'link',
                        'title' => 'Gérer les widgets',
                        'icon' => 'sliders',
                        'url' => '/admin/dashboard/widgets',
                        'class' => 'link-nav',
                        'permission' => 'manage.widgets'
                    ]
                ]
            ]
        ];
    }
}