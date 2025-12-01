<?php

/**
 * Script de Simplification des Syntaxes de Vues
 * 
 * Remplace $app->view->render(...) par view(...)
 */

$modulesPath = __DIR__ . '/Modules';
$updatedFiles = [];

echo "=== Début de la simplification des syntaxes de vues ===\n\n";

$controllers = glob($modulesPath . '/*/Controllers/*Controller.php');

foreach ($controllers as $controller) {
    echo "Traitement: " . basename($controller) . "\n";

    $content = file_get_contents($controller);
    $originalContent = $content;

    // Pattern 1: echo $app->view->render(...) -> echo view(...)
    $pattern1 = '/echo\s+\$app->view->render\(/';
    $replacement1 = 'echo view(';
    $content = preg_replace($pattern1, $replacement1, $content);

    // Pattern 2: $app->view->render(...) (sans echo) -> view(...)
    $pattern2 = '/(?<!echo\s)\$app->view->render\(/';
    $replacement2 = 'view(';
    $content = preg_replace($pattern2, $replacement2, $content);

    // Nettoyer les lignes $app = Application::getInstance() qui ne sont plus nécessaires
    // Seulement si elles ne sont utilisées que pour view
    $lines = explode("\n", $content);
    $newLines = [];
    $skipNext = false;

    foreach ($lines as $i => $line) {
        // Si la ligne contient uniquement $app = Application::getInstance()
        // et que la ligne suivante utilise view(), on peut la supprimer
        if (preg_match('/^\s*\$app\s*=\s*Application::getInstance\(\);/', $line)) {
            // Regarder les prochaines lignes pour voir s'il y a d'autres usages de $app
            $hasOtherUsage = false;
            for ($j = $i + 1; $j < count($lines) && $j < $i + 10; $j++) {
                if (preg_match('/\$app->(?!view->render)/', $lines[$j])) {
                    $hasOtherUsage = true;
                    break;
                }
            }

            if (!$hasOtherUsage && isset($lines[$i + 1]) && strpos($lines[$i + 1], 'view(') !== false) {
                // Supprimer cette ligne et la ligne vide qui suit si elle existe
                continue;
            }
        }

        $newLines[] = $line;
    }

    $content = implode("\n", $newLines);

    // Vérifier si des changements ont été effectués
    if ($content !== $originalContent) {
        file_put_contents($controller, $content);
        $updatedFiles[] = $controller;
        echo "  ✓ Simplifié\n";
    } else {
        echo "  - Aucun changement nécessaire\n";
    }
}

echo "\n=== Résumé ===\n";
echo "Fichiers simplifiés: " . count($updatedFiles) . "\n\n";

if (!empty($updatedFiles)) {
    echo "Fichiers modifiés:\n";
    foreach ($updatedFiles as $file) {
        echo "  - " . str_replace($modulesPath . '/', '', $file) . "\n";
    }
}

echo "\n=== Simplification terminée ===\n";
