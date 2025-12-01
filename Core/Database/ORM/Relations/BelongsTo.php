<?php

namespace App\Core\Database\ORM\Relations;

use App\Core\Database\Model;
use PDO;

/**
 * BelongsTo Relation
 * Example: Post belongsTo User
 */
class BelongsTo extends Relation
{
    protected string $foreignKey;
    protected string $ownerKey;

    public function __construct(Model $parent, Model $related, string $foreignKey = null, string $ownerKey = 'id')
    {
        parent::__construct($parent, $related);

        // Default foreign key: related_id (e.g., user_id)
        if ($foreignKey === null) {
            $relatedClass = basename(str_replace('\\', '/', get_class($related)));
            $foreignKey = strtolower($relatedClass) . '_id';
        }

        $this->foreignKey = $foreignKey;
        $this->ownerKey = $ownerKey;
    }

    /**
     * Get the results of the relationship.
     */
    public function getResults()
    {
        $foreignValue = $this->parent->{$this->foreignKey};

        if ($foreignValue === null) {
            return null;
        }

        $table = $this->related->getTable();
        $sql = "SELECT * FROM `{$table}` WHERE `{$this->ownerKey}` = ? LIMIT 1";

        $stmt = $this->db->query($sql, [$foreignValue]);
        $result = $stmt->fetchObject(get_class($this->related));

        return $result ?: null;
    }

    /**
     * Associate the model with the given model.
     */
    public function associate(Model $model): Model
    {
        $this->parent->{$this->foreignKey} = $model->{$this->ownerKey};
        return $this->parent;
    }

    /**
     * Dissociate the model from its parent.
     */
    public function dissociate(): Model
    {
        $this->parent->{$this->foreignKey} = null;
        return $this->parent;
    }

    public function getForeignKey(): string
    {
        return $this->foreignKey;
    }

    public function getOwnerKey(): string
    {
        return $this->ownerKey;
    }

    public function getRelated(): Model
    {
        return $this->related;
    }
}
