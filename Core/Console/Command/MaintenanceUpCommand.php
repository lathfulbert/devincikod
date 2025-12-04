<?php

namespace App\Core\Console\Command;

use App\Core\Application;
use Modules\Settings\Models\MaintenanceMode;

/**
 * Activer le mode maintenance
 * Usage: php sunu maintenance:up
 */
class MaintenanceUpCommand
{
    public function execute(Application $app, array $args): void
    {
        try {
            $config = MaintenanceMode::getCurrent();

            if (!$config) {
                echo "❌ Configuration de maintenance introuvable.\n";
                exit(1);
            }

            if ($config->is_enabled) {
                echo "ℹ️  Le mode maintenance est déjà activé.\n";
                exit(0);
            }

            // Options de ligne de commande
            $message = $this->getOption($args, '--message');
            $retryAfter = $this->getOption($args, '--retry', 3600);
            $endTime = $this->getOption($args, '--end');

            $data = ['is_enabled' => 1];

            if ($message) {
                $data['message'] = $message;
            }

            if ($retryAfter) {
                $data['retry_after'] = (int) $retryAfter;
            }

            if ($endTime) {
                // Valider et formater la date
                $timestamp = strtotime($endTime);
                if ($timestamp === false) {
                    echo "❌ Format de date invalide pour --end. Utilisez: YYYY-MM-DD HH:MM\n";
                    exit(1);
                }
                $data['end_time'] = date('Y-m-d H:i:s', $timestamp);
            }

            if ($config->update($data)) {
                echo "🔧 Mode maintenance ACTIVÉ\n";
                echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
                echo "Titre: " . $config->title . "\n";
                echo "Message: " . ($data['message'] ?? $config->message) . "\n";
                if (isset($data['end_time'])) {
                    echo "Fin prévue: " . $data['end_time'] . "\n";
                }
                echo "Retry-After: " . ($data['retry_after'] ?? $config->retry_after) . " secondes\n";
                echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
                echo "✅ Le site est maintenant en maintenance.\n";
            } else {
                echo "❌ Erreur lors de l'activation.\n";
                exit(1);
            }

        } catch (\Exception $e) {
            echo "❌ Erreur : " . $e->getMessage() . "\n";
            exit(1);
        }
    }

    /**
     * Get command line option
     */
    private function getOption(array $args, string $name, $default = null)
    {
        foreach ($args as $i => $arg) {
            if (str_starts_with($arg, $name . '=')) {
                return substr($arg, strlen($name) + 1);
            }
            if ($arg === $name && isset($args[$i + 1])) {
                return $args[$i + 1];
            }
        }
        return $default;
    }
}
