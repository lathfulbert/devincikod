<?php

use App\Core\Database\Migration;
use App\Core\Database\Database;

return new class extends Migration
{
    public function up(): void
    {
        $db = Database::getInstance();

        // Add module column to permissions table
        $sql = "ALTER TABLE permissions ADD COLUMN module VARCHAR(255) NULL AFTER slug";

        try {
            $db->query($sql);
        } catch (\Exception $e) {
            // Column might already exist, ignore the error
            if (!str_contains($e->getMessage(), "Duplicate column name")) {
                throw $e;
            }
        }
    }

    public function down(): void
    {
        $db = Database::getInstance();

        // Remove module column from permissions table
        $sql = "ALTER TABLE permissions DROP COLUMN module";

        try {
            $db->query($sql);
        } catch (\Exception $e) {
            // Column might not exist, ignore the error
            if (!str_contains($e->getMessage(), "check that column/key exists")) {
                throw $e;
            }
        }
    }
};
