<?php

declare(strict_types=1);

// Preload script for OPcache
// This file should be referenced in php.ini: opcache.preload=/path/to/preload.php

// Core classes to preload
$coreClasses = [
    __DIR__ . '/Core/Application.php',
    __DIR__ . '/Core/Database/Database.php',
    __DIR__ . '/Core/Database/QueryBuilder.php',
    __DIR__ . '/Core/Database/Model.php',
    __DIR__ . '/Core/Routing/Router.php',
    __DIR__ . '/Core/Events/EventDispatcher.php',
    __DIR__ . '/Core/Http/SecurityHeaders.php',
    __DIR__ . '/Core/Security/InputValidator.php',
];

foreach ($coreClasses as $file) {
    if (file_exists($file)) {
        opcache_compile_file($file);
    }
}

echo "Preload completed: " . count($coreClasses) . " files\n";
