<?php

declare(strict_types=1);

namespace Modules\Notifications\Enums;

/**
 * Notification Channel Enum
 * 
 * Represents available notification delivery channels
 */
enum NotificationChannel: string
{
    case Email = 'email';
    case SMS = 'sms';
    case Push = 'push';
    case Database = 'database';
    case Slack = 'slack';

    /**
     * Get human-readable label
     */
    public function label(): string
    {
        return match ($this) {
            self::Email => 'Email',
            self::SMS => 'SMS',
            self::Push => 'Notification Push',
            self::Database => 'Base de données',
            self::Slack => 'Slack',
        };
    }

    /**
     * Check if channel requires external service
     */
    public function requiresExternalService(): bool
    {
        return match ($this) {
            self::Email, self::SMS, self::Push, self::Slack => true,
            self::Database => false,
        };
    }

    /**
     * Get icon class
     */
    public function icon(): string
    {
        return match ($this) {
            self::Email => 'fa-envelope',
            self::SMS => 'fa-mobile',
            self::Push => 'fa-bell',
            self::Database => 'fa-database',
            self::Slack => 'fa-slack',
        };
    }
}
