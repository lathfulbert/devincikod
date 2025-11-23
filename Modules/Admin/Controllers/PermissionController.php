<?php

namespace Modules\Admin\Controllers;

use App\Core\Application;
use Modules\RBAC\Models\Permission;
use Modules\RBAC\Models\Module;

class PermissionController
{
    public function index()
    {
        $app = Application::getInstance();
        $permissions = Permission::all();
        echo $app->view->render('backend/permissions/index', ['title' => 'Permissions', 'permissions' => $permissions]);
    }

    public function create()
    {
        $app = Application::getInstance();
        $modules = Module::all();
        echo $app->view->render('backend/permissions/create', [
            'title' => 'Créer une Permission',
            'modules' => $modules
        ]);
    }

    public function store()
    {
        $name = sanitize($_POST['name'] ?? '', 'string');
        $slug = sanitize($_POST['slug'] ?? '', 'alphanumeric');
        $module_id = !empty($_POST['module_id']) ? (int)$_POST['module_id'] : null;
        $description = sanitize($_POST['description'] ?? '', 'string');

        if (empty($name) || empty($slug)) {
            flash('error', 'Le nom et le slug sont requis.');
            redirect('/admin/permissions/create');
            exit;
        }

        $permission = new Permission();
        $permission->name = $name;
        $permission->slug = $slug;
        $permission->module_id = $module_id;
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

        echo $app->view->render('backend/permissions/edit', [
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
        $module_id = !empty($_POST['module_id']) ? (int)$_POST['module_id'] : null;
        $description = sanitize($_POST['description'] ?? '', 'string');

        $permission->name = $name;
        $permission->slug = $slug;
        $permission->module_id = $module_id;
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
