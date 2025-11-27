<?php
require_once __DIR__ . '/vendor/autoload.php';
session_start();

use App\Core\Security\CSRF;

$csrf = CSRF::getInstance();
$token = $csrf->getToken();

echo "=== CSRF Token Test ===\n\n";
echo "Session ID: " . session_id() . "\n";
echo "Generated Token: " . $token . "\n";
echo "Token in Session: " . ($_SESSION[CSRF::getTokenName()] ?? 'NOT SET') . "\n";
echo "Tokens Match: " . ($token === ($_SESSION[CSRF::getTokenName()] ?? '') ? 'YES' : 'NO') . "\n\n";

// Test validation
echo "=== Validation Test ===\n";
echo "Validate with correct token: " . ($csrf->validateToken($token) ? 'PASS' : 'FAIL') . "\n";
echo "Validate with wrong token: " . ($csrf->validateToken('wrong_token') ? 'FAIL (should be false)' : 'PASS') . "\n";
echo "Validate with null: " . ($csrf->validateToken(null) ? 'FAIL (should be false)' : 'PASS') . "\n";
