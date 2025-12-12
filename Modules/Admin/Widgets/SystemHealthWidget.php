<?php

namespace Modules\Admin\Widgets;

use App\Core\Widget\AbstractWidget;
use App\Core\Database\Database;

/**
 * Widget SystemHealth
 *
 * Affiche la santé globale du système
 *
 * Type: Information / Alerte
 * Permission: admin.access
 */
class SystemHealthWidget extends AbstractWidget
{
    protected string $name = 'admin.system_health';
    protected string $type = 'info';
    protected ?string $permission = 'admin.access';
    protected bool $cacheable = true;
    protected int $cacheDuration = 120; // 2 minutes

    protected array $config = [
        'title' => 'Santé du système',
        'description' => 'État global du système',
        'icon' => 'activity',
        'color' => 'success',
        'order' => 1
    ];

    protected function calculateData(): array
    {
        $checks = [
            'database' => $this->checkDatabase(),
            'cache' => $this->checkCache(),
            'storage' => $this->checkStorage(),
            'memory' => $this->checkMemory()
        ];

        $healthScore = 0;
        foreach ($checks as $check) {
            if ($check['status'] === 'ok') {
                $healthScore += 25;
            }
        }

        $status = $healthScore >= 75 ? 'Optimal' : ($healthScore >= 50 ? 'Attention' : 'Critique');
        $severity = $healthScore >= 75 ? 'success' : ($healthScore >= 50 ? 'warning' : 'danger');

        return [
            'title' => 'Santé du système',
            'value' => $status,
            'description' => "Score: {$healthScore}%",
            'icon' => 'activity',
            'trend' => [
                'percentage' => $healthScore,
                'direction' => $healthScore >= 75 ? 'up' : ($healthScore < 50 ? 'down' : 'stable')
            ],
            'meta' => [
                'checks' => $checks,
                'health_score' => $healthScore,
                'severity' => $severity
            ]
        ];
    }

    private function checkDatabase(): array
    {
        try {
            $db = Database::getInstance();
            $db->query("SELECT 1")->fetch();
            return [
                'name' => 'Base de données',
                'status' => 'ok',
                'message' => 'Connexion active'
            ];
        } catch (\Exception $e) {
            return [
                'name' => 'Base de données',
                'status' => 'error',
                'message' => 'Erreur de connexion'
            ];
        }
    }

    private function checkCache(): array
    {
        $cacheDir = __DIR__ . '/../../../storage/cache';
        if (is_dir($cacheDir) && is_writable($cacheDir)) {
            return [
                'name' => 'Cache',
                'status' => 'ok',
                'message' => 'Accessible en écriture'
            ];
        }
        return [
            'name' => 'Cache',
            'status' => 'warning',
            'message' => 'Répertoire non accessible'
        ];
    }

    private function checkStorage(): array
    {
        $storageDir = __DIR__ . '/../../../storage';
        if (is_dir($storageDir) && is_writable($storageDir)) {
            $freeSpace = disk_free_space($storageDir);
            $totalSpace = disk_total_space($storageDir);
            $usedPercentage = 100 - (($freeSpace / $totalSpace) * 100);

            return [
                'name' => 'Stockage',
                'status' => $usedPercentage < 90 ? 'ok' : 'warning',
                'message' => round($usedPercentage, 1) . '% utilisé'
            ];
        }
        return [
            'name' => 'Stockage',
            'status' => 'error',
            'message' => 'Répertoire non accessible'
        ];
    }

    private function checkMemory(): array
    {
        $memoryLimit = ini_get('memory_limit');
        $memoryUsage = memory_get_usage(true);

        if ($memoryLimit === '-1') {
            return [
                'name' => 'Mémoire',
                'status' => 'ok',
                'message' => 'Illimitée'
            ];
        }

        $limitBytes = $this->convertToBytes($memoryLimit);
        $usedPercentage = ($memoryUsage / $limitBytes) * 100;

        return [
            'name' => 'Mémoire',
            'status' => $usedPercentage < 80 ? 'ok' : 'warning',
            'message' => round($usedPercentage, 1) . '% utilisée'
        ];
    }

    private function convertToBytes(string $value): int
    {
        $unit = strtolower(substr($value, -1));
        $number = (int)$value;

        return match ($unit) {
            'g' => $number * 1024 * 1024 * 1024,
            'm' => $number * 1024 * 1024,
            'k' => $number * 1024,
            default => $number,
        };
    }
}
