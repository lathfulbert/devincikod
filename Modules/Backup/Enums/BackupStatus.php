<?php

declare(strict_types=1);

namespace Modules\Backup\Enums;

/**
 * Backup Status Enum
 * 
 * Represents the possible states of a backup operation
 */
enum BackupStatus: string
{
    case Pending = 'pending';
    case Processing = 'processing';
    case Completed = 'completed';
    case Failed = 'failed';

    /**
     * Get human-readable label
     */
    public function label(): string
    {
        return match ($this) {
            self::Pending => 'En attente',
            self::Processing => 'En cours',
            self::Completed => 'Terminé',
            self::Failed => 'Échoué',
        };
    }

    /**
     * Get CSS class for UI display
     */
    public function cssClass(): string
    {
        return match ($this) {
            self::Pending => 'badge-warning',
            self::Processing => 'badge-info',
            self::Completed => 'badge-success',
            self::Failed => 'badge-danger',
        };
    }

    /**
     * Check if status is final (no more processing)
     */
    public function isFinal(): bool
    {
        return match ($this) {
            self::Completed, self::Failed => true,
            self::Pending, self::Processing => false,
        };
    }
}
