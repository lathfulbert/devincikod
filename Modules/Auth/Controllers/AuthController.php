<?php

namespace Modules\Auth\Controllers;

use Modules\Auth\Services\MfaManager;
use Modules\Auth\Services\TokenManager;
use Modules\Auth\Services\AuditLogger;
use Modules\Auth\Providers\PasswordProvider;
use Modules\Users\Models\User;

/**
 * Authentication Controller
 * Handles login, logout, and registration
 */
class AuthController
{
    private MfaManager $mfaManager;
    private TokenManager $tokenManager;
    private AuditLogger $auditLogger;

    public function __construct()
    {
        $this->mfaManager = new MfaManager();
        $this->tokenManager = new TokenManager();
        $this->auditLogger = new AuditLogger();
    }

    /**
     * Show login form
     */
    public function showLogin()
    {
        echo view('auth/login');
    }

    /**
     * Handle login request
     */
    public function login()
    {
        $identifier = $_POST['identifier'] ?? $_POST['email'] ?? $_POST['username'] ?? null;
        $password = $_POST['password'] ?? null;

        if (!$identifier || !$password) {
            $_SESSION['flash_error'] = 'Email/Username et mot de passe requis';
            redirect('/auth/login');
            return;
        }

        // Check rate limiting
        if ($this->auditLogger->shouldRateLimit(null, null, 5)) {
            $_SESSION['flash_error'] = 'Trop de tentatives. Réessayez plus tard.';
            redirect('/auth/login');
            return;
        }

        // Verify password
        $passwordProvider = new PasswordProvider();
        $user = $passwordProvider->verify($identifier, $password);

        if (!$user) {
            $_SESSION['flash_error'] = 'Identifiants invalides';
            redirect('/auth/login');
            return;
        }

        $userId = $user['id'];

        // Check if MFA is required
        if ($this->mfaManager->isRequired($userId)) {
            // Store user ID in session for MFA verification
            $_SESSION['mfa_user_id'] = $userId;
            $_SESSION['mfa_required'] = true;

            redirect('/auth/mfa/challenge');
            return;
        }

        // No MFA required - complete login
        $this->completeLogin($user);
    }

    /**
     * Complete login (set session, generate tokens)
     */
    private function completeLogin(array $user)
    {
        $_SESSION['user'] = $user;
        $_SESSION['user_id'] = $user['id'];
        unset($_SESSION['mfa_user_id']);
        unset($_SESSION['mfa_required']);

        // Generate API tokens for SPA/Mobile
        $tokens = $this->tokenManager->generateTokenPair($user['id']);
        $_SESSION['api_tokens'] = $tokens;

        $_SESSION['flash_success'] = 'Welcome back!';
        redirect('/admin/dashboard');
    }

    /**
     * Handle logout
     */
    public function logout()
    {
        $userId = $_SESSION['user_id'] ?? null;

        if ($userId) {
            $this->auditLogger->logLogout($userId);
        }

        session_destroy();
        redirect('/auth/login');
    }

    /**
     * Show registration form
     */
    public function showRegister()
    {
        echo view('auth/register');
    }

    /**
     * Handle registration
     */
    public function register()
    {
        $email = $_POST['email'] ?? null;
        $password = $_POST['password'] ?? null;
        $passwordConfirm = $_POST['password_confirm'] ?? null;

        // Validation
        if (!$email || !$password) {
            $_SESSION['flash_error'] = 'All fields are required';
            redirect('/auth/register');
            return;
        }

        if ($password !== $passwordConfirm) {
            $_SESSION['flash_error'] = 'Passwords do not match';
            redirect('/auth/register');
            return;
        }

        // Check if user exists
        $existing = User::query()->where('email', $email)->first();
        if ($existing) {
            $_SESSION['flash_error'] = 'Email already registered';
            redirect('/auth/register');
            return;
        }

        // Create user
        $user = new User();
        $user->email = $email;
        $user->password = password_hash($password, PASSWORD_ARGON2ID);
        $user->is_active = 1;
        $user->status = 'active';
        $user->save();

        $_SESSION['flash_success'] = 'Registration successful! Please login.';
        redirect('/auth/login');
    }
}
