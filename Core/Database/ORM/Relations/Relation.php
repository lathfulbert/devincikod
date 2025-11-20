<?php

namespace App\Core\Database\ORM\Relations;

use App\Core\Database\Model;
use App\Core\Database\Database;

abstract class Relation
{
    protected Model $parent;
    protected Model $related;
    protected Database $db;

    public function __construct(Model $parent, Model $related)
    {
        $this->parent = $parent;
        $this->related = $related;
        $this->db = Database::getInstance();
    }

    abstract public function getResults();
}
