<?php

require __DIR__ . '/vendor/autoload.php';

use App\Core\Application;
use App\Core\Database\Database;

$app = new Application(__DIR__);
$app->boot();

$dbConfig = $app->config->get('database');
Database::getInstance()->connect($dbConfig);

$tables = Database::getInstance()->query('SHOW TABLES')->fetchAll(PDO::FETCH_COLUMN);

echo "Tables créées dans la base de données:\n";
echo str_repeat("=", 50) . "\n";
foreach ($tables as $table) {
    echo "  ✓ {$table}\n";
}
echo str_repeat("=", 50) . "\n";
echo "Total: " . count($tables) . " tables\n";
