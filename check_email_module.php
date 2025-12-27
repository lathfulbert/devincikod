<?php
require __DIR__ . '/vendor/autoload.php';

use App\Core\Application;
use App\Core\Database\Database;

$app = new Application(__DIR__);
$app->boot();

$db = Database::getInstance();
$stm = $db->query("SELECT * FROM modules WHERE name = 'EmailMarketing'");
$module = $stm->fetch(PDO::FETCH_ASSOC);

if ($module) {
    echo "Found EmailMarketing:\n";
    print_r($module);
} else {
    echo "EmailMarketing NOT FOUND in DB.\n";

    // Force enable attempt
    echo "Attempting to insert/enable...\n";
    try {
        $db->query("INSERT INTO modules (name, is_enabled, version) VALUES ('EmailMarketing', 1, '1.0.0')");
        echo "Inserted.\n";
    } catch (Exception $e) {
        echo "Insert failed: " . $e->getMessage() . "\n";
    }
}
