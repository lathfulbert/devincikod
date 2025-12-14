<?php
// Migration : Ajout de la permission sms.see_all pour dashboard SMS
// Permet de voir tous les SMS sur le dashboard, même ceux des autres utilisateurs

use Modules\RBAC\Models\Permission;

return new class {
    public function up() {
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
    }
    public function down() {
        $slug = 'sms.see_all';
        $perm = Permission::where('slug', $slug)->first();
        if ($perm) {
            $perm->delete();
            echo "✓ Permission $slug supprimée\n";
        }
    }
};
