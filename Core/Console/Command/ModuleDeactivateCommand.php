<?php

namespace App\Core\Console\Command;

use App\Core\Application;

class ModuleDeactivateCommand
{
    public function execute(Application $app, array $args): void
    {
        $moduleName = $args[0] ?? null;

        if (!$moduleName) {
            echo "❌ Erreur : vous devez fournir le nom du module.\n";
            echo "Usage: php sunu module:deactivate <nom>\n";
            exit(1);
        }

        try {
            $registry = $app->moduleManager->getRegistry();

            if (!$registry->isInstalled($moduleName)) {
                echo "❌ Le module '$moduleName' n'est pas installé.\n";
                exit(1);
            }

            if (!$registry->isEnabled($moduleName)) {
                echo "ℹ️  Le module '$moduleName' est déjà désactivé.\n";
                exit(0);
            }

            // Désactiver le module via le ModuleManager
            $module = $app->moduleManager->getModule($moduleName);
            if ($module && $app->moduleManager->deactivateModule($moduleName)) {
                echo "✅ Module '$moduleName' désactivé avec succès.\n";
            } else {
                echo "❌ Impossible de désactiver le module '$moduleName'.\n";
                exit(1);
            }
        } catch (\Exception $e) {
            echo "❌ Erreur lors de la désactivation : " . $e->getMessage() . "\n";
            exit(1);
        }
    }
}
