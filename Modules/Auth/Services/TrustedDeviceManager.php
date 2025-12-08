<?php

namespace Modules\Auth\Services;

use Modules\Auth\Models\TrustedDevice;

/**
 * Trusted Device Manager Service
 * Manages trusted devices for MFA bypass with 1 month validity
 */
class TrustedDeviceManager
{
    private const TRUST_DURATION_DAYS = 30; // 1 month

    /**
     * Generate a unique device fingerprint based on user agent and IP
     */
    public function generateFingerprint(): string
    {
        $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? 'unknown';
        $ip = $this->getClientIp();

        // Create a fingerprint from browser info
        // Note: For production, consider using a JavaScript-based fingerprinting library
        // that captures more device details (canvas, fonts, screen resolution, etc.)
        $fingerprint = hash('sha256', $userAgent . '|' . $ip);

        return $fingerprint;
    }

    /**
     * Get client IP address (handles proxies)
     */
    private function getClientIp(): string
    {
        $ipKeys = [
            'HTTP_CLIENT_IP',
            'HTTP_X_FORWARDED_FOR',
            'HTTP_X_FORWARDED',
            'HTTP_X_CLUSTER_CLIENT_IP',
            'HTTP_FORWARDED_FOR',
            'HTTP_FORWARDED',
            'REMOTE_ADDR'
        ];

        foreach ($ipKeys as $key) {
            if (isset($_SERVER[$key])) {
                $ips = explode(',', $_SERVER[$key]);
                $ip = trim($ips[0]);

                if (filter_var($ip, FILTER_VALIDATE_IP)) {
                    return $ip;
                }
            }
        }

        return $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
    }

    /**
     * Check if the current device is trusted for the user
     */
    public function isTrusted(int $userId): bool
    {
        $fingerprint = $this->generateFingerprint();

        $device = TrustedDevice::query()
            ->where('user_id', $userId)
            ->where('device_fingerprint', $fingerprint)
            ->first();

        if (!$device) {
            return false;
        }

        // Check if device trust has expired
        if ($device->isExpired()) {
            $device->delete();
            return false;
        }

        // Update last used timestamp
        $device->markAsUsed();

        return true;
    }

    /**
     * Trust the current device for the user (1 month validity)
     */
    public function trustDevice(int $userId): TrustedDevice
    {
        $fingerprint = $this->generateFingerprint();
        $ip = $this->getClientIp();
        $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? 'unknown';

        // Extract device name from user agent
        $deviceName = $this->extractDeviceName($userAgent);

        // Check if device already exists
        $existing = TrustedDevice::query()
            ->where('user_id', $userId)
            ->where('device_fingerprint', $fingerprint)
            ->first();

        if ($existing) {
            // Extend existing device trust
            $existing->extend();
            $existing->ip_address = $ip;
            $existing->user_agent = $userAgent;
            $existing->save();
            return $existing;
        }

        // Create new trusted device
        $device = new TrustedDevice();
        $device->user_id = $userId;
        $device->device_fingerprint = $fingerprint;
        $device->device_name = $deviceName;
        $device->ip_address = $ip;
        $device->user_agent = $userAgent;
        $device->last_used_at = date('Y-m-d H:i:s');
        $device->expires_at = date('Y-m-d H:i:s', strtotime('+' . self::TRUST_DURATION_DAYS . ' days'));
        $device->save();

        return $device;
    }

    /**
     * Revoke trust for the current device
     */
    public function revokeCurrentDevice(int $userId): bool
    {
        $fingerprint = $this->generateFingerprint();

        $device = TrustedDevice::query()
            ->where('user_id', $userId)
            ->where('device_fingerprint', $fingerprint)
            ->first();

        if ($device) {
            return $device->delete();
        }

        return false;
    }

    /**
     * Revoke all trusted devices for a user
     */
    public function revokeAllDevices(int $userId): int
    {
        return TrustedDevice::revokeAllForUser($userId);
    }

    /**
     * Get all trusted devices for a user
     */
    public function getUserDevices(int $userId): array
    {
        return TrustedDevice::getUserDevices($userId);
    }

    /**
     * Extract a readable device name from user agent
     */
    private function extractDeviceName(string $userAgent): string
    {
        // Mobile devices
        if (preg_match('/(iPhone|iPad|iPod)/i', $userAgent, $matches)) {
            return $matches[1];
        }
        if (preg_match('/Android/i', $userAgent)) {
            if (preg_match('/Mobile/i', $userAgent)) {
                return 'Android Phone';
            }
            return 'Android Tablet';
        }

        // Browsers
        if (preg_match('/Edge/i', $userAgent)) {
            return 'Microsoft Edge';
        }
        if (preg_match('/Chrome/i', $userAgent)) {
            return 'Google Chrome';
        }
        if (preg_match('/Firefox/i', $userAgent)) {
            return 'Mozilla Firefox';
        }
        if (preg_match('/Safari/i', $userAgent)) {
            return 'Safari';
        }
        if (preg_match('/Opera|OPR/i', $userAgent)) {
            return 'Opera';
        }

        // OS
        if (preg_match('/Windows/i', $userAgent)) {
            return 'Windows Device';
        }
        if (preg_match('/Macintosh|Mac OS X/i', $userAgent)) {
            return 'Mac Device';
        }
        if (preg_match('/Linux/i', $userAgent)) {
            return 'Linux Device';
        }

        return 'Unknown Device';
    }

    /**
     * Clean up expired devices (should be called periodically, e.g., via cron)
     */
    public function cleanupExpired(): int
    {
        return TrustedDevice::cleanupExpired();
    }

    /**
     * Get device info for display
     */
    public function getDeviceInfo(TrustedDevice $device): array
    {
        return [
            'id' => $device->id,
            'name' => $device->device_name ?? 'Unknown Device',
            'ip' => $device->ip_address,
            'last_used' => $device->last_used_at,
            'expires' => $device->expires_at,
            'is_current' => $device->device_fingerprint === $this->generateFingerprint()
        ];
    }
}
