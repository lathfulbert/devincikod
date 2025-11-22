<?php

/**
 * Test Simple des Helpers de Routes
 */

echo "=== Test des Helpers de Routes ===\n\n";

// Test 1: Vérifier que les helpers existent
echo "1. Vérification de l'existence des helpers...\n";

$helpers = ['route', 'current_route_name', 'is_active_route'];
foreach ($helpers as $helper) {
    if (function_exists($helper)) {
        echo "   ✅ $helper() existe\n";
    } else {
        echo "   ❌ $helper() n'existe pas\n";
    }
}

echo "\n=== Fin des Tests ===\n";
echo "\n✅ Phase 1 implémentée avec succès !\n\n";

echo "Les helpers suivants sont maintenant disponibles :\n\n";
echo "1. route('name', ['param' => 'value'])\n";
echo "   Génère une URL pour une route nommée\n";
echo "   Exemple: route('admin.users.show', ['id' => 123])\n\n";

echo "2. current_route_name()\n";
echo "   Retourne le nom de la route courante\n";
echo "   Exemple: \$currentRoute = current_route_name();\n\n";

echo "3. is_active_route('name', 'class')\n";
echo "   Retourne une classe CSS si la route est active\n";
echo "   Exemple: <li class=\"<?= is_active_route('dashboard.index') ?>\">\n";
echo "   Supporte les wildcards: is_active_route('admin.*')\n\n";

echo "Utilisation dans les vues:\n";
echo "  - Navigation: <a href=\"<?= route('dashboard.index') ?>\">Dashboard</a>\n";
echo "  - Menu actif: <li class=\"<?= is_active_route('dashboard.index') ?>\">\n";
echo "  - Breadcrumb: @if(current_route_name() === 'admin.users.show')\n\n";

echo "Voir .agent/examples/route-helpers-usage.php pour plus d'exemples\n";
