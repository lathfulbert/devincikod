<?php

namespace App\Core\Console\Command;

use App\Core\Application;
use App\Core\Cache\CacheManager;

class CacheStatsCommand
{
    public function execute(Application $app, array $args): void
    {
        try {
            $cache = CacheManager::getInstance();
            $stats = $cache->getStats();

            echo "\n📊 Statistiques du Cache\n";
            echo str_repeat("─", 50) . "\n";
            echo "Driver        : " . ($stats['driver'] ?? 'inconnu') . "\n";
            echo "État          : " . ($stats['enabled'] ? '✅ Activé' : '❌ Désactivé') . "\n";
            echo "Préfixe       : " . ($stats['prefix'] ?? 'aucun') . "\n";

            if (isset($stats['hits'])) {
                echo "Hits          : " . number_format($stats['hits']) . "\n";
            }
            if (isset($stats['misses'])) {
                echo "Misses        : " . number_format($stats['misses']) . "\n";
            }
            if (isset($stats['keys_count'])) {
                echo "Clés actives  : " . number_format($stats['keys_count']) . "\n";
            }
            if (isset($stats['memory_usage'])) {
                echo "Mémoire       : " . $this->formatBytes($stats['memory_usage']) . "\n";
            }

            echo str_repeat("─", 50) . "\n\n";
        } catch (\Exception $e) {
            echo "❌ Erreur : " . $e->getMessage() . "\n";
            exit(1);
        }
    }

    private function formatBytes(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        $i = 0;
        while ($bytes >= 1024 && $i < count($units) - 1) {
            $bytes /= 1024;
            $i++;
        }
        return round($bytes, 2) . ' ' . $units[$i];
    }
}
