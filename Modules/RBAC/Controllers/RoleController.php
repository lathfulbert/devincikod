<?php

namespace Modules\RBAC\Controllers;

use App\Core\Application;
use Modules\RBAC\Models\Role;
use Modules\RBAC\Models\Permission;
use App\Core\Database\Database;

class RoleController
{
    public function index()
    {
        $app = Application::getInstance();
        $roles = Role::all();
        echo view('RBAC/admin/roles/index', ['title' => 'Roles', 'roles' => $roles]);
    }

    public function create()
    {
        $app = Application::getInstance();
        $db = Database::getInstance();

        // Get all permissions with their modules
        $sql = "SELECT p.*, m.name as module_name, m.icon as module_icon
                FROM permissions p
                LEFT JOIN modules m ON p.module_id = m.id
                ORDER BY m.name, p.name";
        $stmt = $db->query($sql);
        $permissions = $stmt->fetchAll(\PDO::FETCH_OBJ);

        // Group permissions by module
        $permissionsByModule = [];
        foreach ($permissions as $permission) {
            $moduleName = $permission->module_name ?? $permission->module ?? 'Sans module';
            if (!isset($permissionsByModule[$moduleName])) {
                $permissionsByModule[$moduleName] = [
                    'name' => $moduleName,
                    'icon' => $permission->module_icon ?? 'box',
                    'permissions' => []
                ];
            }
            $permissionsByModule[$moduleName]['permissions'][] = $permission;
        }

        // Sort modules alphabetically
        ksort($permissionsByModule);

        echo view('RBAC/admin/roles/create', [
            'title' => 'Créer un rôle',
            'permissions' => $permissions,
            'permissionsByModule' => $permissionsByModule
        ]);
    }

    public function store()
    {
        $name = $_POST['name'] ?? '';
        $slug = $_POST['slug'] ?? '';
        $permissionIds = $_POST['permissions'] ?? [];

        if (empty($name) || empty($slug)) {
            redirect('/admin/roles/create');
            exit;
        }

        $role = new Role();
        $role->name = $name;
        $role->slug = $slug;
        $role->save();

        if (!empty($permissionIds)) {
            $role->permissions()->attach($permissionIds);
        }

        redirect('/admin/roles');
        exit;
    }

    public function edit(array $params = [])
    {
        $id = $params['id'] ?? null;
        if (!$id) {
            redirect('/admin/roles');
            return;
        }

        $app = Application::getInstance();
        $db = Database::getInstance();
        $role = Role::find($id);

        // Get all permissions with their modules
        $sql = "SELECT p.*, m.name as module_name, m.icon as module_icon
                FROM permissions p
                LEFT JOIN modules m ON p.module_id = m.id
                ORDER BY m.name, p.name";
        $stmt = $db->query($sql);
        $permissions = $stmt->fetchAll(\PDO::FETCH_OBJ);

        // Group permissions by module
        $permissionsByModule = [];
        foreach ($permissions as $permission) {
            $moduleName = $permission->module_name ?? $permission->module ?? 'Sans module';
            if (!isset($permissionsByModule[$moduleName])) {
                $permissionsByModule[$moduleName] = [
                    'name' => $moduleName,
                    'icon' => $permission->module_icon ?? 'box',
                    'permissions' => []
                ];
            }
            $permissionsByModule[$moduleName]['permissions'][] = $permission;
        }

        // Sort modules alphabetically
        ksort($permissionsByModule);

        $rolePermissions = $role->permissions()->getResults();
        $rolePermissionIds = array_map(fn($p) => $p->id, $rolePermissions);

        echo view('RBAC/admin/roles/edit', [
            'title' => 'Éditer le rôle',
            'role' => $role,
            'permissions' => $permissions,
            'permissionsByModule' => $permissionsByModule,
            'rolePermissionIds' => $rolePermissionIds
        ]);
    }

    public function update(array $params = [])
    {
        $id = $params['id'] ?? null;
        if (!$id) {
            redirect('/admin/roles');
            return;
        }

        $role = Role::find($id);
        if (!$role) {
            redirect('/admin/roles');
            exit;
        }

        $name = $_POST['name'] ?? '';
        $slug = $_POST['slug'] ?? '';
        $permissionIds = $_POST['permissions'] ?? [];

        $role->name = $name;
        $role->slug = $slug;
        $role->save();

        $role->permissions()->sync($permissionIds);

        redirect('/admin/roles');
        exit;
    }

    public function delete(array $params = [])
    {
        $id = $params['id'] ?? null;
        if (!$id) {
            redirect('/admin/roles');
            return;
        }

        $role = Role::find($id);
        if ($role) {
            $role->permissions()->detach();
            $role->users()->detach();
            $role->delete();
        }
        redirect('/admin/roles');
        exit;
    }
}
