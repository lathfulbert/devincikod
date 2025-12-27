<?php
require_once __DIR__ . '/bootstrap.php';

$db = \App\Core\Database\Database::getInstance();
$ids = [148,149,150,151,152,153,154];
foreach ($ids as $id) {
    $db->query('DELETE FROM permissions WHERE id = ?', [$id]);
    echo 'Suppression permission id ' . $id . "\n";
}
echo "Nettoyage terminé.\n";
