<?php

/**
 * Script helper pour activer le Author Tracking sur un modèle
 *
 * Usage:
 * php Core/Database/Scripts/enable_author_tracking.php Modules/YourModule/Models/YourModel.php
 */

if (php_sapi_name() !== 'cli') {
    die("Ce script ne peut être exécuté que depuis la ligne de commande.\n");
}

// Vérifier les arguments
if ($argc < 2) {
    echo "❌ Usage: php {$argv[0]} <chemin-vers-le-modele>\n";
    echo "\nExemples:\n";
    echo "  php {$argv[0]} Modules/Users/Models/User.php\n";
    echo "  php {$argv[0]} Modules/Settings/Models/Setting.php\n";
    exit(1);
}

$modelPath = $argv[1];
$basePath = dirname(__DIR__, 3); // Remonter à la racine du projet
$fullPath = $basePath . '/' . $modelPath;

// Vérifier que le fichier existe
if (!file_exists($fullPath)) {
    echo "❌ Erreur: Le fichier '$fullPath' n'existe pas.\n";
    exit(1);
}

echo "🔧 Activation du Author Tracking\n";
echo "=====================================\n\n";
echo "📁 Fichier: $modelPath\n\n";

// Lire le contenu du fichier
$content = file_get_contents($fullPath);

// Vérifier si le trait est déjà ajouté
if (strpos($content, 'use HasAuthor') !== false) {
    echo "⚠️  Le trait HasAuthor est déjà présent dans ce modèle.\n";
    exit(0);
}

// Vérifier si c'est bien un modèle
if (!preg_match('/class\s+\w+\s+extends\s+Model/', $content)) {
    echo "❌ Erreur: Ce fichier ne semble pas être un modèle (ne extends pas Model).\n";
    exit(1);
}

// Faire une sauvegarde
$backupPath = $fullPath . '.backup_' . date('YmdHis');
copy($fullPath, $backupPath);
echo "✅ Sauvegarde créée: {$backupPath}\n\n";

// Étape 1 : Ajouter l'import
if (!preg_match('/use\s+App\\\\Core\\\\Database\\\\Traits\\\\HasAuthor;/', $content)) {
    // Trouver la dernière ligne "use"
    if (preg_match('/^use\s+.+;$/m', $content, $matches, PREG_OFFSET_CAPTURE)) {
        $lastUsePosition = $matches[0][1] + strlen($matches[0][0]);
        $content = substr_replace($content, "\nuse App\\Core\\Database\\Traits\\HasAuthor;", $lastUsePosition, 0);
        echo "✅ Import ajouté: use App\\Core\\Database\\Traits\\HasAuthor;\n";
    } else {
        // Ajouter après le namespace
        $content = preg_replace(
            '/(namespace\s+[^;]+;)/i',
            "$1\n\nuse App\\Core\\Database\\Traits\\HasAuthor;",
            $content
        );
        echo "✅ Import ajouté après le namespace\n";
    }
}

// Étape 2 : Ajouter le trait dans la classe
if (preg_match('/(class\s+\w+\s+extends\s+Model\s*\{)/s', $content, $matches, PREG_OFFSET_CAPTURE)) {
    $classPosition = $matches[0][1] + strlen($matches[0][0]);

    // Vérifier s'il y a déjà un "use SomeTrait;"
    $afterClass = substr($content, $classPosition, 200);

    if (preg_match('/^\s*use\s+/', $afterClass)) {
        // Il y a déjà des traits, ajouter HasAuthor
        $content = substr_replace($content, "\n    use HasAuthor;", $classPosition, 0);
        echo "✅ Trait ajouté: use HasAuthor;\n";
    } else {
        // Pas de traits, en ajouter un
        $content = substr_replace($content, "\n    use HasAuthor;\n", $classPosition, 0);
        echo "✅ Trait ajouté: use HasAuthor;\n";
    }
}

// Étape 3 : Vérifier SoftDeletes
$hasSoftDeletes = strpos($content, 'use SoftDeletes') !== false;

if ($hasSoftDeletes) {
    echo "✅ Le modèle utilise déjà SoftDeletes (deleted_by sera trackée)\n";

    // S'assurer que l'import SoftDeletes existe
    if (!preg_match('/use\s+App\\\\Core\\\\Database\\\\Traits\\\\SoftDeletes;/', $content)) {
        $content = preg_replace(
            '/(use\s+App\\\\Core\\\\Database\\\\Traits\\\\HasAuthor;)/i',
            "$1\nuse App\\Core\\Database\\Traits\\SoftDeletes;",
            $content
        );
        echo "✅ Import SoftDeletes ajouté\n";
    }
} else {
    echo "ℹ️  Le modèle n'utilise pas SoftDeletes (deleted_by ne sera pas trackée)\n";
    echo "   Pour activer SoftDeletes, ajoutez manuellement:\n";
    echo "   - use App\\Core\\Database\\Traits\\SoftDeletes;\n";
    echo "   - use SoftDeletes; (dans la classe)\n";
}

// Sauvegarder les modifications
file_put_contents($fullPath, $content);

echo "\n✅ Modifications enregistrées!\n\n";

// Afficher le résumé
echo "=====================================\n";
echo "📋 Résumé des modifications:\n";
echo "=====================================\n";
echo "✅ Import HasAuthor ajouté\n";
echo "✅ Trait HasAuthor ajouté à la classe\n";
if ($hasSoftDeletes) {
    echo "✅ Compatible avec SoftDeletes\n";
}
echo "\n";

echo "🎯 Prochaines étapes:\n";
echo "1. Vérifiez le fichier modifié : $modelPath\n";
echo "2. Exécutez les tests pour vérifier que tout fonctionne\n";
echo "3. Committez vos changements\n";
echo "\n";

echo "💡 Utilisation dans le code:\n";
echo "   \$model->getCreatorName();   // Obtenir le nom du créateur\n";
echo "   \$model->getUpdaterName();   // Obtenir le nom du modificateur\n";
echo "   \$model->isCreatedBy(\$userId); // Vérifier l'auteur\n";
echo "\n";

echo "📚 Documentation:\n";
echo "   Core/Database/AUTHOR_TRACKING.md\n";
echo "   Core/Database/QUICK_START_AUTHOR_TRACKING.md\n";
echo "\n";

echo "✅ Author Tracking activé avec succès! 🎉\n";
