<?php

namespace Modules\Auth\Controllers;

use App\Core\Application;
use App\Core\Auth\Auth;

class AuthController
{
    protected Auth $auth;

    public function __construct()
    {
        $this->auth = new Auth();
    }

    public function login()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Simple hardcoded check for demo
            $username = $_POST['username'] ?? '';
            $password = $_POST['password'] ?? '';

            $username = $_POST['username'] ?? '';
            $password = $_POST['password'] ?? '';

            // Use Model to find user
            // For MVP, we don't have 'where' on Model yet, let's add it or use raw query via Model
            // Let's use the Model's underlying DB for now or implement a simple where in Model later.
            // Actually, let's implement a simple findBy in Model or just raw query here for speed.
            
            $db = \App\Core\Database\Database::getInstance();
            $stmt = $db->query("SELECT * FROM users WHERE username = ?", [$username]);
            $user = $stmt->fetch();

            if ($user && password_verify($password, $user['password'])) {
                $this->auth->login(['id' => $user['id'], 'username' => $user['username'], 'role' => $user['role']]);
                redirect('/admin/dashboard');
                exit;
            } else {
                $error = "Invalid credentials";
            }
        }

        $app = Application::getInstance();
        echo $app->view->render('auth/login', ['title' => 'Login', 'error' => $error ?? null]);
    }

    public function logout()
    {
        $this->auth->logout();
        redirect('/login');
        exit;
    }
    
    public function dashboard()
    {
        $app = Application::getInstance();
        $user = $this->auth->user();
        echo $app->view->render('auth/dashboard', ['title' => 'Dashboard', 'user' => $user]);
    }
}
