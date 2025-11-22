<?php

namespace App\Core\Database;

class QueryBuilder
{
    protected $model;
    protected $table;
    protected $select = '*';
    protected $wheres = [];
    protected $bindings = [];
    protected $orderBy = [];
    protected $limit;
    protected $offset;

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

        $this->wheres[] = "{$column} {$operator} ?";
        $this->bindings[] = $value;
        return $this;
    }

    public function orderBy($column, $direction = 'ASC')
    {
        $this->orderBy[] = "{$column} {$direction}";
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
        return $stmt->fetchAll(\PDO::FETCH_CLASS, $this->model);
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
        $sql = "SELECT {$this->select} FROM {$this->table}";

        if (!empty($this->wheres)) {
            $sql .= " WHERE " . implode(' AND ', $this->wheres);
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
}
