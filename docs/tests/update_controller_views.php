<?php

/**
 * Script de Migration Automatique des Chemins de Vues
 * 
 * Ce script met à jour tous les contrôleurs pour utiliser le nouveau format:
 * {module_lowercase}/{subfolder}/{view}
 */

$modulesPath = __DIR__ . '/Modules';
$updatedFiles = [];
$errors = [];

// Mapping des modules avec des noms spéciaux
$moduleMapping = [
    'SmsCore' => 'smscore',
    'ApiKeys' => 'apikeys',
    // Ajouter d'autres si nécessaire
];

echo "=== Début de la migration des chemins de vues ===\n\n";

// Parcourir tous les contrôleurs
$controllers = glob($modulesPath . '/*/Controllers/*Controller.php');

foreach ($controllers as $controller) {
    echo "Traitement: " . basename($controller) . "\n";

    // Lire le contenu
    $content = file_get_contents($controller);
    $originalContent = $content;

    // Extraire le nom du module depuis le chemin
    if (preg_match('#Modules/([^/]+)/Controllers#', $controller, $matches)) {
        $moduleNameOriginal = $matches[1];
        $moduleName = $moduleMapping[$moduleNameOriginal] ?? strtolower($moduleNameOriginal);

        // Pattern 1: backend/{subfolder}/{view} -> {module}/{subfolder}/{view}
        $pattern1 = '/(\$app->view->render\([\'"])backend\/([^\/]+)\/([^\'"]+)([\'"])/';
        $replacement1 = function ($match) use ($moduleName) {
            $subfolder = $match[2];
            $view = $match[3];
            return $match[1] . $moduleName . '/' . $subfolder . '/' . $view . $match[4];
        };
        $content = preg_replace_callback($pattern1, $replacement1, $content);

        // Pattern 2: {subfolder}/{view} -> {module}/{subfolder}/{view}
        // Seulement si le module est Contacts ou similaire (déjà partiellement correct)
        if (in_array($moduleNameOriginal, ['Contacts', 'Demo'])) {
            $pattern2 = '/(\$app->view->render\([\'"])([a-z]+)\/([^\'"]+)([\'"])/';
            $replacement2 = function ($match) use ($moduleName) {
                $subfolder = $match[2];
                $view = $match[3];
                // Ne pas changer si déjà au bon format
                if ($subfolder === $moduleName) {
                    return $match[0];
                }
                return $match[1] . $moduleName . '/' . $subfolder . '/' . $view . $match[4];
            };
            $content = preg_replace_callback($pattern2, $replacement2, $content);
        }

        // Vérifier si des changements ont été effectués
        if ($content !== $originalContent) {
            // Sauvegarder le fichier
            file_put_contents($controller, $content);
            $updatedFiles[] = $controller;
            echo "  ✓ Mis à jour\n";
        } else {
            echo "  - Aucun changement nécessaire\n";
        }
    } else {
        $errors[] = "Impossible d'extraire le nom du module pour: $controller";
        echo "  ✗ Erreur\n";
    }
}

echo "\n=== Résumé de la migration ===\n";
echo "Fichiers mis à jour: " . count($updatedFiles) . "\n";
echo "Erreurs: " . count($errors) . "\n\n";

if (!empty($updatedFiles)) {
    echo "Fichiers modifiés:\n";
    foreach ($updatedFiles as $file) {
        echo "  - " . str_replace($modulesPath . '/', '', $file) . "\n";
    }
}

if (!empty($errors)) {
    echo "\nErreurs:\n";
    foreach ($errors as $error) {
        echo "  ! $error\n";
    }
}

echo "\n=== Migration terminée ===\n";
