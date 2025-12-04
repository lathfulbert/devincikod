<?php

namespace App\Core\Console\Command;

use App\Core\Application;
use Modules\Settings\Models\MaintenanceMode;

/**
 * Planifier une maintenance
 * Usage: php sunu maintenance:schedule --start="2025-12-10 02:00" --end="2025-12-10 06:00"
 */
class MaintenanceScheduleCommand
{
    public function execute(Application $app, array $args): void
    {
        try {
            $config = MaintenanceMode::getCurrent();

            if (!$config) {
                echo "❌ Configuration de maintenance introuvable.\n";
                exit(1);
            }

            $startTime = $this->getOption($args, '--start');
            $endTime = $this->getOption($args, '--end');
            $message = $this->getOption($args, '--message');
            $title = $this->getOption($args, '--title');

            if (!$startTime && !$endTime) {
                echo "❌ Veuillez spécifier au moins --start ou --end\n";
                echo "\nUsage:\n";
                echo "  php sunu maintenance:schedule --start=\"YYYY-MM-DD HH:MM\" --end=\"YYYY-MM-DD HH:MM\"\n";
                echo "\nOptions:\n";
                echo "  --start    Date et heure de début (format: YYYY-MM-DD HH:MM)\n";
                echo "  --end      Date et heure de fin (format: YYYY-MM-DD HH:MM)\n";
                echo "  --title    Titre personnalisé\n";
                echo "  --message  Message personnalisé\n";
                echo "\nExemple:\n";
                echo "  php sunu maintenance:schedule --start=\"2025-12-10 02:00\" --end=\"2025-12-10 06:00\" --message=\"Mise à jour système\"\n";
                exit(1);
            }

            $data = ['is_enabled' => 1];

            if ($startTime) {
                $timestamp = strtotime($startTime);
                if ($timestamp === false) {
                    echo "❌ Format de date invalide pour --start\n";
                    exit(1);
                }
                $data['start_time'] = date('Y-m-d H:i:s', $timestamp);
            }

            if ($endTime) {
                $timestamp = strtotime($endTime);
                if ($timestamp === false) {
                    echo "❌ Format de date invalide pour --end\n";
                    exit(1);
                }
                $data['end_time'] = date('Y-m-d H:i:s', $timestamp);
            }

            if ($title) {
                $data['title'] = $title;
            }

            if ($message) {
                $data['message'] = $message;
            }

            // Validation : start_time doit être avant end_time
            if (isset($data['start_time']) && isset($data['end_time'])) {
                if (strtotime($data['start_time']) >= strtotime($data['end_time'])) {
                    echo "❌ La date de début doit être avant la date de fin.\n";
                    exit(1);
                }
            }

            if ($config->update($data)) {
                echo "✅ Maintenance planifiée avec succès\n";
                echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
                if (isset($data['start_time'])) {
                    echo "Début: " . $data['start_time'] . "\n";
                }
                if (isset($data['end_time'])) {
                    echo "Fin: " . $data['end_time'] . "\n";
                }
                if (isset($data['title'])) {
                    echo "Titre: " . $data['title'] . "\n";
                }
                if (isset($data['message'])) {
                    echo "Message: " . $data['message'] . "\n";
                }
                echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
                echo "ℹ️  La maintenance s'activera et se désactivera automatiquement.\n";
            } else {
                echo "❌ Erreur lors de la planification.\n";
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
