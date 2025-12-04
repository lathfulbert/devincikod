<?php

/**
 * Manual SMS Queue Processor
 *
 * Execute this script manually to process the SMS queue
 * Usage: php process_sms_queue_manual.php
 */

require_once __DIR__ . '/vendor/autoload.php';

use App\Core\Application;
use Modules\SmsCore\Cron\ProcessSmsQueue;

// Initialize application
$app = new Application(__DIR__);
$app->boot();

echo "═══════════════════════════════════════════════════════════\n";
echo " SMS QUEUE PROCESSOR (Manual Execution)\n";
echo "═══════════════════════════════════════════════════════════\n\n";

// Create and run the cron task
$task = new ProcessSmsQueue();
$task->handle();

echo "═══════════════════════════════════════════════════════════\n";
echo "✅ Done!\n";
echo "═══════════════════════════════════════════════════════════\n";
