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

    public function edit($id)
    {
        $app = Application::getInstance();
        $permission = Permission::find($id);
        echo $app->view->render('admin/permissions/edit', ['title' => 'Edit Permission', 'permission' => $permission]);
    }

    public function update($id)
    {
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

    public function delete($id)
    {
        $permission = Permission::find($id);
        if ($permission) {
            // TODO: Remove from role_permissions first? Or rely on FK cascade if exists?
            // For now, manual cleanup might be safer if no FK cascade
            $db = \App\Core\Database\Database::getInstance();
            $db->query("DELETE FROM role_permissions WHERE permission_id = ?", [$permission->id]);
            $permission->delete();
        }
        redirect('/admin/permissions');
        exit;
    }
}
