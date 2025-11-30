<?php

require_once __DIR__ . '/vendor/autoload.php';

use App\Core\Application;
use App\Core\Database\Database;

$app = new Application(__DIR__);
$app->boot();

$db = Database::getInstance();

$password = password_hash('password', PASSWORD_BCRYPT);

// Check if user exists
$check = $db->query("SELECT id FROM users WHERE email = ?", ['admin@example.com'])->fetch();

if ($check) {
    echo "User already exists with ID: " . $check['id'] . "\n";
    // Update password
    $db->query("UPDATE users SET password = ? WHERE id = ?", [$password, $check['id']]);
    echo "Password updated.\n";
} else {
    $sql = "INSERT INTO users (username, email, password, created_at, updated_at) VALUES (?, ?, ?, NOW(), NOW())";
    $stmt = $db->query($sql, ['Admin', 'admin@example.com', $password]);
    echo "User created with ID: " . $db->getPdo()->lastInsertId() . "\n";
}
