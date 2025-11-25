<?php

namespace App\Core\Console\Command;

use App\Core\Application;

class ModuleDeleteCommand
{
    public function execute(Application $app, array $args): void
    {
        $moduleName = $args[0] ?? null;

        if (!$moduleName) {
            echo "❌ Erreur : vous devez fournir le nom du module.\n";
            echo "Usage: php sunu module:delete <nom>\n";
            exit(1);
        }

        try {
            // Utiliser le ModuleInstaller pour la désinstallation complète
            $installer = new \Modules\Admin\Services\ModuleInstaller();
            $result = $installer->uninstall($moduleName);

            if ($result['success']) {
                echo "✅ " . $result['message'] . "\n";
            } else {
                echo "❌ " . $result['message'] . "\n";
                exit(1);
            }
        } catch (\Exception $e) {
            echo "❌ Erreur lors de la suppression : " . $e->getMessage() . "\n";
            exit(1);
        }
    }
}
