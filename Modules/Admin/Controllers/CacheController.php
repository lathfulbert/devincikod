<?php

namespace Modules\Admin\Controllers;

use App\Core\Application;
use App\Core\Cache\CacheManager;
use Modules\Admin\Models\CacheConfig;

/**
 * CacheController
 * 
 * Contrôleur pour gérer la configuration du cache depuis l'admin
 */
class CacheController
{
    /**
     * Page principale de configuration du cache
     */
    public function index()
    {
        $app = Application::getInstance();
        $config = CacheConfig::getConfig();

        if (!$config) {
            // Créer une config par défaut si elle n'existe pas
            CacheConfig::updateConfig([
                'driver' => 'filesystem',
                'enabled' => 1,
                'prefix' => 'cache_',
                'default_ttl' => 3600,
                'filesystem_path' => 'storage/cache',
                'redis_host' => '127.0.0.1',
                'redis_port' => 6379,
                'redis_database' => 0,
                'memcached_servers' => json_encode([['host' => '127.0.0.1', 'port' => 11211]]),
                'apcu_enabled' => 0
            ]);
            $config = CacheConfig::getConfig();
        }

        // Vérifier la disponibilité des extensions
        $extensions = [
            'redis' => class_exists('Predis\Client'),
            'memcached' => extension_loaded('memcached'),
            'apcu' => extension_loaded('apcu') && ini_get('apc.enabled')
        ];

        echo $app->view->render('admin/cache/index', [
            'title' => 'Configuration du Cache',
            'config' => $config,
            'extensions' => $extensions
        ]);
    }

    /**
     * Met à jour la configuration du cache
     */
    public function update()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('/admin/cache');
            return;
        }

        $driver = sanitize($_POST['driver'] ?? 'filesystem', 'alphanumeric');
        $enabled = isset($_POST['enabled']) ? 1 : 0;
        $prefix = sanitize($_POST['prefix'] ?? 'cache_', 'string');
        $defaultTtl = (int)($_POST['default_ttl'] ?? 3600);

        $data = [
            'driver' => $driver,
            'enabled' => $enabled,
            'prefix' => $prefix,
            'default_ttl' => $defaultTtl,
        ];

        // Configuration Filesystem
        if ($driver === 'filesystem') {
            $data['filesystem_path'] = sanitize($_POST['filesystem_path'] ?? 'storage/cache', 'string');
        }

        // Configuration Redis
        if ($driver === 'redis') {
            $data['redis_host'] = sanitize($_POST['redis_host'] ?? '127.0.0.1', 'string');
            $data['redis_port'] = (int)($_POST['redis_port'] ?? 6379);
            $data['redis_password'] = sanitize($_POST['redis_password'] ?? '', 'string');
            $data['redis_database'] = (int)($_POST['redis_database'] ?? 0);
        }

        // Configuration Memcached
        if ($driver === 'memcached') {
            $servers = [];
            if (isset($_POST['memcached_hosts']) && is_array($_POST['memcached_hosts'])) {
                foreach ($_POST['memcached_hosts'] as $i => $host) {
                    $port = $_POST['memcached_ports'][$i] ?? 11211;
                    if (!empty($host)) {
                        $servers[] = [
                            'host' => sanitize($host, 'string'),
                            'port' => (int)$port
                        ];
                    }
                }
            }
            if (empty($servers)) {
                $servers = [['host' => '127.0.0.1', 'port' => 11211]];
            }
            $data['memcached_servers'] = json_encode($servers);
        }

        // Configuration APCu
        if ($driver === 'apcu') {
            $data['apcu_enabled'] = 1;
        }

        if (CacheConfig::updateConfig($data)) {
            // Recharger le CacheManager
            CacheManager::getInstance()->reload();

            flash('success', 'Configuration du cache mise à jour avec succès.');
        } else {
            flash('error', 'Erreur lors de la mise à jour de la configuration.');
        }

        redirect('/admin/cache');
    }

    /**
     * Teste la connexion à un driver (AJAX)
     */
    public function testDriver()
    {
        header('Content-Type: application/json');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Méthode non autorisée']);
            return;
        }

        $driver = sanitize($_POST['driver'] ?? '', 'alphanumeric');

        try {
            $config = CacheConfig::getConfig();

            if (!$config) {
                echo json_encode(['success' => false, 'message' => 'Configuration non trouvée']);
                return;
            }

            $configArray = (array)$config;
            $testResult = $this->performDriverTest($driver, $configArray);

            echo json_encode($testResult);
        } catch (\Exception $e) {
            echo json_encode([
                'success' => false,
                'message' => 'Erreur: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Effectue le test d'un driver
     */
    private function performDriverTest(string $driver, array $config): array
    {
        try {
            $driverClass = match ($driver) {
                'redis' => \App\Core\Cache\Drivers\RedisDriver::class,
                'memcached' => \App\Core\Cache\Drivers\MemcachedDriver::class,
                'apcu' => \App\Core\Cache\Drivers\APCuDriver::class,
                'filesystem' => \App\Core\Cache\Drivers\FilesystemDriver::class,
                default => null
            };

            if (!$driverClass) {
                return ['success' => false, 'message' => 'Driver inconnu'];
            }

            $instance = new $driverClass($config, 'test_');

            // Test basique : set et get
            $testKey = 'connection_test_' . time();
            $testValue = 'OK';

            if ($instance->set($testKey, $testValue, 60)) {
                $retrieved = $instance->get($testKey);
                $instance->delete($testKey);

                if ($retrieved === $testValue) {
                    return [
                        'success' => true,
                        'message' => 'Connexion réussie au driver ' . ucfirst($driver)
                    ];
                }
            }

            return ['success' => false, 'message' => 'Échec du test read/write'];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Erreur de connexion: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Vide tout le cache
     */
    public function clear()
    {
        try {
            $cache = CacheManager::getInstance();
            $cache->clear();

            flash('success', 'Cache vidé avec succès.');
        } catch (\Exception $e) {
            flash('error', 'Erreur lors du vidage du cache: ' . $e->getMessage());
        }

        redirect('/admin/cache/stats');
    }

    /**
     * Affiche les statistiques du cache
     */
    public function stats()
    {
        $app = Application::getInstance();
        $cache = CacheManager::getInstance();

        $stats = $cache->getStats();

        echo $app->view->render('admin/cache/stats', [
            'title' => 'Statistiques du Cache',
            'stats' => $stats
        ]);
    }
}
