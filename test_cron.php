<?php
// Test direct du chargement de l'environnement
require_once __DIR__ . '/vendor/autoload.php';

// Charger .env directement
$dotenv = new \App\Core\Support\DotEnv(__DIR__ . '/.env');
$dotenv->load();

echo "APP_KEY from \$_ENV: " . ($_ENV['APP_KEY'] ?? 'NOT SET') . "\n";
echo "APP_KEY from \$_SERVER: " . ($_SERVER['APP_KEY'] ?? 'NOT SET') . "\n";
echo "APP_KEY from getenv: " . (getenv('APP_KEY') ?: 'NOT SET') . "\n";

// Maintenant initialiser l'application
use App\Core\Application;
$app = new Application(__DIR__);
echo "Application initialized successfully!\n";
