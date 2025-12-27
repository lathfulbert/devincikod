<?php
require 'vendor/autoload.php';

// Initialize App (loads .env)
$app = new \App\Core\Application(__DIR__);

// Load Config directly
$dbConfig = require __DIR__ . '/config/database.php';

// Connect DB
$db = \App\Core\Database\Database::getInstance();
$db->connect($dbConfig['connections'][$dbConfig['default']]);

// Query
$tables = $db->query('SHOW TABLES')->fetchAll(\PDO::FETCH_COLUMN);
foreach ($tables as $table) {
    echo "- $table\n";
}
