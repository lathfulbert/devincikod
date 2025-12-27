<?php

namespace Modules\Admin\Controllers;

use App\Core\Database\Database;
use App\Core\Application;

class MonitoringController
{
    protected $db;
    protected $view;

    public function __construct()
    {
        $this->db = Database::getInstance();
        $this->view = Application::getInstance()->view;
    }

    /**
     * Display the monitoring dashboard.
     */
    public function index()
    {
        // Parse all log files
        $allLogs = $this->parseLogFiles();

        // Filter to keep only WARNING, ERROR, CRITICAL, ALERT, EMERGENCY (exclude DEBUG, INFO)
        $importantLevels = ['warning', 'error', 'critical', 'alert', 'emergency'];
        $allLogs = array_filter($allLogs, fn($l) => in_array(strtolower($l->level), $importantLevels));
        $allLogs = array_values($allLogs); // Re-index

        // Get filter parameters
        $filterLevel = $_GET['level'] ?? '';
        $filterChannel = $_GET['channel'] ?? '';
        $filterDate = $_GET['date'] ?? '';

        // Apply filters
        $logEntries = $allLogs;
        if ($filterLevel) {
            $logEntries = array_filter($logEntries, fn($l) => strtolower($l->level) === strtolower($filterLevel));
        }
        if ($filterChannel) {
            $logEntries = array_filter($logEntries, fn($l) => strtolower($l->channel) === strtolower($filterChannel));
        }
        if ($filterDate) {
            $logEntries = array_filter($logEntries, fn($l) => date('Y-m-d', strtotime($l->date)) === $filterDate);
        }

        // Re-index array after filtering
        $logEntries = array_values($logEntries);

        // Get unique values for filter dropdowns
        $levels = array_unique(array_map(fn($l) => $l->level, $allLogs));
        $channels = array_unique(array_map(fn($l) => $l->channel, $allLogs));
        sort($levels);
        sort($channels);

        // Get statistics (after filtering)
        $stats = [
            'total_logs' => count($logEntries),
            'errors' => count(array_filter($logEntries, fn($l) => in_array(strtolower($l->level), ['error', 'critical', 'alert', 'emergency']))),
            'warnings' => count(array_filter($logEntries, fn($l) => strtolower($l->level) === 'warning')),
            'today' => count(array_filter($logEntries, fn($l) => date('Y-m-d', strtotime($l->date)) === date('Y-m-d'))),
        ];

        // Pagination
        $page = $_GET['page'] ?? 1;
        $perPage = 50;
        $offset = ($page - 1) * $perPage;

        $logs = array_slice($logEntries, $offset, $perPage);
        $totalLogs = count($logEntries);
        $totalPages = ceil($totalLogs / $perPage);

        return view('admin.monitoring.index', [
            'title' => 'Monitoring Dashboard',
            'stats' => $stats,
            'logs' => $logs,
            'currentPage' => $page,
            'totalPages' => $totalPages,
            'levels' => $levels,
            'channels' => $channels,
            'filterLevel' => $filterLevel,
            'filterChannel' => $filterChannel,
            'filterDate' => $filterDate,
        ]);
    }

    /**
     * Clear all logs.
     */
    public function clear()
    {
        try {
            // Clear log files only
            $logDir = __DIR__ . '/../../../storage/logs';
            $files = glob($logDir . '/*.log');
            foreach ($files as $file) {
                if (is_file($file)) {
                    file_put_contents($file, '');
                }
            }

            $_SESSION['flash_success'] = 'Tous les logs ont été supprimés avec succès';
        } catch (\Exception $e) {
            $_SESSION['flash_error'] = 'Erreur lors de la suppression des logs : ' . $e->getMessage();
        }

        redirect('/admin/monitoring');
        exit;
    }

    /**
     * Parse log files and return array of log entries
     */
    protected function parseLogFiles(): array
    {
        $logDir = __DIR__ . '/../../../storage/logs';
        $files = glob($logDir . '/*.log');
        $entries = [];

        foreach ($files as $file) {
            if (!is_file($file)) continue;

            $content = file_get_contents($file);
            $lines = explode("\n", $content);

            foreach ($lines as $line) {
                if (empty(trim($line))) continue;

                $level = 'INFO';
                $message = $line;
                $date = date('Y-m-d H:i:s');
                $channel = 'app';

                // Format 1: [2024-01-01 10:00:00] local.ERROR: Message
                if (preg_match('/\[(\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2})\]\s+(\w+)\.(\w+):\s+(.+)/', $line, $matches)) {
                    $date = $matches[1];
                    $channel = $matches[2];
                    $level = $this->normalizeLogLevel($matches[3]);
                    $message = $matches[4];
                }
                // Format 2: [number] text - skip number prefix
                elseif (preg_match('/^\[\d+\]\s+(.+)/', $line, $matches)) {
                    $message = $matches[1];
                    // Try to detect level keyword in message
                    if (preg_match('/\b(DEBUG|INFO|NOTICE|WARNING|ERROR|CRITICAL|ALERT|EMERGENCY)\b/i', $message, $levelMatch)) {
                        $level = strtoupper($levelMatch[1]);
                    } else {
                        $level = 'DEBUG'; // Default for debug logs
                    }
                }
                // Format 3: [LEVEL] Message
                elseif (preg_match('/^\[([A-Z]+)\]\s+(.+)/', $line, $matches)) {
                    $level = $this->normalizeLogLevel($matches[1]);
                    $message = $matches[2];
                }

                $entries[] = (object)[
                    'date' => $date,
                    'created_at' => $date,
                    'channel' => $channel,
                    'level' => $level,
                    'message' => $message,
                    'context' => null,
                ];
            }
        }

        // Sort by date DESC (most recent first)
        usort($entries, function ($a, $b) {
            return strtotime($b->created_at) - strtotime($a->created_at);
        });

        return $entries;
    }

    /**
     * Normalize log level (convert numeric to text)
     */
    protected function normalizeLogLevel($level): string
    {
        // Convert numeric PSR-3/Monolog levels to text
        $numericLevels = [
            '100' => 'DEBUG',
            '200' => 'INFO',
            '250' => 'NOTICE',
            '300' => 'WARNING',
            '400' => 'ERROR',
            '500' => 'CRITICAL',
            '550' => 'ALERT',
            '600' => 'EMERGENCY',
        ];

        $level = (string) $level;

        // If numeric, convert
        if (isset($numericLevels[$level])) {
            return $numericLevels[$level];
        }

        // If already text, return uppercase
        return strtoupper($level);
    }
}
