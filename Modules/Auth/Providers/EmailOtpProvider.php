<?php

namespace Modules\Auth\Providers;

use Modules\Users\Models\User;
use Modules\Auth\Models\UserMfaSetup;
use Modules\Auth\Models\AuthLog;

/**
 * Email OTP Provider
 * Sends 6-digit OTP codes via email
 */
class EmailOtpProvider implements AuthProviderInterface
{
    private const OTP_LENGTH = 6;
    private const OTP_EXPIRY = 300; // 5 minutes
    private const MAX_ATTEMPTS = 3;

    public function getName(): string
    {
        return 'email';
    }

    /**
     * Verify Email OTP code
     * 
     * @param mixed $identifier User ID
     * @param mixed $credential 6-digit OTP code
     * @return array|false
     */
    public function verify($identifier, $credential)
    {
        $userId = is_numeric($identifier) ? (int)$identifier : null;

        if (!$userId) {
            return false;
        }

        // Get stored OTP from session
        $sessionKey = "email_otp_{$userId}";
        if (!isset($_SESSION[$sessionKey])) {
            AuthLog::logEvent($userId, AuthLog::EVENT_MFA_FAILED, null, null, [
                'method' => 'email',
                'reason' => 'no_otp_sent'
            ]);
            return false;
        }

        $otpData = $_SESSION[$sessionKey];

        // Check expiry
        if (time() > $otpData['expires_at']) {
            unset($_SESSION[$sessionKey]);
            AuthLog::logEvent($userId, AuthLog::EVENT_MFA_FAILED, null, null, [
                'method' => 'email',
                'reason' => 'otp_expired'
            ]);
            return false;
        }

        // Check attempts
        if ($otpData['attempts'] >= self::MAX_ATTEMPTS) {
            unset($_SESSION[$sessionKey]);
            AuthLog::logEvent($userId, AuthLog::EVENT_MFA_FAILED, null, null, [
                'method' => 'email',
                'reason' => 'max_attempts_exceeded'
            ]);
            return false;
        }

        // Verify code
        if (!hash_equals($otpData['code_hash'], hash('sha256', $credential))) {
            $_SESSION[$sessionKey]['attempts']++;
            AuthLog::logEvent($userId, AuthLog::EVENT_MFA_FAILED, null, null, [
                'method' => 'email',
                'reason' => 'invalid_code'
            ]);
            return false;
        }

        // Success - clear OTP
        unset($_SESSION[$sessionKey]);

        AuthLog::logEvent($userId, AuthLog::EVENT_MFA_SUCCESS, null, null, [
            'method' => 'email'
        ]);

        $user = User::find($userId);
        return $user ? $user->toArray() : false;
    }

    /**
     * Setup Email OTP (uses user's existing email)
     * 
     * @param int $userId
     * @param array $data
     * @return array
     */
    public function setup(int $userId, array $data): array
    {
        $user = User::find($userId);

        if (!$user) {
            throw new \Exception("User not found");
        }

        // Use user's email
        $email = $user->email;

        // Check if already setup
        $existing = UserMfaSetup::query()
            ->where('user_id', $userId)
            ->where('method_type', 'email')
            ->first();

        if ($existing) {
            $existing->update([
                'secret' => $email,
                'is_verified' => 1 // Email is already verified during registration
            ]);
        } else {
            $setup = new UserMfaSetup();
            $setup->user_id = $userId;
            $setup->method_type = 'email';
            $setup->secret = $email;
            $setup->is_verified = 1;
            $setup->save();
        }

        return [
            'success' => true,
            'email' => $this->maskEmail($email),
            'message' => 'Email OTP enabled. Codes will be sent to your email.'
        ];
    }

    /**
     * Send OTP code to user's email
     */
    public function sendOtp(int $userId): bool
    {
        $user = User::find($userId);
        if (!$user) {
            return false;
        }

        $email = $user->email;

        // Generate OTP
        $code = str_pad((string)random_int(0, 999999), self::OTP_LENGTH, '0', STR_PAD_LEFT);

        // Store hashed OTP in session
        $_SESSION["email_otp_{$userId}"] = [
            'code_hash' => hash('sha256', $code),
            'expires_at' => time() + self::OTP_EXPIRY,
            'attempts' => 0
        ];

        // Send email
        $subject = 'Your Verification Code';
        $message = "Your verification code is: {$code}\n\nThis code will expire in 5 minutes.";

        // Use PHP mail or email service
        if (function_exists('mail')) {
            mail($email, $subject, $message);
        } else {
            // Fallback: log to file for development
            error_log("Email OTP for user {$userId} ({$email}): {$code}");
        }

        return true;
    }

    public function isAvailable(): bool
    {
        return true; // Email is always available
    }

    /**
     * Mask email for display
     */
    private function maskEmail(string $email): string
    {
        $parts = explode('@', $email);
        if (count($parts) !== 2) {
            return $email;
        }

        $username = $parts[0];
        $domain = $parts[1];

        $usernameLength = strlen($username);
        if ($usernameLength <= 2) {
            $maskedUsername = $username;
        } else {
            $maskedUsername = substr($username, 0, 2) . str_repeat('*', $usernameLength - 2);
        }

        return $maskedUsername . '@' . $domain;
    }
}
