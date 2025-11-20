<?php

namespace App\Core\Database;

/**
 * Schema - Facade for database schema operations
 * Provides Laravel-like schema building methods
 */
class Schema
{
    /**
     * Create a new table
     */
    public static function create(string $table, callable $callback): void
    {
        $blueprint = new Blueprint($table);
        $callback($blueprint);
        
        $sql = $blueprint->toSql();
       Database::getInstance()->query($sql);
        
        // Handle foreign keys separately (after table creation)
        self::addForeignKeys($blueprint);
        
        // Handle indexes that need separate statements
        self::addIndexes($blueprint);
    }

    /**
     * Modify an existing table
     */
    public static function table(string $table, callable $callback): void
    {
        // For now, throw exception - ALTER TABLE support can be added later
        throw new \Exception("Schema::table() not yet implemented. Use raw SQL for alterations.");
    }

    /**
     * Drop a table
     */
    public static function drop(string $table): void
    {
        $sql = "DROP TABLE `{$table}`";
        Database::getInstance()->query($sql);
    }

    /**
     * Drop a table if it exists
     */
    public static function dropIfExists(string $table): void
    {
        $sql = "DROP TABLE IF EXISTS `{$table}`";
        Database::getInstance()->query($sql);
    }

    /**
     * Check if a table exists
     */
    public static function hasTable(string $table): bool
    {
        $db = Database::getInstance();
        $driver = $db->getDriver();
        
        if ($driver === 'sqlite') {
            $sql = "SELECT name FROM sqlite_master WHERE type='table' AND name=?";
        } else {
            $sql = "SHOW TABLES LIKE ?";
        }
        
        $stmt = $db->query($sql, [$table]);
        return $stmt->rowCount() > 0;
    }

    /**
     * Check if a column exists in a table
     */
    public static function hasColumn(string $table, string $column): bool
    {
        $db = Database::getInstance();
        $driver = $db->getDriver();
        
        if ($driver === 'sqlite') {
            $sql = "PRAGMA table_info(`{$table}`)";
            $stmt = $db->query($sql);
            $columns = $stmt->fetchAll(\PDO::FETCH_ASSOC);
            foreach ($columns as $col) {
                if ($col['name'] === $column) {
                    return true;
                }
            }
            return false;
        } else {
            $sql = "SHOW COLUMNS FROM `{$table}` LIKE ?";
            $stmt = $db->query($sql, [$column]);
            return $stmt->rowCount() > 0;
        }
    }

    /**
     * Add foreign key constraints to a table
     */
    protected static function addForeignKeys(Blueprint $blueprint): void
    {
        $db = Database::getInstance();
        $driver = $db->getDriver();
        
        // SQLite doesn't support ALTER TABLE ADD CONSTRAINT for foreign keys
        // They must be defined inline during CREATE TABLE
        if ($driver === 'sqlite') {
            return;
        }
        
        foreach ($blueprint->getColumns() as $column) {
            $foreign = $column->getForeignKey();
            if ($foreign) {
                $sql = "ALTER TABLE `{$blueprint->getTable()}` ADD {$foreign->toSql()}";
                $db->query($sql);
            }
        }
    }

    /**
     * Add indexes that need separate statements
     */
    protected static function addIndexes(Blueprint $blueprint): void
    {
        $db = Database::getInstance();
        
        foreach ($blueprint->getColumns() as $column) {
            if ($column->needsIndex()) {
                $table = $blueprint->getTable();
                $name = $column->getName();
                $indexName = "{$table}_{$name}_index";
                
                $sql = "CREATE INDEX `{$indexName}` ON `{$table}` (`{$name}`)";
                $db->query($sql);
            }
        }
    }
}
