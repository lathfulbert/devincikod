<?php

namespace Modules\Auth;

use App\Core\Module\ModuleContract;
use Modules\Auth\Controllers\AuthController;
use App\Core\Auth\Auth;

class AuthModule implements ModuleContract
{
    public function getName(): string
    {
        return 'Auth';
    }

    public function register(): void
    {
    }

    public function boot(): void
    {
    }

    public function getRoutes(): array
    {
        $authMiddleware = function() {
            $auth = new Auth();
            if (!$auth->check()) {
                redirect('/login');
                return false;
            }
            return true;
        };

        return [
            [
                'method' => 'GET',
                'path' => '/login',
                'handler' => [new AuthController(), 'login']
            ],
            [
                'method' => 'POST',
                'path' => '/login',
                'handler' => [new AuthController(), 'login']
            ],
            [
                'method' => 'GET',
                'path' => '/logout',
                'handler' => [new AuthController(), 'logout']
            ],
            [
                'method' => 'GET',
                'path' => '/dashboard',
                'handler' => [new AuthController(), 'dashboard'],
                'middleware' => [$authMiddleware]
            ]
        ];
    }
}
