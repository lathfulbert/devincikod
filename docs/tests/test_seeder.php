<?php

require_once __DIR__ . '/vendor/autoload.php';

use App\Core\Application;
use Modules\Backup\Database\Seeders\BackupNotificationSeeder;

try {
    $app = new Application(__DIR__);
    $app->boot();

    echo "Running BackupNotificationSeeder...\n";
    $seeder = new BackupNotificationSeeder();
    $seeder->run();
    echo "Seeder completed successfully!\n";
} catch (\Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . ":" . $e->getLine() . "\n";
    echo "Trace:\n" . $e->getTraceAsString() . "\n";
}
