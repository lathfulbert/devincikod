<?php

namespace App\Core\Console\Command;

use App\Core\Application;
use Modules\Settings\Models\MaintenanceMode;

/**
 * Désactiver le mode maintenance
 * Usage: php sunu maintenance:down
 */
class MaintenanceDownCommand
{
    public function execute(Application $app, array $args): void
    {
        try {
            $config = MaintenanceMode::getCurrent();

            if (!$config) {
                echo "❌ Configuration de maintenance introuvable.\n";
                exit(1);
            }

            if (!$config->is_enabled) {
                echo "ℹ️  Le mode maintenance est déjà désactivé.\n";
                exit(0);
            }

            if ($config->update(['is_enabled' => 0])) {
                echo "✅ Mode maintenance DÉSACTIVÉ\n";
                echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
                echo "Le site est de nouveau accessible au public.\n";
            } else {
                echo "❌ Erreur lors de la désactivation.\n";
                exit(1);
            }

        } catch (\Exception $e) {
            echo "❌ Erreur : " . $e->getMessage() . "\n";
            exit(1);
        }
    }
}
