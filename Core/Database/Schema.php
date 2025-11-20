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

    public function id(): Column
    {
        return $this->addColumn('id', 'INT AUTO_INCREMENT PRIMARY KEY');
    }

    public function string(string $column, int $length = 255): Column
    {
        return $this->addColumn($column, "VARCHAR({$length})");
    }

    public function text(string $column): Column
    {
        return $this->addColumn($column, 'TEXT');
    }

    public function timestamps(): void
    {
        $this->addColumn('created_at', 'TIMESTAMP DEFAULT CURRENT_TIMESTAMP');
        $this->addColumn('updated_at', 'TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP');
    }

    protected function addColumn(string $name, string $type): Column
    {
        $column = new Column($name, $type);
        $this->columns[] = $column;
        return $column;
    }

    public function toSql(): string
    {
        $cols = array_map(fn($col) => $col->toSql(), $this->columns);
        $colsSql = implode(', ', $cols);
        return "CREATE TABLE {$this->table} ({$colsSql})";
    }
}

class Column
{
    protected string $name;
    protected string $type;
    protected ?string $default = null;
    protected bool $nullable = false;

    public function __construct(string $name, string $type)
    {
        $this->name = $name;
        $this->type = $type;
    }

    public function default(string|int $value): self
    {
        $this->default = (string)$value;
        return $this;
    }

    public function nullable(): self
    {
        $this->nullable = true;
        return $this;
    }

    public function unique(): self
    {
        // Unique constraint logic would go here (e.g. adding UNIQUE index)
        // For MVP, we might just mark it or append UNIQUE to definition if supported inline
        // SQLite supports inline UNIQUE. MySQL supports it too.
        $this->unique = true;
        return $this;
    }

    public function toSql(): string
    {
        $sql = "{$this->name} {$this->type}";
        
        if (!$this->nullable && strpos($this->type, 'PRIMARY KEY') === false) {
            $sql .= " NOT NULL";
        }

        if ($this->default !== null) {
            $sql .= " DEFAULT '{$this->default}'";
        }

        if (isset($this->unique) && $this->unique) {
            $sql .= " UNIQUE";
        }

        return $sql;
    }
}
