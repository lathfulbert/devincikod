<?php
/**
 * Bootstrap file for CLI scripts (cron jobs, workers, etc.)
 */

// Load Composer autoloader
require_once __DIR__ . '/vendor/autoload.php';

// Initialize Application
use App\Core\Application;

$app = new Application(__DIR__);
$app->boot();

return $app;
