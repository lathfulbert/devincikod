<?php

use App\Core\Database\Database;

return new class {
    public function up()
    {
        $db = Database::getInstance();

        $db->query("CREATE TABLE IF NOT EXISTS user_sender_names (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id BIGINT UNSIGNED NOT NULL,
            sender_name_id INT NOT NULL,
            assigned_by BIGINT UNSIGNED NULL COMMENT 'Admin who assigned this sender name',
            assigned_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_user_id (user_id),
            INDEX idx_sender_name_id (sender_name_id),
            UNIQUE KEY unique_user_sender (user_id, sender_name_id),
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
            FOREIGN KEY (sender_name_id) REFERENCES sender_names(id) ON DELETE CASCADE,
            FOREIGN KEY (assigned_by) REFERENCES users(id) ON DELETE SET NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

        echo "  ✓ Created table: user_sender_names\n";
    }

    public function down()
    {
        $db = Database::getInstance();
        $db->query("DROP TABLE IF EXISTS user_sender_names");
        echo "  ✓ Dropped table: user_sender_names\n";
    }
};
