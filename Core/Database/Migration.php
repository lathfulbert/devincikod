<?php

namespace App\Core\Database;

/**
 * Migration - Base class for database migrations
 * Provides Laravel-like methods for schema operations
 */
abstract class Migration
{
    protected Database $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * Run the migration
     */
    abstract public function up(): void;

    /**
     * Reverse the migration
     */
    abstract public function down(): void;

    /**
     * Create a new table
     */
    protected function create(string $table, callable $callback): void
    {
        Schema::create($table, $callback);
    }

    /**
     * Modify an existing table
     */
    protected function table(string $table, callable $callback): void
    {
        Schema::table($table, $callback);
    }

    /**
     * Drop a table
     */
    protected function drop(string $table): void
    {
        Schema::drop($table);
    }

    /**
     * Drop a table if it exists
     */
    protected function dropIfExists(string $table): void
    {
        Schema::dropIfExists($table);
    }

    /**
     * Check if table exists
     */
    protected function hasTable(string $table): bool
    {
        return Schema::hasTable($table);
    }

    /**
     * Check if column exists
     */
    protected function hasColumn(string $table, string $column): bool
    {
        return Schema::hasColumn($table, $column);
    }
}
