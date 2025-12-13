<?php
require __DIR__ . '/../bootstrap.php';

use App\Core\View\TemplateEngine;

$templatePath = __DIR__ . '/../resources/views/backend/dashboard.php';
$engine = new TemplateEngine(__DIR__ . '/../storage/cache/views');
$compiledPath = $engine->compile($templatePath);
echo "Compiled to: $compiledPath\n";
$compiled = file_get_contents($compiledPath);
file_put_contents(__DIR__ . '/compiled_from_compile.php', $compiled);
echo "WROTE tools/compiled_from_compile.php\n";
