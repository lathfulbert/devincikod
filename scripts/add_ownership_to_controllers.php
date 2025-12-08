<?php

/**
 * Script pour ajouter automatiquement le contrôle d'accès basé sur la propriété
 * aux controllers existants
 *
 * Usage: php scripts/add_ownership_to_controllers.php <module_name> <controller_name>
 *
 * Exemple: php scripts/add_ownership_to_controllers.php SmsCore SmsCampaignController
 */

if ($argc < 3) {
    echo "Usage: php scripts/add_ownership_to_controllers.php <module_name> <controller_name>\n";
    echo "Exemple: php scripts/add_ownership_to_controllers.php SmsCore SmsCampaignController\n";
    exit(1);
}

$moduleName = $argv[1];
$controllerName = $argv[2];

$controllerPath = __DIR__ . "/../Modules/{$moduleName}/Controllers/{$controllerName}.php";

if (!file_exists($controllerPath)) {
    echo "❌ Erreur: Le controller {$controllerPath} n'existe pas\n";
    exit(1);
}

$content = file_get_contents($controllerPath);

// Vérifier si le trait est déjà présent
if (strpos($content, 'use AuthorizesOwnership') !== false) {
    echo "✅ Le trait AuthorizesOwnership est déjà présent dans {$controllerName}\n";
    exit(0);
}

echo "🔧 Ajout du contrôle d'accès basé sur la propriété à {$controllerName}...\n\n";

// 1. Ajouter l'import du trait
if (!preg_match('/use App\\\\Core\\\\Authorization\\\\Traits\\\\AuthorizesOwnership;/', $content)) {
    // Trouver la position après le namespace
    $content = preg_replace(
        '/(namespace [^;]+;)/',
        "$1\n\nuse App\\Core\\Authorization\\Traits\\AuthorizesOwnership;",
        $content,
        1
    );
    echo "✓ Import du trait ajouté\n";
}

// 2. Ajouter le trait dans la classe
if (!preg_match('/use AuthorizesOwnership;/', $content)) {
    // Trouver la déclaration de classe et ajouter le trait
    $content = preg_replace(
        '/(class\s+\w+\s*{)/',
        "$1\n    use AuthorizesOwnership;\n",
        $content,
        1
    );
    echo "✓ Trait ajouté à la classe\n";
}

// 3. Ajouter l'initialisation dans le constructeur
if (!preg_match('/\$this->initializeOwnershipPolicy\(\);/', $content)) {
    // Si un constructeur existe déjà
    if (preg_match('/public function __construct\(\)/', $content)) {
        $content = preg_replace(
            '/(public function __construct\(\)\s*{)/',
            "$1\n        \$this->initializeOwnershipPolicy();",
            $content,
            1
        );
        echo "✓ Initialisation ajoutée au constructeur existant\n";
    } else {
        // Créer un nouveau constructeur
        $content = preg_replace(
            '/(use AuthorizesOwnership;\s*)/',
            "$1\n    public function __construct()\n    {\n        \$this->initializeOwnershipPolicy();\n    }\n",
            $content,
            1
        );
        echo "✓ Nouveau constructeur créé avec initialisation\n";
    }
}

// 4. Créer une sauvegarde
$backupPath = $controllerPath . '.backup_' . date('YmdHis');
copy($controllerPath, $backupPath);
echo "✓ Sauvegarde créée: " . basename($backupPath) . "\n";

// 5. Écrire le fichier modifié
file_put_contents($controllerPath, $content);
echo "✓ Fichier modifié écrit\n";

echo "\n✅ Migration terminée avec succès!\n\n";

echo "📝 Prochaines étapes manuelles:\n";
echo "1. Modifier les méthodes index() pour utiliser scopeByOwnership()\n";
echo "2. Ajouter authorizeView/Update/Delete() dans les méthodes appropriées\n";
echo "3. Passer canEdit/canDelete aux vues\n";
echo "4. Tester avec différents rôles utilisateurs\n\n";

echo "📖 Consultez OWNERSHIP_AUTHORIZATION_GUIDE.md pour plus de détails\n";
