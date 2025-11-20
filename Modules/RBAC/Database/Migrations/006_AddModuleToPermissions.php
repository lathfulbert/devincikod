<?php

namespace Modules\RBAC\Database\Migrations;

use App\Core\Database\Migration;
use App\Core\Database\Database;

class AddModuleToPermissions extends Migration
{
    public function up(): void
    {
        $db = Database::getInstance();

        // Add module_id column
        $db->query("ALTER TABLE permissions ADD COLUMN module_id BIGINT UNSIGNED NULL AFTER id");

        // Add description column
        $db->query("ALTER TABLE permissions ADD COLUMN description TEXT NULL AFTER slug");

        // Add foreign key constraint
        $db->query("ALTER TABLE permissions ADD CONSTRAINT fk_permissions_module 
                    FOREIGN KEY (module_id) REFERENCES modules(id) 
                    ON DELETE SET NULL");
    }

    public function down(): void
    {
        $db = Database::getInstance();

        // Drop foreign key first
        $db->query("ALTER TABLE permissions DROP FOREIGN KEY fk_permissions_module");

        // Drop columns
        $db->query("ALTER TABLE permissions DROP COLUMN module_id");
        $db->query("ALTER TABLE permissions DROP COLUMN description");
    }
}
