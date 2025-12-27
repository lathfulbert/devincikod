<?php

require_once __DIR__ . '/bootstrap.php';
require_once __DIR__ . '/Modules/WhatsAppMarketing/Database/Migrations/001_create_whatsapp_tables.php';

try {
    echo "Instantiating migration...\n";
    $migration = new CreateWhatsappTables();
    echo "Running up()...\n";
    $migration->up();
    echo "Success!\n";
} catch (\Throwable $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "Trace: " . $e->getTraceAsString() . "\n";
}
