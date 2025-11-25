<?php

namespace App\Core\Console\Command;

use App\Core\Application;

class ModuleActivateCommand
{
    public function execute(Application $app, array $args): void
    {
        $moduleName = $args[0] ?? null;

        if (!$moduleName) {
            echo "❌ Erreur : vous devez fournir le nom du module.\n";
            echo "Usage: php sunu module:activate <nom>\n";
            exit(1);
        }

        try {
            $registry = $app->moduleManager->getRegistry();

            if (!$registry->isInstalled($moduleName)) {
                echo "❌ Le module '$moduleName' n'est pas installé.\n";
                exit(1);
            }

            if ($registry->isEnabled($moduleName)) {
                echo "ℹ️  Le module '$moduleName' est déjà activé.\n";
                exit(0);
            }

            // Activer le module via le ModuleManager
            $module = $app->moduleManager->getModule($moduleName);
            if ($module && $app->moduleManager->activateModule($moduleName)) {
                echo "✅ Module '$moduleName' activé avec succès.\n";
            } else {
                echo "❌ Impossible d'activer le module '$moduleName'.\n";
                exit(1);
            }
        } catch (\Exception $e) {
            echo "❌ Erreur lors de l'activation : " . $e->getMessage() . "\n";
            exit(1);
        }
    }
}
