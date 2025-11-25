<?php

namespace App\Core\Console\Command;

use App\Core\Application;
use Exception;

class ModuleCreateCommand
{
    public function execute(Application $app, array $args): void
    {
        $moduleName = $args[0] ?? null;

        if (!$moduleName) {
            echo "❌ Erreur : vous devez fournir le nom du module.\n";
            echo "Usage: php sunu module:create <nom>\n";
            exit(1);
        }

        // Normaliser le nom (Première lettre majuscule)
        $moduleName = ucfirst($moduleName);
        $basePath   = $app->getBasePath() . '/Modules/' . $moduleName;

        if (is_dir($basePath)) {
            echo "❌ Le répertoire du module existe déjà : $basePath\n";
            exit(1);
        }

        try {
            echo "🔨 Création du module '$moduleName'...\n";

            // Créer l'arborescence
            $dirs = [
                $basePath,
                "$basePath/Controllers",
                "$basePath/Views",
                "$basePath/Database",
                "$basePath/Database/Migrations",
                "$basePath/Models",
                "$basePath/Config"
            ];

            foreach ($dirs as $d) {
                if (!mkdir($d, 0755, true) && !is_dir($d)) {
                    throw new Exception("Impossible de créer le répertoire $d");
                }
            }

            // 1️⃣ module.json
            $moduleJson = [
                "name"        => $moduleName,
                "version"     => "1.0.0",
                "description" => "Module $moduleName créé via CLI",
                "author"      => "VotreNom",
                "license"     => "MIT"
            ];
            file_put_contents("$basePath/module.json", json_encode($moduleJson, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

            // 2️⃣ Controller de base
            $controller = <<<PHP
<?php

namespace Modules\\$moduleName\\Controllers;

class {$moduleName}Controller
{
    public function index()
    {
        echo "Bienvenue dans le module $moduleName !";
    }
}

PHP;
            file_put_contents("$basePath/Controllers/{$moduleName}Controller.php", $controller);

            // 3️⃣ Vue d'exemple
            $view = <<<HTML
<!DOCTYPE html>
<html>
<head>
    <title>Module $moduleName</title>
</head>
<body>
    <h1>Bienvenue dans le module $moduleName</h1>
    <p>Cette vue a été générée automatiquement.</p>
</body>
</html>
HTML;
            file_put_contents("$basePath/Views/index.php", $view);

            // 4️⃣ Migration d'exemple
            $timestamp = date('Y_m_d_His');
            $tableName = strtolower($moduleName);
            $migrationName = "001_create_{$tableName}_table.php";
            $migration = <<<PHP
<?php

use App\Core\Database\Database;

return new class {
    public function up()
    {
        \$db = Database::getInstance();
        \$db->query("CREATE TABLE IF NOT EXISTS {$tableName}_data (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(255) NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )");
    }

    public function down()
    {
        \$db = Database::getInstance();
        \$db->query("DROP TABLE IF EXISTS {$tableName}_data");
    }
};

PHP;
            file_put_contents("$basePath/Database/Migrations/$migrationName", $migration);

            // 5️⃣ Module class
            $moduleClass = <<<PHP
<?php

namespace Modules\\$moduleName;

use App\Core\Module\AbstractModule;

class {$moduleName}Module extends AbstractModule
{
    public function getRoutes(): array
    {
        return [
            ['GET', '/admin/$moduleName', ['Modules\\\\$moduleName\\\\Controllers\\\\{$moduleName}Controller', 'index']],
        ];
    }

    protected function getModulePath(): string
    {
        return __DIR__;
    }
}

PHP;
            file_put_contents("$basePath/{$moduleName}Module.php", $moduleClass);

            echo "✅ Module '$moduleName' créé avec succès dans $basePath\n";
            echo "\n📋 Prochaines étapes :\n";
            echo "   1. Installer le module : accédez à l'admin ou utilisez php sunu migrate\n";
            echo "   2. Activer le module  : php sunu module:activate $moduleName\n";
            echo "   3. Accéder au module  : /admin/$moduleName\n";
        } catch (Exception $e) {
            echo "❌ Erreur lors de la création : " . $e->getMessage() . "\n";
            exit(1);
        }
    }
}
