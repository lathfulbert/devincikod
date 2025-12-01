<?php

namespace Modules\Auth\Controllers;

use App\Core\Application;
use App\Core\Auth\Auth;
use Modules\Users\Services\UserService;

class AuthController
{
    protected UserService $userService;
    protected Auth $auth;

    public function __construct()
    {
        $this->auth = new Auth();
        $this->userService = new UserService();
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
            $user = $this->userService->findByUsername($username);

            if ($user && password_verify($password, $user->password)) {
                $this->auth->login([
                    'id' => $user->id,
                    'username' => $user->username,
                    'email' => $user->email ?? null
                ]);

                flash('success', 'Connexion réussie! Bienvenue ' . $user->username . '.');
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

        echo view('auth/auth/login', ['title' => 'Login']);
    }

    public function register()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Validation des données
            $validator = validator($_POST, [
                'username' => 'required|min:3|max:50|alpha_dash|unique:users,username',
                'email' => 'required|email|unique:users,email',
                'first_name' => 'alpha',
                'last_name' => 'alpha',
                'password' => 'required|min:8|confirmed',
                'terms' => 'required'
            ], [
                'username.required' => 'Le nom d\'utilisateur est obligatoire.',
                'username.min' => 'Le nom d\'utilisateur doit contenir au moins 3 caractères.',
                'username.alpha_dash' => 'Le nom d\'utilisateur ne peut contenir que des lettres, chiffres, tirets et underscores.',
                'username.unique' => 'Ce nom d\'utilisateur est déjà utilisé.',
                'email.required' => 'L\'email est obligatoire.',
                'email.email' => 'L\'email doit être une adresse valide.',
                'email.unique' => 'Cet email est déjà utilisé.',
                'first_name.alpha' => 'Le prénom ne peut contenir que des lettres.',
                'last_name.alpha' => 'Le nom ne peut contenir que des lettres.',
                'password.required' => 'Le mot de passe est obligatoire.',
                'password.min' => 'Le mot de passe doit contenir au moins 8 caractères.',
                'password.confirmed' => 'Les mots de passe ne correspondent pas.',
                'terms.required' => 'Vous devez accepter les conditions d\'utilisation.'
            ]);

            if ($validator->fails()) {
                flash('danger', 'Veuillez corriger les erreurs de saisie.');
                redirect_back_with_errors($validator->errors());
                return;
            }

            $validatedData = $validator->validated();

            // Créer l'utilisateur
            $user = $this->userService->createUser([
                'username' => $validatedData['username'],
                'email' => $validatedData['email'],
                'first_name' => $validatedData['first_name'] ?? null,
                'last_name' => $validatedData['last_name'] ?? null,
                'password' => $validatedData['password']
            ]);

            // Connexion automatique après inscription
            $this->auth->login([
                'id' => $user->id,
                'username' => $user->username,
                'email' => $user->email
            ]);

            flash('success', 'Inscription réussie! Bienvenue ' . $user->username . '!');
            redirect('/admin/dashboard');
            return;
        }

        echo view('auth/auth/register', ['title' => 'Inscription']);
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
        echo view('auth/auth/dashboard', ['title' => 'Dashboard', 'user' => $user]);
    }
}
