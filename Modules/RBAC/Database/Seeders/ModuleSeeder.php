<?php

namespace Modules\RBAC\Database\Seeders;

use App\Core\Database\Database;
use Modules\RBAC\Models\Module;

class ModuleSeeder
{
    public function run(): void
    {
        $db = Database::getInstance();

        $modules = [
            [
                'name' => 'Gestion des Utilisateurs',
                'slug' => 'users-management',
                'icon' => 'users',
                'description' => 'Gestion des utilisateurs, profils et authentification',
                'is_active' => 1
            ],
            [
                'name' => 'Rôles et Permissions',
                'slug' => 'roles-permissions',
                'icon' => 'shield',
                'description' => 'Gestion des rôles, permissions et contrôle d\'accès',
                'is_active' => 1
            ],
            [
                'name' => 'Administration',
                'slug' => 'admin',
                'icon' => 'settings',
                'description' => 'Administration générale du système',
                'is_active' => 1
            ],
            [
                'name' => 'Modules',
                'slug' => 'modules',
                'icon' => 'package',
                'description' => 'Gestion des modules du système',
                'is_active' => 1
            ]
        ];

        foreach ($modules as $moduleData) {
            // Check if module already exists
            $check = $db->query(
                "SELECT id FROM modules WHERE slug = ?",
                [$moduleData['slug']]
            );

            if ($check->rowCount() === 0) {
                $module = new Module();
                $module->name = $moduleData['name'];
                $module->slug = $moduleData['slug'];
                $module->icon = $moduleData['icon'];
                $module->description = $moduleData['description'];
                $module->is_active = $moduleData['is_active'];
                $module->save();

                echo "Module créé : {$moduleData['name']}\n";
            } else {
                echo "Module déjà existant : {$moduleData['name']}\n";
            }
        }
    }
}
