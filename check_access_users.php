<?php
require_once __DIR__ . '/bootstrap.php';

use App\Core\Database\Database;

$db = Database::getInstance();
$result = $db->query('SELECT p.slug FROM permissions p JOIN role_permissions rp ON p.id = rp.permission_id JOIN roles r ON rp.role_id = r.id WHERE r.name = "Utilisateur" AND p.slug = "access.users"')->fetchAll();
echo count($result) > 0 ? 'OUI' : 'NON';
?>