<?php

namespace Modules\Auth\Services;

use Modules\Auth\Models\MfaMethod;
use Modules\Auth\Models\UserMfaSetup;
use Modules\Users\Models\User;

/**
 * MFA Manager Service
 * Orchestrates Multi-Factor Authentication
 */
class MfaManager
{
    /**
     * Check if MFA is required for a user
     */
    public function isRequired(int $userId): bool
    {
        $user = User::find($userId);
        if (!$user) {
            return false;
        }

        return $user->hasMfaEnabled();
    }

    /**
     * Get available MFA methods for a user
     */
    public function getAvailableMethods(int $userId): array
    {
        $setups = UserMfaSetup::query()
            ->where('user_id', $userId)
            ->where('is_verified', 1)
            ->get();

        return array_map(function ($setup) {
            return [
                'type' => $setup->method_type,
                'value' => $setup->getSecret(), // Add the value (phone/email)
                'last_used' => $setup->last_used_at
            ];
        }, $setups);
    }

    /**
     * Get all user MFA methods (including unverified)
     */
    public function getAllUserMethods(int $userId): array
    {
        $setups = UserMfaSetup::query()
            ->where('user_id', $userId)
            ->get();

        return array_map(function ($setup) {
            return [
                'type' => $setup->method_type,
                'is_verified' => (bool)$setup->is_verified,
                'last_used' => $setup->last_used_at
            ];
        }, $setups);
    }

    /**
     * Verify MFA code
     */
    public function verify(int $userId, string $methodType, string $code): bool
    {
        $method = MfaMethod::query()->where('type', $methodType)->first();

        if (!$method || !$method->isActive()) {
            return false;
        }

        $provider = $method->getProvider();
        $result = $provider->verify($userId, $code);

        return $result !== false;
    }

    /**
     * Setup MFA method for a user
     */
    public function setupMethod(int $userId, string $methodType, array $data = []): array
    {
        $method = MfaMethod::query()->where('type', $methodType)->first();

        if (!$method || !$method->isActive()) {
            throw new \Exception("MFA method not available");
        }

        $provider = $method->getProvider();

        if (!$provider->isAvailable()) {
            throw new \Exception("MFA provider not configured");
        }

        return $provider->setup($userId, $data);
    }

    /**
     * Disable MFA method for a user
     */
    public function disableMethod(int $userId, string $methodType): bool
    {
        $setup = UserMfaSetup::query()
            ->where('user_id', $userId)
            ->where('method_type', $methodType)
            ->first();

        if ($setup) {
            $setup->delete();
            return true;
        }

        return false;
    }

    /**
     * Get MFA method provider
     */
    public function getProvider(string $methodType)
    {
        $method = MfaMethod::query()->where('type', $methodType)->first();

        if (!$method) {
            return null;
        }

        return $method->getProvider();
    }

    /**
     * Send OTP (for SMS/Email methods)
     */
    public function sendOtp(int $userId, string $methodType): bool
    {
        $provider = $this->getProvider($methodType);

        if (!$provider) {
            $this->log('warning', "MFA provider not found for method: {$methodType}");
            return false;
        }

        if (!method_exists($provider, 'sendOtp')) {
            $this->log('warning', "Provider " . get_class($provider) . " does not have sendOtp method");
            return false;
        }

        try {
            $result = $provider->sendOtp($userId);

            if ($result) {
                $this->log('info', "OTP sent successfully", [
                    'user_id' => $userId,
                    'method' => $methodType
                ]);
            } else {
                $this->log('warning', "OTP send returned false", [
                    'user_id' => $userId,
                    'method' => $methodType
                ]);
            }

            return $result;
        } catch (\Exception $e) {
            $this->log('error', "Exception while sending OTP", [
                'user_id' => $userId,
                'method' => $methodType,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }

    /**
     * Safe logging helper
     */
    private function log(string $level, string $message, array $context = []): void
    {
        try {
            if (function_exists('logger')) {
                logger()->$level($message, $context);
            }
        } catch (\Exception $e) {
            // Fallback to error_log if logger fails
            error_log("[MFA {$level}] {$message} " . json_encode($context));
        }
    }
}
