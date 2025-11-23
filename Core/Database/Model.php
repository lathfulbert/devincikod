<?php

namespace App\Core\Database;

abstract class Model
{
    protected static string $table;
    protected array $attributes = [];

    public function __construct(array $attributes = [])
    {
        if (!empty($attributes)) {
            $this->attributes = $attributes;
        }
    }

    public static function getTable(): string
    {
        if (isset(static::$table)) {
            return static::$table;
        }
        $class = basename(str_replace('\\', '/', static::class));
        return strtolower($class) . 's';
    }

    public static function all(): array
    {
        $db = Database::getInstance();
        $table = static::getTable();

        // Check if model uses SoftDeletes trait
        $usesSoftDeletes = in_array('App\Core\Database\Traits\SoftDeletes', class_uses(static::class));

        if ($usesSoftDeletes) {
            $stmt = $db->query("SELECT * FROM `{$table}` WHERE `deleted_at` IS NULL");
        } else {
            $stmt = $db->query("SELECT * FROM `{$table}`");
        }

        return $stmt->fetchAll(\PDO::FETCH_CLASS, static::class);
    }

    public static function find(int $id): ?static
    {
        $db = Database::getInstance();
        $table = static::getTable();

        // Check if model uses SoftDeletes trait
        $usesSoftDeletes = in_array('App\Core\Database\Traits\SoftDeletes', class_uses(static::class));

        if ($usesSoftDeletes) {
            $stmt = $db->query("SELECT * FROM `{$table}` WHERE id = ? AND `deleted_at` IS NULL", [$id]);
        } else {
            $stmt = $db->query("SELECT * FROM `{$table}` WHERE id = ?", [$id]);
        }

        $result = $stmt->fetchObject(static::class);
        return $result ?: null;
    }

    public function save(): void
    {
        $db = Database::getInstance();
        $table = static::getTable();

        if (isset($this->attributes['id']) && $this->attributes['id'] !== null) {
            // Update
            $set = [];
            $params = [];
            foreach ($this->attributes as $key => $value) {
                if ($key === 'id') continue;
                $set[] = "`{$key}` = ?";
                $params[] = $value;
            }
            $params[] = $this->attributes['id'];
            $sql = "UPDATE `{$table}` SET " . implode(', ', $set) . " WHERE id = ?";
            $db->query($sql, $params);
        } else {
            // Insert - exclude 'id' field for auto-increment
            $insertData = $this->attributes;
            unset($insertData['id']); // Remove id if it exists

            $keys = array_keys($insertData);
            $placeholders = array_fill(0, count($keys), '?');

            $columnNames = array_map(fn($k) => "`{$k}`", $keys);
            $sql = "INSERT INTO `{$table}` (" . implode(', ', $columnNames) . ") VALUES (" . implode(', ', $placeholders) . ")";
            $db->query($sql, array_values($insertData));
            $this->attributes['id'] = $db->getPdo()->lastInsertId();
        }
    }

    public function delete(): void
    {
        if (isset($this->attributes['id'])) {
            $db = Database::getInstance();
            $table = static::getTable();
            $db->query("DELETE FROM {$table} WHERE id = ?", [$this->attributes['id']]);
        }
    }

    public function __get($key)
    {
        return $this->attributes[$key] ?? null;
    }

    public function __set($key, $value)
    {
        $this->attributes[$key] = $value;
    }

    public function belongsToMany(string $related, string $table = null, string $foreignPivotKey = null, string $relatedPivotKey = null): ORM\Relations\BelongsToMany
    {
        $instance = new $related();

        if ($table === null) {
            // Alphabetical order of table names
            $tables = [static::getTable(), $instance->getTable()];
            sort($tables);
            $table = implode('_', $tables);
        }

        if ($foreignPivotKey === null) {
            $foreignPivotKey = strtolower(basename(str_replace('\\', '/', static::class))) . '_id';
        }

        if ($relatedPivotKey === null) {
            $relatedPivotKey = strtolower(basename(str_replace('\\', '/', $related))) . '_id';
        }

        return new ORM\Relations\BelongsToMany($this, $instance, $table, $foreignPivotKey, $relatedPivotKey);
    }

    /**
     * Begin a new query on the model.
     *
     * @return QueryBuilder
     */
    public static function query()
    {
        return new QueryBuilder(static::class);
    }

    /**
     * Handle dynamic static method calls into the method.
     *
     * @param  string  $method
     * @param  array  $parameters
     * @return mixed
     */
    public static function __callStatic($method, $parameters)
    {
        return (new QueryBuilder(static::class))->$method(...$parameters);
    }
}
