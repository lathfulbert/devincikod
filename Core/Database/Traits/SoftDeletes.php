<?php

namespace App\Core\Database\Traits;

use App\Core\Database\Database;

trait SoftDeletes
{
    /**
     * Boot the soft deleting trait for a model.
     */
    public static function bootSoftDeletes()
    {
        // Auto-add where clause to exclude soft deleted records
    }

    /**
     * Perform a soft delete on the model.
     */
    public function delete(): void
    {
        if (isset($this->attributes['id'])) {
            $db = Database::getInstance();
            $table = static::getTable();

            // Set deleted_at timestamp instead of actually deleting
            $now = date('Y-m-d H:i:s');
            $db->query(
                "UPDATE `{$table}` SET `deleted_at` = ? WHERE id = ?",
                [$now, $this->attributes['id']]
            );

            $this->attributes['deleted_at'] = $now;
        }
    }

    /**
     * Force delete the model (permanent deletion).
     */
    public function forceDelete(): void
    {
        if (isset($this->attributes['id'])) {
            $db = Database::getInstance();
            $table = static::getTable();
            $db->query("DELETE FROM `{$table}` WHERE id = ?", [$this->attributes['id']]);
        }
    }

    /**
     * Restore a soft-deleted model.
     */
    public function restore(): void
    {
        if (isset($this->attributes['id'])) {
            $db = Database::getInstance();
            $table = static::getTable();

            $db->query(
                "UPDATE `{$table}` SET `deleted_at` = NULL WHERE id = ?",
                [$this->attributes['id']]
            );

            $this->attributes['deleted_at'] = null;
        }
    }

    /**
     * Determine if the model instance has been soft-deleted.
     */
    public function trashed(): bool
    {
        return isset($this->attributes['deleted_at']) && $this->attributes['deleted_at'] !== null;
    }

    /**
     * Get all models including soft deleted.
     */
    public static function withTrashed(): array
    {
        $db = Database::getInstance();
        $table = static::getTable();
        $stmt = $db->query("SELECT * FROM `{$table}`");
        return $stmt->fetchAll(\PDO::FETCH_CLASS, static::class);
    }

    /**
     * Get only soft deleted models.
     */
    public static function onlyTrashed(): array
    {
        $db = Database::getInstance();
        $table = static::getTable();
        $stmt = $db->query("SELECT * FROM `{$table}` WHERE `deleted_at` IS NOT NULL");
        return $stmt->fetchAll(\PDO::FETCH_CLASS, static::class);
    }

    /**
     * Get the name of the "deleted at" column.
     */
    public function getDeletedAtColumn(): string
    {
        return 'deleted_at';
    }
}
