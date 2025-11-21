<?php

require_once __DIR__ . '/vendor/autoload.php'; // Assuming autoloader is here, or I might need to manually include helpers if not

// Manually include helpers if autoloader doesn't pick them up or if I want to test in isolation
require_once __DIR__ . '/Core/Support/helpers.php';

// Mock Application/Config if needed, but let's try to use the real one if possible.
// If not, I'll use the logic from helpers.php directly or mock it.

echo "Current Directory: " . __DIR__ . "\n";
echo "Document Root: " . $_SERVER['DOCUMENT_ROOT'] . "\n";

$filesToCheck = [
    'public/assets/css/vendors/bootstrap.css',
    'public/assets/css/style.css',
    'public/assets/css/responsive.css',
    'public/build/assets/style-EoSbLfwg.css' // Example from previous check
<?php

require_once __DIR__ . '/vendor/autoload.php'; // Assuming autoloader is here, or I might need to manually include helpers if not

// Manually include helpers if autoloader doesn't pick them up or if I want to test in isolation
require_once __DIR__ . '/Core/Support/helpers.php';

// Mock Application/Config if needed, but let's try to use the real one if possible.
// If not, I'll use the logic from helpers.php directly or mock it.

echo "Current Directory: " . __DIR__ . "\n";
echo "Document Root: " . $_SERVER['DOCUMENT_ROOT'] . "\n";

$filesToCheck = [
    'public/assets/css/vendors/bootstrap.css',
    'public/assets/css/style.css',
    'public/assets/css/responsive.css',
    'public/build/assets/style-EoSbLfwg.css' // Example from previous check
];

echo "\n--- Checking File Existence ---\n";
foreach ($filesToCheck as $file) {
    $path = __DIR__ . '/' . $file;
    if (file_exists($path)) {
        echo "[OK] Found: $file (Size: " . filesize($path) . " bytes)\n";
    } else {
        echo "[ERR] Missing: $file\n";
    }
}

// Removed conflicting url function definition


// Loop removed to avoid crash

