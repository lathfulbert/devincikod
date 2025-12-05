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
            return false;
        }

        if (method_exists($provider, 'sendOtp')) {
            return $provider->sendOtp($userId);
        }

        return false;
    }
}
