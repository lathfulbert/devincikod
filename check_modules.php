<?php
require 'vendor/autoload.php';

$app = new \App\Core\Application(__DIR__);
$modules = $app->moduleManager->getAllModules();

echo "Modules found: " . count($modules) . "\n";
foreach ($modules as $name => $module) {
    echo "- $name\n";
}
