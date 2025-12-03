<?php

use App\Core\Database\Database;

return new class {
    public function up()
    {
        $db = Database::getInstance();

        $db->query("CREATE TABLE IF NOT EXISTS sender_names (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(11) NOT NULL UNIQUE,
            operator VARCHAR(50) NULL COMMENT 'Operator that validated this sender name',
            status ENUM('pending', 'approved', 'rejected') NOT NULL DEFAULT 'pending',
            is_active TINYINT(1) NOT NULL DEFAULT 1,
            validation_date DATE NULL,
            notes TEXT NULL COMMENT 'Admin notes about this sender name',
            created_by BIGINT UNSIGNED NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            INDEX idx_name (name),
            INDEX idx_status (status),
            INDEX idx_is_active (is_active),
            FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

        echo "  ✓ Created table: sender_names\n";
    }

    public function down()
    {
        $db = Database::getInstance();
        $db->query("DROP TABLE IF EXISTS sender_names");
        echo "  ✓ Dropped table: sender_names\n";
    }
};
