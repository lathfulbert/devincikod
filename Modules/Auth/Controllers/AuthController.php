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
            // Validation des données
            $validator = validator($_POST, [
                'username' => 'required|min:3|max:50',
                'password' => 'required|min:6'
            ], [
                'username.required' => 'Le nom d\'utilisateur est obligatoire.',
                'username.min' => 'Le nom d\'utilisateur doit contenir au moins 3 caractères.',
                'password.required' => 'Le mot de passe est obligatoire.',
                'password.min' => 'Le mot de passe doit contenir au moins 6 caractères.'
            ]);

            if ($validator->fails()) {
                flash('danger', 'Veuillez corriger les erreurs de saisie.');
                redirect_back_with_errors($validator->errors());
                return;
            }

            $validatedData = $validator->validated();
            $username = $validatedData['username'];
            $password = $validatedData['password'];

            // Recherche de l'utilisateur
            $db = \App\Core\Database\Database::getInstance();
            $stmt = $db->query("SELECT * FROM users WHERE username = ?", [$username]);
            $user = $stmt->fetch();

            if ($user && password_verify($password, $user['password'])) {
                $this->auth->login([
                    'id' => $user['id'],
                    'username' => $user['username'],
                    'role' => $user['role']
                ]);

                flash('success', 'Connexion réussie! Bienvenue ' . $user['username'] . '.');
                redirect('/admin/dashboard');
                return;
            } else {
                $errorBag = new \App\Core\Validation\ErrorBag();
                $errorBag->add('username', 'Nom d\'utilisateur ou mot de passe incorrect.');
                flash('danger', 'Identifiants invalides. Veuillez réessayer.');
                redirect_back_with_errors($errorBag);
                return;
            }
        }

        $app = Application::getInstance();
        echo $app->view->render('auth/login', ['title' => 'Login']);
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
