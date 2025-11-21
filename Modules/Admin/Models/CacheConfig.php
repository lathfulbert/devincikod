<?php

namespace Modules\Admin\Models;

use App\Core\Database\Model;
use App\Core\Database\Database;

/**
 * CacheConfig Model - Singleton pattern for cache configuration
 */
class CacheConfig extends Model
{
    protected static string $table = 'cache_config';
    protected array $fillable = [
        'driver',
        'enabled',
        'prefix',
        'default_ttl',
        'filesystem_path',
        'redis_host',
        'redis_port',
        'redis_password',
        'redis_database',
        'memcached_servers',
        'apcu_enabled'
    ];

    /**
     * Récupère la configuration singleton
     */
    public static function getConfig(): ?CacheConfig
    {
        $configs = self::all();
        return $configs[0] ?? null;
    }

    /**
     * Crée ou met à jour la configuration
     */
    public static function updateConfig(array $data): bool
    {
        $config = self::getConfig();

        if ($config) {
            // Mise à jour
            foreach ($data as $key => $value) {
                $config->$key = $value;
            }
            $config->save();
            return true;
        } else {
            // Création
            $db = Database::getInstance();
            $fields = [];
            $values = [];

            foreach ($data as $key => $value) {
                $fields[] = "`{$key}`";
                $values[] = $value;
            }

            $placeholders = array_fill(0, count($values), '?');
            $sql = "INSERT INTO cache_config (" . implode(', ', $fields) . ") VALUES (" . implode(', ', $placeholders) . ")";
            $db->query($sql, $values);
            return true;
        }
    }

    /**
     * Parse les serveurs Memcached depuis JSON
     */
    public function getMemcachedServersArray(): array
    {
        $servers = $this->memcached_servers ?? '';
        if (empty($servers)) {
            return [];
        }

        $decoded = json_decode($servers, true);
        return is_array($decoded) ? $decoded : [];
    }

    /**
     * Set les serveurs Memcached en JSON
     */
    public function setMemcachedServersFromArray(array $servers): void
    {
        $this->memcached_servers = json_encode($servers);
    }
}
