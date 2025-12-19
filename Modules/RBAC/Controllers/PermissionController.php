<?php

namespace Modules\RBAC\Controllers;

use App\Core\Application;
use Modules\RBAC\Models\Permission;
use Modules\RBAC\Models\Module;

class PermissionController
{
    public function index()
    {
        $app = Application::getInstance();
        $db = \App\Core\Database\Database::getInstance();

        // Get filter parameters
        $moduleSlug = $_GET['module_slug'] ?? null;
        $roleId = $_GET['role_id'] ?? null;

        // Build query with JOIN to get module name
        $sql = "SELECT p.*, m.name as module_name
                FROM permissions p
                LEFT JOIN modules m ON p.module_slug = m.slug";

        $params = [];
        $conditions = [];

        if ($moduleSlug) {
            $conditions[] = "p.module_slug = ?";
            $params[] = $moduleSlug;
        }

        if ($roleId) {
            $conditions[] = "p.id IN (SELECT permission_id FROM role_permissions WHERE role_id = ?)";
            $params[] = $roleId;
        }

        if (!empty($conditions)) {
            $sql .= " WHERE " . implode(" AND ", $conditions);
        }

        $sql .= " ORDER BY p.id";

        $stmt = $db->query($sql, $params);
        $permissions = $stmt->fetchAll(\PDO::FETCH_OBJ);

        // Get all modules and roles for filter dropdowns
        $modules = \Modules\RBAC\Models\Module::all();
        $roles = \Modules\RBAC\Models\Role::all();

        echo view('RBAC/admin/permissions/index', [
            'title' => 'Permissions',
            'permissions' => $permissions,
            'modules' => $modules,
            'roles' => $roles,
            'selectedModuleSlug' => $moduleSlug,
            'selectedRoleId' => $roleId
        ]);
    }

    public function create()
    {
        $app = Application::getInstance();
        $modules = Module::all();
        echo view('RBAC/admin/permissions/create', [
            'title' => 'Créer une Permission',
            'modules' => $modules
        ]);
    }

    public function store()
    {
        $name = sanitize($_POST['name'] ?? '', 'string');
        $slug = sanitize($_POST['slug'] ?? '', 'alphanumeric');
        $module_slug = !empty($_POST['module_slug']) ? sanitize($_POST['module_slug'], 'alphanumeric') : null;
        $description = sanitize($_POST['description'] ?? '', 'string');

        if (empty($name) || empty($slug)) {
            flash('error', 'Le nom et le slug sont requis.');
            redirect('/admin/permissions/create');
            exit;
        }

        $permission = new Permission();
        $permission->name = $name;
        $permission->slug = $slug;
        $permission->module_slug = $module_slug;
        $permission->description = $description;
        $permission->save();

        flash('success', 'Permission créée avec succès.');
        redirect('/admin/permissions');
        exit;
    }

    public function edit(array $params = [])
    {
        $id = $params['id'] ?? null;
        if (!$id) {
            redirect('/admin/permissions');
            return;
        }

        $app = Application::getInstance();
        $permission = Permission::find($id);
        $modules = Module::all();

        echo view('RBAC/admin/permissions/edit', [
            'title' => 'Modifier la Permission',
            'permission' => $permission,
            'modules' => $modules
        ]);
    }

    public function update(array $params = [])
    {
        $id = $params['id'] ?? null;
        if (!$id) {
            redirect('/admin/permissions');
            return;
        }

        $permission = Permission::find($id);
        if (!$permission) {
            redirect('/admin/permissions');
            exit;
        }

        $name = sanitize($_POST['name'] ?? '', 'string');
        $slug = sanitize($_POST['slug'] ?? '', 'alphanumeric');
        $module_slug = !empty($_POST['module_slug']) ? sanitize($_POST['module_slug'], 'alphanumeric') : null;
        $description = sanitize($_POST['description'] ?? '', 'string');

        $permission->name = $name;
        $permission->slug = $slug;
        $permission->module_slug = $module_slug;
        $permission->description = $description;
        $permission->save();

        flash('success', 'Permission mise à jour avec succès.');
        redirect('/admin/permissions');
        exit;
    }

    public function delete(array $params = [])
    {
        $id = $params['id'] ?? null;
        if (!$id) {
            redirect('/admin/permissions');
            return;
        }

        $permission = Permission::find($id);
        if ($permission) {
            $permission->roles()->detach();
            $permission->delete();
        }
        redirect('/admin/permissions');
        exit;
    }
}
