<?php

namespace Modules\AI;

use App\Core\Module\AbstractModule;

class AIModule extends AbstractModule
{
    public function getRoutes(): array
    {
        return [
            ['GET', '/admin/ai', ['Modules\AI\Controllers\AIController', 'index']],
            ['GET', '/admin/ai/settings', ['Modules\AI\Controllers\AIController', 'settings']],
            ['POST', '/admin/ai/settings', ['Modules\AI\Controllers\AIController', 'settings']],
            ['GET', '/admin/ai/logs', ['Modules\AI\Controllers\AIController', 'logs']],
            ['GET', '/admin/ai/test', ['Modules\AI\Controllers\AIController', 'test']],
            ['POST', '/admin/ai/test', ['Modules\AI\Controllers\AIController', 'test']],
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
                'type' => 'dropdown',
                'title' => 'AI Module',
                'icon' => 'cpu', // Feather icon name
                'children' => [
                    ['title' => 'Dashboard', 'url' => '/admin/ai'],
                    ['title' => 'Test AI', 'url' => '/admin/ai/test'],
                    ['title' => 'Logs', 'url' => '/admin/ai/logs'],
                    ['title' => 'Settings', 'url' => '/admin/ai/settings'],
                ]
            ]
        ];
    }
}
