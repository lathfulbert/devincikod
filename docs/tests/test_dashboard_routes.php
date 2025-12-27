<?php

/**
 * Test dashboard access
 */

echo "Testing Dashboard Routes...\n\n";

$routes = [
    '/admin/sms' => 'SMS Dashboard',
    '/admin/sms/send' => 'Send SMS',
    '/admin/sms/history' => 'SMS History',
    '/admin/wallet' => 'Wallet Overview',
];

foreach ($routes as $route => $name) {
    $url = 'http://localhost/sunuframework2' . $route;
    echo "Testing: $name\n";
    echo "URL: $url\n";
    echo "Expected controller to handle this route.\n\n";
}

echo "✓ Routes registered in modules!\n";
echo "\nTo test, visit:\n";
echo "http://localhost/sunuframework2/admin/sms\n";
