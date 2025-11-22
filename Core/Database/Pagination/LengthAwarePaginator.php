<?php

namespace App\Core\Database\Pagination;

use ArrayAccess;
use Countable;
use IteratorAggregate;
use ArrayIterator;
use Traversable;

class LengthAwarePaginator implements ArrayAccess, Countable, IteratorAggregate
{
    protected array $items;
    protected int $total;
    protected int $perPage;
    protected int $currentPage;
    protected string $path;
    protected array $query = [];
    protected string $fragment = '';

    /**
     * Create a new paginator instance.
     */
    public function __construct(
        array $items,
        int $total,
        int $perPage,
        ?int $currentPage = null,
        array $options = []
    ) {
        $this->items = $items;
        $this->total = $total;
        $this->perPage = $perPage;
        $this->currentPage = $this->setCurrentPage($currentPage);
        $this->path = $options['path'] ?? $this->getCurrentPath();
        $this->query = $options['query'] ?? $_GET ?? [];
        $this->fragment = $options['fragment'] ?? '';
    }

    /**
     * Set the current page.
     */
    protected function setCurrentPage(?int $currentPage): int
    {
        $currentPage = $currentPage ?: $this->getCurrentPageFromRequest();
        return $this->isValidPageNumber($currentPage) ? (int) $currentPage : 1;
    }

    /**
     * Get current page from request.
     */
    protected function getCurrentPageFromRequest(): int
    {
        $page = $_GET['page'] ?? 1;
        return is_numeric($page) && $page >= 1 ? (int) $page : 1;
    }

    /**
     * Determine if the given value is a valid page number.
     */
    protected function isValidPageNumber($page): bool
    {
        return $page >= 1 && filter_var($page, FILTER_VALIDATE_INT) !== false;
    }

    /**
     * Get the current path.
     */
    protected function getCurrentPath(): string
    {
        return parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) ?? '/';
    }

    /**
     * Get the paginator items.
     */
    public function items(): array
    {
        return $this->items;
    }

    /**
     * Get the total number of items.
     */
    public function total(): int
    {
        return $this->total;
    }

    /**
     * Get the number of items per page.
     */
    public function perPage(): int
    {
        return $this->perPage;
    }

    /**
     * Get the current page.
     */
    public function currentPage(): int
    {
        return $this->currentPage;
    }

    /**
     * Get the last page.
     */
    public function lastPage(): int
    {
        return max((int) ceil($this->total / $this->perPage), 1);
    }

    /**
     * Determine if there are more items in the data store.
     */
    public function hasMorePages(): bool
    {
        return $this->currentPage() < $this->lastPage();
    }

    /**
     * Determine if there are enough items to split into multiple pages.
     */
    public function hasPages(): bool
    {
        return $this->currentPage() != 1 || $this->hasMorePages();
    }

    /**
     * Determine if the paginator is on the first page.
     */
    public function onFirstPage(): bool
    {
        return $this->currentPage() <= 1;
    }

    /**
     * Get the number of the first item in the slice.
     */
    public function firstItem(): ?int
    {
        return count($this->items) > 0 ? ($this->currentPage - 1) * $this->perPage + 1 : null;
    }

    /**
     * Get the number of the last item in the slice.
     */
    public function lastItem(): ?int
    {
        return count($this->items) > 0 ? $this->firstItem() + count($this->items) - 1 : null;
    }

    /**
     * Get the URL for a given page number.
     */
    public function url(int $page): string
    {
        if ($page <= 0) {
            $page = 1;
        }

        $parameters = $this->query;
        $parameters['page'] = $page;

        $queryString = http_build_query($parameters);
        $url = $this->path . ($queryString ? '?' . $queryString : '');

        return $this->fragment ? $url . '#' . $this->fragment : $url;
    }

    /**
     * Get the URL for the previous page.
     */
    public function previousPageUrl(): ?string
    {
        if ($this->currentPage() > 1) {
            return $this->url($this->currentPage() - 1);
        }

        return null;
    }

    /**
     * Get the URL for the next page.
     */
    public function nextPageUrl(): ?string
    {
        if ($this->hasMorePages()) {
            return $this->url($this->currentPage() + 1);
        }

        return null;
    }

    /**
     * Render the pagination links.
     */
    public function links(string $view = 'pagination.default'): string
    {
        if (!$this->hasPages()) {
            return '';
        }

        ob_start();
        $paginator = $this;
        $viewPath = dirname(dirname(dirname(__DIR__))) . '/templates/components/' . str_replace('.', '/', $view) . '.php';

        if (file_exists($viewPath)) {
            include $viewPath;
        }

        return ob_get_clean();
    }

    /**
     * Get the instance as an array.
     */
    public function toArray(): array
    {
        return [
            'current_page' => $this->currentPage(),
            'data' => $this->items,
            'first_page_url' => $this->url(1),
            'from' => $this->firstItem(),
            'last_page' => $this->lastPage(),
            'last_page_url' => $this->url($this->lastPage()),
            'next_page_url' => $this->nextPageUrl(),
            'path' => $this->path,
            'per_page' => $this->perPage(),
            'prev_page_url' => $this->previousPageUrl(),
            'to' => $this->lastItem(),
            'total' => $this->total(),
        ];
    }

    /**
     * Convert to JSON.
     */
    public function toJson(int $options = 0): string
    {
        return json_encode($this->toArray(), $options);
    }

    /**
     * Determine if the given item exists.
     */
    public function offsetExists($key): bool
    {
        return array_key_exists($key, $this->items);
    }

    /**
     * Get the item at the given offset.
     */
    public function offsetGet($key): mixed
    {
        return $this->items[$key];
    }

    /**
     * Set the item at the given offset.
     */
    public function offsetSet($key, $value): void
    {
        $this->items[$key] = $value;
    }

    /**
     * Unset the item at the given offset.
     */
    public function offsetUnset($key): void
    {
        unset($this->items[$key]);
    }

    /**
     * Get the number of items for the current page.
     */
    public function count(): int
    {
        return count($this->items);
    }

    /**
     * Get an iterator for the items.
     */
    public function getIterator(): Traversable
    {
        return new ArrayIterator($this->items);
    }

    /**
     * Get the paginator as JSON.
     */
    public function __toString(): string
    {
        return $this->toJson();
    }
}
