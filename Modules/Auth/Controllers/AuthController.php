<?php

namespace Modules\Auth\Controllers;

use Modules\Auth\Services\MfaManager;
use Modules\Auth\Services\TokenManager;
use Modules\Auth\Services\AuditLogger;
use Modules\Auth\Services\TrustedDeviceManager;
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
    private TrustedDeviceManager $trustedDeviceManager;

    public function __construct()
    {
        $this->mfaManager = new MfaManager();
        $this->tokenManager = new TokenManager();
        $this->auditLogger = new AuditLogger();
        $this->trustedDeviceManager = new TrustedDeviceManager();
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
            // Check if this device is already trusted (valid for 1 month)
            if ($this->trustedDeviceManager->isTrusted($userId)) {
                // Device is trusted - skip MFA and complete login
                $this->completeLogin($user);
                return;
            }

            // Device not trusted - require MFA verification
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
    /**
     * Redirect to OAuth Provider
     */
    public function oauthRedirect(string $provider)
    {
        try {
            // Get provider configuration
            $clientId = \Modules\Settings\Models\Setting::get("auth_oauth_{$provider}_client_id");
            $clientSecret = \Modules\Settings\Models\Setting::get("auth_oauth_{$provider}_client_secret");

            if (!$clientId || !$clientSecret) {
                $_SESSION['flash_error'] = "Provider $provider not configured";
                redirect('/auth/login');
                return;
            }

            $config = [
                "auth_oauth_{$provider}_client_id" => $clientId,
                "auth_oauth_{$provider}_client_secret" => $clientSecret
            ];

            $oauthProvider = new \Modules\Auth\Providers\OauthProvider($config);
            $authUrl = $oauthProvider->getAuthUrl($provider);

            redirect($authUrl);
        } catch (\Exception $e) {
            $_SESSION['flash_error'] = $e->getMessage();
            redirect('/auth/login');
        }
    }

    /**
     * Handle OAuth Callback
     */
    public function oauthCallback(string $provider)
    {
        $code = $_GET['code'] ?? null;
        $state = $_GET['state'] ?? null;

        if (!$code || !$state) {
            $_SESSION['flash_error'] = 'Invalid request';
            redirect('/auth/login');
            return;
        }

        try {
            $oauthProvider = new \Modules\Auth\Providers\OauthProvider();
            $oauthUser = $oauthProvider->handleCallback($provider, $code, $state);

            // Check if user exists by email
            $existingUser = User::where('email', $oauthUser['email'])->first();

            if ($existingUser) {
                // Link account if not linked
                $this->linkOauthAccount($existingUser->id, $oauthUser);

                // Login user
                $this->completeLogin($existingUser->toArray());
            } else {
                // Register new user
                $newUser = new User();
                $newUser->email = $oauthUser['email'];
                $newUser->first_name = $oauthUser['name']; // Simplify name handling
                $newUser->is_active = 1;
                $newUser->status = 'active';
                $newUser->email_verified_at = date('Y-m-d H:i:s'); // OAuth users are verified
                $newUser->save();

                // Link account
                $this->linkOauthAccount($newUser->id, $oauthUser);

                // Login user
                $this->completeLogin($newUser->toArray());
            }
        } catch (\Exception $e) {
            $_SESSION['flash_error'] = 'Authentication failed: ' . $e->getMessage();
            redirect('/auth/login');
        }
    }

    /**
     * Link OAuth Account to User
     */
    private function linkOauthAccount(int $userId, array $oauthUser)
    {
        $existingAccount = \Modules\Auth\Models\OauthAccount::where('user_id', $userId)
            ->where('provider', $oauthUser['provider'])
            ->first();

        $tokenData = $oauthUser['token'];

        if ($existingAccount) {
            $existingAccount->updateTokens(
                $tokenData['access_token'],
                $tokenData['refresh_token'] ?? null,
                $tokenData['expires_in'] ?? null
            );
        } else {
            \Modules\Auth\Models\OauthAccount::create([
                'user_id' => $userId,
                'provider' => $oauthUser['provider'],
                'provider_user_id' => $oauthUser['provider_user_id'],
                'access_token' => $tokenData['access_token'],
                'refresh_token' => $tokenData['refresh_token'] ?? null,
                'expires_at' => isset($tokenData['expires_in']) ? date('Y-m-d H:i:s', time() + $tokenData['expires_in']) : null
            ]);
        }
    }
}
