<?php
require_once __DIR__ . '/bootstrap.php';

use App\Core\Database\Database;

$db = Database::getInstance();

$roles = $db->query('SELECT * FROM roles')->fetchAll(PDO::FETCH_ASSOC);
echo "Rôles existants :\n";
foreach($roles as $role) {
    echo $role['name'] . ' (id: ' . $role['id'] . ")\n";
}

echo "\nPermissions :\n";
$permissions = $db->query('SELECT * FROM permissions')->fetchAll(PDO::FETCH_ASSOC);
foreach($permissions as $perm) {
    echo $perm['slug'] . "\n";
}