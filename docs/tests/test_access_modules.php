<?php
require_once __DIR__ . '/bootstrap.php';

use App\Core\Application;
use App\Core\Module\Middleware\ModuleAccessMiddleware;
use Modules\Users\Models\User;

$app = Application::getInstance();

// Test pour l'admin
$admin = User::with('roles')->where('username', 'admin')->first();
echo "Test pour l'admin :\n";
echo "Utilisateur : {$admin->username}\n";

$accessMiddleware = new ModuleAccessMiddleware();

// Tester directement canAccessModule pour différents modules
$modules = ['admin', 'sms_core', 'auth', 'users'];
foreach ($modules as $module) {
    $canAccess = $accessMiddleware->canAccessModule($module, $admin);
    echo "  {$module}: " . ($canAccess ? 'OUI' : 'NON') . "\n";
}

echo "\nTest pour l'utilisateur normal :\n";
$user = User::with('roles')->where('username', 'client')->first();
echo "Utilisateur : {$user->username}\n";

foreach ($modules as $module) {
    $canAccess = $accessMiddleware->canAccessModule($module, $user);
    echo "  {$module}: " . ($canAccess ? 'OUI' : 'NON') . "\n";
}