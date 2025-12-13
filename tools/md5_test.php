<?php
$paths = [
    'C:\\laragon\\www\\sunuframework2/resources/views/backend/dashboard.php',
    __DIR__ . '/../resources/views/backend/dashboard.php',
    realpath(__DIR__ . '/../resources/views/backend/dashboard.php')
];
foreach ($paths as $p) {
    echo "Path: [$p]\n";
    echo "md5: " . md5($p) . "\n\n";
}
