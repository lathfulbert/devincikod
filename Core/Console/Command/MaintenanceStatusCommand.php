<?php

namespace App\Core\Console\Command;

use App\Core\Application;
use Modules\Settings\Models\MaintenanceMode;

/**
 * Afficher le statut du mode maintenance
 * Usage: php sunu maintenance:status
 */
class MaintenanceStatusCommand
{
    public function execute(Application $app, array $args): void
    {
        try {
            $config = MaintenanceMode::getCurrent();

            if (!$config) {
                echo "❌ Configuration de maintenance introuvable.\n";
                exit(1);
            }

            echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
            echo "   STATUT DU MODE MAINTENANCE\n";
            echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";

            // Statut principal
            if ($config->is_enabled) {
                echo "🔧 Statut: ACTIVÉ (Site en maintenance)\n";
            } else {
                echo "✅ Statut: DÉSACTIVÉ (Site accessible)\n";
            }

            echo "\nConfiguration:\n";
            echo "  Titre: " . ($config->title ?? 'N/A') . "\n";
            echo "  Message: " . ($config->message ?? 'N/A') . "\n";

            if ($config->start_time) {
                echo "  Début: " . $config->start_time . "\n";
            }

            if ($config->end_time) {
                echo "  Fin prévue: " . $config->end_time . "\n";

                if ($config->is_enabled) {
                    $remaining = $config->getTimeRemaining();
                    if ($remaining !== null && $remaining > 0) {
                        $hours = floor($remaining / 3600);
                        $minutes = floor(($remaining % 3600) / 60);
                        echo "  Temps restant: {$hours}h {$minutes}m\n";
                    }
                }
            }

            echo "  Retry-After: " . ($config->retry_after ?? 3600) . " secondes\n";
            echo "  Compte à rebours: " . ($config->show_countdown ? 'Activé' : 'Désactivé') . "\n";

            // Contrôle d'accès
            echo "\nContrôle d'accès:\n";

            $allowedIps = $config->allowed_ips ?? [];
            if (is_array($allowedIps) && count($allowedIps) > 0) {
                echo "  IPs autorisées: " . count($allowedIps) . "\n";
                foreach ($allowedIps as $ip) {
                    echo "    - " . $ip . "\n";
                }
            } else {
                echo "  IPs autorisées: Aucune\n";
            }

            $allowedRoles = $config->allowed_roles ?? [];
            if (is_array($allowedRoles) && count($allowedRoles) > 0) {
                echo "  Rôles autorisés: " . count($allowedRoles) . " rôle(s)\n";
            } else {
                echo "  Rôles autorisés: Aucun\n";
            }

            $allowedUsers = $config->allowed_users ?? [];
            if (is_array($allowedUsers) && count($allowedUsers) > 0) {
                echo "  Utilisateurs autorisés: " . count($allowedUsers) . " utilisateur(s)\n";
            } else {
                echo "  Utilisateurs autorisés: Aucun\n";
            }

            // Apparence
            echo "\nApparence:\n";
            echo "  Couleur de fond: " . ($config->background_color ?? '#4466f2') . "\n";
            echo "  Image de fond: " . ($config->background_image ? 'Configurée' : 'Non configurée') . "\n";

            echo "\n━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";

        } catch (\Exception $e) {
            echo "❌ Erreur : " . $e->getMessage() . "\n";
            exit(1);
        }
    }
}
