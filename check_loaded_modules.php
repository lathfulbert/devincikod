<?php
require_once __DIR__ . '/bootstrap.php';

use App\Core\Application;

$app = Application::getInstance();
$modules = $app->moduleManager->getModules();
echo 'Modules chargés : ' . count($modules) . PHP_EOL;
foreach($modules as $module) {
    echo '- ' . $module->getName() . PHP_EOL;
}
?>