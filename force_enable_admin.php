<?php

require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/Core/Application.php';

use App\Core\Database\Database;

$db = Database::getInstance();
// Manually connect using PDO to avoid framework overhead issues
try {
    $pdo = new PDO('mysql:host=localhost;dbname=sunuframework2;charset=utf8mb4', 'root', '');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    echo "Connected to database.\n";

    // Check if Admin module exists
    $stmt = $pdo->query("SELECT * FROM modules WHERE name = 'Admin'");
    $admin = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($admin) {
        echo "Admin module found. Status: " . ($admin['is_enabled'] ? 'ENABLED' : 'DISABLED') . "\n";
        if (!$admin['is_enabled']) {
            echo "Enabling Admin module...\n";
            $pdo->exec("UPDATE modules SET is_enabled = 1 WHERE name = 'Admin'");
            echo "Admin module ENABLED.\n";
        }
    } else {
        echo "Admin module NOT found in database.\n";
        // We can't easily insert it here without the manifest, but the app boot should have done it.
        // If it's not here, it means the app boot didn't sync.
    }

    // List all modules
    echo "All Modules:\n";
    $stmt = $pdo->query("SELECT name, is_enabled FROM modules");
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo "- {$row['name']}: " . ($row['is_enabled'] ? 'Enabled' : 'Disabled') . "\n";
    }
} catch (PDOException $e) {
    echo "Connection failed: " . $e->getMessage();
}
