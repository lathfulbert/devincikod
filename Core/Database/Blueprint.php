<?php

namespace App\Core\Database;

/**
 * Blueprint - Fluent column builder for database migrations
 * Supports Laravel-like syntax for defining table columns
 */
class Blueprint
{
    protected string $table;
    protected array $columns = [];
    protected array $indexes = [];
    protected array $foreigns = [];
    protected Database $db;

    public function __construct(string $table)
    {
        $this->table = $table;
        $this->db = Database::getInstance();
    }

    // ==================== ID & PRIMARY KEYS ====================
    
    /**
     * Create BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY column
     */
    public function id(string $column = 'id'): Column
    {
        $driver = $this->db->getDriver();
        if ($driver === 'sqlite') {
            return $this->addColumn($column, 'INTEGER', true);
        }
        return $this->addColumn($column, 'BIGINT UNSIGNED', true);
    }

    public function increments(string $column): Column
    {
        $driver = $this->db->getDriver();
        if ($driver === 'sqlite') {
            return $this->addColumn($column, 'INTEGER', true);
        }
        return $this->addColumn($column, 'INT UNSIGNED', true)->autoIncrement();
    }

    public function bigIncrements(string $column): Column
    {
        return $this->id($column);
    }

    // ==================== TEXT FIELDS ====================
    
    public function string(string $column, int $length = 255): Column
    {
        return $this->addColumn($column, "VARCHAR($length)");
    }

    public function email(string $column = 'email'): Column
    {
        return $this->string($column, 255);
    }

    public function password(string $column = 'password'): Column
    {
        return $this->string($column, 255);
    }

    public function url(string $column): Column
    {
        return $this->string($column, 2048);
    }

    public function text(string $column): Column
    {
        return $this->addColumn($column, 'TEXT');
    }

    public function longText(string $column): Column
    {
        return $this->addColumn($column, 'LONGTEXT');
    }

    public function slug(string $column = 'slug'): Column
    {
        return $this->string($column, 255);
    }

    // ==================== NUMERIC FIELDS ====================
    
    public function tinyInteger(string $column): Column
    {
        return $this->addColumn($column, 'TINYINT');
    }

    public function smallInteger(string $column): Column
    {
        return $this->addColumn($column, 'SMALLINT');
    }

    public function integer(string $column): Column
    {
        return $this->addColumn($column, 'INT');
    }

    public function bigInteger(string $column): Column
    {
        return $this->addColumn($column, 'BIGINT');
    }

    // Unsigned variants
    public function unsignedTinyInteger(string $column): Column
    {
        return $this->addColumn($column, 'TINYINT UNSIGNED');
    }

    public function unsignedSmallInteger(string $column): Column
    {
        return $this->addColumn($column, 'SMALLINT UNSIGNED');
    }

    public function unsignedInteger(string $column): Column
    {
        return $this->addColumn($column, 'INT UNSIGNED');
    }

    public function unsignedBigInteger(string $column): Column
    {
        return $this->addColumn($column, 'BIGINT UNSIGNED');
    }

    // Decimal and Float
    public function decimal(string $column, int $total = 8, int $places = 2): Column
    {
        return $this->addColumn($column, "DECIMAL($total,$places)");
    }

    public function unsignedDecimal(string $column, int $total = 8, int $places = 2): Column
    {
        return $this->addColumn($column, "DECIMAL($total,$places) UNSIGNED");
    }

    public function float(string $column, int $total = 8, int $places = 2): Column
    {
        return $this->addColumn($column, "FLOAT($total,$places)");
    }

    public function double(string $column, int $total = 8, int $places = 2): Column
    {
        return $this->addColumn($column, "DOUBLE($total,$places)");
    }

    public function unsignedFloat(string $column, int $total = 8, int $places = 2): Column
    {
        return $this->addColumn($column, "FLOAT($total,$places) UNSIGNED");
    }

    public function unsignedDouble(string $column, int $total = 8, int $places = 2): Column
    {
        return $this->addColumn($column, "DOUBLE($total,$places) UNSIGNED");
    }

    // ==================== DATE & TIME ====================
    
    public function date(string $column): Column
    {
        return $this->addColumn($column, 'DATE');
    }

    public function datetime(string $column): Column
    {
        return $this->addColumn($column, 'DATETIME');
    }

    public function timestamp(string $column): Column
    {
        return $this->addColumn($column, 'TIMESTAMP');
    }

    public function time(string $column): Column
    {
        return $this->addColumn($column, 'TIME');
    }

    public function year(string $column): Column
    {
        return $this->addColumn($column, 'YEAR');
    }

    /**
     * Add created_at and updated_at TIMESTAMP columns
     */
    public function timestamps(): void
    {
        $driver = $this->db->getDriver();
        
        if ($driver === 'sqlite') {
            $this->addColumn('created_at', 'TIMESTAMP')->useCurrent();
            $this->addColumn('updated_at', 'TIMESTAMP')->useCurrent();
        } else {
            $this->addColumn('created_at', 'TIMESTAMP')->useCurrent();
            $this->addColumn('updated_at', 'TIMESTAMP')->useCurrent()->onUpdate();
        }
    }

    public function timestampsWithDefault(): void
    {
        $this->timestamps();
    }

    public function timestampsWithoutTimezones(): void
    {
        $this->addColumn('created_at', 'DATETIME')->nullable();
        $this->addColumn('updated_at', 'DATETIME')->nullable();
    }

    // ==================== BOOLEAN & ENUM ====================
    
    public function boolean(string $column): Column
    {
        $driver = $this->db->getDriver();
        if ($driver === 'pgsql') {
            return $this->addColumn($column, 'BOOLEAN');
        }
        return $this->addColumn($column, 'TINYINT(1)');
    }

    public function enum(string $column, array $allowed): Column
    {
        $values = implode("','", $allowed);
        return $this->addColumn($column, "ENUM('$values')");
    }

    // ==================== BINARY & JSON ====================
    
    public function binary(string $column): Column
    {
        return $this->addColumn($column, 'BLOB');
    }

    public function longBinary(string $column): Column
    {
        return $this->addColumn($column, 'LONGBLOB');
    }

    public function json(string $column): Column
    {
        return $this->addColumn($column, 'JSON');
    }

    public function jsonb(string $column): Column
    {
        $driver = $this->db->getDriver();
        return $this->addColumn($column, $driver === 'pgsql' ? 'JSONB' : 'JSON');
    }

    // ==================== SPECIALIZED ====================
    
    public function uuid(string $column = 'id'): Column
    {
        return $this->addColumn($column, 'CHAR(36)');
    }

    public function ipAddress(string $column): Column
    {
        return $this->addColumn($column, 'VARCHAR(45)');
    }

    public function macAddress(string $column): Column
    {
        return $this->addColumn($column, 'VARCHAR(17)');
    }

    public function foreignId(string $column): Column
    {
        return $this->unsignedBigInteger($column);
    }

    // ==================== INDEXES & CONSTRAINTS ====================
    
    /**
     * Add a multi-column index
     */
    public function multiIndex(array $columns, string $name = null): void
    {
        $name = $name ?? $this->table . '_' . implode('_', $columns) . '_index';
        $this->indexes[] = [
            'type' => 'INDEX',
            'name' => $name,
            'columns' => $columns
        ];
    }

    /**
     * Add a multi-column unique index
     */
    public function multiUnique(array $columns, string $name = null): void
    {
        $name = $name ?? $this->table . '_' . implode('_', $columns) . '_unique';
        $this->indexes[] = [
            'type' => 'UNIQUE',
            'name' => $name,
            'columns' => $columns
        ];
    }

    // ==================== HELPER METHODS ====================
    
    protected function addColumn(string $name, string $type, bool $isPrimary = false): Column
    {
        $column = new Column($name, $type, $this->db->getDriver());
        if ($isPrimary) {
            $column->primary();
        }
        $this->columns[] = $column;
        return $column;
    }

    public function getColumns(): array
    {
        return $this->columns;
    }

    public function getIndexes(): array
    {
        return $this->indexes;
    }

    public function getForeigns(): array
    {
        return $this->foreigns;
    }

    public function addForeign(array $foreign): void
    {
        $this->foreigns[] = $foreign;
    }

    public function getTable(): string
    {
        return $this->table;
    }

    /**
     * Generate CREATE TABLE SQL
     */
    public function toSql(): string
    {
        $driver = $this->db->getDriver();
        $cols = array_map(fn($col) => $col->toSql(), $this->columns);
        
        // Add indexes
        foreach ($this->indexes as $index) {
            $columns = implode(', ', $index['columns']);
            if ($index['type'] === 'UNIQUE') {
                $cols[] = "UNIQUE KEY {$index['name']} ($columns)";
            } else {
                $cols[] = "KEY {$index['name']} ($columns)";
            }
        }
        
        // Add foreign keys
        foreach ($this->foreigns as $foreign) {
            $cols[] = $foreign['sql'];
        }
        
        $colsSql = implode(', ', $cols);
        
        if ($driver === 'sqlite') {
            return "CREATE TABLE {$this->table} ($colsSql)";
        }
        
        return "CREATE TABLE {$this->table} ($colsSql) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
    }
}
