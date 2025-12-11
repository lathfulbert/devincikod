<?php

namespace Modules\SmsCore\Database\Seeds;

use App\Core\Database\Database;

class SmsPermissionsSeeder
{
    protected Database $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function run()
    {
        echo "Creating SMS permissions...\n";

        // Module information
        $moduleInfo = [
            'module' => 'SMS',
            'module_slug' => 'sms-core',
            'module_id' => null  // Will be set when modules table is created
        ];

        // Define SMS permissions with their roles
        $permissions = [
            // Dashboard
            [
                'name' => 'sms.dashboard.view',
                'description' => 'Voir le tableau de bord SMS',
                'roles' => ['admin', 'owner', 'user']
            ],

            // Send SMS
            [
                'name' => 'sms.send',
                'description' => 'Envoyer des SMS',
                'roles' => ['admin', 'owner', 'user']
            ],
            [
                'name' => 'sms.send.view',
                'description' => 'Voir la page d\'envoi SMS',
                'roles' => ['admin', 'owner', 'user']
            ],

            // Bulk SMS
            [
                'name' => 'sms.bulk',
                'description' => 'Envoyer des SMS en masse',
                'roles' => ['admin', 'owner', 'user']
            ],
            [
                'name' => 'sms.bulk.view',
                'description' => 'Voir la page d\'envoi en masse',
                'roles' => ['admin', 'owner', 'user']
            ],

            // Campaigns
            [
                'name' => 'sms.campaigns.view',
                'description' => 'Voir les campagnes SMS',
                'roles' => ['admin', 'owner', 'user']
            ],
            [
                'name' => 'sms.campaigns.create',
                'description' => 'Créer des campagnes SMS',
                'roles' => ['admin', 'owner']
            ],
            [
                'name' => 'sms.campaigns.edit',
                'description' => 'Modifier les campagnes SMS',
                'roles' => ['admin', 'owner']
            ],
            [
                'name' => 'sms.campaigns.delete',
                'description' => 'Supprimer les campagnes SMS',
                'roles' => ['admin', 'owner']
            ],

            // History
            [
                'name' => 'sms.history.view',
                'description' => 'Voir l\'historique des SMS',
                'roles' => ['admin', 'owner', 'user']
            ],
            [
                'name' => 'sms.history.view_all',
                'description' => 'Voir l\'historique de tous les utilisateurs',
                'roles' => ['admin', 'owner']
            ],

            // Statistics
            [
                'name' => 'sms.statistics.view',
                'description' => 'Voir les statistiques SMS',
                'roles' => ['admin', 'owner', 'user']
            ],
            [
                'name' => 'sms.statistics.view_all',
                'description' => 'Voir les statistiques de tous les utilisateurs',
                'roles' => ['admin', 'owner']
            ],

            // Sender Names
            [
                'name' => 'sms.sender_names.view',
                'description' => 'Voir les noms d\'expéditeur',
                'roles' => ['admin', 'owner', 'user']
            ],
            [
                'name' => 'sms.sender_names.manage',
                'description' => 'Gérer les noms d\'expéditeur',
                'roles' => ['admin', 'owner']
            ],
            [
                'name' => 'sms.sender_names.assign',
                'description' => 'Assigner les noms d\'expéditeur aux utilisateurs',
                'roles' => ['admin', 'owner']
            ],

            // Pricing (Tarification)
            [
                'name' => 'sms.pricing.view',
                'description' => 'Voir les tarifs SMS',
                'roles' => ['admin', 'owner']
            ],
            [
                'name' => 'sms.pricing.manage',
                'description' => 'Gérer les tarifs SMS',
                'roles' => ['admin']
            ],

            // Billing (Facturation)
            [
                'name' => 'sms.billing.view',
                'description' => 'Voir la facturation SMS',
                'roles' => ['admin', 'owner', 'user']
            ],
            [
                'name' => 'sms.billing.view_all',
                'description' => 'Voir la facturation de tous les utilisateurs',
                'roles' => ['admin', 'owner']
            ],

            // Provider Statistics (Fournisseurs)
            [
                'name' => 'sms.providers.view',
                'description' => 'Voir les statistiques des fournisseurs',
                'roles' => ['admin', 'owner']
            ],

            // API Documentation
            [
                'name' => 'sms.api.docs',
                'description' => 'Voir la documentation API SMS',
                'roles' => ['admin', 'owner', 'user']
            ],

            // API Keys
            [
                'name' => 'sms.api.keys.view',
                'description' => 'Voir ses clés API',
                'roles' => ['admin', 'owner', 'user']
            ],
            [
                'name' => 'sms.api.keys.manage',
                'description' => 'Gérer ses clés API',
                'roles' => ['admin', 'owner', 'user']
            ],
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
                // Create permission with module information
                // Generate slug from name
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

                // Update existing permission with module information if not set
                $this->db->query(
                    "UPDATE permissions
                     SET module = COALESCE(module, ?),
                         module_slug = COALESCE(module_slug, ?),
                         module_id = COALESCE(module_id, ?),
                         updated_at = NOW()
                     WHERE id = ?",
                    [
                        $moduleInfo['module'],
                        $moduleInfo['module_slug'],
                        $moduleInfo['module_id'],
                        $permissionId
                    ]
                );
                echo "  - Permission exists: {$perm['name']} (module info updated)\n";
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

        echo "\n✓ SMS permissions created successfully!\n";
    }
}

// Run the seeder if executed directly
if (php_sapi_name() === 'cli' && basename(__FILE__) === basename($_SERVER['SCRIPT_FILENAME'])) {
    require_once __DIR__ . '/../../../../vendor/autoload.php';
    $seeder = new SmsPermissionsSeeder();
    $seeder->run();
}
