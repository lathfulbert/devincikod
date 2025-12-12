# Installation et Configuration du Système de Widgets

## 📦 Installation

### Étape 1 : Vérifier les fichiers

Assurez-vous que tous les fichiers suivants sont présents :

```
Core/Widget/
├── Contracts/
│   └── WidgetInterface.php
├── AbstractWidget.php
├── WidgetRegistry.php
├── WidgetController.php
├── WidgetRenderer.php
├── WidgetServiceProvider.php
├── helpers.php
├── routes.php
├── config.php
├── README.md
├── QUICK_START.md
├── WIDGET_TEMPLATE.php
└── INSTALLATION.md (ce fichier)
```

### Étape 2 : Créer le répertoire de cache

```bash
mkdir -p storage/cache/widgets
chmod 755 storage/cache/widgets
```

Ou en PHP :

```php
use App\Core\Widget\WidgetServiceProvider;

WidgetServiceProvider::ensureCacheDirectory();
```

### Étape 3 : Charger les helpers

Dans votre fichier bootstrap principal (`public/index.php` ou équivalent), ajoutez :

```php
// Charger les helpers de widgets
require_once __DIR__ . '/../Core/Widget/helpers.php';
```

### Étape 4 : Initialiser le système

Dans votre fichier bootstrap, après l'initialisation de l'application :

```php
use App\Core\Widget\WidgetServiceProvider;

// Initialiser le système de widgets
WidgetServiceProvider::boot();
```

### Étape 5 : Enregistrer les routes API

Dans votre fichier de routes principal (par exemple `routes/web.php` ou `public/index.php`), incluez :

```php
// Routes API des widgets
require_once __DIR__ . '/../Core/Widget/routes.php';
```

Ou enregistrez manuellement les routes :

```php
use App\Core\Widget\WidgetController;

$router->group(['prefix' => '/api/widgets', 'middleware' => ['auth']], function ($router) {
    $router->get('', [WidgetController::class, 'index']);
    $router->get('/module/{module}', [WidgetController::class, 'moduleWidgets']);
    $router->get('/{widget}', [WidgetController::class, 'show']);
    $router->post('/batch', [WidgetController::class, 'batch']);
    $router->post('/{widget}/clear-cache', [WidgetController::class, 'clearCache']);
    $router->post('/discover', [WidgetController::class, 'discover']);
});
```

---

## 🎯 Configuration

### Configuration par défaut

Le fichier `Core/Widget/config.php` contient toutes les configurations par défaut. Vous pouvez le modifier selon vos besoins.

### Options importantes

```php
return [
    // Découverte automatique des widgets
    'auto_discover' => true,

    // Cache
    'cache' => [
        'enabled' => true,
        'default_duration' => 300, // 5 minutes
    ],

    // Rendu
    'render' => [
        'default_columns' => 3,
        'show_unauthorized' => false,
    ],
];
```

---

## 🔧 Créer vos premiers widgets

### 1. Créer le dossier Widgets dans votre module

```bash
mkdir -p Modules/{VotreModule}/Widgets
```

### 2. Créer un widget (exemple complet)

Copiez `Core/Widget/WIDGET_TEMPLATE.php` vers votre module et adaptez-le :

```bash
cp Core/Widget/WIDGET_TEMPLATE.php Modules/Users/Widgets/TotalUsersWidget.php
```

Éditez et complétez le template :

```php
<?php

namespace Modules\Users\Widgets;

use App\Core\Widget\AbstractWidget;
use Modules\Users\Models\User;

class TotalUsersWidget extends AbstractWidget
{
    protected string $name = 'users.total';
    protected string $type = 'stat';
    protected ?string $permission = 'users.view';
    protected int $cacheDuration = 600; // 10 minutes

    protected function calculateData(): array
    {
        $total = User::count();

        return [
            'title' => 'Utilisateurs totaux',
            'value' => number_format($total, 0, ',', ' '),
            'description' => 'Tous les utilisateurs enregistrés',
            'icon' => 'users',
            'trend' => [
                'percentage' => 0,
                'direction' => 'stable'
            ],
            'meta' => [
                'url' => '/admin/users'
            ]
        ];
    }
}
```

### 3. Vérifier la découverte automatique

```php
use App\Core\Widget\WidgetRegistry;

$registry = WidgetRegistry::getInstance();
$discovered = $registry->discoverModuleWidgets('Users');

echo "Discovered {$discovered} widgets in Users module";
```

---

## 🎨 Utilisation dans les vues

### Afficher un widget unique

```php
<?php echo widget('users.total'); ?>
```

### Afficher tous les widgets d'un module

```php
<?php echo module_widgets('Users', ['columns' => 3]); ?>
```

### Afficher tous les widgets

```php
<?php echo all_widgets(['columns' => 4]); ?>
```

### Options de rendu

```php
<?php
echo widget('users.total', [
    'show_unauthorized' => false,
    'stat_card_class' => 'card shadow-sm'
]);
?>
```

---

## 🚀 Intégration dans le Dashboard

### Exemple de dashboard complet

Créez `Modules/Admin/Views/dashboard.php` :

```php
@extends('backend.layouts.master')

@section('content')
<div class="container-fluid">
    <h1>Dashboard</h1>

    <!-- Widgets utilisateurs -->
    <h2>Utilisateurs</h2>
    <?php echo module_widgets('Users', ['columns' => 3]); ?>

    <!-- Widgets SMS -->
    <h2>SMS</h2>
    <?php echo module_widgets('SmsCore', ['columns' => 3]); ?>

    <!-- Widgets système -->
    <h2>Système</h2>
    <?php echo module_widgets('Admin', ['columns' => 2]); ?>
</div>
@endsection
```

---

## 🔐 Permissions

### Créer les permissions dans la base de données

Exécutez les requêtes SQL suivantes (ou utilisez votre système de migrations) :

```sql
-- Permission générale pour voir les widgets
INSERT INTO permissions (slug, name, description, module_id)
VALUES ('widgets.view', 'Voir les widgets', 'Permission de base pour afficher les widgets', NULL);

-- Permissions spécifiques par module (exemples)
INSERT INTO permissions (slug, name, description, module_id)
VALUES
('users.view', 'Voir les utilisateurs', 'Voir les statistiques utilisateurs', 1),
('sms.view', 'Voir les SMS', 'Voir les statistiques SMS', 2);
```

### Assigner les permissions aux rôles

```sql
-- Assigner les permissions au rôle Admin
INSERT INTO role_permission (role_id, permission_id)
SELECT 1, id FROM permissions WHERE slug IN ('widgets.view', 'users.view', 'sms.view');
```

---

## 🧪 Tests

### Test 1 : Vérifier que le système est chargé

```php
use App\Core\Widget\WidgetRegistry;

$registry = WidgetRegistry::getInstance();
$stats = \App\Core\Widget\WidgetServiceProvider::getStats();

print_r($stats);
```

### Test 2 : Tester un widget spécifique

```php
use App\Core\Widget\WidgetRegistry;

$registry = WidgetRegistry::getInstance();
$widget = $registry->get('users.total');

if ($widget) {
    $data = $widget->getData();
    print_r($data);
} else {
    echo "Widget not found";
}
```

### Test 3 : Tester l'API

```bash
# Liste tous les widgets
curl http://localhost/api/widgets

# Widget spécifique
curl http://localhost/api/widgets/users.total

# Batch
curl -X POST http://localhost/api/widgets/batch \
  -H "Content-Type: application/json" \
  -d '{"widgets":["users.total","users.active"]}'
```

---

## 📊 Widgets d'exemple fournis

Le système inclut plusieurs widgets d'exemple :

### Module Users
- `users.total` - Nombre total d'utilisateurs
- `users.active` - Utilisateurs actifs

### Module SmsCore
- `sms.total` - Total SMS envoyés
- `sms.status` - Répartition par statut
- `sms.sender_names` - Noms d'expéditeur disponibles

### Module Admin
- `admin.system_health` - Santé du système
- `admin.recent_activity` - Activité récente

---

## 🛠️ Maintenance

### Nettoyer le cache

```php
use App\Core\Widget\WidgetServiceProvider;

// Nettoyer tous les caches
$cleared = WidgetServiceProvider::clearAllCache();
echo "Cleared {$cleared} cache files";
```

### Redécouvrir les widgets

```php
use App\Core\Widget\WidgetRegistry;

$registry = WidgetRegistry::getInstance();
$discovered = $registry->discoverAllWidgets();

foreach ($discovered as $module => $count) {
    echo "{$module}: {$count} widgets\n";
}
```

### Obtenir les statistiques

```php
use App\Core\Widget\WidgetServiceProvider;

$stats = WidgetServiceProvider::getStats();
echo "Total widgets: " . $stats['total_widgets'] . "\n";
echo "Widgets cachés: " . $stats['cacheable'] . "\n";
```

---

## 🐛 Dépannage

### Problème : Les widgets ne s'affichent pas

**Solutions** :
1. Vérifier que `WidgetServiceProvider::boot()` est appelé
2. Vérifier les permissions de l'utilisateur
3. Vérifier les logs d'erreurs PHP
4. Tester manuellement : `$registry->get('widget.name')->getData()`

### Problème : "Widget not found"

**Solutions** :
1. Vérifier que le fichier est dans `Modules/{Module}/Widgets/`
2. Vérifier que la classe étend `AbstractWidget`
3. Forcer la découverte : `$registry->discoverModuleWidgets('Module')`
4. Vérifier le nom du widget (`protected string $name`)

### Problème : Permission denied

**Solutions** :
1. Vérifier que la permission existe dans la table `permissions`
2. Vérifier que le rôle de l'utilisateur a cette permission
3. Définir `protected ?string $permission = null;` pour rendre le widget public

### Problème : Le cache ne se met pas à jour

**Solutions** :
1. Invalider manuellement : `$widget->clearCache()`
2. Nettoyer tous les caches : `WidgetServiceProvider::clearAllCache()`
3. Vérifier que le répertoire `storage/cache/widgets/` est accessible en écriture
4. Réduire `$cacheDuration` pour les tests

### Problème : Erreur dans calculateData()

**Solutions** :
1. Ajouter des logs : `error_log('Debug: ' . print_r($data, true));`
2. Wrapper dans try/catch
3. Désactiver temporairement le cache pour débugger
4. Vérifier les requêtes SQL et l'accès aux données

---

## 📚 Ressources et Documentation

- **Guide de démarrage rapide** : `Core/Widget/QUICK_START.md`
- **Documentation complète** : `Core/Widget/README.md`
- **Template de widget** : `Core/Widget/WIDGET_TEMPLATE.php`
- **Exemples de widgets** : `Modules/{Module}/Widgets/`
- **Configuration** : `Core/Widget/config.php`

---

## ✅ Checklist post-installation

- [ ] Tous les fichiers du système sont présents
- [ ] Le répertoire `storage/cache/widgets/` existe et est accessible en écriture
- [ ] `WidgetServiceProvider::boot()` est appelé au démarrage
- [ ] Les helpers sont chargés
- [ ] Les routes API sont enregistrées
- [ ] Au moins un widget d'exemple fonctionne
- [ ] L'API REST répond correctement (`/api/widgets`)
- [ ] Les permissions sont créées dans la base de données
- [ ] Un dashboard de test affiche les widgets
- [ ] Le cache fonctionne correctement

---

## 🎓 Formation et Support

### Tutoriel étape par étape

1. **Jour 1** : Lire `QUICK_START.md` et créer votre premier widget
2. **Jour 2** : Créer 3-5 widgets pour votre module principal
3. **Jour 3** : Intégrer les widgets dans votre dashboard
4. **Jour 4** : Configurer les permissions et les caches
5. **Jour 5** : Utiliser l'API REST pour des dashboards dynamiques

### Ressources supplémentaires

- **Icônes Feather** : https://feathericons.com/
- **Bootstrap 5** : https://getbootstrap.com/docs/5.0/
- **Exemples de widgets** : Voir `Modules/*/Widgets/`

---

## 📝 Changelog

### Version 1.0.0 (2025-12-12)
- ✨ Première version du système de widgets
- ✅ Support des 8 types de widgets
- ✅ Système de cache intégré
- ✅ Permissions RBAC
- ✅ API REST complète
- ✅ Découverte automatique des widgets
- ✅ Rendu HTML avec Bootstrap 5
- ✅ Documentation complète

---

**Version** : 1.0.0
**Date** : 2025-12-12
**Auteur** : Équipe de développement Sunu Framework
