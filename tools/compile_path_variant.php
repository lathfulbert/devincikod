<?php
require __DIR__ . '/../bootstrap.php';

use App\Core\View\TemplateEngine;

$variant = 'C:\\laragon\\www\\sunuframework2/resources/views/backend/dashboard.php';
$engine = new TemplateEngine(__DIR__ . '/../storage/cache/views');
$compiledPath = $engine->compile($variant);
echo "Compiled to: $compiledPath\n";
file_put_contents(__DIR__ . '/compiled_variant.php', file_get_contents($compiledPath));
echo "WROTE compiled_variant.php\n";
