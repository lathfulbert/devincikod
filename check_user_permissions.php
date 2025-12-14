<?php
require_once __DIR__ . '/bootstrap.php';

use App\Core\Database\Database;

$db = Database::getInstance();
$result = $db->query("SELECT r.name as role_name, p.slug as permission FROM roles r JOIN role_permissions rp ON r.id = rp.role_id JOIN permissions p ON rp.permission_id = p.id WHERE r.name = 'Utilisateur' AND p.slug LIKE 'access.%'")->fetchAll(PDO::FETCH_ASSOC);
foreach($result as $row) {
    echo $row['role_name'] . ': ' . $row['permission'] . PHP_EOL;
}