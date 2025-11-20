<?php

namespace App\Core\Database;

class Schema
{
    public static function create(string $table, callable $callback): void
    {
        $blueprint = new Blueprint($table);
        $callback($blueprint);
        
        $sql = $blueprint->toSql();
        Database::getInstance()->query($sql);
    }

    public static function dropIfExists(string $table): void
    {
        $sql = "DROP TABLE IF EXISTS {$table}";
        Database::getInstance()->query($sql);
    }
}

class Blueprint
{
    protected string $table;
    protected array $columns = [];

    public function __construct(string $table)
    {
        $this->table = $table;
    }

    public function id(): void
    {
        $this->columns[] = "id INT AUTO_INCREMENT PRIMARY KEY";
    }

    public function string(string $column, int $length = 255): void
    {
        $this->columns[] = "{$column} VARCHAR({$length})";
    }

    public function text(string $column): void
    {
        $this->columns[] = "{$column} TEXT";
    }

    public function timestamps(): void
    {
        $this->columns[] = "created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP";
        $this->columns[] = "updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP";
    }

    public function toSql(): string
    {
        $cols = implode(', ', $this->columns);
        return "CREATE TABLE {$this->table} ({$cols})";
    }
}
