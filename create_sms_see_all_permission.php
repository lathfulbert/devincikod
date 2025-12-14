<?php
// Script pour créer la permission sms.see_all si elle n'existe pas
require_once __DIR__ . '/bootstrap.php';
use Modules\RBAC\Models\Permission;

$slug = 'sms.see_all';
$name = 'Voir tous les SMS (Dashboard)';
$module = 'sms_core';

$existing = Permission::where('slug', $slug)->first();
if (!$existing) {
    $perm = new Permission();
    $perm->name = $name;
    $perm->slug = $slug;
    $perm->module_slug = $module;
    $perm->description = 'Permet de voir tous les SMS sur le dashboard, même ceux des autres utilisateurs.';
    $perm->save();
    echo "✓ Permission $slug créée\n";
} else {
    echo "✓ Permission $slug déjà existante\n";
}