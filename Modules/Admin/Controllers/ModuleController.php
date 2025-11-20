<?php

namespace Modules\Admin\Controllers;

use App\Core\Application;
use Modules\RBAC\Models\Module;

class ModuleController
{
    public function index()
    {
        $app = Application::getInstance();
        $modules = Module::all();
        echo $app->view->render('admin/modules/index', ['title' => 'Modules', 'modules' => $modules]);
    }

    public function create()
    {
        $app = Application::getInstance();
        echo $app->view->render('admin/modules/create', ['title' => 'Créer un Module']);
    }

    public function store()
    {
        $name = sanitize($_POST['name'] ?? '', 'string');
        $slug = sanitize($_POST['slug'] ?? '', 'alphanumeric');
        $icon = sanitize($_POST['icon'] ?? '', 'string');
        $description = sanitize($_POST['description'] ?? '', 'string');
        $is_active = isset($_POST['is_active']) ? 1 : 0;

        if (empty($name) || empty($slug)) {
            flash('error', 'Le nom et le slug sont requis.');
            redirect('/admin/modules/create');
            exit;
        }

        $module = new Module();
        $module->name = $name;
        $module->slug = $slug;
        $module->icon = $icon;
        $module->description = $description;
        $module->is_active = $is_active;
        $module->save();

        flash('success', 'Module créé avec succès.');
        redirect('/admin/modules');
        exit;
    }

    public function edit(array $params = [])
    {
        $id = $params['id'] ?? null;
        if (!$id) {
            redirect('/admin/modules');
            return;
        }

        $app = Application::getInstance();
        $module = Module::find($id);

        if (!$module) {
            redirect('/admin/modules');
            return;
        }

        echo $app->view->render('admin/modules/edit', [
            'title' => 'Modifier le Module',
            'module' => $module
        ]);
    }

    public function update(array $params = [])
    {
        $id = $params['id'] ?? null;
        if (!$id) {
            redirect('/admin/modules');
            return;
        }

        $module = Module::find($id);
        if (!$module) {
            redirect('/admin/modules');
            exit;
        }

        $name = sanitize($_POST['name'] ?? '', 'string');
        $slug = sanitize($_POST['slug'] ?? '', 'alphanumeric');
        $icon = sanitize($_POST['icon'] ?? '', 'string');
        $description = sanitize($_POST['description'] ?? '', 'string');
        $is_active = isset($_POST['is_active']) ? 1 : 0;

        $module->name = $name;
        $module->slug = $slug;
        $module->icon = $icon;
        $module->description = $description;
        $module->is_active = $is_active;
        $module->save();

        flash('success', 'Module mis à jour avec succès.');
        redirect('/admin/modules');
        exit;
    }

    public function delete(array $params = [])
    {
        $id = $params['id'] ?? null;
        if (!$id) {
            redirect('/admin/modules');
            return;
        }

        $module = Module::find($id);
        if ($module) {
            // Note: Permissions with this module_id will have it set to NULL (ON DELETE SET NULL)
            $module->delete();
            flash('success', 'Module supprimé avec succès.');
        }

        redirect('/admin/modules');
        exit;
    }
}
