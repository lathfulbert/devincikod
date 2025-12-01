<?php

namespace App\Core\Database\ORM\Relations;

use App\Core\Database\Model;
use PDO;

/**
 * HasOne Relation
 * Example: User hasOne Profile
 */
class HasOne extends Relation
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
    public function getResults()
    {
        $localValue = $this->parent->{$this->localKey};

        if ($localValue === null) {
            return null;
        }

        $table = $this->related->getTable();
        $sql = "SELECT * FROM `{$table}` WHERE `{$this->foreignKey}` = ? LIMIT 1";

        $stmt = $this->db->query($sql, [$localValue]);
        $result = $stmt->fetchObject(get_class($this->related));

        return $result ?: null;
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
     * Update the related model.
     */
    public function update(array $attributes): bool
    {
        $related = $this->getResults();

        if ($related) {
            return $related->update($attributes);
        }

        return false;
    }

    public function getForeignKey(): string
    {
        return $this->foreignKey;
    }

    public function getLocalKey(): string
    {
        return $this->localKey;
    }
}
