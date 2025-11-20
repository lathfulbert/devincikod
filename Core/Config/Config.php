<?php

namespace App\Core\Config;

class Config
{
    protected array $items = [];

    public function load(string $path): void
    {
        if (file_exists($path)) {
            $this->items = require $path;
        }
    }

    public function get(string $key, mixed $default = null): mixed
    {
        $keys = explode('.', $key);
        $value = $this->items;

        foreach ($keys as $segment) {
            if (!is_array($value) || !array_key_exists($segment, $value)) {
                return $default;
            }
            $value = $value[$segment];
        }

        return $value;
    }
}
