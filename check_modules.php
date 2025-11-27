<?php
require 'vendor/autoload.php';
use App\Core\Database\Database;
$db = Database::getInstance();
$modules = $db->query('SELECT name, enabled FROM modules ORDER BY name');
echo "Modules in database:\n";
foreach ($modules as $m) {
    echo $m->name . ' - enabled: ' . $m->enabled . "\n";
}
