<?php

namespace Modules\Backup\Services;

class MonitoringService
{
    public function getSystemHealth(): array
    {
        return [
            'disk' => $this->getDiskUsage(),
            'cpu' => $this->getCpuUsage(),
            'memory' => $this->getMemoryUsage(),
            'services' => $this->getServicesStatus(),
        ];
    }

    protected function getDiskUsage(): array
    {
        $path = '.'; // Current directory
        $total = disk_total_space($path);
        $free = disk_free_space($path);
        $used = $total - $free;
        $percent = ($used / $total) * 100;

        return [
            'total' => $this->formatBytes($total),
            'used' => $this->formatBytes($used),
            'free' => $this->formatBytes($free),
            'percent' => round($percent, 2),
            'status' => $percent > 90 ? 'critical' : ($percent > 80 ? 'warning' : 'healthy'),
        ];
    }

    protected function getCpuUsage(): array
    {
        // Windows specific
        if (stristr(PHP_OS, 'win')) {
            $cmd = "wmic cpu get loadpercentage /value";
            exec($cmd, $output);
            $load = 0;
            foreach ($output as $line) {
                if (preg_match("/LoadPercentage=(\d+)/", $line, $matches)) {
                    $load = $matches[1];
                    break;
                }
            }
            return [
                'load' => $load,
                'status' => $load > 85 ? 'critical' : ($load > 70 ? 'warning' : 'healthy'),
            ];
        }

        // Linux/Unix
        $load = sys_getloadavg();
        return [
            'load' => $load[0], // 1 min avg
            'status' => $load[0] > 0.85 ? 'critical' : ($load[0] > 0.70 ? 'warning' : 'healthy'), // Assuming 1 core for simplicity, needs adjustment for multi-core
        ];
    }

    protected function getMemoryUsage(): array
    {
        // Windows specific
        if (stristr(PHP_OS, 'win')) {
            $cmd = "wmic OS get FreePhysicalMemory,TotalVisibleMemorySize /Value";
            exec($cmd, $output);
            $total = 0;
            $free = 0;
            foreach ($output as $line) {
                if (preg_match("/TotalVisibleMemorySize=(\d+)/", $line, $matches)) {
                    $total = $matches[1] * 1024;
                }
                if (preg_match("/FreePhysicalMemory=(\d+)/", $line, $matches)) {
                    $free = $matches[1] * 1024;
                }
            }
            $used = $total - $free;
            $percent = $total > 0 ? ($used / $total) * 100 : 0;

            return [
                'total' => $this->formatBytes($total),
                'used' => $this->formatBytes($used),
                'free' => $this->formatBytes($free),
                'percent' => round($percent, 2),
                'status' => $percent > 90 ? 'critical' : ($percent > 80 ? 'warning' : 'healthy'),
            ];
        }

        // Linux
        $free = shell_exec('free');
        $free = (string)trim($free);
        $free_arr = explode("\n", $free);
        $mem = explode(" ", $free_arr[1]);
        $mem = array_filter($mem);
        $mem = array_merge($mem);
        $memory_usage = $mem[2] / $mem[1] * 100;

        return [
            'total' => $this->formatBytes($mem[1] * 1024),
            'used' => $this->formatBytes($mem[2] * 1024),
            'free' => $this->formatBytes($mem[3] * 1024),
            'percent' => round($memory_usage, 2),
            'status' => $memory_usage > 90 ? 'critical' : ($memory_usage > 80 ? 'warning' : 'healthy'),
        ];
    }

    protected function getServicesStatus(): array
    {
        $services = [
            'database' => $this->checkDatabase(),
            // 'redis' => $this->checkRedis(),
        ];
        return $services;
    }

    protected function checkDatabase(): string
    {
        try {
            \App\Core\Database\Database::getInstance()->query("SELECT 1");
            return 'running';
        } catch (\Exception $e) {
            return 'down';
        }
    }

    protected function formatBytes($bytes, $precision = 2)
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        $bytes /= pow(1024, $pow);
        return round($bytes, $precision) . ' ' . $units[$pow];
    }
}
