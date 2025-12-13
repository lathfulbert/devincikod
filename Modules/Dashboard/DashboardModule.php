<?php

namespace Modules\Dashboard;

use App\Core\Module\AbstractModule;

class DashboardModule extends AbstractModule
{
    protected function getModulePath(): string
    {
        return __DIR__;
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
                'class' => 'link-nav'
            ]
        ];
    }
}