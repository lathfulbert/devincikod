<?php

namespace Modules\Admin\Services;

use App\Core\Queue\QueueManager;
use App\Core\Database\Database;

/**
 * Class QueueService
 * 
 * Service layer for Queue management in Backoffice.
 */
class QueueService
{
    private Database $db;
    private QueueManager $queueManager;

    public function __construct()
    {
        $this->db = Database::getInstance();
        $this->queueManager = QueueManager::getInstance();
    }

    /**
     * Get dashboard statistics.
     */
    public function getDashboardStats(): array
    {
        $driver = $this->queueManager->getDriver();

        // Active jobs count
        $activeJobs = $driver->size('default');

        // Failed jobs  
        $failedJobs = $this->db->query("SELECT COUNT(*) as count FROM failed_jobs")->fetch();

        // Jobs last 24h
        $jobs24h = $this->db->query("
            SELECT COUNT(*) as count FROM jobs 
            WHERE created_at >= UNIX_TIMESTAMP(DATE_SUB(NOW(), INTERVAL 24 HOUR))
        ")->fetch();

        $failed24h = $this->db->query("
            SELECT COUNT(*) as count FROM failed_jobs
            WHERE failed_at >= DATE_SUB(NOW(), INTERVAL 24 HOUR)
        ")->fetch();

        $total24h = ($jobs24h['count'] ?? 0) + ($failed24h['count'] ?? 0);
        $successRate = $total24h > 0 ? round((($total24h - $failed24h['count']) / $total24h) * 100, 1) : 100;

        return [
            'active_jobs' => $activeJobs,
            'failed_jobs' => $failedJobs['count'] ?? 0,
            'success_rate' => $successRate,
            'total_24h' => $total24h,
        ];
    }

    /**
     * Get active jobs list.
     */
    public function getActiveJobs(int $page = 1, int $perPage = 20): array
    {
        $offset = ($page - 1) * $perPage;

        $jobs = $this->db->query("
            SELECT * FROM jobs 
            WHERE reserved_at IS NULL
            ORDER BY created_at DESC
            LIMIT ? OFFSET ?
        ", [$perPage, $offset])->fetchAll();

        $total = $this->db->query("SELECT COUNT(*) as count FROM jobs WHERE reserved_at IS NULL")->fetch();

        return [
            'jobs' => $jobs,
            'total' => $total['count'],
            'page' => $page,
            'per_page' => $perPage,
            'pages' => ceil($total['count'] / $perPage),
        ];
    }

    /**
     * Get failed jobs list.
     */
    public function getFailedJobs(int $page = 1, int $perPage = 20): array
    {
        $offset = ($page - 1) * $perPage;

        $jobs = $this->db->query("
            SELECT * FROM failed_jobs 
            ORDER BY failed_at DESC
            LIMIT ? OFFSET ?
        ", [$perPage, $offset])->fetchAll();

        $total = $this->db->query("SELECT COUNT(*) as count FROM failed_jobs")->fetch();

        return [
            'jobs' => $jobs,
            'total' => $total['count'],
            'page' => $page,
            'per_page' => $perPage,
            'pages' => ceil($total['count'] / $perPage),
        ];
    }

    /**
     * Retry a failed job.
     */
    public function retryFailedJob(int $id): bool
    {
        // Get failed job data
        $job = $this->db->query("SELECT * FROM failed_jobs WHERE id = ?", [$id])->fetch();
        if (!$job) return false;

        // Recreate job
        $payload = json_decode($job['payload'], true);

        // Extract job class and data
        $jobClass = $payload['job'] ?? 'Unknown';
        $jobData = $payload['data'] ?? '[]';

        // Ensure data is an array
        if (is_string($jobData)) {
            $jobData = json_decode($jobData, true) ?? [];
        }

        $this->queueManager->push($jobClass, $jobData, $job['queue']);

        // Delete from failed_jobs
        $this->db->query("DELETE FROM failed_jobs WHERE id = ?", [$id]);

        return true;
    }

    /**
     * Retry all failed jobs.
     */
    public function retryAllFailedJobs(): int
    {
        $failedJobs = $this->db->query("SELECT id FROM failed_jobs")->fetchAll();

        $count = 0;
        foreach ($failedJobs as $job) {
            if ($this->retryFailedJob($job['id'])) {
                $count++;
            }
        }

        return $count;
    }

    /**
     * Delete a failed job.
     */
    public function deleteFailedJob(int $id): bool
    {
        $this->db->query("DELETE FROM failed_jobs WHERE id = ?", [$id]);
        return true;
    }

    /**
     * Get statistics for charts.
     */
    public function getStatistics(string $period = '24h'): array
    {
        return [
            'jobs_over_time' => [],
            'failed_over_time' => [],
        ];
    }

    /**
     * Get recent activity for dashboard.
     */
    public function getRecentActivity(int $limit = 10): array
    {
        return $this->db->query("
            SELECT id, queue, payload, attempts, reserved_at, created_at
            FROM jobs
            ORDER BY created_at DESC
            LIMIT ?
        ", [$limit])->fetchAll();
    }
}
