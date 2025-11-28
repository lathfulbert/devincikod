<?php

require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/Core/Support/helpers.php';

use App\Core\Application;
use App\Core\Database\Database;

$app = new Application(__DIR__);
$app->boot();

$db = Database::getInstance();

echo "Checking Modules...\n";

$modulesToCheck = ['SmsCore', 'Wallet'];

foreach ($modulesToCheck as $name) {
    try {
        // Check if exists
        $stmt = $db->query("SELECT id, is_enabled FROM modules WHERE name = ?", [$name]);
        $module = $stmt->fetch();

        if ($module) {
            echo "Module $name found. Status: " . ($module['is_enabled'] ? 'Enabled' : 'Disabled') . "\n";
            if (!$module['is_enabled']) {
                $db->query("UPDATE modules SET is_enabled = 1 WHERE name = ?", [$name]);
                echo "-> Enabled $name.\n";
            }
        } else {
            echo "Module $name NOT found. Inserting...\n";
            $db->query("INSERT INTO modules (name, description, version, author, is_enabled, is_installed, config, created_at, updated_at) VALUES (?, ?, ?, ?, 1, 1, '{}', NOW(), NOW())", [
                $name,
                "$name Module",
                '1.0.0',
                'LathDevinci'
            ]);
            echo "-> Inserted and Enabled $name.\n";
        }
    } catch (\Throwable $e) {
        echo "Error processing $name: " . $e->getMessage() . "\n";
    }
}

echo "\nDone.\n";
