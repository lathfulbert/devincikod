<?php

namespace Modules\Auth\Providers;

use Modules\Users\Models\User;
use Modules\Auth\Models\UserMfaSetup;
use Modules\Auth\Models\AuthLog;

/**
 * SMS OTP Provider
 * Sends 6-digit OTP codes via SMS
 */
class SmsOtpProvider implements AuthProviderInterface
{
    private const OTP_LENGTH = 6;
    private const OTP_EXPIRY = 120; // 2 minutes
    private const MAX_ATTEMPTS = 3;

    public function getName(): string
    {
        return 'sms';
    }

    /**
     * Verify SMS OTP code
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

        // Get user's SMS setup
        $setup = UserMfaSetup::query()
            ->where('user_id', $userId)
            ->where('method_type', 'sms')
            ->where('is_verified', 1)
            ->first();

        if (!$setup) {
            AuthLog::logEvent($userId, AuthLog::EVENT_MFA_FAILED, null, null, [
                'method' => 'sms',
                'reason' => 'not_setup'
            ]);
            return false;
        }

        // Get stored OTP from session
        $sessionKey = "sms_otp_{$userId}";
        if (!isset($_SESSION[$sessionKey])) {
            AuthLog::logEvent($userId, AuthLog::EVENT_MFA_FAILED, null, null, [
                'method' => 'sms',
                'reason' => 'no_otp_sent'
            ]);
            return false;
        }

        $otpData = $_SESSION[$sessionKey];

        // Check expiry
        if (time() > $otpData['expires_at']) {
            unset($_SESSION[$sessionKey]);
            AuthLog::logEvent($userId, AuthLog::EVENT_MFA_FAILED, null, null, [
                'method' => 'sms',
                'reason' => 'otp_expired'
            ]);
            return false;
        }

        // Check attempts
        if ($otpData['attempts'] >= self::MAX_ATTEMPTS) {
            unset($_SESSION[$sessionKey]);
            AuthLog::logEvent($userId, AuthLog::EVENT_MFA_FAILED, null, null, [
                'method' => 'sms',
                'reason' => 'max_attempts_exceeded'
            ]);
            return false;
        }

        // Verify code
        if (!hash_equals($otpData['code_hash'], hash('sha256', $credential))) {
            $_SESSION[$sessionKey]['attempts']++;
            AuthLog::logEvent($userId, AuthLog::EVENT_MFA_FAILED, null, null, [
                'method' => 'sms',
                'reason' => 'invalid_code'
            ]);
            return false;
        }

        // Success - clear OTP
        unset($_SESSION[$sessionKey]);

        AuthLog::logEvent($userId, AuthLog::EVENT_MFA_SUCCESS, null, null, [
            'method' => 'sms'
        ]);

        $setup->markAsUsed();

        $user = User::find($userId);
        return $user ? $user->toArray() : false;
    }

    /**
     * Setup SMS OTP for a user
     * 
     * @param int $userId
     * @param array $data ['phone' => '+1234567890']
     * @return array
     */
    public function setup(int $userId, array $data): array
    {
        $user = User::find($userId);

        if (!$user) {
            throw new \Exception("User not found");
        }

        if (!isset($data['phone'])) {
            throw new \Exception("Phone number is required");
        }

        $phone = $this->normalizePhone($data['phone']);

        // Check if already setup
        $existing = UserMfaSetup::query()
            ->where('user_id', $userId)
            ->where('method_type', 'sms')
            ->first();

        if ($existing) {
            $existing->update([
                'secret' => $phone,
                'is_verified' => 0 // Needs verification
            ]);
        } else {
            $setup = new UserMfaSetup();
            $setup->user_id = $userId;
            $setup->method_type = 'sms';
            $setup->secret = $phone;
            $setup->is_verified = 0;
            $setup->save();
        }

        // Send verification code
        $this->sendOtp($userId, $phone);

        return [
            'success' => true,
            'phone' => $this->maskPhone($phone),
            'message' => 'Verification code sent to your phone'
        ];
    }

    /**
     * Send OTP code to user's phone
     */
    public function sendOtp(int $userId, ?string $phone = null): bool
    {
        if (!$phone) {
            $setup = UserMfaSetup::query()
                ->where('user_id', $userId)
                ->where('method_type', 'sms')
                ->first();

            if (!$setup) {
                return false;
            }

            $phone = $setup->getSecret();
        }

        // Generate OTP
        $code = str_pad((string)random_int(0, 999999), self::OTP_LENGTH, '0', STR_PAD_LEFT);

        // Store hashed OTP in session
        $_SESSION["sms_otp_{$userId}"] = [
            'code_hash' => hash('sha256', $code),
            'expires_at' => time() + self::OTP_EXPIRY,
            'attempts' => 0
        ];

        // Send SMS via SmsCore module
        if (class_exists('\Modules\SmsCore\Services\SmsSenderService')) {
            try {
                // Format phone number
                $phone = \Modules\SmsCore\Services\PhoneNumberService::format($phone);

                if (!\Modules\SmsCore\Services\PhoneNumberService::validate($phone)) {
                    error_log("Invalid phone number format: $phone");
                    return false;
                }

                // Get default gateway configuration
                $gatewayConfig = null;
                if (class_exists('\Modules\Settings\Models\SmsGateway')) {
                    $gatewayConfig = \Modules\Settings\Models\SmsGateway::getDefault();
                }

                // Fallback to mock if no gateway configured
                if (!$gatewayConfig) {
                    error_log("No SMS gateway configured, using mock");
                    $gatewayConfig = new \Modules\Settings\Models\SmsGateway();
                    $gatewayConfig->provider_code = 'mock';
                    $gatewayConfig->name = 'Mock Gateway';
                    $gatewayConfig->api_key = 'mock_key';
                    $gatewayConfig->is_active = 1;
                }

                $gatewayFactory = new \Modules\SmsCore\Services\SmsGatewayFactory();
                $gateway = $gatewayFactory->create($gatewayConfig);

                $pricingService = new \Modules\SmsCore\Services\SmsPricingService();
                $billingService = new \Modules\SmsCore\Services\SmsBillingService();

                $smsService = new \Modules\SmsCore\Services\SmsSenderService(
                    $gateway,
                    $pricingService,
                    $billingService
                );

                // Get configured Sender ID or default to AUTH
                $senderId = \Modules\Settings\Models\Setting::get('auth_sms_sender_id', 'AUTH');

                // Create SMS Message record for history
                $smsMessage = \Modules\SmsCore\Models\SmsMessage::create([
                    'user_id' => $userId,
                    'to' => $phone,
                    'from' => $senderId,
                    'message' => "Votre code de vérification est: {$code}. Valide pour 2 minutes.",
                    'gateway' => $gatewayConfig->provider_code,
                    'status' => 'pending',
                    'message_id' => 'OTP-' . uniqid(),
                    // 'type' => 'otp' // Add type column if exists or use metadata
                ]);

                // Send OTP
                $result = $smsService->send($phone, "Votre code de vérification est: {$code}. Valide pour 2 minutes.", $senderId, [
                    'user_id' => $userId,
                    'type' => 'otp'
                ]);

                // Update history with result
                $smsMessage->gateway_response = $result['gateway_response'] ?? null;

                if ($result['success']) {
                    $smsMessage->markAsSent($result['gateway_message_id'] ?? '');
                } else {
                    $smsMessage->markAsFailed($result['message'] ?? 'Unknown error');
                    error_log("Failed to send SMS OTP: " . ($result['message'] ?? 'Unknown error'));
                    // Fallback to log for dev
                    error_log("SMS OTP for user {$userId}: {$code}");
                    return true; // Allow flow to continue for dev
                }

                return true;
            } catch (\Exception $e) {
                if (isset($smsMessage)) {
                    $smsMessage->markAsFailed($e->getMessage());
                }
                error_log("Exception sending SMS OTP: " . $e->getMessage());
                // Fallback to log for dev
                error_log("SMS OTP for user {$userId}: {$code}");
                return true;
            }
        } else {
            // Fallback: log to file for development
            error_log("SMS OTP for user {$userId}: {$code}");
        }

        return true;
    }

    public function isAvailable(): bool
    {
        // Check if SmsCore module is active
        return class_exists('\Modules\SmsCore\Services\SmsSenderService');
    }

    /**
     * Normalize phone number
     */
    private function normalizePhone(string $phone): string
    {
        return preg_replace('/[^0-9+]/', '', $phone);
    }

    /**
     * Mask phone number for display
     */
    private function maskPhone(string $phone): string
    {
        $length = strlen($phone);
        if ($length <= 4) {
            return $phone;
        }

        return substr($phone, 0, -4) . str_repeat('*', 4);
    }
}
