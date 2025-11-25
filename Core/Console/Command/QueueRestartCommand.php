<?php

namespace App\Core\Console\Command;

use App\Core\Application;
use App\Core\Database\Database;

/**
 * Class QueueRestartCommand
 * 
 * Signal all queue workers to restart gracefully.
 * Workers check for a restart signal file and exit when found.
 * Supervisor/systemd will automatically restart them.
 * 
 * Usage: php sunu queue:restart
 */
class QueueRestartCommand
{
    public function execute(Application $app, array $args): void
    {
        echo "\n";
        echo "╔════════════════════════════════════════╗\n";
        echo "║     Restart Queue Workers              ║\n";
        echo "╚════════════════════════════════════════╝\n\n";

        $restartFile = $app->getBasePath() . '/storage/queue/restart';

        // Create storage/queue directory if it doesn't exist
        $dir = dirname($restartFile);
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        // Touch the restart file
        touch($restartFile);

        echo "✅ Restart signal sent to all workers!\n";
        echo "   Workers will gracefully shutdown and restart.\n";
        echo "   Signal file: $restartFile\n\n";
    }
}
