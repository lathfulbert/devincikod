<?php

declare(strict_types=1);

namespace Modules\Backup\Enums;

/**
 * Backup Type Enum
 * 
 * Represents different types of backups
 */
enum BackupType: string
{
    case Database = 'database';
    case Files = 'files';
    case Full = 'full';

    /**
     * Get human-readable label
     */
    public function label(): string
    {
        return match ($this) {
            self::Database => 'Base de données',
            self::Files => 'Fichiers',
            self::Full => 'Complète',
        };
    }

    /**
     * Get description
     */
    public function description(): string
    {
        return match ($this) {
            self::Database => 'Sauvegarde uniquement de la base de données',
            self::Files => 'Sauvegarde uniquement des fichiers',
            self::Full => 'Sauvegarde complète (base de données + fichiers)',
        };
    }

    /**
     * Check if includes database
     */
    public function includesDatabase(): bool
    {
        return match ($this) {
            self::Database, self::Full => true,
            self::Files => false,
        };
    }

    /**
     * Check if includes files
     */
    public function includesFiles(): bool
    {
        return match ($this) {
            self::Files, self::Full => true,
            self::Database => false,
        };
    }
}
