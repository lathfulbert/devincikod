<?php

namespace Modules\Auth\Models;

use App\Core\Database\Model;
use Modules\Users\Models\User;

class AuthLog extends Model
{
    protected static string $table = 'auth_logs';

    protected array $fillable = [
        'user_id',
        'event_type',
        'ip_address',
        'user_agent',
        'details'
    ];

    protected array $casts = [
        'details' => 'json'
    ];

    // Event types
    const EVENT_LOGIN_SUCCESS = 'login_success';
    const EVENT_LOGIN_FAILED = 'login_failed';
    const EVENT_MFA_SUCCESS = 'mfa_success';
    const EVENT_MFA_FAILED = 'mfa_failed';
    const EVENT_LOGOUT = 'logout';
    const EVENT_PASSWORD_RESET = 'password_reset';
    const EVENT_ACCOUNT_LOCKED = 'account_locked';

    /**
     * Get the user who triggered this event
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    /**
     * Log an authentication event
     */
    public static function logEvent(
        ?int $userId,
        string $eventType,
        ?string $ipAddress = null,
        ?string $userAgent = null,
        ?array $details = null
    ): self {
        $log = new self();
        $log->user_id = $userId;
        $log->event_type = $eventType;
        $log->ip_address = $ipAddress ?? ($_SERVER['REMOTE_ADDR'] ?? null);
        $log->user_agent = $userAgent ?? ($_SERVER['HTTP_USER_AGENT'] ?? null);
        $log->details = $details ? json_encode($details) : null;
        $log->save();

        return $log;
    }

    /**
     * Get recent failed login attempts for a user
     */
    public static function getRecentFailedAttempts(int $userId, int $minutes = 15): int
    {
        $since = date('Y-m-d H:i:s', time() - ($minutes * 60));

        return static::query()
            ->where('user_id', $userId)
            ->where('event_type', self::EVENT_LOGIN_FAILED)
            ->where('created_at', '>=', $since)
            ->count();
    }

    /**
     * Get recent failed attempts by IP
     */
    public static function getRecentFailedAttemptsByIp(string $ipAddress, int $minutes = 15): int
    {
        $since = date('Y-m-d H:i:s', time() - ($minutes * 60));

        return static::query()
            ->where('ip_address', $ipAddress)
            ->where('event_type', self::EVENT_LOGIN_FAILED)
            ->where('created_at', '>=', $since)
            ->count();
    }
}
