<?php

declare(strict_types=1);

namespace App\Core\Database;

use PDO;
use App\Core\Database\QueryBuilder;

abstract class Model
{
    protected static string $table;
    protected static string $primaryKey = 'id';
    protected array $attributes = [];

    public function __construct(array $attributes = [])
    {
        if (!empty($attributes)) {
            $this->attributes = $attributes;
        }
    }

    /**
     * Hook called before creating a new record.
     */
    protected function beforeCreate(): void
    {
        //
    }

    /**
     * Hook called before updating an existing record.
     */
    protected function beforeUpdate(): void
    {
        //
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

        return $stmt->fetchAll(PDO::FETCH_CLASS, static::class);
    }

    public static function find(int $id): ?static
    {
        $db = Database::getInstance();
        $table = static::getTable();
        $pk = static::$primaryKey ?? 'id';

        // Check if model uses SoftDeletes trait
        $usesSoftDeletes = in_array('App\Core\Database\Traits\SoftDeletes', class_uses(static::class));

        if ($usesSoftDeletes) {
            $stmt = $db->query("SELECT * FROM `{$table}` WHERE `{$pk}` = ? AND `deleted_at` IS NULL", [$id]);
        } else {
            $stmt = $db->query("SELECT * FROM `{$table}` WHERE `{$pk}` = ?", [$id]);
        }

        $result = $stmt->fetchObject(static::class);
        return $result ?: null;
    }

    public function save(): void
    {
        $db = Database::getInstance();
        $table = static::getTable();
        $pk = static::$primaryKey ?? 'id';

        $isUpdate = false;
        if (isset($this->attributes[$pk])) {
            if ($pk === 'id') {
                $isUpdate = true;
            } else {
                // Check DB for custom PK
                $check = $db->query("SELECT 1 FROM `{$table}` WHERE `{$pk}` = ?", [$this->attributes[$pk]])->fetch();
                if ($check) {
                    $isUpdate = true;
                }
            }
        }

        if ($isUpdate) {
            // Call beforeUpdate hook
            $this->beforeUpdate();

            // Update
            $sets = [];
            $values = [];
            foreach ($this->attributes as $key => $value) {
                if ($key === $pk) continue;
                $sets[] = "`{$key}` = ?";
                $values[] = $value;
            }
            $values[] = $this->attributes[$pk];

            $sql = "UPDATE `{$table}` SET " . implode(', ', $sets) . " WHERE `{$pk}` = ?";
            $db->query($sql, $values);
        } else {
            // Call beforeCreate hook
            $this->beforeCreate();

            // Insert
            $columns = array_keys($this->attributes);
            $placeholders = array_fill(0, count($columns), '?');

            $sql = "INSERT INTO `{$table}` (`" . implode('`, `', $columns) . "`) VALUES (" . implode(', ', $placeholders) . ")";
            $db->query($sql, array_values($this->attributes));

            if ($pk === 'id' && !isset($this->attributes['id'])) {
                $this->attributes['id'] = $db->getPdo()->lastInsertId();
            }
        }
    }

    public function delete(): void
    {
        $pk = static::$primaryKey ?? 'id';
        if (isset($this->attributes[$pk])) {
            $db = Database::getInstance();
            $table = static::getTable();
            $db->query("DELETE FROM {$table} WHERE `{$pk}` = ?", [$this->attributes[$pk]]);
        }
    }

    /**
     * Update the model attributes
     */
    public function update(array $data): bool
    {
        foreach ($data as $key => $value) {
            $this->attributes[$key] = $value;
        }

        $this->save();
        return true;
    }

    public function __get($key)
    {
        return $this->getAttribute($key);
    }

    public function getAttribute($key)
    {
        if (!array_key_exists($key, $this->attributes)) {
            return null;
        }

        $value = $this->attributes[$key];

        if ($this->hasCast($key)) {
            return $this->castAttribute($key, $value);
        }

        return $value;
    }

    protected function hasCast($key): bool
    {
        return isset($this->casts[$key]);
    }

    protected function castAttribute($key, $value)
    {
        if ($value === null) {
            return null;
        }

        $type = $this->casts[$key];

        switch ($type) {
            case 'int':
            case 'integer':
                return (int) $value;
            case 'real':
            case 'float':
            case 'double':
                return (float) $value;
            case 'string':
                return (string) $value;
            case 'bool':
            case 'boolean':
                return (bool) $value;
            case 'array':
            case 'json':
                return json_decode($value, true);
            case 'object':
                return json_decode($value, false);
            case 'datetime':
            case 'date':
                return $value; // TODO: Return DateTime object
            default:
                return $value;
        }
    }

    public function __set($key, $value)
    {
        $this->attributes[$key] = $value;
    }

    public function __isset($key)
    {
        return isset($this->attributes[$key]);
    }

    /**
     * Define a one-to-one relationship.
     */
    public function hasOne(string $related, string $foreignKey = null, string $localKey = 'id'): \App\Core\Database\ORM\Relations\HasOne
    {
        $instance = new $related();
        return new \App\Core\Database\ORM\Relations\HasOne($this, $instance, $foreignKey, $localKey);
    }

    /**
     * Define a one-to-many relationship.
     */
    public function hasMany(string $related, string $foreignKey = null, string $localKey = 'id'): \App\Core\Database\ORM\Relations\HasMany
    {
        $instance = new $related();
        return new \App\Core\Database\ORM\Relations\HasMany($this, $instance, $foreignKey, $localKey);
    }

    /**
     * Define an inverse one-to-one or one-to-many relationship.
     */
    public function belongsTo(string $related, string $foreignKey = null, string $ownerKey = 'id'): \App\Core\Database\ORM\Relations\BelongsTo
    {
        $instance = new $related();
        return new \App\Core\Database\ORM\Relations\BelongsTo($this, $instance, $foreignKey, $ownerKey);
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
    /**
     * Convert the model instance to an array.
     *
     * @return array
     */
    public function toArray(): array
    {
        return $this->attributes;
    }
}
