<?php

namespace Modules\Auth\Services;

/**
 * Token Manager Service
 * Handles JWT token generation and validation
 */
class TokenManager
{
    private string $secretKey;
    private string $algorithm = 'HS256';
    private int $expiryTime = 3600; // 1 hour

    public function __construct()
    {
        $this->secretKey = $_ENV['JWT_SECRET'] ?? hash('sha256', 'default_secret_key_change_me');
    }

    /**
     * Generate JWT token for a user
     */
    public function generateToken(array $payload, ?int $expiryTime = null): string
    {
        $expiryTime = $expiryTime ?? $this->expiryTime;

        $header = [
            'alg' => $this->algorithm,
            'typ' => 'JWT'
        ];

        $payload['iat'] = time();
        $payload['exp'] = time() + $expiryTime;

        $base64UrlHeader = $this->base64UrlEncode(json_encode($header));
        $base64UrlPayload = $this->base64UrlEncode(json_encode($payload));

        $signature = hash_hmac('sha256', $base64UrlHeader . "." . $base64UrlPayload, $this->secretKey, true);
        $base64UrlSignature = $this->base64UrlEncode($signature);

        return $base64UrlHeader . "." . $base64UrlPayload . "." . $base64UrlSignature;
    }

    /**
     * Verify and decode JWT token
     */
    public function verifyToken(string $token): array|false
    {
        $parts = explode('.', $token);

        if (count($parts) !== 3) {
            return false;
        }

        [$base64UrlHeader, $base64UrlPayload, $base64UrlSignature] = $parts;

        // Verify signature
        $signature = $this->base64UrlDecode($base64UrlSignature);
        $expectedSignature = hash_hmac('sha256', $base64UrlHeader . "." . $base64UrlPayload, $this->secretKey, true);

        if (!hash_equals($signature, $expectedSignature)) {
            return false;
        }

        // Decode payload
        $payload = json_decode($this->base64UrlDecode($base64UrlPayload), true);

        // Check expiry
        if (isset($payload['exp']) && time() > $payload['exp']) {
            return false;
        }

        return $payload;
    }

    /**
     * Generate access and refresh tokens
     */
    public function generateTokenPair(int $userId, array $additionalData = []): array
    {
        $accessTokenPayload = array_merge([
            'user_id' => $userId,
            'type' => 'access'
        ], $additionalData);

        $refreshTokenPayload = [
            'user_id' => $userId,
            'type' => 'refresh'
        ];

        return [
            'access_token' => $this->generateToken($accessTokenPayload, 3600), // 1 hour
            'refresh_token' => $this->generateToken($refreshTokenPayload, 86400 * 30), // 30 days
            'expires_in' => 3600
        ];
    }

    /**
     * Refresh access token using refresh token
     */
    public function refreshAccessToken(string $refreshToken): array|false
    {
        $payload = $this->verifyToken($refreshToken);

        if (!$payload || ($payload['type'] ?? '') !== 'refresh') {
            return false;
        }

        $userId = $payload['user_id'];
        return $this->generateTokenPair($userId);
    }

    /**
     * Base64 URL encode
     */
    private function base64UrlEncode(string $data): string
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }

    /**
     * Base64 URL decode
     */
    private function base64UrlDecode(string $data): string
    {
        return base64_decode(strtr($data, '-_', '+/'));
    }
}
