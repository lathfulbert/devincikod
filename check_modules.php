<?php

// Simple test to check module loading without autoloader issues
require_once 'vendor/autoload.php';

$db = \App\Core\Database\Database::getInstance();

// Check enabled modules
$result = $db->query("SELECT name, is_active FROM modules")->fetchAll();

echo "Modules in database:\n";
foreach ($result as $row) {
    echo "  " . $row['name'] . " (is_active: " . $row['is_active'] . ")\n";
}

echo "\nTotal modules: " . count($result) . "\n";
echo "Active modules: " . count(array_filter($result, fn($r) => $r['is_active'] == 1)) . "\n";
