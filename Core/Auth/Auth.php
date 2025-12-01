<?php

namespace App\Core\Auth;

use App\Core\Session\Session;

class Auth
{
    protected Session $session;

    public function __construct()
    {
        $this->session = new Session();
    }

    public function login(array $user): void
    {
        $this->session->set('user', $user);
        $this->session->set('user_id', $user['id'] ?? null);
    }

    public function logout(): void
    {
        $this->session->remove('user');
        $this->session->remove('user_id');
    }

    public function check(): bool
    {
        return $this->session->has('user');
    }

    public function user(): ?array
    {
        return $this->session->get('user');
    }
}
