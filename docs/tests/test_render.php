<?php
require __DIR__ . '/bootstrap.php';

try {
    echo view('backend.dashboard', []);
    echo "\nRENDER_OK\n";
} catch (Throwable $e) {
    echo "RENDER_ERROR: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString();
}
