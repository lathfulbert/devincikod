<?php

namespace App\Core\Database\Pagination;

use App\Core\Database\QueryBuilder;

class DatatablesBuilder
{
    protected QueryBuilder $query;
    protected array $request;
    protected array $columns = [];

    public function __construct(QueryBuilder $query, array $request = null)
    {
        $this->query = $query;
        $this->request = $request ?? $_GET;
    }

    /**
     * Set searchable/orderable columns.
     */
    public function setColumns(array $columns): self
    {
        $this->columns = $columns;
        return $this;
    }

    /**
     * Process DataTables request and return JSON response.
     */
    public function make(): array
    {
        $draw = (int) ($this->request['draw'] ?? 1);
        $start = (int) ($this->request['start'] ?? 0);
        $length = (int) ($this->request['length'] ?? 10);
        $searchValue = $this->request['search']['value'] ?? '';
        $orderColumnIndex = (int) ($this->request['order'][0]['column'] ?? 0);
        $orderDir = $this->request['order'][0]['dir'] ?? 'asc';

        // Get total count before filtering
        $totalRecords = $this->getTotalRecords();

        // Apply search filter
        if (!empty($searchValue) && !empty($this->columns)) {
            $this->applySearch($searchValue);
        }

        // Get filtered count
        $filteredRecords = $this->getFilteredRecords();

        // Apply ordering
        if (isset($this->columns[$orderColumnIndex])) {
            $orderColumn = $this->columns[$orderColumnIndex];
            $this->query->orderBy($orderColumn, strtoupper($orderDir));
        }

        // Apply pagination
        $this->query->offset($start)->limit($length);

        // Get data
        $data = $this->query->get();

        return [
            'draw' => $draw,
            'recordsTotal' => $totalRecords,
            'recordsFiltered' => $filteredRecords,
            'data' => $data
        ];
    }

    /**
     * Get total records count.
     */
    protected function getTotalRecords(): int
    {
        // Clone query to avoid modifying the original
        $countQuery = clone $this->query;
        return $countQuery->count();
    }

    /**
     * Get filtered records count.
     */
    protected function getFilteredRecords(): int
    {
        $countQuery = clone $this->query;
        return $countQuery->count();
    }

    /**
     * Apply search to the query.
     */
    protected function applySearch(string $searchValue): void
    {
        if (empty($this->columns)) {
            return;
        }

        // Build an OR condition for all searchable columns
        // Note: This is a simplified version. In a real implementation,
        // you'd want to use a more sophisticated approach with query grouping
        $searchColumns = array_filter($this->columns, function ($col) {
            return !empty($col);
        });

        if (!empty($searchColumns)) {
            // For the first column
            $firstColumn = array_shift($searchColumns);
            $this->query->where($firstColumn, 'LIKE', "%{$searchValue}%");

            // This is a limitation - we need to extend QueryBuilder to support OR WHERE
            // For now, we'll just search on the first column
            // TODO: Implement orWhere in QueryBuilder
        }
    }

    /**
     * Return JSON response.
     */
    public function toJson(int $options = 0): string
    {
        return json_encode($this->make(), $options);
    }
}
