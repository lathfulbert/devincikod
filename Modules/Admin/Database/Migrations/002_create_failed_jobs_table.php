<?php

use App\Core\Database\Database;

return new class {
    public function up()
    {
        $db = Database::getInstance();

        $db->query("CREATE TABLE IF NOT EXISTS failed_jobs (
            id BIGINT AUTO_INCREMENT PRIMARY KEY,
            connection VARCHAR(255) NOT NULL,
            queue VARCHAR(255) NOT NULL,
            payload LONGTEXT NOT NULL,
            exception LONGTEXT NOT NULL,
            failed_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_queue (queue),
            INDEX idx_failed_at (failed_at)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

        echo "  ✓ Created table: failed_jobs\n";
    }

    public function down()
    {
        $db = Database::getInstance();
        $db->query("DROP TABLE IF EXISTS failed_jobs");
        echo "  ✓ Dropped table: failed_jobs\n";
    }
};
