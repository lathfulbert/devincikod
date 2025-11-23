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

        echo $app->view->render('backend/modules/index', [
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
            $_SESSION['flash']['danger'] = 'Nom du module manquant.';
            redirect('/admin/modules');
            return;
        }

        if ($this->moduleManager->activateModule($name)) {
            $_SESSION['flash']['success'] = "Module {$name} activé avec succès.";
        } else {
            $_SESSION['flash']['danger'] = "Impossible d'activer le module {$name}.";
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
            $_SESSION['flash']['danger'] = 'Nom du module manquant.';
            redirect('/admin/modules');
            return;
        }

        if ($this->moduleManager->deactivateModule($name)) {
            $_SESSION['flash']['success'] = "Module {$name} désactivé avec succès.";
        } else {
            $_SESSION['flash']['danger'] = "Impossible de désactiver le module {$name}.";
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
            $_SESSION['flash']['danger'] = 'Nom du module manquant.';
            redirect('/admin/modules');
            return;
        }

        if ($this->moduleManager->installModule($name)) {
            $_SESSION['flash']['success'] = "Module {$name} installé avec succès.";
        } else {
            $_SESSION['flash']['danger'] = "Impossible d'installer le module {$name}.";
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
            $_SESSION['flash']['danger'] = 'Nom du module manquant.';
            redirect('/admin/modules');
            return;
        }

        if ($this->moduleManager->uninstallModule($name)) {
            $_SESSION['flash']['success'] = "Module {$name} désinstallé avec succès.";
        } else {
            $_SESSION['flash']['danger'] = "Impossible de désinstaller le module {$name}.";
        }

        redirect('/admin/modules');
    }

    /**
     * Show upload form
     */
    public function upload()
    {
        $app = Application::getInstance();

        echo $app->view->render('backend/modules/upload', [
            'title' => 'Installer un Module'
        ]);
    }

    /**
     * Handle module upload from ZIP
     */
    public function processUpload()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('/admin/modules/upload');
            return;
        }

        if (!isset($_FILES['module_zip'])) {
            $_SESSION['flash']['danger'] = 'Aucun fichier sélectionné.';
            redirect('/admin/modules/upload');
            return;
        }

        $installer = new \Modules\Admin\Services\ModuleInstaller();
        $result = $installer->installFromZip($_FILES['module_zip']);

        if ($result['success']) {
            $_SESSION['flash']['success'] = $result['message'];
            redirect('/admin/modules');
        } else {
            $_SESSION['flash']['danger'] = $result['message'];
            redirect('/admin/modules/upload');
        }
    }

    /**
     * Delete module (uninstall + remove files)
     */
    public function delete(array $params = [])
    {
        $name = $params['name'] ?? $_POST['name'] ?? null;

        if (!$name) {
            $_SESSION['flash']['danger'] = 'Nom du module manquant.';
            redirect('/admin/modules');
            return;
        }

        // Prevent deletion of core modules
        $coreModules = ['Admin', 'RBAC'];
        if (in_array($name, $coreModules)) {
            $_SESSION['flash']['danger'] = "Le module {$name} est un module système et ne peut pas être supprimé.";
            redirect('/admin/modules');
            return;
        }

        $installer = new \Modules\Admin\Services\ModuleInstaller();
        $result = $installer->uninstall($name);

        if ($result['success']) {
            $_SESSION['flash']['success'] = $result['message'];
        } else {
            $_SESSION['flash']['danger'] = $result['message'];
        }

        redirect('/admin/modules');
    }
}
