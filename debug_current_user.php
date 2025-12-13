<?php

require 'vendor/autoload.php';
require 'Core/Support/helpers.php';

$app = new \App\Core\Application(__DIR__);
$app->boot();

echo "Current session user_id: " . ($_SESSION['user_id'] ?? 'NOT SET') . PHP_EOL;
echo "Current session user: " . (isset($_SESSION['user']) ? json_encode($_SESSION['user']) : 'NOT SET') . PHP_EOL;

$user = auth()->user();
if ($user) {
    echo "Auth user: " . $user->username . " (ID: " . $user->id . ")" . PHP_EOL;
    echo "User roles: " . json_encode($user->roles->pluck('slug')->toArray()) . PHP_EOL;
} else {
    echo "No authenticated user" . PHP_EOL;
}

// Check admin role
$db = \App\Core\Database\Database::getInstance();
$adminRole = $db->query('SELECT id FROM roles WHERE slug = ?', ['admin'])->fetch();
if ($adminRole) {
    $userRole = $db->query('SELECT * FROM user_roles WHERE user_id = ? AND role_id = ?', [$_SESSION['user_id'] ?? 0, $adminRole['id']])->fetch();
    echo "User has admin role: " . ($userRole ? 'YES' : 'NO') . PHP_EOL;
} else {
    echo "Admin role not found" . PHP_EOL;
}