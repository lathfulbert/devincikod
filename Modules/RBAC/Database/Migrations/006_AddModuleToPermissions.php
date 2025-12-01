<?php

namespace Modules\RBAC\Database\Migrations;

use App\Core\Database\Migration;
use App\Core\Database\Database;

class AddModuleToPermissions extends Migration
{
    public function up(): void
    {
        $db = Database::getInstance();

        // Add module_slug column
        $db->query("ALTER TABLE permissions ADD COLUMN module_slug VARCHAR(100) NULL AFTER id");

        // Add description column if not exists
        $db->query("ALTER TABLE permissions ADD COLUMN description TEXT NULL AFTER slug");

        // Add index on module_slug for better performance
        $db->query("ALTER TABLE permissions ADD INDEX idx_permissions_module_slug (module_slug)");
    }

    public function down(): void
    {
        $db = Database::getInstance();

        // Drop index first
        $db->query("ALTER TABLE permissions DROP INDEX idx_permissions_module_slug");

        // Drop columns
        $db->query("ALTER TABLE permissions DROP COLUMN module_slug");
        $db->query("ALTER TABLE permissions DROP COLUMN description");
    }
}
