<?php

require 'vendor/autoload.php';
require 'Core/Support/helpers.php';

$app = new \App\Core\Application(__DIR__);
$app->boot();

$db = \App\Core\Database\Database::getInstance();

// Get admin role
$role = $db->query('SELECT id FROM roles WHERE slug = ?', ['admin'])->fetch();
if (!$role) {
    echo 'Admin role not found' . PHP_EOL;
    exit;
}
$roleId = $role['id'];

echo 'Assigning ALL permissions to admin role...' . PHP_EOL;

// Get all permissions
$allPermissions = $db->query('SELECT id, slug FROM permissions ORDER BY slug')->fetchAll();

$assigned = 0;
$alreadyAssigned = 0;

foreach ($allPermissions as $perm) {
    // Check if already assigned
    $exists = $db->query('SELECT COUNT(*) as count FROM role_permissions WHERE role_id = ? AND permission_id = ?', [$roleId, $perm['id']])->fetch();

    if ($exists['count'] > 0) {
        $alreadyAssigned++;
        continue;
    }

    // Assign permission
    $db->query('INSERT INTO role_permissions (role_id, permission_id, created_at) VALUES (?, ?, NOW())', [$roleId, $perm['id']]);
    $assigned++;
    echo '✓ Assigned: ' . $perm['slug'] . PHP_EOL;
}

echo PHP_EOL . 'Summary:' . PHP_EOL;
echo 'Assigned: ' . $assigned . PHP_EOL;
echo 'Already assigned: ' . $alreadyAssigned . PHP_EOL;
echo 'Total permissions: ' . count($allPermissions) . PHP_EOL;