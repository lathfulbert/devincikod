<?php
require_once __DIR__ . '/bootstrap.php';

use App\Core\Application;
use App\Core\Database\Database;

$app = Application::getInstance();
$db = Database::getInstance();

// Vérifier les rôles de l'utilisateur 'client'
$userRoles = $db->query("
    SELECT r.name as role_name 
    FROM users u 
    JOIN user_roles ur ON u.id = ur.user_id 
    JOIN roles r ON ur.role_id = r.id 
    WHERE u.username = 'client'
")->fetchAll(PDO::FETCH_ASSOC);

echo "Rôles de l'utilisateur 'client' :\n";
foreach ($userRoles as $role) {
    echo "- " . $role['role_name'] . "\n";
}

// Vérifier les permissions de l'utilisateur 'client'
$userPermissions = $db->query("
    SELECT p.slug as permission_slug
    FROM users u 
    JOIN user_roles ur ON u.id = ur.user_id 
    JOIN role_permissions rp ON ur.role_id = rp.role_id 
    JOIN permissions p ON rp.permission_id = p.id 
    WHERE u.username = 'client'
")->fetchAll(PDO::FETCH_ASSOC);

echo "\nPermissions de l'utilisateur 'client' :\n";
foreach ($userPermissions as $perm) {
    echo "- " . $perm['permission_slug'] . "\n";
}