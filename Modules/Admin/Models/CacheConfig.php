<?php

namespace Modules\Admin\Models;

use App\Core\Database\ORM\Model;

/**
 * CacheConfig Model
 * 
 * Modèle pour la configuration du système de cache.
 * Table singleton (une seule ligne).
 */
class CacheConfig extends Model
{
    protected string $table = 'cache_config';
    protected string $primaryKey = 'id';
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
                if (in_array($key, (new self())->fillable)) {
                    $config->$key = $value;
                }
            }
            return $config->save();
        } else {
            // Création
            $config = new self();
            foreach ($data as $key => $value) {
                if (in_array($key, $config->fillable)) {
                    $config->$key = $value;
                }
            }
            return $config->save();
        }
    }

    /**
     * Parse les serveurs Memcached depuis JSON
     */
    public function getMemcachedServersArray(): array
    {
        if (empty($this->memcached_servers)) {
            return [];
        }

        $servers = json_decode($this->memcached_servers, true);
        return is_array($servers) ? $servers : [];
    }

    /**
     * Set les serveurs Memcached en JSON
     */
    public function setMemcachedServersFromArray(array $servers): void
    {
        $this->memcached_servers = json_encode($servers);
    }
}
