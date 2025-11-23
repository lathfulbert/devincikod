<?php

namespace Modules\Settings\Models;

use App\Core\Database\Model;

class Setting extends Model
{
    protected static string $table = 'settings';
    protected array $fillable = ['key', 'value', 'type', 'setting_group', 'description', 'is_public'];
    protected array $casts = [
        'is_public' => 'boolean',
        'value' => 'json'
    ];

    // Cache duration in seconds (1 hour)
    const CACHE_DURATION = 3600;

    /**
     * Get setting by key
     */
    public static function get(string $key, $default = null)
    {
        $setting = static::where('key', $key)->first();

        if (!$setting) {
            return $default;
        }

        return static::castValue($setting->value, $setting->type ?? 'string');
    }

    /**
     * Set or update a setting
     */
    public static function set(string $key, $value, string $type = 'string', string $group = 'general'): bool
    {
        $setting = static::where('key', $key)->first();

        $data = [
            'key' => $key,
            'value' => static::prepareValue($value, $type),
            'type' => $type,
            'setting_group' => $group,
            'updated_at' => date('Y-m-d H:i:s')
        ];

        if ($setting) {
            return static::where('key', $key)->update($data);
        } else {
            $data['created_at'] = date('Y-m-d H:i:s');
            return static::create($data);
        }
    }

    /**
     * Get all settings by group
     */
    public static function getByGroup(string $group): array
    {
        $settings = static::where('setting_group', $group)->get();
        $result = [];

        foreach ($settings as $setting) {
            $result[$setting->key] = static::castValue($setting->value, $setting->type ?? 'string');
        }

        return $result;
    }

    /**
     * Get all settings as key-value pairs
     */
    public static function getAll(): array
    {
        $settings = static::all();
        $result = [];

        foreach ($settings as $setting) {
            $result[$setting->key] = static::castValue($setting->value, $setting->type ?? 'string');
        }

        return $result;
    }

    /**
     * Prepare value for storage
     */
    protected static function prepareValue($value, string $type)
    {
        switch ($type) {
            case 'boolean':
                return $value ? '1' : '0';
            case 'json':
            case 'array':
                return json_encode($value);
            case 'integer':
                return (int) $value;
            case 'float':
                return (float) $value;
            default:
                return (string) $value;
        }
    }

    /**
     * Cast value to appropriate type
     */
    protected static function castValue($value, string $type)
    {
        switch ($type) {
            case 'boolean':
                return (bool) $value;
            case 'json':
            case 'array':
                return is_string($value) ? json_decode($value, true) : $value;
            case 'integer':
                return (int) $value;
            case 'float':
                return (float) $value;
            default:
                return $value;
        }
    }

    /**
     * Delete a setting
     */
    public static function remove(string $key): bool
    {
        return static::where('key', $key)->delete();
    }

    /**
     * Check if setting exists
     */
    public static function has(string $key): bool
    {
        return static::where('key', $key)->exists();
    }
}
