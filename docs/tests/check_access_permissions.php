<?php

require 'vendor/autoload.php';
require 'Core/Support/helpers.php';

$app = new \App\Core\Application(__DIR__);
$app->boot();

$db = \App\Core\Database\Database::getInstance();

$permissions = $db->query('SELECT * FROM permissions WHERE slug LIKE ? ORDER BY id DESC', ['access.%'])->fetchAll();

echo 'Permissions starting with access.*:' . PHP_EOL;
foreach ($permissions as $perm) {
    echo 'ID: ' . $perm['id'] . ' - Slug: ' . $perm['slug'] . ' - Name: ' . $perm['name'] . PHP_EOL;
}

echo PHP_EOL . 'Checking admin role permissions...' . PHP_EOL;

// Get admin role ID
$role = $db->query('SELECT id FROM roles WHERE slug = ?', ['admin'])->fetch();
if (!$role) {
    echo 'Admin role not found' . PHP_EOL;
    exit;
}
$roleId = $role['id'];
echo 'Admin role ID: ' . $roleId . PHP_EOL;

// Get permissions for admin role
$adminPermissions = $db->query('SELECT p.slug FROM permissions p INNER JOIN role_permissions rp ON p.id = rp.permission_id WHERE rp.role_id = ? ORDER BY p.slug', [$roleId])->fetchAll();

echo 'Admin permissions:' . PHP_EOL;
foreach ($adminPermissions as $perm) {
    echo '- ' . $perm['slug'] . PHP_EOL;
}

// Check if admin has access permissions
echo PHP_EOL . 'Checking if admin has access.* permissions:' . PHP_EOL;
$accessPermissions = array_column($permissions, 'slug');
$adminPermissionSlugs = array_column($adminPermissions, 'slug');

foreach ($accessPermissions as $accessPerm) {
    if (in_array($accessPerm, $adminPermissionSlugs)) {
        echo '✓ ' . $accessPerm . PHP_EOL;
    } else {
        echo '✗ ' . $accessPerm . ' MISSING' . PHP_EOL;
    }
}

// Check for all permissions not assigned to admin
echo PHP_EOL . 'Checking all permissions not assigned to admin...' . PHP_EOL;
$allPermissions = $db->query('SELECT slug FROM permissions ORDER BY slug')->fetchAll(\PDO::FETCH_COLUMN);
$missingPermissions = array_diff($allPermissions, $adminPermissionSlugs);

if (empty($missingPermissions)) {
    echo '✓ Admin has ALL permissions!' . PHP_EOL;
} else {
    echo '✗ Admin is missing ' . count($missingPermissions) . ' permissions:' . PHP_EOL;
    foreach ($missingPermissions as $perm) {
        echo '  - ' . $perm . PHP_EOL;
    }
}