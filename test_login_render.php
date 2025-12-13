<?php
require __DIR__ . '/bootstrap.php';

try {
    echo view('auth/auth/login', []);
    echo "\nRENDER_OK\n";
} catch (Throwable $e) {
    echo "RENDER_ERROR: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString();
}