# Guide de Démarrage Rapide - Système de Widgets

## 🚀 En 5 minutes, créez votre premier widget

### Étape 1 : Créer le fichier du widget

Créez un fichier dans `Modules/{VotreModule}/Widgets/MonWidget.php` :

```php
<?php

namespace Modules\VotreModule\Widgets;

use App\Core\Widget\AbstractWidget;

class MonWidget extends AbstractWidget
{
    protected string $name = 'votre_module.mon_widget';
    protected string $type = 'stat';
    protected ?string $permission = null; // Accessible à tous
    protected int $cacheDuration = 300; // 5 minutes

    protected function calculateData(): array
    {
        return [
            'title' => 'Mon Premier Widget',
            'value' => '42',
            'description' => 'La réponse à tout',
            'icon' => 'star',
            'trend' => [
                'percentage' => 15.5,
                'direction' => 'up'
            ]
        ];
    }
}
```

### Étape 2 : Initialiser le système de widgets

Dans votre fichier `public/index.php` ou bootstrap, ajoutez :

```php
use App\Core\Widget\WidgetServiceProvider;

// Initialiser le système de widgets
WidgetServiceProvider::boot();
```

### Étape 3 : Charger les helpers

Dans votre fichier d'autoload ou bootstrap :

```php
require_once __DIR__ . '/Core/Widget/helpers.php';
```

### Étape 4 : Afficher le widget dans une vue

```php
<!-- Simple -->
<?php echo widget('votre_module.mon_widget'); ?>

<!-- Avec options -->
<?php echo widget('votre_module.mon_widget', [
    'stat_card_class' => 'card shadow-sm'
]); ?>

<!-- Tous les widgets d'un module -->
<?php echo module_widgets('VotreModule', ['columns' => 3]); ?>
```

---

## 📋 Checklist d'Intégration

### Backend

- [ ] Créer le dossier `Modules/{Module}/Widgets/`
- [ ] Créer la classe du widget étendant `AbstractWidget`
- [ ] Implémenter `calculateData()`
- [ ] Définir `$name`, `$type`, `$permission`
- [ ] Initialiser `WidgetServiceProvider::boot()`
- [ ] Charger les helpers

### Routes API

Ajoutez dans votre fichier de routes (`routes/api.php` ou équivalent) :

```php
use App\Core\Widget\WidgetController;

// Liste tous les widgets
$router->get('/api/widgets', [WidgetController::class, 'index'])
    ->middleware('auth');

// Widgets d'un module
$router->get('/api/widgets/module/{module}', [WidgetController::class, 'moduleWidgets'])
    ->middleware('auth');

// Widget spécifique
$router->get('/api/widgets/{widget}', [WidgetController::class, 'show'])
    ->middleware('auth');

// Batch
$router->post('/api/widgets/batch', [WidgetController::class, 'batch'])
    ->middleware('auth');

// Clear cache
$router->post('/api/widgets/{widget}/clear-cache', [WidgetController::class, 'clearCache'])
    ->middleware('auth');
```

### Frontend (Vues)

- [ ] Utiliser les helpers `widget()` ou `module_widgets()`
- [ ] Ajouter Feather Icons si non présent
- [ ] Tester l'affichage des widgets

---

## 🎨 Exemples de Types de Widgets

### 1. Widget Statistique (stat / kpi)

```php
protected string $type = 'stat';

protected function calculateData(): array
{
    $total = User::count();

    return [
        'title' => 'Total Utilisateurs',
        'value' => number_format($total, 0, ',', ' '),
        'description' => 'Utilisateurs actifs',
        'icon' => 'users',
        'trend' => [
            'percentage' => 12.5,
            'direction' => 'up'
        ]
    ];
}
```

### 2. Widget Liste

```php
protected string $type = 'list';

protected function calculateData(): array
{
    return [
        'title' => 'Top Produits',
        'value' => '',
        'description' => 'Les plus vendus',
        'icon' => 'shopping-cart',
        'trend' => ['percentage' => 0, 'direction' => 'stable'],
        'meta' => [
            'items' => [
                ['title' => 'Produit 1', 'value' => '150'],
                ['title' => 'Produit 2', 'value' => '120'],
                ['title' => 'Produit 3', 'value' => '100']
            ]
        ]
    ];
}
```

### 3. Widget Activité

```php
protected string $type = 'activity';

protected function calculateData(): array
{
    return [
        'title' => 'Activité Récente',
        'value' => '5 événements',
        'description' => 'Dernières actions',
        'icon' => 'clock',
        'trend' => ['percentage' => 0, 'direction' => 'stable'],
        'meta' => [
            'activities' => [
                [
                    'title' => 'Utilisateur créé',
                    'time' => 'Il y a 5 min',
                    'icon' => 'user-plus'
                ],
                [
                    'title' => 'SMS envoyé',
                    'time' => 'Il y a 10 min',
                    'icon' => 'send'
                ]
            ]
        ]
    ];
}
```

---

## 🔌 API REST - Exemples d'Utilisation

### JavaScript / Fetch API

```javascript
// Récupérer tous les widgets
fetch('/api/widgets')
    .then(response => response.json())
    .then(data => console.log(data));

// Récupérer un widget spécifique
fetch('/api/widgets/users.total')
    .then(response => response.json())
    .then(data => console.log(data));

// Batch de widgets
fetch('/api/widgets/batch', {
    method: 'POST',
    headers: {'Content-Type': 'application/json'},
    body: JSON.stringify({
        widgets: ['users.total', 'users.active', 'sms.total']
    })
})
.then(response => response.json())
.then(data => console.log(data));
```

### cURL

```bash
# Liste tous les widgets
curl -X GET http://localhost/api/widgets

# Widget spécifique
curl -X GET http://localhost/api/widgets/users.total

# Batch
curl -X POST http://localhost/api/widgets/batch \
  -H "Content-Type: application/json" \
  -d '{"widgets":["users.total","users.active"]}'

# Clear cache
curl -X POST http://localhost/api/widgets/users.total/clear-cache
```

---

## 🛠️ Commandes Utiles

### Découvrir tous les widgets

```php
use App\Core\Widget\WidgetRegistry;

$registry = WidgetRegistry::getInstance();
$discovered = $registry->discoverAllWidgets();
// Returns: ['Users' => 2, 'SmsCore' => 3, ...]
```

### Nettoyer tous les caches

```php
use App\Core\Widget\WidgetServiceProvider;

$cleared = WidgetServiceProvider::clearAllCache();
echo "Cleared {$cleared} cache files";
```

### Obtenir les statistiques

```php
use App\Core\Widget\WidgetServiceProvider;

$stats = WidgetServiceProvider::getStats();
print_r($stats);
/*
Array (
    [total_widgets] => 7
    [by_type] => Array (
        [stat] => 5
        [list] => 1
        [activity] => 1
    )
    [by_module] => Array (
        [users] => 2
        [sms] => 3
        [admin] => 2
    )
    [cacheable] => 7
    [with_permission] => 5
)
*/
```

---

## 🎯 Cas d'Usage Courants

### 1. Widget avec données de base de données

```php
use App\Core\Database\Database;

protected function calculateData(): array
{
    $db = Database::getInstance();
    $result = $db->query("SELECT COUNT(*) as total FROM orders WHERE status = 'completed'")->fetch();

    return [
        'title' => 'Commandes Complétées',
        'value' => number_format($result['total'], 0, ',', ' '),
        'icon' => 'check-circle'
    ];
}
```

### 2. Widget avec permission spécifique

```php
protected ?string $permission = 'orders.view';

// Le système vérifiera automatiquement si l'utilisateur a cette permission
```

### 3. Widget sans cache

```php
protected bool $cacheable = false;

// Données toujours fraîches, mais performance réduite
```

### 4. Widget avec cache long

```php
protected int $cacheDuration = 3600; // 1 heure

// Pour des données rarement modifiées
```

---

## 🐛 Dépannage Express

| Problème | Solution |
|----------|----------|
| Widget ne s'affiche pas | 1. Vérifier que `WidgetServiceProvider::boot()` est appelé<br>2. Vérifier la permission de l'utilisateur<br>3. Lancer `$registry->discoverModuleWidgets('Module')` |
| Erreur "Widget not found" | Vérifier le nom du widget (`$name`) et le chemin du fichier |
| Données obsolètes | Invalider le cache : `$widget->clearCache()` ou `WidgetServiceProvider::clearAllCache()` |
| Permission denied | Vérifier la permission dans le widget et dans la table `permissions` |
| Erreur dans calculateData() | Ajouter try/catch et logger les erreurs |

---

## 📚 Ressources

- **Documentation complète** : `Core/Widget/README.md`
- **Template de widget** : `Core/Widget/WIDGET_TEMPLATE.php`
- **Exemples** : `Modules/{Module}/Widgets/`
- **Icônes Feather** : https://feathericons.com/

---

## 💡 Tips & Astuces

1. **Nommage cohérent** : Utilisez toujours `module.action` (ex: `users.total`, `sms.sent`)
2. **Cache intelligent** : Ajustez `$cacheDuration` selon la fréquence de mise à jour des données
3. **Permissions granulaires** : Créez des permissions spécifiques pour chaque widget sensible
4. **Format des nombres** : Utilisez `number_format()` pour les grandes valeurs
5. **Gestion d'erreurs** : Ajoutez des try/catch dans `calculateData()` pour éviter les crashs

---

## ✅ Checklist de Production

Avant de déployer vos widgets en production :

- [ ] Tous les widgets sont testés avec les bonnes permissions
- [ ] Les caches sont configurés correctement
- [ ] Les requêtes SQL sont optimisées
- [ ] Les erreurs sont loggées, pas affichées
- [ ] La documentation est à jour
- [ ] Les permissions sont créées dans la base de données
- [ ] Le répertoire `storage/cache/widgets/` est accessible en écriture
- [ ] Les routes API sont protégées par authentification

---

**Version** : 1.0.0
**Support** : Consultez `Core/Widget/README.md` pour plus de détails
