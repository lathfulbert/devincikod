<?php

namespace Modules\Auth\Models;

use App\Core\Database\Model;

class AuthSetting extends Model
{
    protected static string $table = 'auth_settings';

    protected array $fillable = [
        'key',
        'value',
        'type',
        'description',
    ];

    /**
     * Récupère la valeur d'un paramètre auth (avec fallback par défaut)
     */
    public static function getValue(string $key, $default = null)
    {
        $setting = static::query()->where('key', $key)->first();
        if (!$setting) return $default;
        $value = $setting['value'];
        switch ($setting['type']) {
            case 'int': return (int)$value;
            case 'bool': return (bool)$value;
            case 'json': return json_decode($value, true);
            default: return $value;
        }
    }
}
