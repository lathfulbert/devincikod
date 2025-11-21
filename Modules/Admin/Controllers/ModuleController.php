<?php

namespace Modules\Admin\Controllers;

use App\Core\Application;
use App\Core\Module\ModuleManager;

class ModuleController
{
    protected ModuleManager $moduleManager;

    public function __construct()
    {
        $this->moduleManager = Application::getInstance()->moduleManager;
    }

    /**
     * List all modules.
     */
    public function index()
    {
        $app = Application::getInstance();

        // Ensure we have the latest state
        $this->moduleManager->discover();
        $this->moduleManager->syncToRegistry();

        $modules = $this->moduleManager->getAllModules();
        $registry = $this->moduleManager->getRegistry();

        // Prepare view data
        $viewData = [];
        foreach ($modules as $name => $module) {
            $viewData[] = [
                'name' => $module->getName(),
                'version' => $module->getVersion(),
                'description' => $module->getDescription(),
                'author' => $module->getAuthor(),
                'is_enabled' => $registry->isEnabled($name),
                'is_installed' => $registry->isInstalled($name),
            ];
        }

        echo $app->view->render('admin/modules/index', [
            'title' => 'Gestion des Modules',
            'modules' => $viewData
        ]);
    }

    /**
     * Enable a module.
     */
    public function enable(array $params = [])
    {
        $name = $params['name'] ?? $_POST['name'] ?? null;

        if (!$name) {
            flash('error', 'Nom du module manquant.');
            redirect('/admin/modules');
            return;
        }

        if ($this->moduleManager->activateModule($name)) {
            flash('success', "Module {$name} activé avec succès.");
        } else {
            flash('error', "Impossible d'activer le module {$name}.");
        }

        redirect('/admin/modules');
    }

    /**
     * Disable a module.
     */
    public function disable(array $params = [])
    {
        $name = $params['name'] ?? $_POST['name'] ?? null;

        if (!$name) {
            flash('error', 'Nom du module manquant.');
            redirect('/admin/modules');
            return;
        }

        if ($this->moduleManager->deactivateModule($name)) {
            flash('success', "Module {$name} désactivé avec succès.");
        } else {
            flash('error', "Impossible de désactiver le module {$name}.");
        }

        redirect('/admin/modules');
    }

    /**
     * Install a module.
     */
    public function install(array $params = [])
    {
        $name = $params['name'] ?? $_POST['name'] ?? null;

        if (!$name) {
            flash('error', 'Nom du module manquant.');
            redirect('/admin/modules');
            return;
        }

        if ($this->moduleManager->installModule($name)) {
            flash('success', "Module {$name} installé avec succès.");
        } else {
            flash('error', "Impossible d'installer le module {$name}.");
        }

        redirect('/admin/modules');
    }

    /**
     * Uninstall a module.
     */
    public function uninstall(array $params = [])
    {
        $name = $params['name'] ?? $_POST['name'] ?? null;

        if (!$name) {
            flash('error', 'Nom du module manquant.');
            redirect('/admin/modules');
            return;
        }

        if ($this->moduleManager->uninstallModule($name)) {
            flash('success', "Module {$name} désinstallé avec succès.");
        } else {
            flash('error', "Impossible de désinstaller le module {$name}.");
        }

        redirect('/admin/modules');
    }
}
