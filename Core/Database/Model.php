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
        $stmt = $db->query("SELECT * FROM {$table}");
        return $stmt->fetchAll(\PDO::FETCH_CLASS, static::class);
    }

    public static function find(int $id): ?static
    {
        $db = Database::getInstance();
        $table = static::getTable();
        $stmt = $db->query("SELECT * FROM {$table} WHERE id = ?", [$id]);
        $result = $stmt->fetchObject(static::class);
        return $result ?: null;
    }

    public function save(): void
    {
        $db = Database::getInstance();
        $table = static::getTable();
        
        if (isset($this->attributes['id'])) {
            // Update
            $set = [];
            $params = [];
            foreach ($this->attributes as $key => $value) {
                if ($key === 'id') continue;
                $set[] = "{$key} = ?";
                $params[] = $value;
            }
            $params[] = $this->attributes['id'];
            $sql = "UPDATE {$table} SET " . implode(', ', $set) . " WHERE id = ?";
            $db->query($sql, $params);
        } else {
            // Insert
            $keys = array_keys($this->attributes);
            $placeholders = array_fill(0, count($keys), '?');
            $sql = "INSERT INTO {$table} (" . implode(', ', $keys) . ") VALUES (" . implode(', ', $placeholders) . ")";
            $db->query($sql, array_values($this->attributes));
            $this->attributes['id'] = $db->getPdo()->lastInsertId();
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
}
