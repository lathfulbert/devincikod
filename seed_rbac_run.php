<?php

require_once __DIR__ . '/vendor/autoload.php';

use App\Core\Application;
use Modules\RBAC\Database\Seeders\RBACSeeder;

$app = new Application(__DIR__);
$app->boot();

$seeder = new RBACSeeder();
$seeder->run();
