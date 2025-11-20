<?php

namespace App\Core\Database;

/**
 * ForeignKeyDefinition - Represents a foreign key constraint
 */
class ForeignKeyDefinition
{
    protected string $column;
    protected string $references;
    protected string $on;
    protected string $onDelete = 'RESTRICT';
    protected string $onUpdate = 'RESTRICT';

    public function __construct(string $column, string $table, string $references = 'id')
    {
        $this->column = $column;
        $this->on = $table;
        $this->references = $references;
    }

    /**
     * Set ON DELETE action
     */
    public function onDelete(string $action): self
    {
        $this->onDelete = strtoupper($action);
        return $this;
    }

    /**
     * Set ON UPDATE action
     */
    public function onUpdate(string $action): self
    {
        $this->onUpdate = strtoupper($action);
        return $this;
    }

    /**
     * Shortcut for CASCADE on both delete and update
     */
    public function cascadeOnDelete(): self
    {
        return $this->onDelete('CASCADE');
    }

    /**
     * Generate foreign key SQL
     */
    public function toSql(): string
    {
        return "FOREIGN KEY (`{$this->column}`) REFERENCES `{$this->on}`(`{$this->references}`) " .
               "ON DELETE {$this->onDelete} ON UPDATE {$this->onUpdate}";
    }
}
