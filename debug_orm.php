<?php

require_once __DIR__ . '/vendor/autoload.php';

use App\Core\Application;
use Modules\Auth\Models\User;

$app = new Application(__DIR__);
$app->boot();

echo "Testing User::all()...\n";
$users = User::all();

foreach ($users as $user) {
    echo "User class: " . get_class($user) . "\n";
    echo "Attributes: " . print_r($user, true) . "\n";

    // Test property access
    try {
        echo "ID: " . $user->id . "\n";
        echo "Username: " . $user->username . "\n";
    } catch (\Throwable $e) {
        echo "Error accessing properties: " . $e->getMessage() . "\n";
    }
}
