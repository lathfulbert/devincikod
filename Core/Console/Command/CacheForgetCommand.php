<?php

namespace App\Core\Console\Command;

use App\Core\Application;
use App\Core\Cache\CacheManager;

class CacheForgetCommand
{
    public function execute(Application $app, array $args): void
    {
        $key = $args[0] ?? null;

        if (!$key) {
            echo "❌ Erreur : vous devez fournir une clé.\n";
            echo "Usage: php sunu cache:forget <clé>\n";
            exit(1);
        }

        try {
            $cache = CacheManager::getInstance();

            if ($cache->delete($key)) {
                echo "✅ Clé '$key' supprimée du cache.\n";
            } else {
                echo "⚠️  Clé '$key' introuvable dans le cache.\n";
            }
        } catch (\Exception $e) {
            echo "❌ Erreur : " . $e->getMessage() . "\n";
            exit(1);
        }
    }
}
