# Architecture de Modules Auto-Suffisants

## Philosophie

Les modules SunuFramework suivent le principe **"Module Auto-Suffisant"** : chaque module contient **toutes** ses ressources (contrôleurs, vues, migrations, assets) dans son propre dossier.

## Structure d'un Module

```
Modules/
└── NomModule/
    ├── module.json                 # Manifest du module
    ├── NomModuleModule.php         # Classe principale
    ├── Controllers/                # Contrôleurs
    │   └── NomController.php
    ├── Views/                      # Vues (templates)
    │   ├── index.php
    │   ├── create.php
    │   └── edit.php
    ├── Database/                   # Base de données
    │   └── Migrations/
    │       └── 001_CreateTable.php
    ├── Assets/                     # Ressources (optionnel)
    │   ├── css/
    │   └── js/
    └── README.md                   # Documentation
```

## Résolution des Vues

### Ordre de Recherche

Le système de vues cherche dans cet ordre :

1. **Templates globaux** (priorité) : `templates/{view}.php`
2. **Module Views** (fallback) : `Modules/{ModuleName}/Views/{view}.php`

### Exemples

#### Vue du module Akpa

```php
// Dans AkpaController.php
echo $app->view->render('akpa/index', [
    'title' => 'Liste'
]);
```

**Recherche** :

1. ❌ `templates/akpa/index.php` (n'existe pas)
2. ✅ `Modules/Akpa/Views/index.php` (trouvé !)

#### Vue globale (layouts)

```php
echo $app->view->render('backend/layouts/master', $data);
```

**Recherche** :

1. ✅ `templates/backend/layouts/master.php` (trouvé !)

### Convention de Nommage

Pour qu'un module soit auto-détecté, la structure de la vue doit être :

```
{modulename}/{viewname}
```

- `modulename` → Premier segment, converti en PascalCase pour trouver le module
- `viewname` → Reste du chemin vers la vue

## Avantages de Cette Approche

### ✅ Distribution Simplifiée

- Un seul fichier ZIP contient tout le module
- Upload → Installation automatique
- Pas de fichiers éparpillés

### ✅ Désinstallation Propre

```bash
# Supprimer un module = supprimer son dossier
rm -rf Modules/Akpa
```

### ✅ Isolation

- Chaque module est indépendant
- Pas de conflits de noms de vues
- Facile à versionner

### ✅ Compatibilité

- Override possible en créant `templates/akpa/index.php`
- Le système cherchera d'abord dans templates/

## Installation d'un Module

### Étapes Automatiques

1. **Upload du ZIP** via `/admin/modules/upload`
2. **Extraction** dans `Modules/NomModule/`
3. **Validation** du `module.json`
4. **Enregistrement** dans la base de données
5. **Prêt à l'utilisation** !

### Aucune Configuration Supplémentaire

- ✅ Les vues sont automatiquement trouvées
- ✅ Les routes sont chargées depuis `getRoutes()`
- ✅ Les migrations peuvent être exécutées
- ✅ Le menu apparaît dans le sidebar

## Exemple Complet : Module Blog

```
Modules/Blog/
├── module.json
├── BlogModule.php
├── Controllers/
│   └── BlogController.php
├── Views/
│   ├── index.php      ← Automatiquement trouvé via 'blog/index'
│   ├── create.php     ← Automatiquement trouvé via 'blog/create'
│   └── edit.php       ← Automatiquement trouvé via 'blog/edit'
└── Database/
    └── Migrations/
        └── 001_CreatePostsTable.php
```

**Utilisation dans le contrôleur** :

```php
namespace Modules\Blog\Controllers;

use App\Core\Application;

class BlogController
{
    public function index()
    {
        $app = Application::getInstance();

        // Vue automatiquement trouvée dans Modules/Blog/Views/index.php
        echo $app->view->render('blog/index', [
            'posts' => []
        ]);
    }
}
```

## Override de Vues

Si vous voulez personnaliser une vue de module sans modifier le module :

```
templates/blog/index.php  ← Cette vue sera utilisée en priorité
```

Le système cherche d'abord dans `templates/`, puis dans le module.

## Bonnes Pratiques

### ✅ À FAIRE

- Préfixer les vues par le nom du module : `blog/index`
- Utiliser `@extends('backend.layouts.master')` pour hériter du layout
- Documenter les vues dans le README du module

### ❌ À ÉVITER

- Ne pas utiliser de chemins absolus
- Ne pas référencer des vues d'autres modules directement
- Ne pas mélanger vues de module et vues globales pour un même module

## Debugging

Les logs de résolution de vues sont dans :

```
storage/logs/debug_view_render.log
```

Vous y verrez :

```
Resolving 'blog/index' -> viewPath='blog/index'
  tpl=templates/blog/index.tpl (exists=NO)
  php=templates/blog/index.php (exists=NO)
  Checking module views:
    tpl=Modules/Blog/Views/index.tpl (exists=NO)
    php=Modules/Blog/Views/index.php (exists=YES)
  => Resolved to MODULE: Modules/Blog/Views/index.php
```

## Migration depuis l'Ancien Système

Si vous avez des vues dans `templates/`, elles continueront de fonctionner sans changement. Le nouveau système est **rétrocompatible**.
