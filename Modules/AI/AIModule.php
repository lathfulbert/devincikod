<?php

namespace Modules\AI;

use App\Core\Module\AbstractModule;

class AIModule extends AbstractModule
{
    public function getRoutes(): array
    {
        return [
            '/admin/ai' => ['controller' => 'Modules\AI\Controllers\AIController', 'action' => 'index'],
            '/admin/ai/settings' => ['controller' => 'Modules\AI\Controllers\AIController', 'action' => 'settings'],
            '/admin/ai/logs' => ['controller' => 'Modules\AI\Controllers\AIController', 'action' => 'logs'],
            '/admin/ai/test' => ['controller' => 'Modules\AI\Controllers\AIController', 'action' => 'test'],
        ];
    }

    protected function getModulePath(): string
    {
        return __DIR__;
    }
}
