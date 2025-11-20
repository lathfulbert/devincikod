<?php

namespace Modules\Admin\Controllers;

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
        echo $app->view->render('admin/roles/index', ['title' => 'Roles', 'roles' => $roles]);
    }

    public function create()
    {
        $app = Application::getInstance();
        $permissions = Permission::all();
        echo $app->view->render('admin/roles/create', ['title' => 'Create Role', 'permissions' => $permissions]);
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
            $db = Database::getInstance();
            foreach ($permissionIds as $permId) {
                $db->query("INSERT INTO role_permissions (role_id, permission_id) VALUES (?, ?)", [$role->id, $permId]);
            }
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
        $role = Role::find($id);
        $permissions = Permission::all();
        $rolePermissions = $role->permissions();
        $rolePermissionIds = array_map(fn($p) => $p->id, $rolePermissions);

        echo $app->view->render('admin/roles/edit', [
            'title' => 'Edit Role', 
            'role' => $role, 
            'permissions' => $permissions,
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

        $db = Database::getInstance();
        $db->query("DELETE FROM role_permissions WHERE role_id = ?", [$role->id]);

        if (!empty($permissionIds)) {
            foreach ($permissionIds as $permId) {
                $db->query("INSERT INTO role_permissions (role_id, permission_id) VALUES (?, ?)", [$role->id, $permId]);
            }
        }

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
            $db = Database::getInstance();
            $db->query("DELETE FROM role_permissions WHERE role_id = ?", [$role->id]);
            $db->query("DELETE FROM user_roles WHERE role_id = ?", [$role->id]);
            $role->delete();
        }
        redirect('/admin/roles');
        exit;
    }
}
