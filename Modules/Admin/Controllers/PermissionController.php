<?php

namespace Modules\Admin\Controllers;

use App\Core\Application;
use Modules\RBAC\Models\Permission;

class PermissionController
{
    public function index()
    {
        $app = Application::getInstance();
        $permissions = Permission::all();
        echo $app->view->render('admin/permissions/index', ['title' => 'Permissions', 'permissions' => $permissions]);
    }

    public function create()
    {
        $app = Application::getInstance();
        echo $app->view->render('admin/permissions/create', ['title' => 'Create Permission']);
    }

    public function store()
    {
        $name = $_POST['name'] ?? '';
        $slug = $_POST['slug'] ?? '';

        if (empty($name) || empty($slug)) {
            redirect('/admin/permissions/create');
            exit;
        }

        $permission = new Permission();
        $permission->name = $name;
        $permission->slug = $slug;
        $permission->save();

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
        echo $app->view->render('admin/permissions/edit', ['title' => 'Edit Permission', 'permission' => $permission]);
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

        $name = $_POST['name'] ?? '';
        $slug = $_POST['slug'] ?? '';

        $permission->name = $name;
        $permission->slug = $slug;
        $permission->save();

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
