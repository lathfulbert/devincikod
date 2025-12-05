<?php

namespace Modules\Auth\Providers;

/**
 * Authentication Provider Interface
 * All auth providers must implement this interface
 */
interface AuthProviderInterface
{
    /**
     * Get the provider name
     */
    public function getName(): string;

    /**
     * Verify credentials
     * 
     * @param mixed $identifier User identifier (email, phone, etc.)
     * @param mixed $credential The credential to verify (password, OTP code, token, etc.)
     * @return array|false Returns user data array on success, false on failure
     */
    public function verify($identifier, $credential);

    /**
     * Setup/enroll a user for this auth method
     * 
     * @param int $userId
     * @param array $data Setup data (phone number, secret, etc.)
     * @return array Returns setup data (QR code, backup codes, etc.)
     */
    public function setup(int $userId, array $data): array;

    /**
     * Check if this provider is available/configured
     */
    public function isAvailable(): bool;
}
