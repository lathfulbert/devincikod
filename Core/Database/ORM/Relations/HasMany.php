<?php

namespace App\Core\Database\ORM\Relations;

use App\Core\Database\Model;
use PDO;

/**
 * HasMany Relation
 * Example: User hasMany Posts
 */
class HasMany extends Relation
{
    protected string $foreignKey;
    protected string $localKey;

    public function __construct(Model $parent, Model $related, string $foreignKey = null, string $localKey = 'id')
    {
        parent::__construct($parent, $related);

        // Default foreign key: parent_id (e.g., user_id)
        if ($foreignKey === null) {
            $parentClass = basename(str_replace('\\', '/', get_class($parent)));
            $foreignKey = strtolower($parentClass) . '_id';
        }

        $this->foreignKey = $foreignKey;
        $this->localKey = $localKey;
    }

    /**
     * Get the results of the relationship.
     */
    public function getResults(): array
    {
        $localValue = $this->parent->{$this->localKey};

        if ($localValue === null) {
            return [];
        }

        $table = $this->related->getTable();
        $sql = "SELECT * FROM `{$table}` WHERE `{$this->foreignKey}` = ?";

        $stmt = $this->db->query($sql, [$localValue]);
        return $stmt->fetchAll(PDO::FETCH_CLASS, get_class($this->related));
    }

    /**
     * Get the count of related models.
     */
    public function count(): int
    {
        $table = $this->related->getTable();
        $sql = "SELECT COUNT(*) as count FROM `{$table}` WHERE `{$this->foreignKey}` = ?";

        $stmt = $this->db->query($sql, [$this->parent->{$this->localKey}]);
        $result = $stmt->fetch(\PDO::FETCH_OBJ);

        return (int) ($result->count ?? 0);
    }

    /**
     * Create a new related model.
     */
    public function create(array $attributes): Model
    {
        $attributes[$this->foreignKey] = $this->parent->{$this->localKey};

        $related = new (get_class($this->related))($attributes);
        $related->save();

        return $related;
    }

    /**
     * Create multiple related models.
     */
    public function createMany(array $records): array
    {
        $created = [];

        foreach ($records as $attributes) {
            $created[] = $this->create($attributes);
        }

        return $created;
    }

    /**
     * Update all related models.
     */
    public function update(array $attributes): bool
    {
        $table = $this->related->getTable();
        $sets = [];
        $values = [];

        foreach ($attributes as $column => $value) {
            $sets[] = "`{$column}` = ?";
            $values[] = $value;
        }

        $values[] = $this->parent->{$this->localKey};

        $sql = "UPDATE `{$table}` SET " . implode(', ', $sets) . " WHERE `{$this->foreignKey}` = ?";
        $this->db->query($sql, $values);

        return true;
    }

    /**
     * Delete all related models.
     */
    public function delete(): bool
    {
        $table = $this->related->getTable();
        $sql = "DELETE FROM `{$table}` WHERE `{$this->foreignKey}` = ?";

        $this->db->query($sql, [$this->parent->{$this->localKey}]);

        return true;
    }

    public function getForeignKey(): string
    {
        return $this->foreignKey;
    }

    public function getLocalKey(): string
    {
        return $this->localKey;
    }

    public function getRelated(): Model
    {
        return $this->related;
    }
}
