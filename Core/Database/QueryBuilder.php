<?php

namespace App\Core\Database;

class QueryBuilder
{
    protected $model;
    protected $table;
    protected $select = '*';
    protected $wheres = [];
    protected $whereGroups = []; // For grouping OR conditions
    protected $bindings = [];
    protected $orderBy = [];
    protected $limit;
    protected $offset;
    protected $with = []; // Relations to eager load

    public function __construct(string $modelClass)
    {
        $this->model = $modelClass;
        $this->table = $modelClass::getTable();
    }

    public function select($columns = ['*'])
    {
        $this->select = is_array($columns) ? implode(', ', $columns) : $columns;
        return $this;
    }

    public function where($column, $operator = null, $value = null)
    {
        if ($value === null) {
            $value = $operator;
            $operator = '=';
        }

        $this->wheres[] = [
            'type' => 'and',
            'column' => $column,
            'operator' => $operator,
            'value' => $value
        ];
        $this->bindings[] = $value;
        return $this;
    }

    public function orWhere($column, $operator = null, $value = null)
    {
        if ($value === null) {
            $value = $operator;
            $operator = '=';
        }

        $this->wheres[] = [
            'type' => 'or',
            'column' => $column,
            'operator' => $operator,
            'value' => $value
        ];
        $this->bindings[] = $value;
        return $this;
    }

    /**
     * Add a grouped OR WHERE clause.
     * Example: ->whereGroup(function($q) { $q->where('a', 1)->orWhere('b', 2); })
     */
    public function whereGroup(callable $callback)
    {
        $subQuery = new static($this->model);
        $callback($subQuery);

        if (!empty($subQuery->wheres)) {
            $this->whereGroups[] = $subQuery->wheres;
            $this->bindings = array_merge($this->bindings, $subQuery->bindings);
        }

        return $this;
    }

    /**
     * Specify relations to eager load.
     *
     * @param string|array $relations
     * @return $this
     */
    public function with($relations)
    {
        if (is_string($relations)) {
            $relations = [$relations];
        }

        $this->with = array_merge($this->with, $relations);
        return $this;
    }

    public function orderBy($column, $direction = 'ASC')
    {
        $this->orderBy[] = "`{$column}` {$direction}";
        return $this;
    }

    public function limit($limit)
    {
        $this->limit = $limit;
        return $this;
    }

    public function offset($offset)
    {
        $this->offset = $offset;
        return $this;
    }

    public function get()
    {
        $sql = $this->toSql();
        $db = Database::getInstance();
        $stmt = $db->query($sql, $this->bindings);
        $results = $stmt->fetchAll(\PDO::FETCH_CLASS, $this->model);

        // Load relations if specified
        if (!empty($this->with) && !empty($results)) {
            $this->loadRelations($results);
        }

        return $results;
    }

    /**
     * Load relations for a collection of models.
     */
    protected function loadRelations(array $models): void
    {
        foreach ($this->with as $relation) {
            $this->loadRelationForModels($models, $relation);
        }
    }

    /**
     * Load a specific relation for models.
     */
    protected function loadRelationForModels(array $models, string $relation): void
    {
        // Get the first model to determine the relation type
        $firstModel = $models[0];

        // Check if the relation method exists
        if (!method_exists($firstModel, $relation)) {
            return;
        }

        // Call the relation method
        $relationInstance = $firstModel->$relation();

        // Only support BelongsToMany for now
        if ($relationInstance instanceof \App\Core\Database\ORM\Relations\BelongsToMany) {
            $this->loadBelongsToManyRelation($models, $relation, $relationInstance);
        }
    }

    /**
     * Load BelongsToMany relation for multiple models.
     */
    protected function loadBelongsToManyRelation(array $models, string $relationName, $relationInstance): void
    {
        // Get all IDs
        $ids = array_map(fn($model) => $model->id, $models);

        // Get pivot table info from the relation
        $pivotTable = $relationInstance->getPivotTable();
        $foreignKey = $relationInstance->getForeignPivotKey();
        $relatedKey = $relationInstance->getRelatedPivotKey();
        $relatedTable = $relationInstance->getRelated()->getTable();

        // Query to get all related records
        $db = Database::getInstance();
        $placeholders = implode(',', array_fill(0, count($ids), '?'));

        $sql = "SELECT {$relatedTable}.*, {$pivotTable}.{$foreignKey} as pivot_parent_id 
                FROM {$relatedTable}
                INNER JOIN {$pivotTable} ON {$relatedTable}.id = {$pivotTable}.{$relatedKey}
                WHERE {$pivotTable}.{$foreignKey} IN ({$placeholders})";

        $stmt = $db->query($sql, $ids);
        $relatedRecords = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        // Group by parent ID
        $grouped = [];
        foreach ($relatedRecords as $record) {
            $parentId = $record['pivot_parent_id'];
            unset($record['pivot_parent_id']);

            if (!isset($grouped[$parentId])) {
                $grouped[$parentId] = [];
            }

            // Create model instance
            $relatedModel = new ($relationInstance->getRelated())($record);
            $grouped[$parentId][] = $relatedModel;
        }

        // Assign to models
        foreach ($models as $model) {
            $model->$relationName = $grouped[$model->id] ?? [];
        }
    }

    public function first()
    {
        $this->limit(1);
        $results = $this->get();
        return $results[0] ?? null;
    }

    public function count()
    {
        $originalSelect = $this->select;
        $this->select = 'COUNT(*) as count';

        $sql = $this->toSql();
        $db = Database::getInstance();
        $stmt = $db->query($sql, $this->bindings);
        $result = $stmt->fetch(\PDO::FETCH_OBJ);

        $this->select = $originalSelect; // Restore select

        return $result->count;
    }

    /**
     * Paginate the given query.
     *
     * @param int $perPage
     * @param int|null $currentPage
     * @return \App\Core\Database\Pagination\LengthAwarePaginator
     */
    public function paginate(int $perPage = 15, ?int $currentPage = null)
    {
        // Get total count
        $total = $this->count();

        // Get current page
        $currentPage = $currentPage ?: ($_GET['page'] ?? 1);
        $currentPage = max(1, (int) $currentPage);

        // Calculate offset
        $offset = ($currentPage - 1) * $perPage;

        // Clone the current query and apply limit/offset
        $this->limit($perPage)->offset($offset);

        // Get the items
        $items = $this->get();

        // Return paginator
        return new \App\Core\Database\Pagination\LengthAwarePaginator(
            $items,
            $total,
            $perPage,
            $currentPage
        );
    }

    /**
     * Create a DataTables builder for server-side processing.
     *
     * @param array $columns Searchable/orderable column names
     * @return \App\Core\Database\Pagination\DatatablesBuilder
     */
    public function datatables(array $columns = [])
    {
        $builder = new \App\Core\Database\Pagination\DatatablesBuilder($this);

        if (!empty($columns)) {
            $builder->setColumns($columns);
        }

        return $builder;
    }

    public function toSql()
    {
        $sql = "SELECT {$this->select} FROM `{$this->table}`";

        // Build WHERE clause
        $whereClauses = [];

        // Process regular wheres
        if (!empty($this->wheres)) {
            $whereClauses[] = $this->buildWhereClause($this->wheres);
        }

        // Process where groups (parenthesized OR groups)
        foreach ($this->whereGroups as $group) {
            $whereClauses[] = '(' . $this->buildWhereClause($group) . ')';
        }

        if (!empty($whereClauses)) {
            $sql .= " WHERE " . implode(' AND ', $whereClauses);
        }

        if (!empty($this->orderBy)) {
            $sql .= " ORDER BY " . implode(', ', $this->orderBy);
        }

        if (isset($this->limit)) {
            $sql .= " LIMIT {$this->limit}";
        }

        if (isset($this->offset)) {
            $sql .= " OFFSET {$this->offset}";
        }

        return $sql;
    }

    /**
     * Build WHERE clause from array of conditions.
     */
    protected function buildWhereClause(array $conditions): string
    {
        $clauses = [];

        foreach ($conditions as $index => $condition) {
            $clause = "`{$condition['column']}` {$condition['operator']} ?";

            if ($index === 0) {
                // First condition, no conjunction needed
                $clauses[] = $clause;
            } else {
                // Add AND or OR based on type
                $conjunction = strtoupper($condition['type']);
                $clauses[] = "{$conjunction} {$clause}";
            }
        }

        return implode(' ', $clauses);
    }

    /**
     * Update records
     */
    public function update(array $data): bool
    {
        if (empty($data)) {
            return false;
        }

        $sets = [];
        $values = [];

        foreach ($data as $column => $value) {
            $sets[] = "`{$column}` = ?";
            $values[] = $value;
        }

        $sql = "UPDATE `{$this->table}` SET " . implode(', ', $sets);

        if (!empty($this->wheres)) {
            $sql .= " WHERE " . $this->buildWhereClause($this->wheres);
            $values = array_merge($values, $this->bindings);
        }

        $db = Database::getInstance();
        $db->query($sql, $values);

        return true;
    }

    /**
     * Delete records
     */
    public function delete(): bool
    {
        $sql = "DELETE FROM `{$this->table}`";

        if (!empty($this->wheres)) {
            $sql .= " WHERE " . $this->buildWhereClause($this->wheres);
        }

        $db = Database::getInstance();
        $db->query($sql, $this->bindings);

        return true;
    }

    /**
     * Create a new record
     */
    public function create(array $data): mixed
    {
        $model = new ($this->model)();

        foreach ($data as $key => $value) {
            $model->$key = $value;
        }

        $model->save();
        return $model;
    }

    /**
     * Check if records exist
     */
    public function exists(): bool
    {
        return $this->count() > 0;
    }
}
