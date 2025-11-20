<?php

namespace App\Core\Database;

/**
 * Column - Represents a database column with modifiers
 * Supports Laravel-like fluent syntax for column definition
 */
class Column
{
    protected string $name;
    protected string $type;
    protected string $driver;
    protected ?string $default = null;
    protected bool $nullable = false;
    protected bool $unique = false;
    protected bool $index = false;
    protected bool $primary = false;
    protected bool $autoIncrement = false;
    protected bool $useCurrent = false;
    protected bool $onUpdate = false;
    protected ?array $foreignKey = null;

    public function __construct(string $name, string $type, string $driver = 'mysql')
    {
        $this->name = $name;
        $this->type = $type;
        $this->driver = $driver;
    }

    // ==================== MODIFIERS ====================
    
    /**
     * Make column nullable
     */
    public function nullable(): self
    {
        $this->nullable = true;
        return $this;
    }

    /**
     * Set default value
     */
    public function default(mixed $value): self
    {
        if (is_bool($value)) {
            $this->default = $value ? '1' : '0';
        } elseif (is_null($value)) {
            $this->default = 'NULL';
        } elseif (is_numeric($value)) {
            $this->default = (string)$value;
        } else {
            $this->default = "'" . addslashes($value) . "'";
        }
        return $this;
    }

    /**
     * Add UNIQUE constraint
     */
    public function unique(): self
    {
        $this->unique = true;
        return $this;
    }

    /**
     * Add INDEX
     */
    public function index(): self
    {
        $this->index = true;
        return $this;
    }

    /**
     * Mark as PRIMARY KEY
     */
    public function primary(): self
    {
        $this->primary = true;
        return $this;
    }

    /**
     * Add AUTO_INCREMENT
     */
    public function autoIncrement(): self
    {
        $this->autoIncrement = true;
        return $this;
    }

    /**
     * Use CURRENT_TIMESTAMP as default
     */
    public function useCurrent(): self
    {
        $this->useCurrent = true;
        return $this;
    }

    /**
     * Add ON UPDATE CURRENT_TIMESTAMP
     */
    public function onUpdate(): self
    {
        $this->onUpdate = true;
        return $this;
    }

    // ==================== FOREIGN KEYS ====================
    
    /**
     * Create foreign key constraint
     */
    public function constrained(string $table = null, string $column = 'id'): ForeignKeyDefinition
    {
        // Infer table name from column name if not provided
        if ($table === null) {
            // Remove _id suffix and pluralize
            $inferredTable = str_replace('_id', '', $this->name);
            $table = $inferredTable . 's'; // Simple pluralization
        }
        
        $foreign = new ForeignKeyDefinition($this->name, $table, $column);
        $this->foreignKey = $foreign;
        
        return $foreign;
    }

    public function getForeignKey(): ?ForeignKeyDefinition
    {
        return $this->foreignKey;
    }

    // ==================== SQL GENERATION ====================
    
    /**
     * Generate SQL for this column
     */
    public function toSql(): string
    {
        $sql = "`{$this->name}` {$this->type}";
        
        // AUTO_INCREMENT (MySQL/MariaDB)
        if ($this->autoIncrement && $this->driver !== 'sqlite') {
            $sql .= " AUTO_INCREMENT";
        }
        
        // NULL / NOT NULL
        if ($this->nullable) {
            $sql .= " NULL";
        } elseif (!$this->primary && !strpos($this->type, 'PRIMARY KEY')) {
            $sql .= " NOT NULL";
        }
        
        // DEFAULT value
        if ($this->default !== null) {
            $sql .= " DEFAULT {$this->default}";
        } elseif ($this->useCurrent) {
            $sql .= " DEFAULT CURRENT_TIMESTAMP";
        }
        
        // ON UPDATE (for timestamps)
        if ($this->onUpdate && $this->driver !== 'sqlite') {
            $sql .= " ON UPDATE CURRENT_TIMESTAMP";
        }
        
        // UNIQUE constraint
        if ($this->unique) {
            $sql .= " UNIQUE";
        }
        
        // PRIMARY KEY
        if ($this->primary) {
            if ($this->driver === 'sqlite') {
                $sql .= " PRIMARY KEY";
                if ($this->autoIncrement) {
                    $sql .= " AUTOINCREMENT";
                }
            } else {
                $sql .= " PRIMARY KEY";
            }
        }
        
        return $sql;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function needsIndex(): bool
    {
        return $this->index;
    }
}
