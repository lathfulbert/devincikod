<?php
require_once __DIR__ . '/bootstrap.php';

$db = \App\Core\Database\Database::getInstance();
$perms = $db->query('SELECT id, name, slug FROM permissions WHERE slug LIKE ?', ['%wallet%'])->fetchAll();
echo "Permissions Wallet en base :\n";
foreach ($perms as $p) {
    echo $p['id'] . ': ' . $p['name'] . ' (' . $p['slug'] . ")\n";
}
