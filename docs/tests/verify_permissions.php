<?php

require_once __DIR__ . '/vendor/autoload.php';

// Bootstrap the application
require_once __DIR__ . '/Core/Support/helpers.php';
$app = new \App\Core\Application(__DIR__);
$app->boot();

// Manually trigger the PermissionLoader
echo "Loading permissions...\n";
$loader = new \Modules\RBAC\Services\PermissionLoader();
$count = $loader->scanAndLoad();
echo "Loaded {$count} permissions.\n";

$loader->seedDefaultRoles();
echo "Seeded default roles.\n";

// Verify database content
$permissions = \Modules\RBAC\Models\Permission::all();
echo "Total permissions in DB: " . count($permissions) . "\n";
foreach ($permissions as $p) {
    echo "- {$p->slug}: {$p->description}\n";
}
