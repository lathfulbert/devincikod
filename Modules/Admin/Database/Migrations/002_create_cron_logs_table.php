<?php

use App\Core\Database\Database;

return new class {
    public function up()
    {
        $db = Database::getInstance();

        $db->query("CREATE TABLE IF NOT EXISTS cron_logs (
            id BIGINT AUTO_INCREMENT PRIMARY KEY,
            task_id INT NOT NULL,
            started_at DATETIME NOT NULL,
            finished_at DATETIME NULL,
            status ENUM('running', 'success', 'failed') NOT NULL DEFAULT 'running',
            output TEXT NULL,
            error TEXT NULL,
            duration_ms INT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_task_id (task_id),
            INDEX idx_status (status),
            INDEX idx_started_at (started_at),
            FOREIGN KEY (task_id) REFERENCES cron_tasks(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

        echo "  ✓ Created table: cron_logs\n";
    }

    public function down()
    {
        $db = Database::getInstance();
        $db->query("DROP TABLE IF EXISTS cron_logs");
        echo "  ✓ Dropped table: cron_logs\n";
    }
};
