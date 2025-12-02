<?php

require 'vendor/autoload.php';
require 'Core/Support/helpers.php';

$app = new \App\Core\Application(__DIR__);
$app->boot();

$db = \App\Core\Database\Database::getInstance();

echo "=== Vérification de la table logs ===\n\n";

try {
    // Vérifier si la table existe
    $result = $db->query("SHOW TABLES LIKE 'logs'")->fetch();

    if ($result) {
        echo "✓ Table 'logs' existe\n\n";

        // Vérifier la structure
        echo "Structure de la table:\n";
        $columns = $db->query("DESCRIBE logs")->fetchAll();
        foreach ($columns as $col) {
            echo "  - {$col['Field']} ({$col['Type']}) " . ($col['Null'] === 'NO' ? 'NOT NULL' : 'NULL') . "\n";
        }

        // Compter les logs
        echo "\nNombre de logs : ";
        $count = $db->query("SELECT COUNT(*) as count FROM logs")->fetch();
        echo $count['count'] ?? 0;
        echo "\n";

    } else {
        echo "✗ Table 'logs' N'EXISTE PAS\n\n";
        echo "Création de la table logs...\n";

        $db->query("
            CREATE TABLE IF NOT EXISTS logs (
                id INT AUTO_INCREMENT PRIMARY KEY,
                level VARCHAR(50) NOT NULL,
                message TEXT NOT NULL,
                context TEXT,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                INDEX idx_level (level),
                INDEX idx_created_at (created_at)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");

        echo "✓ Table 'logs' créée avec succès\n";
    }

} catch (Exception $e) {
    echo "✗ Erreur : " . $e->getMessage() . "\n";
}
