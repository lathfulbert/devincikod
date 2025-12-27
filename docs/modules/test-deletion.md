# Test de Suppression de Module

## Diagnostic

Pour supprimer un module avec succès, le système doit :

1. ✅ **Flash messages corrigés** - Utilise maintenant `$_SESSION['flash']`
2. ✅ **Route définie** - `/admin/modules/uninstall` existe3. ✅ **Contrôleur fonctionnel** - `ModuleController::uninstall()` appelle `ModuleManager`
3. ✅ **ModuleActivator** - Désactive automatiquement si activé
4. ❌ **Rollback migrations** - La méthode existe mais est vide

## Processus Actuel

```
1. Utilisateur clique "Désinstaller"
2. POST → /admin/modules/uninstall (name=Akpa)
3. ModuleController::uninstall()
4. ModuleManager::uninstallModule('Akpa')
5. ModuleActivator::uninstall(module)
   ├─ Désactive si activé ✅
   ├─ Appelle module->onUninstall() ✅
   ├─ rollbackMigrations() ❌ (vide)
   └─ registry->unregister() ✅
6. ModuleInstaller::cleanup() - Supprime les fichiers ❌ (non appelé)
```

## Problème Identifié

`ModuleActivator::rollbackMigrations()` est vide :

```php
protected function rollbackMigrations(ModuleContract $module): void
{
    // Rollback logic can be implemented here if needed
    // For now, we'll keep it simple
}
```

**Résultat** : Les tables restent en BDD, seuls les fichiers et l'entrée de registre sont supprimés.

## Solution Temporaire

Pour tester la suppression :

1. Le module Akpa devrait être supprimé de `Modules/Akpa/`
2. L'entrée BDD devrait être supprimée
3. Mais la table `akpa_posts` restera

## Test Manuel

```sql
-- Vérifier si la table existe encore
SHOW TABLES LIKE 'akpa_posts';

-- Vérifier si le module est dans la BDD
SELECT * FROM modules WHERE name = 'Akpa';

-- Vérifier si le dossier existe
-- Modules/Akpa/ devrait être supprimé
```

## À Implémenter

La méthode `rollbackMigrations()` dans `ModuleActivator` devrait :

```php
protected function rollbackMigrations(ModuleContract $module): void
{
    $moduleName = $module->getName();
    $migrationPath = dirname((new \ReflectionClass($module))->getFileName()) . '/Database/Migrations';

    if (!is_dir($migrationPath)) {
        return;
    }

    $files = glob($migrationPath . '/*.php');
    rsort($files); // Ordre inverse

    foreach ($files as $file) {
        require_once $file;
        $className = $this->getMigrationClassName($file, $moduleName);

        if (class_exists($className)) {
            $migration = new $className();
            if (method_exists($migration, 'down')) {
                $migration->down();
            }
        }
    }
}
```
