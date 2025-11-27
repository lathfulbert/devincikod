<?php
require_once __DIR__ . '/../preload.php';

try {
    $locales = supported_locales();
    echo "Supported Locales: " . print_r($locales, true) . "\n";

    $config = config('languages');
    echo "Language Config: " . print_r($config, true) . "\n";
} catch (Throwable $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
