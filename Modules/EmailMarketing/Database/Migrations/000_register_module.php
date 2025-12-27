<?php

use App\Core\Database\Migration;
use App\Core\Database\Database;

return new class extends Migration
{
    public function up(): void
    {
        $db = Database::getInstance();

        // Check if module exists
        $stmt = $db->query("SELECT id FROM modules WHERE name = ?", ['EmailMarketing']);
        if ($stmt->fetch()) {
            return;
        }

        // Check if incorrect name exists and update it
        $stmt = $db->query("SELECT id FROM modules WHERE name = ?", ['Email Marketing']);
        $existing = $stmt->fetch();

        if ($existing) {
            $db->query("UPDATE modules SET name = ? WHERE id = ?", ['EmailMarketing', $existing['id']]);
        } else {
            // Insert new
            $sql = "INSERT INTO modules (name, slug, version, icon, description, author, is_active, is_installed)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?)";

            $db->query($sql, [
                'EmailMarketing',
                'email-marketing',
                '1.0.0',
                'mail',
                'Module de marketing par email avec campagnes, templates, workflows et analytics',
                'SunuFramework Team',
                1,
                1
            ]);
        }
    }

    public function down(): void
    {
        // We generally don't delete the module record in down() as it might have been created manually
        // But for completeness:
        // $this->db->query("DELETE FROM modules WHERE name = ?", ['EmailMarketing']);
    }
};
