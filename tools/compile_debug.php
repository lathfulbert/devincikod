<?php
require __DIR__ . '/../bootstrap.php';

use App\Core\View\TemplateEngine;

$templatePath = __DIR__ . '/../resources/views/backend/dashboard.php';
$contents = file_get_contents($templatePath);
$engine = new TemplateEngine(__DIR__ . '/../storage/cache/views');
$compiled = $engine->compileString($contents);
file_put_contents(__DIR__ . '/compiled_debug_output.php', $compiled);
echo "WROTE debug output to tools/compiled_debug_output.php\n";
