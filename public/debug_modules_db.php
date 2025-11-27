<?php
require_once __DIR__ . '/../vendor/autoload.php';

use App\Core\Database\Database;

$app = new \App\Core\Application(dirname(__DIR__));
$app->boot();

$db = Database::getInstance();
try {
    $stmt = $db->query("SELECT * FROM modules");
    $modules = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo "<pre>";
    print_r($modules);
    echo "</pre>";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
