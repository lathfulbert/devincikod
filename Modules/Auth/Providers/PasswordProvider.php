<?php

namespace Modules\Auth\Providers;

use Modules\Users\Models\User;
use Modules\Auth\Models\AuthLog;

/**
 * Password Authentication Provider
 * Handles traditional username/password authentication
 */
class PasswordProvider implements AuthProviderInterface
{
    public function getName(): string
    {
        return 'password';
    }

    /**
     * Verify username/password combination
     * 
     * @param mixed $identifier Email or username
     * @param mixed $credential Password
     * @return array|false User data on success, false on failure
     */
    public function verify($identifier, $credential)
    {
        // Find user by email or username
        $user = User::query()
            ->where('email', $identifier)
            ->orWhere('username', $identifier)
            ->first();

        if (!$user) {
            // Log failed attempt
            AuthLog::logEvent(null, AuthLog::EVENT_LOGIN_FAILED, null, null, [
                'identifier' => $identifier,
                'reason' => 'user_not_found'
            ]);
            return false;
        }

        // Verify password
        if (!password_verify($credential, $user->password)) {
            // Log failed attempt
            AuthLog::logEvent($user->id, AuthLog::EVENT_LOGIN_FAILED, null, null, [
                'reason' => 'invalid_password'
            ]);
            return false;
        }

        // Check if user is active
        if (isset($user->is_active) && !$user->is_active) {
            AuthLog::logEvent($user->id, AuthLog::EVENT_LOGIN_FAILED, null, null, [
                'reason' => 'account_inactive'
            ]);
            return false;
        }

        // Check rate limiting
        $failedAttempts = AuthLog::getRecentFailedAttempts($user->id, 15);
        if ($failedAttempts >= 5) {
            AuthLog::logEvent($user->id, AuthLog::EVENT_ACCOUNT_LOCKED, null, null, [
                'reason' => 'too_many_failed_attempts',
                'attempts' => $failedAttempts
            ]);
            return false;
        }

        // Log successful login
        AuthLog::logEvent($user->id, AuthLog::EVENT_LOGIN_SUCCESS);

        // Update last login info
        $user->update([
            'last_login_at' => date('Y-m-d H:i:s'),
            'last_login_ip' => $_SERVER['REMOTE_ADDR'] ?? null
        ]);

        return $user->toArray();
    }

    /**
     * Setup password (change password)
     * 
     * @param int $userId
     * @param array $data ['password' => 'new_password']
     * @return array
     */
    public function setup(int $userId, array $data): array
    {
        $user = User::find($userId);

        if (!$user) {
            throw new \Exception("User not found");
        }

        if (!isset($data['password'])) {
            throw new \Exception("Password is required");
        }

        // Hash password using Argon2id (or bcrypt fallback)
        $hashedPassword = password_hash($data['password'], PASSWORD_ARGON2ID);

        $user->update(['password' => $hashedPassword]);

        return [
            'success' => true,
            'message' => 'Password updated successfully'
        ];
    }

    public function isAvailable(): bool
    {
        return true; // Always available
    }
}
