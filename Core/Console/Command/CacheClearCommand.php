<?php

namespace App\Core\Console\Command;

use App\Core\Application;
use App\Core\Cache\CacheManager;

class CacheClearCommand
{
    public function execute(Application $app, array $args): void
    {
        try {
            $cache = CacheManager::getInstance();

            if ($cache->clear()) {
                echo "✅ Cache vidé avec succès.\n";

                // Aussi vider le cache des vues compilées
                $viewCachePath = $app->getBasePath() . '/storage/cache/views';
                if (is_dir($viewCachePath)) {
                    $files = glob($viewCachePath . '/*.php');
                    foreach ($files as $file) {
                        if (is_file($file)) {
                            unlink($file);
                        }
                    }
                    echo "✅ Cache des vues vidé (" . count($files) . " fichiers supprimés).\n";
                }
            } else {
                echo "❌ Impossible de vider le cache.\n";
                exit(1);
            }
        } catch (\Exception $e) {
            echo "❌ Erreur : " . $e->getMessage() . "\n";
            exit(1);
        }
    }
}
