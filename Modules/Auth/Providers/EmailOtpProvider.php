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

        // Send email using EmailSenderService
        try {
            // Instantiate service (manually for now as we don't have DI in providers yet)
            $gatewayFactory = new \Modules\EmailMarketing\Services\EmailGatewayFactory();
            $emailService = new \Modules\EmailMarketing\Services\EmailSenderService($gatewayFactory);

            $subject = 'Votre code de vérification';

            // Simple HTML Template
            $html = "
            <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; padding: 20px; border: 1px solid #e0e0e0; border-radius: 8px; background-color: #fafafa;'>
                <div style='text-align: center; margin-bottom: 20px;'>
                    <h2 style='color: #333;'>Vérification de sécurité</h2>
                </div>
                <div style='background-color: #fff; padding: 20px; border-radius: 5px; text-align: center; box-shadow: 0 2px 4px rgba(0,0,0,0.05);'>
                    <p style='color: #666; margin-bottom: 10px;'>Votre code de vérification est :</p>
                    <h1 style='color: #2563eb; font-size: 32px; letter-spacing: 5px; margin: 10px 0;'>{$code}</h1>
                    <p style='color: #999; font-size: 14px; margin-top: 20px;'>Ce code expire dans 5 minutes.</p>
                </div>
                <div style='text-align: center; margin-top: 20px; color: #aaa; font-size: 12px;'>
                    Si vous n'êtes pas à l'origine de cette demande, veuillez ignorer cet email.
                </div>
            </div>";

            // Get Sender info from settings
            $fromName = \Modules\Settings\Models\Setting::get('auth_email_sender_name', 'Security Team');
            $fromEmail = \Modules\Settings\Models\Setting::get('auth_email_sender_address', 'noreply@' . ($_SERVER['HTTP_HOST'] ?? 'localhost'));

            $result = $emailService->send($email, $subject, $html, [
                'user_id' => $userId,
                'from_name' => $fromName,
                'from' => $fromEmail,
                'metadata' => ['type' => 'otp']
            ]);

            return $result['success'];
        } catch (\Exception $e) {
            error_log("Failed to send Email OTP: " . $e->getMessage());
            // Fallback to mail() if service fails completely? No, let's trust the service or fail.
            // Actually, if EmailMarketing module is not active or set up, this might fail.
            // But we should assume it works if the file exists.

            // Fallback log
            error_log("Email OTP for user {$userId} ({$email}): {$code}");
            return false;
        }
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
