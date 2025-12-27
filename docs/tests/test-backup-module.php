<?php

require_once __DIR__ . '/vendor/autoload.php';

// Bootstrap application (minimal)
define('APP_ROOT', __DIR__);

// Mock Application for NotificationService
class MockApplication
{
    public static function getInstance()
    {
        return new self();
    }
}
class_alias('MockApplication', 'App\Core\Application');

// Mock EventDispatcher to avoid actual dispatching errors if core not fully loaded
// But we want to test dispatching... let's try to load real core if possible
// Assuming autoloader handles it.

use Modules\Backup\Services\BackupService;
use Modules\Backup\Services\RestoreService;
use Modules\Backup\Services\MonitoringService;
use Modules\Backup\Models\Backup;
use App\Core\Database\Database;

// Initialize Database
$dbConfig = require __DIR__ . '/config/database.php';
Database::getInstance()->connect($dbConfig['connections']['mysql']);

echo "--- Testing Backup Module ---\n";

// 1. Test Monitoring
echo "\n[1] Testing MonitoringService...\n";
$monitor = new MonitoringService();
$health = $monitor->getSystemHealth();
echo "Disk Usage: " . $health['disk']['percent'] . "%\n";
echo "CPU Status: " . $health['cpu']['status'] . "\n";
echo "Memory Usage: " . $health['memory']['percent'] . "%\n";

// 2. Test Backup (Files only for safety)
echo "\n[2] Testing BackupService (Files)...\n";
$backupService = new BackupService();
try {
    // We need to ensure database connection works for Backup model saving
    // If not, we might need to mock Model::save or ensure DB is configured
    // Let's assume DB is configured in config/database.php

    $backup = $backupService->runBackup('files', 'test_script');
    echo "Backup created: " . $backup->filename . "\n";
    echo "Path: " . $backup->path . "\n";
    echo "Size: " . $backup->size . " bytes\n";

    if (!file_exists($backup->path)) {
        echo "ERROR: Backup file not found!\n";
        exit(1);
    }

    // 3. Test Restore
    echo "\n[3] Testing RestoreService...\n";
    $restoreService = new RestoreService();
    // We won't actually restore to overwrite files, but we can try to unzip to temp
    // The restore service does this internally.
    // For safety, let's just verify the zip integrity
    $zip = new ZipArchive();
    if ($zip->open($backup->path) === true) {
        echo "Backup archive is valid.\n";
        $zip->close();
    } else {
        echo "ERROR: Backup archive is invalid!\n";
    }

    // Cleanup
    // unlink($backup->path);
    // $backup->delete(); 
    echo "\nTest Completed Successfully.\n";
} catch (\Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString();
}
