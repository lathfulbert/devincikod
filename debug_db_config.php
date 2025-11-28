<?php

require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/Core/Support/helpers.php';

use App\Core\Config\Config;
use App\Core\Support\DotEnv;

// Load env
(new DotEnv(__DIR__ . '/.env'))->load();

// Load config
$config = new Config();
$config->loadDirectory(__DIR__ . '/config');

$default = $config->get('database.default');
echo "Default DB: $default\n";

$connectionConfig = $config->get("database.connections.{$default}");

if (!is_array($connectionConfig)) {
    echo "Error: Connection config is not an array!\n";
    var_dump($connectionConfig);
    exit;
}

echo "Host key exists: " . (array_key_exists('host', $connectionConfig) ? 'YES' : 'NO') . "\n";
echo "Host value: " . ($connectionConfig['host'] ?? 'NULL') . "\n";
echo "Database value: " . ($connectionConfig['database'] ?? 'NULL') . "\n";

// Check if we are getting the right array
echo "\nFull Config Keys:\n";
print_r(array_keys($connectionConfig));
