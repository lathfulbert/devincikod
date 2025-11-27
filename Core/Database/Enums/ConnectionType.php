<?php

declare(strict_types=1);

namespace App\Core\Database\Enums;

/**
 * Database Connection Type Enum
 * 
 * Supported database drivers
 */
enum ConnectionType: string
{
    case MySQL = 'mysql';
    case PostgreSQL = 'pgsql';
    case SQLite = 'sqlite';

    /**
     * Get DSN prefix
     */
    public function dsnPrefix(): string
    {
        return match ($this) {
            self::MySQL => 'mysql',
            self::PostgreSQL => 'pgsql',
            self::SQLite => 'sqlite',
        };
    }

    /**
     * Get default port
     */
    public function defaultPort(): ?int
    {
        return match ($this) {
            self::MySQL => 3306,
            self::PostgreSQL => 5432,
            self::SQLite => null,
        };
    }

    /**
     * Check if supports transactions
     */
    public function supportsTransactions(): bool
    {
        return true; // All supported databases support transactions
    }

    /**
     * Get driver class name
     */
    public function driverClass(): string
    {
        return match ($this) {
            self::MySQL => \PDO::class,
            self::PostgreSQL => \PDO::class,
            self::SQLite => \PDO::class,
        };
    }
}
