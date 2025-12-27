<?php
require_once __DIR__ . '/bootstrap.php';
// Script pour attribuer la permission manage.widgets au rôle admin

use Modules\RBAC\Models\Role;
use Modules\RBAC\Models\Permission;

$role = Role::where('name', 'admin')->first();
if (!$role) {
    exit("Rôle admin introuvable\n");
}

$permission = Permission::where('name', 'manage.widgets')->first();
if (!$permission) {
    // Créer la permission si elle n'existe pas
    $permission = new Permission();
    $permission->name = 'manage.widgets';
    $permission->description = 'Gérer les widgets du dashboard';
    $permission->save();
    echo "Permission manage.widgets créée\n";
}

if (!$role->hasPermission('manage.widgets')) {
    $role->givePermissionTo('manage.widgets');
    echo "Permission manage.widgets attribuée au rôle admin\n";
} else {
    echo "Le rôle admin possède déjà la permission manage.widgets\n";
}
