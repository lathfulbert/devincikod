<?php

use App\Core\Database\Database;

return new class {
    public function up()
    {
        $db = Database::getInstance();

        $db->query("CREATE TABLE IF NOT EXISTS jobs (
            id BIGINT AUTO_INCREMENT PRIMARY KEY,
            queue VARCHAR(255) NOT NULL DEFAULT 'default',
            payload LONGTEXT NOT NULL,
            attempts TINYINT UNSIGNED NOT NULL DEFAULT 0,
            reserved_at INT UNSIGNED NULL,
            available_at INT UNSIGNED NOT NULL,
            created_at INT UNSIGNED NOT NULL,
            INDEX idx_queue (queue),
            INDEX idx_reserved_at (reserved_at)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

        echo "  ✓ Created table: jobs\n";
    }

    public function down()
    {
        $db = Database::getInstance();
        $db->query("DROP TABLE IF EXISTS jobs");
        echo "  ✓ Dropped table: jobs\n";
    }
};
