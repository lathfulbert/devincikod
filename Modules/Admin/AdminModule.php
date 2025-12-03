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
                'title' => __('admin.menu.dashboard'),
                'icon' => 'home',
                'url' => '/admin/dashboard',
                'class' => 'link-nav'
            ],
            [
                'type' => 'link',
                'title' => __('admin.menu.monitoring'),
                'icon' => 'activity',
                'url' => '/admin/monitoring',
                'class' => 'link-nav'
            ],
            [
                'type' => 'separator',
                'label' => __('admin.menu.management'),
                'class' => 'badge-light-primary'
            ],
            [
                'type' => 'separator',
                'label' => __('admin.menu.system'),
                'class' => 'badge-light-secondary'
            ],
            [
                'type' => 'dropdown',
                'title' => __('admin.menu.queue_jobs'),
                'icon' => 'activity',
                'children' => [
                    ['title' => __('admin.menu.dashboard'), 'url' => '/admin/queue'],
                    ['title' => __('admin.menu.active_jobs'), 'url' => '/admin/queue/jobs'],
                    ['title' => __('admin.menu.failed_jobs'), 'url' => '/admin/queue/failed'],
                    ['title' => __('admin.menu.statistics'), 'url' => '/admin/queue/stats'],
                ]
            ],
            [
                'type' => 'dropdown',
                'title' => __('admin.menu.cron_tasks'),
                'icon' => 'clock',
                'children' => [
                    ['title' => __('admin.menu.tasks_list'), 'url' => '/admin/cron'],
                    ['title' => __('admin.menu.execution_logs'), 'url' => '/admin/cron/logs'],
                    ['title' => __('admin.menu.statistics'), 'url' => '/admin/cron/stats'],
                ]
            ],
            [
                'type' => 'dropdown',
                'title' => __('admin.menu.configuration'),
                'icon' => 'settings',
                'children' => [
                    ['title' => __('admin.menu.modules'), 'url' => '/admin/modules'],
                    ['title' => __('admin.menu.cache'), 'url' => '/admin/cache'],
                ]
            ],
            [
                'type' => 'link',
                'title' => __('admin.menu.back_to_site'),
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
