<?php

use App\Core\Database\Database;

return new class {
    public function up()
    {
        $db = Database::getInstance();

        $db->query("CREATE TABLE IF NOT EXISTS cron_tasks (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(255) NOT NULL UNIQUE,
            module VARCHAR(255) NOT NULL,
            class VARCHAR(255) NOT NULL,
            expression VARCHAR(255) NOT NULL,
            description TEXT NULL,
            enabled TINYINT(1) NOT NULL DEFAULT 1,
            last_run_at DATETIME NULL,
            next_run_at DATETIME NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            INDEX idx_enabled (enabled),
            INDEX idx_next_run_at (next_run_at),
            INDEX idx_module (module)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

        echo "  ✓ Created table: cron_tasks\n";
    }

    public function down()
    {
        $db = Database::getInstance();
        $db->query("DROP TABLE IF EXISTS cron_tasks");
        echo "  ✓ Dropped table: cron_tasks\n";
    }
};
