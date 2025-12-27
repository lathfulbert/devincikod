<?php
require __DIR__ . '/vendor/autoload.php';

use App\Core\Application;
use App\Core\Database\Database;

$app = new Application(__DIR__);
$app->boot();

$db = Database::getInstance();

// 1. Delete duplicates or bad names if they exist and we want to keep one canonical 'EmailMarketing'
// First, check what we have
$modules = $db->query("SELECT * FROM modules WHERE name LIKE '%mail%'")->fetchAll(PDO::FETCH_ASSOC);

$canonicalName = 'EmailMarketing';
$canonicalExists = false;

foreach ($modules as $module) {
    if ($module['name'] === $canonicalName) {
        $canonicalExists = true;
    }
}

echo "Canonical '$canonicalName' exists: " . ($canonicalExists ? 'YES' : 'NO') . "\n";

foreach ($modules as $module) {
    if ($module['name'] !== $canonicalName) {
        echo "Processing incorrect name: " . $module['name'] . "\n";

        if ($canonicalExists) {
            // If canonical exists, delete the duplicate/incorrect one
            echo "Deleting duplicate ID: " . $module['id'] . "\n";
            $db->query("DELETE FROM modules WHERE id = ?", [$module['id']]);
        } else {
            // If canonical doesn't exist, rename this one
            echo "Renaming ID: " . $module['id'] . " to '$canonicalName'\n";
            $db->query("UPDATE modules SET name = ? WHERE id = ?", [$canonicalName, $module['id']]);
            $canonicalExists = true; // Now it exists
        }
    }
}

echo "Done. Current state:\n";
$modules = $db->query("SELECT * FROM modules WHERE name LIKE '%mail%'")->fetchAll(PDO::FETCH_ASSOC);
foreach ($modules as $module) {
    echo "ID: " . $module['id'] . " | Name: " . $module['name'] . " | Active: " . $module['is_active'] . "\n";
}
