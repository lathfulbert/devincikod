<?php
require_once 'bootstrap.php';
$engine = new App\Core\View\TemplateEngine(storage_path('cache/views'));
$templatePath = 'Modules/Auth/Views/auth/login.php';
$compiled = $engine->compile($templatePath);
echo 'Compiled path: ' . $compiled . PHP_EOL;
echo 'First 300 chars:' . PHP_EOL;
echo substr(file_get_contents($compiled), 0, 300) . PHP_EOL;
?>