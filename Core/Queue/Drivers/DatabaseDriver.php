<?php

namespace App\Core\Queue\Drivers;

use App\Core\Queue\Contracts\QueueDriverContract;
use App\Core\Database\Database;

/**
 * Class DatabaseDriver
 * 
 * Database-backed queue driver using the `jobs` table.
 * This is the default and most reliable driver.
 */
class DatabaseDriver implements QueueDriverContract
{
    private Database $db;
    private string $table;
    private string $failedTable;

    public function __construct(array $config)
    {
        $this->db = Database::getInstance();
        $this->table = $config['connections']['database']['table'] ?? 'jobs';
        $this->failedTable = $config['connections']['database']['failed_table'] ?? 'failed_jobs';
    }

    /**
     * Push a new job onto the queue.
     */
    public function push(string $queue, array $payload): bool
    {
        try {
            $sql = "INSERT INTO {$this->table} (queue, payload, attempts, available_at, created_at) 
                    VALUES (?, ?, ?, ?, ?)";

            $this->db->query($sql, [
                $queue,
                json_encode($payload),
                $payload['attempts'] ?? 0,
                $payload['available_at'] ?? time(),
                $payload['created_at'] ?? time(),
            ]);

            return true;
        } catch (\Throwable $e) {
            echo "DEBUG: Exception in push: " . $e->getMessage() . "\n";
            echo "  File: " . $e->getFile() . ":" . $e->getLine() . "\n";
            error_log("Failed to push job to queue: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Pop the next job off of the queue.
     */
    public function pop(string $queue): ?array
    {
        try {
            // Start transaction to avoid race conditions
            $this->db->beginTransaction();

            // Find the next available job
            $sql = "SELECT * FROM {$this->table} 
                    WHERE queue = ? 
                      AND (reserved_at IS NULL OR reserved_at < ?) 
                      AND available_at <= ? 
                    ORDER BY id ASC 
                    LIMIT 1 
                    FOR UPDATE";

            $job = $this->db->query($sql, [
                $queue,
                time() - 300, // Jobs reserved more than 5 min ago are considered abandoned
                time(),
            ])->fetch(\PDO::FETCH_ASSOC);

            if (!$job) {
                $this->db->commit();
                return null;
            }

            // Reserve the job
            $sql = "UPDATE {$this->table} 
                    SET reserved_at = ?, attempts = attempts + 1 
                    WHERE id = ?";

            $this->db->query($sql, [time(), $job['id']]);

            $this->db->commit();

            // Decode payload
            $job['payload'] = json_decode($job['payload'], true);

            return $job;
        } catch (\Throwable $e) {
            $this->db->rollback();
            error_log("Failed to pop job from queue: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Release a reserved job back onto the queue.
     */
    public function release(string $queue, array $job, int $delay = 0): bool
    {
        try {
            $sql = "UPDATE {$this->table} 
                    SET reserved_at = NULL, available_at = ? 
                    WHERE id = ?";

            $this->db->query($sql, [
                time() + $delay,
                $job['id'],
            ]);

            return true;
        } catch (\Throwable $e) {
            error_log("Failed to release job: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Delete a job from the queue.
     */
    public function delete(string $queue, string $id): bool
    {
        try {
            $sql = "DELETE FROM {$this->table} WHERE id = ?";
            $this->db->query($sql, [$id]);
            return true;
        } catch (\Throwable $e) {
            error_log("Failed to delete job: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Get the size of the queue.
     */
    public function size(string $queue): int
    {
        try {
            $sql = "SELECT COUNT(*) as count FROM {$this->table} 
                    WHERE queue = ? AND reserved_at IS NULL";

            $result = $this->db->query($sql, [$queue])->fetch(\PDO::FETCH_ASSOC);

            return (int)($result['count'] ?? 0);
        } catch (\Throwable $e) {
            error_log("Failed to get queue size: " . $e->getMessage());
            return 0;
        }
    }

    /**
     * Clear all jobs from the queue.
     */
    public function clear(string $queue): bool
    {
        try {
            $sql = "DELETE FROM {$this->table} WHERE queue = ?";
            $this->db->query($sql, [$queue]);
            return true;
        } catch (\Throwable $e) {
            error_log("Failed to clear queue: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Store a failed job.
     */
    public function storeFailed(string $queue, array $payload, \Throwable $exception): bool
    {
        try {
            $sql = "INSERT INTO {$this->failedTable} (connection, queue, payload, exception, failed_at) 
                    VALUES (?, ?, ?, ?, NOW())";

            $this->db->query($sql, [
                'database',
                $queue,
                json_encode($payload),
                $exception->getMessage() . "\n" . $exception->getTraceAsString(),
            ]);

            return true;
        } catch (\Throwable $e) {
            error_log("Failed to store failed job: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Get all failed jobs.
     */
    public function getFailedJobs(int $limit = 100): array
    {
        try {
            $sql = "SELECT * FROM {$this->failedTable} 
                    ORDER BY failed_at DESC 
                    LIMIT ?";

            $result = $this->db->query($sql, [$limit]);

            return $result->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\Throwable $e) {
            error_log("Failed to get failed jobs: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Retry a failed job.
     */
    public function retryFailedJob(int $id): bool
    {
        try {
            // Get the failed job
            $sql = "SELECT * FROM {$this->failedTable} WHERE id = ?";
            $failed = $this->db->query($sql, [$id])->fetch(\PDO::FETCH_ASSOC);

            if (!$failed) {
                return false;
            }

            // Re-queue the job
            $payload = json_decode($failed['payload'], true);
            $this->push($failed['queue'], $payload);

            // Delete from failed jobs
            $sql = "DELETE FROM {$this->failedTable} WHERE id = ?";
            $this->db->query($sql, [$id]);

            return true;
        } catch (\Throwable $e) {
            error_log("Failed to retry failed job: " . $e->getMessage());
            return false;
        }
    }
}
