<?php

namespace Modules\Auth\Services;

use Modules\Auth\Models\AuthLog;

/**
 * Audit Logger Service
 * Centralized authentication event logging
 */
class AuditLogger
{
    /**
     * Log authentication event
     */
    public function log(
        ?int $userId,
        string $eventType,
        ?array $details = null,
        ?string $ipAddress = null,
        ?string $userAgent = null
    ): void {
        AuthLog::logEvent($userId, $eventType, $ipAddress, $userAgent, $details);
    }

    /**
     * Log successful login
     */
    public function logLoginSuccess(int $userId, array $details = []): void
    {
        $this->log($userId, AuthLog::EVENT_LOGIN_SUCCESS, $details);
    }

    /**
     * Log failed login
     */
    public function logLoginFailed(?int $userId, string $reason, array $details = []): void
    {
        $details['reason'] = $reason;
        $this->log($userId, AuthLog::EVENT_LOGIN_FAILED, $details);
    }

    /**
     * Log MFA success
     */
    public function logMfaSuccess(int $userId, string $method): void
    {
        $this->log($userId, AuthLog::EVENT_MFA_SUCCESS, ['method' => $method]);
    }

    /**
     * Log MFA failure
     */
    public function logMfaFailed(int $userId, string $method, string $reason): void
    {
        $this->log($userId, AuthLog::EVENT_MFA_FAILED, [
            'method' => $method,
            'reason' => $reason
        ]);
    }

    /**
     * Log logout
     */
    public function logLogout(int $userId): void
    {
        $this->log($userId, AuthLog::EVENT_LOGOUT);
    }

    /**
     * Log password reset
     */
    public function logPasswordReset(int $userId): void
    {
        $this->log($userId, AuthLog::EVENT_PASSWORD_RESET);
    }

    /**
     * Log account lockout
     */
    public function logAccountLocked(int $userId, string $reason): void
    {
        $this->log($userId, AuthLog::EVENT_ACCOUNT_LOCKED, ['reason' => $reason]);
    }

    /**
     * Get recent login attempts (for rate limiting)
     */
    public function getRecentFailedAttempts(int $userId, int $minutes = 15): int
    {
        return AuthLog::getRecentFailedAttempts($userId, $minutes);
    }

    /**
     * Get recent failed attempts by IP
     */
    public function getRecentFailedAttemptsByIp(string $ipAddress, int $minutes = 15): int
    {
        return AuthLog::getRecentFailedAttemptsByIp($ipAddress, $minutes);
    }

    /**
     * Check if user or IP should be rate limited
     */
    public function shouldRateLimit(?int $userId, ?string $ipAddress = null, int $maxAttempts = 5): bool
    {
        $ipAddress = $ipAddress ?? ($_SERVER['REMOTE_ADDR'] ?? null);

        if ($userId && $this->getRecentFailedAttempts($userId) >= $maxAttempts) {
            return true;
        }

        if ($ipAddress && $this->getRecentFailedAttemptsByIp($ipAddress) >= $maxAttempts) {
            return true;
        }

        return false;
    }
}
