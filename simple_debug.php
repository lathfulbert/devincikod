<?php

// Simple debug script to check file existence
$files = [
    'public/assets/css/vendors/bootstrap.css',
    'public/assets/css/style.css',
    'public/build/assets/style-EoSbLfwg.css'
];

echo "Checking files in " . __DIR__ . "\n";
foreach ($files as $file) {
    if (file_exists(__DIR__ . '/' . $file)) {
        echo "[OK] $file exists\n";
    } else {
        echo "[ERR] $file missing\n";
    }
}
