# Désinstallation Complète des Modules

## Statut Actuel de la Désinstallation

Le `ModuleInstaller::uninstall()` effectue actuellement :

### ✅ Ce qui est géré

1. **Suppression de l'entrée BDD** - Via `ModuleRegistry::unregister()`
2. **Suppression des fichiers** - Dossier `Modules/{NomModule}` complètement supprimé

### ❌ Ce qui manque

3. **Rollback des migrations** - Les tables créées restent en BDD
4. **Nettoyage du cache** - Le cache compilé des vues reste

---

## Améliorations Nécessaires

### 1. Rollback des Migrations

#### Problème

Quand un module est désinstallé, ses tables restent dans la base de données.

#### Solution

Appeler automatiquement la méthode `down()` de chaque migration :

```php
private function rollbackMigrations(string $modulePath, string $moduleName): void
{
    $migrationsPath = $modulePath . '/Database/Migrations';

    if (!is_dir($migrationsPath)) {
        return; // Pas de migrations
    }

    $migrations = glob($migrationsPath . '/*.php');
    rsort($migrations); // Ordre inverse

    foreach ($migrations as $migrationFile) {
        try {
            require_once $migrationFile;

            // Construire le nom de classe
            $filename = basename($migrationFile, '.php');
            $className = preg_replace('/^\d+_/', '', $filename);
            $fullClassName = "Modules\\{$moduleName}\\Database\\Migrations\\{$className}";

            if (class_exists($fullClassName)) {
                $migration = new $fullClassName();
                if (method_exists($migration, 'down')) {
                    $migration->down(); // Supprime la table
                }
            }
        } catch (Exception $e) {
            error_log("Erreur rollback migration: " . $e->getMessage());
        }
    }
}
```

### 2. Nettoyage du Cache

#### Problème

Les fichiers compilés de vues restent dans `storage/cache/views/`.

#### Solution

```php
private function clearModuleCache(string $moduleName): void
{
    $app = Application::getInstance();
    $cachePath = $app->getBasePath() . '/storage/cache';

    // Cache des vues
    $viewCachePath = $cachePath . '/views';
    if (is_dir($viewCachePath)) {
        $cacheFiles = glob($viewCachePath . '/*.php');
        foreach ($cacheFiles as $file) {
            @unlink($file);
        }
    }

    // Cache spécifique au module (si existe)
    $moduleCachePath = $cachePath . '/modules/' . strtolower($moduleName);
    if (is_dir($moduleCachePath)) {
        $this->cleanup($moduleCachePath);
    }
}
```

### 3. Processus Complet de Désinstallation

```php
public function uninstall(string $moduleName): array
{
    $modulePath = $this->modulesPath . '/' . $moduleName;

    if (!is_dir($modulePath)) {
        return [
            'success' => false,
            'message' => "Le module '$moduleName' n'existe pas."
        ];
    }

    try {
        $app = Application::getInstance();
        $registry = $app->moduleManager->getRegistry();

        // 1. Rollback migrations (supprimer les tables)
        $this->rollbackMigrations($modulePath, $moduleName);

        // 2. Désactiver le module (si activé)
        if ($registry->isEnabled($moduleName)) {
            $registry->setEnabled($moduleName, false);
        }

        // 3. Supprimer l'entrée BDD
        $registry->unregister($moduleName);

        // 4. Nettoyer le cache
        $this->clearModuleCache($moduleName);

        // 5. Supprimer les fichiers
        $this->cleanup($modulePath);

        return [
            'success' => true,
            'message' => "Module '{$moduleName}' complètement désinstallé (fichiers, tables, cache)."
        ];

    } catch (Exception $e) {
        return [
            'success' => false,
            'message' => "Erreur lors de la désinstallation : " . $e->getMessage()
        ];
    }
}
```

---

## Checklist de Désinstallation Complète

Lors de la désinstallation d'un module, le système doit :

- [x] **1. Dossier Modules** → Supprimer `Modules/{NomModule}/`
- [ ] **2. Tables BDD** → Exécuter `down()` de chaque migration
- [x] **3. Registre BDD** → Supprimer l'entrée dans la table `modules`
- [ ] **4. Cache vues** → Vider `storage/cache/views/`
- [ ] **5. Cache module** → Vider `storage/cache/modules/{nom}/`
- [ ] **6. Sidebar** → Le menu disparaît automatiquement (dynamique)
- [ ] **7. Routes** → Les routes disparaissent automatiquement (dynamique)

---

## Exemple de Migration avec `down()`

Pour que le rollback fonctionne, les migrations doivent impl émenter la méthode `down()` :

```php
<?php

namespace Modules\Blog\Database\Migrations;

use App\Core\Database\Migration;
use App\Core\Database\Schema\Schema;
use App\Core\Database\Schema\Blueprint;

class CreateBlogPostsTable extends Migration
{
    public function up(): void
    {
        Schema::create('blog_posts', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('content');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('blog_posts'); // ← Important !
    }
}
```

---

## Protection des Modules Core

Les modules système ne doivent pas être supprimables :

```php
public function uninstall(string $moduleName): array
{
    // Modules protégés
    $coreModules = ['Admin', 'RBAC'];

    if (in_array($moduleName, $coreModules)) {
        return [
            'success' => false,
            'message' => "Le module '{$moduleName}' est un module système et ne peut pas être supprimé."
        ];
    }

    // Suite de la désinstallation...
}
```

---

## Recommandations

### Pour les développeurs de modules

1. **Toujours implémenter `down()`** dans les migrations
2. **Tester la désinstallation** du module pendant le développement
3. **Documenter les dépendances** dans le `README.md`

### Pour l'administrateur

1. **Sauvegarder la BDD** avant désinstallation
2. **Vérifier les dépendances** (autres modules qui utilisent celui-ci)
3. **Désactiver d'abord**, puis désinstaller

---

## À Faire

Pour compléter le système de désinstallation, ajouter :

1. ✅ Documentation (ce fichier)
2. ⏳ Implémentation de `rollbackMigrations()`
3. ⏳ Implémentation de `clearModuleCache()`
4. ⏳ Protection des modules core
5. ⏳ Confirmation avant suppression dans l'UI
6. ⏳ Log des désinstallations
