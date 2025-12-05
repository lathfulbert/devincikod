<?php

namespace App\Core\Middleware;

use App\Core\Auth\Auth;

class AuthMiddleware
{
    public function handle(): bool
    {
        $auth = new Auth();
        if (!$auth->check()) {
            redirect('/auth/login');
            return false;
        }
        return true;
    }

    public function __invoke()
    {
        return $this->handle();
    }
}
