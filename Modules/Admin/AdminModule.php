<?php

namespace Modules\Admin;

use App\Core\Module\AbstractModule;

class AdminModule extends AbstractModule
{
    public function getRoutes(): array
    {
        return [
            'web' => __DIR__ . '/Routes/web.php',
        ];
    }

    protected function getModulePath(): string
    {
        return __DIR__;
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
            ],
            [
                'type' => 'link',
                'title' => 'Monitoring',
                'icon' => 'activity',
                'url' => '/admin/monitoring',
                'class' => 'link-nav'
            ],
            [
                'type' => 'separator',
                'label' => 'Gestion',
                'class' => 'badge-light-primary'
            ],
            [
                'type' => 'dropdown',
                'title' => 'Utilisateurs',
                'icon' => 'users',
                'children' => [
                    ['title' => 'Liste des utilisateurs', 'url' => '/admin/users'],
                    ['title' => 'Rôles', 'url' => '/admin/roles'],
                    ['title' => 'Permissions', 'url' => '/admin/permissions'],
                ]
            ],
            [
                'type' => 'separator',
                'label' => 'Système',
                'class' => 'badge-light-secondary'
            ],
            [
                'type' => 'dropdown',
                'title' => 'Queue & Jobs',
                'icon' => 'activity',
                'children' => [
                    ['title' => 'Dashboard', 'url' => '/admin/queue'],
                    ['title' => 'Active Jobs', 'url' => '/admin/queue/jobs'],
                    ['title' => 'Failed Jobs', 'url' => '/admin/queue/failed'],
                    ['title' => 'Statistics', 'url' => '/admin/queue/stats'],
                ]
            ],
            [
                'type' => 'dropdown',
                'title' => 'Cron Tasks',
                'icon' => 'clock',
                'children' => [
                    ['title' => 'Tasks List', 'url' => '/admin/cron'],
                    ['title' => 'Execution Logs', 'url' => '/admin/cron/logs'],
                    ['title' => 'Statistics', 'url' => '/admin/cron/stats'],
                ]
            ],
            [
                'type' => 'dropdown',
                'title' => 'Configuration',
                'icon' => 'settings',
                'children' => [
                    ['title' => 'Modules', 'url' => '/admin/modules'],
                    ['title' => 'Cache', 'url' => '/admin/cache'],
                ]
            ],
            [
                'type' => 'link',
                'title' => 'Retour au site',
                'icon' => 'external-link',
                'url' => '/',
                'class' => 'link-nav'
            ]
        ];
    }

    public function getApiRoutes(): array
    {
        return [
            ['GET', '/users/datatable', [\Modules\Admin\Controllers\UserApiController::class, 'datatable']],
        ];
    }
}
