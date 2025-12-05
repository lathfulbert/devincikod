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
        // Get statistics
        $stats = [
            'total_logs' => $this->countLogs(),
            'errors' => $this->countLogs('error'),
            'warnings' => $this->countLogs('warning'),
            'today' => $this->countLogsToday(),
        ];

        // Pagination
        $page = $_GET['page'] ?? 1;
        $perPage = 20;
        $offset = ($page - 1) * $perPage;

        $logs = $this->getLogs($perPage, $offset);
        $totalLogs = $stats['total_logs'];
        $totalPages = ceil($totalLogs / $perPage);

        echo view('admin.monitoring.index', [
            'title' => 'Monitoring Dashboard',
            'stats' => $stats,
            'logs' => $logs,
            'currentPage' => $page,
            'totalPages' => $totalPages,
        ]);
    }

    /**
     * Clear all logs.
     */
    public function clear()
    {
        // CSRF check could be added here
        try {
            $this->db->query("TRUNCATE TABLE logs");

            // Clear log files
            $logDir = __DIR__ . '/../../../storage/logs';
            $files = glob($logDir . '/*.log');
            foreach ($files as $file) {
                if (is_file($file)) {
                    file_put_contents($file, '');
                }
            }

            // Flash success message
            $_SESSION['flash_success'] = 'Tous les logs ont été supprimés avec succès';
        } catch (\Exception $e) {
            // Flash error message
            $_SESSION['flash_error'] = 'Erreur lors de la suppression des logs : ' . $e->getMessage();
        }

        // Redirect back to monitoring page
        redirect('/admin/monitoring');
        exit;
    }

    protected function getLogs(int $limit, int $offset): array
    {
        try {
            $stmt = $this->db->query(
                "SELECT * FROM logs ORDER BY created_at DESC LIMIT ? OFFSET ?",
                [$limit, $offset]
            );
            return $stmt->fetchAll(\PDO::FETCH_OBJ);
        } catch (\Exception $e) {
            return [];
        }
    }

    protected function countLogs(?string $level = null): int
    {
        try {
            if ($level) {
                $stmt = $this->db->query("SELECT COUNT(*) as count FROM logs WHERE level = ?", [$level]);
            } else {
                $stmt = $this->db->query("SELECT COUNT(*) as count FROM logs");
            }
            return (int) ($stmt->fetch(\PDO::FETCH_OBJ)->count ?? 0);
        } catch (\Exception $e) {
            return 0;
        }
    }

    protected function countLogsToday(): int
    {
        try {
            $today = date('Y-m-d');
            $stmt = $this->db->query("SELECT COUNT(*) as count FROM logs WHERE created_at LIKE ?", ["$today%"]);
            return (int) ($stmt->fetch(\PDO::FETCH_OBJ)->count ?? 0);
        } catch (\Exception $e) {
            return 0;
        }
    }
}
