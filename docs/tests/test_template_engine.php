<?php

require_once __DIR__ . '/Core/View/TemplateEngine.php';

use App\Core\View\TemplateEngine;

$cachePath = __DIR__ . '/storage/cache/views_test';
if (!is_dir($cachePath)) {
    mkdir($cachePath, 0755, true);
}

$engine = new TemplateEngine($cachePath);

$template = '
<form>
    @csrf
</form>
';

$compiled = $engine->compileString($template);

$output = "Template Original:\n" . $template . "\n";
$output .= "Template Compile:\n" . $compiled . "\n";

if (strpos($compiled, '<?= csrf_field() ?>') !== false) {
    $output .= "SUCCESS: @csrf directive compiled correctly.\n";
} else {
    $output .= "FAILURE: @csrf directive NOT compiled correctly.\n";
}

file_put_contents(__DIR__ . '/test_result.txt', $output);


// Clean up
array_map('unlink', glob("$cachePath/*"));
rmdir($cachePath);
