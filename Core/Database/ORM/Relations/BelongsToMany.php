<?php

namespace App\Core\Database\ORM\Relations;

use App\Core\Database\Model;

class BelongsToMany extends Relation
{
    protected string $table;
    protected string $foreignPivotKey;
    protected string $relatedPivotKey;

    public function __construct(Model $parent, Model $related, string $table, string $foreignPivotKey, string $relatedPivotKey)
    {
        parent::__construct($parent, $related);
        $this->table = $table;
        $this->foreignPivotKey = $foreignPivotKey;
        $this->relatedPivotKey = $relatedPivotKey;
    }

    public function getResults(): array
    {
        $relatedTable = $this->related->getTable();
        $pivotTable = $this->table;
        $foreignKey = $this->foreignPivotKey;
        $relatedKey = $this->relatedPivotKey;
        $parentId = $this->parent->id;

        $sql = "SELECT r.* FROM {$relatedTable} r 
                JOIN {$pivotTable} p ON r.id = p.{$relatedKey} 
                WHERE p.{$foreignKey} = ?";

        $stmt = $this->db->query($sql, [$parentId]);
        return $stmt->fetchAll(\PDO::FETCH_CLASS, get_class($this->related));
    }

    public function attach(mixed $ids): void
    {
        $ids = is_array($ids) ? $ids : [$ids];
        $parentId = $this->parent->id;

        foreach ($ids as $id) {
            $this->db->query(
                "INSERT INTO {$this->table} ({$this->foreignPivotKey}, {$this->relatedPivotKey}) VALUES (?, ?)",
                [$parentId, $id]
            );
        }
    }

    public function detach(mixed $ids = null): void
    {
        $parentId = $this->parent->id;

        if ($ids === null) {
            $this->db->query("DELETE FROM {$this->table} WHERE {$this->foreignPivotKey} = ?", [$parentId]);
            return;
        }

        $ids = is_array($ids) ? $ids : [$ids];
        $placeholders = implode(',', array_fill(0, count($ids), '?'));
        $params = array_merge([$parentId], $ids);

        $this->db->query(
            "DELETE FROM {$this->table} WHERE {$this->foreignPivotKey} = ? AND {$this->relatedPivotKey} IN ({$placeholders})",
            $params
        );
    }

    public function sync(array $ids): void
    {
        $this->detach();
        $this->attach($ids);
    }

    /**
     * Get the pivot table name.
     */
    public function getPivotTable(): string
    {
        return $this->table;
    }

    /**
     * Get the foreign pivot key.
     */
    public function getForeignPivotKey(): string
    {
        return $this->foreignPivotKey;
    }

    /**
     * Get the related pivot key.
     */
    public function getRelatedPivotKey(): string
    {
        return $this->relatedPivotKey;
    }

    /**
     * Get the related model instance.
     */
    public function getRelated(): Model
    {
        return $this->related;
    }
}
