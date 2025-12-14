<?php
// Migration pour ajouter la permission Dashboard si manquante
if (!class_exists('App\\Core\\Application')) {
    require_once __DIR__ . '/../../bootstrap.php';
}

class AddDashboardPermissionMigration
{
    private $db;

    public function __construct()
    {
        $this->db = \App\Core\Database\Database::getInstance();
    }

    public function up()
    {
        $slug = 'access.dashboard';
        $name = 'Accès au Dashboard';
        $module = 'dashboard';
        $existing = $this->db->query('SELECT id FROM permissions WHERE slug = ?', [$slug])->fetch();
        if (!$existing) {
            $this->db->query('INSERT INTO permissions (name, slug, module_slug, created_at, updated_at) VALUES (?, ?, ?, NOW(), NOW())', [$name, $slug, $module]);
            echo "✓ Permission $name ($slug) créée.\n";
        } else {
            echo "✓ Permission $name ($slug) déjà existante.\n";
        }
    }

    public function down()
    {
        $slug = 'access.dashboard';
        $this->db->query('DELETE FROM permissions WHERE slug = ?', [$slug]);
        echo "✓ Permission access.dashboard supprimée.\n";
    }
}
