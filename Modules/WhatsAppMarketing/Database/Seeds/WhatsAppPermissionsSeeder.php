<?php

namespace Modules\WhatsAppMarketing\Database\Seeds;

use App\Core\Database\Database;

class WhatsAppPermissionsSeeder
{
    protected Database $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function run()
    {
        echo "Creating WhatsApp Marketing permissions...\n";

        // Module information
        $moduleInfo = [
            'module' => 'WhatsAppMarketing',
            'module_slug' => 'whatsapp-marketing',
            'module_id' => null
        ];

        // Define Permissions
        $permissions = [
            // Dashboard
            [
                'name' => 'whatsapp.dashboard.view',
                'description' => 'Voir le tableau de bord WhatsApp',
                'roles' => ['admin', 'owner', 'user']
            ],

            // Gateways (Admin only usually)
            [
                'name' => 'whatsapp.gateways.view',
                'description' => 'Voir les passerelles WhatsApp',
                'roles' => ['admin', 'owner']
            ],
            [
                'name' => 'whatsapp.gateways.manage',
                'description' => 'Gérer les passerelles WhatsApp (Ajout/Modif/Suppr)',
                'roles' => ['admin', 'owner']
            ],

            // Templates
            [
                'name' => 'whatsapp.templates.view',
                'description' => 'Voir les templates WhatsApp',
                'roles' => ['admin', 'owner', 'user']
            ],
            [
                'name' => 'whatsapp.templates.sync',
                'description' => 'Synchroniser les templates WhatsApp',
                'roles' => ['admin', 'owner']
            ],

            // Campaigns
            [
                'name' => 'whatsapp.campaigns.view',
                'description' => 'Voir les campagnes WhatsApp',
                'roles' => ['admin', 'owner', 'user']
            ],
            [
                'name' => 'whatsapp.campaigns.create',
                'description' => 'Créer des campagnes WhatsApp',
                'roles' => ['admin', 'owner'] // Maybe user too if allowed?
            ],
            [
                'name' => 'whatsapp.campaigns.edit',
                'description' => 'Modifier les campagnes WhatsApp',
                'roles' => ['admin', 'owner']
            ],
            [
                'name' => 'whatsapp.campaigns.delete',
                'description' => 'Supprimer les campagnes WhatsApp',
                'roles' => ['admin', 'owner']
            ],

            // Messages/Conversations
            [
                'name' => 'whatsapp.messages.view',
                'description' => 'Voir l\'historique des messages WhatsApp',
                'roles' => ['admin', 'owner', 'user']
            ]
        ];

        // Get role IDs
        $roles = $this->db->query("SELECT id, name FROM roles WHERE name IN ('admin', 'owner', 'user')")->fetchAll();
        $roleMap = [];
        foreach ($roles as $role) {
            $roleMap[$role['name']] = $role['id'];
        }

        foreach ($permissions as $perm) {
            // Check if permission exists
            $existing = $this->db->query(
                "SELECT id FROM permissions WHERE name = ?",
                [$perm['name']]
            )->fetch();

            if (!$existing) {
                $slug = str_replace('.', '-', $perm['name']);

                $this->db->query(
                    "INSERT INTO permissions (name, slug, description, module, module_slug, module_id, created_at, updated_at)
                     VALUES (?, ?, ?, ?, ?, ?, NOW(), NOW())",
                    [
                        $perm['name'],
                        $slug,
                        $perm['description'],
                        $moduleInfo['module'],
                        $moduleInfo['module_slug'],
                        $moduleInfo['module_id']
                    ]
                );
                $permissionId = $this->db->lastInsertId();
                echo "  ✓ Created permission: {$perm['name']}\n";
            } else {
                $permissionId = $existing['id'];
                echo "  - Permission exists: {$perm['name']}\n";
            }

            // Assign to roles
            foreach ($perm['roles'] as $roleName) {
                if (isset($roleMap[$roleName])) {
                    $roleId = $roleMap[$roleName];

                    // Check if already assigned
                    $assigned = $this->db->query(
                        "SELECT id FROM role_permissions WHERE role_id = ? AND permission_id = ?",
                        [$roleId, $permissionId]
                    )->fetch();

                    if (!$assigned) {
                        $this->db->query(
                            "INSERT INTO role_permissions (role_id, permission_id, created_at) VALUES (?, ?, NOW())",
                            [$roleId, $permissionId]
                        );
                        echo "    → Assigned to role: {$roleName}\n";
                    }
                }
            }
        }

        echo "\n✓ WhatsApp Marketing permissions created successfully!\n";
    }
}
