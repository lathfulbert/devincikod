<?php

namespace Modules\Auth;

use App\Core\Module\AbstractModule;

class AuthModule extends AbstractModule
{
    public function getRoutes(): array
    {
        $authMiddleware = function () {
            $auth = new \App\Core\Auth\Auth();
            if (!$auth->check()) {
                redirect('/login');
                return false;
            }
            return true;
        };

        return [
            [
                'method' => 'GET',
                'path' => '/',
                'handler' => function () {
                    redirect('/login');
                }
            ],
            [
                'method' => 'GET',
                'path' => '/login',
                'handler' => [new \Modules\Auth\Controllers\AuthController(), 'login']
            ],
            [
                'method' => 'POST',
                'path' => '/login',
                'handler' => [new \Modules\Auth\Controllers\AuthController(), 'login']
            ],
            [
                'method' => 'GET',
                'path' => '/register',
                'handler' => [new \Modules\Auth\Controllers\AuthController(), 'register']
            ],
            [
                'method' => 'POST',
                'path' => '/register',
                'handler' => [new \Modules\Auth\Controllers\AuthController(), 'register']
            ],
            [
                'method' => 'GET',
                'path' => '/logout',
                'handler' => [new \Modules\Auth\Controllers\AuthController(), 'logout']
            ]
        ];
    }

    protected function getModulePath(): string
    {
        return __DIR__;
    }
}
