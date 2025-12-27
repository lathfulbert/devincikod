<?php
require 'vendor/autoload.php';

$app = new \App\Core\Application(__DIR__);
$dbConfig = require __DIR__ . '/config/database.php';
$db = \App\Core\Database\Database::getInstance();
$db->connect($dbConfig['connections'][$dbConfig['default']]);

$migrations = $db->query("SELECT * FROM migrations")->fetchAll(\PDO::FETCH_ASSOC);
echo "Executed migrations:\n";
foreach ($migrations as $m) {
    echo "- {$m['migration']} ({$m['created_at']})\n";
}
