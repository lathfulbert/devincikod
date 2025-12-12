# Système de Widgets Modulaires

## Vue d'ensemble

Le système de widgets modulaires offre une méthodologie uniforme pour créer et afficher des widgets (statistiques, KPIs, graphiques, informations) à travers tous les modules du framework.

### Avantages

✅ **Uniformisation** : Architecture et conventions communes pour tous les widgets
✅ **Modularité totale** : Chaque module peut avoir ses propres widgets indépendants
✅ **Extensibilité** : Ajout facile de nouveaux widgets sans modifier les existants
✅ **Permissions RBAC** : Contrôle d'accès intégré basé sur les rôles et permissions
✅ **Performance** : Système de cache intégré et configurable
✅ **Dashboard dynamique** : Widgets chargés automatiquement selon les permissions utilisateur

---

## Architecture

### Structure des fichiers

```
Core/Widget/
├── Contracts/
│   └── WidgetInterface.php       # Interface que tous les widgets doivent implémenter
├── AbstractWidget.php             # Classe de base avec fonctionnalités communes
├── WidgetRegistry.php             # Registre central des widgets
├── WidgetController.php           # API REST pour les widgets
├── WidgetRenderer.php             # Rendu HTML des widgets
├── helpers.php                    # Fonctions helper pour les vues
└── README.md                      # Cette documentation

Modules/{Module}/Widgets/
├── Widget1.php                    # Widget spécifique au module
├── Widget2.php
└── ...
```

---

## 1. Créer un Widget

### Étape 1 : Créer la classe du widget

Créez un fichier dans `Modules/{VotreModule}/Widgets/NomWidget.php` :

```php
<?php

namespace Modules\VotreModule\Widgets;

use App\Core\Widget\AbstractWidget;

/**
 * Widget Description
 *
 * Description détaillée de ce que fait le widget
 *
 * Type: stat|kpi|chart|list|info|alert|trend|activity
 * Permission: module.permission.view
 */
class NomWidget extends AbstractWidget
{
    // Nom unique du widget (convention: module.nom_widget)
    protected string $name = 'votre_module.nom_widget';

    // Type de widget (stat, kpi, chart, list, info, alert, trend, activity)
    protected string $type = 'stat';

    // Permission requise (null = accessible à tous)
    protected ?string $permission = 'votre_module.view';

    // Activer le cache
    protected bool $cacheable = true;

    // Durée du cache en secondes
    protected int $cacheDuration = 300; // 5 minutes

    // Configuration supplémentaire
    protected array $config = [
        'title' => 'Titre du widget',
        'description' => 'Description',
        'icon' => 'feather-icon-name',
        'color' => 'primary',
        'order' => 1
    ];

    /**
     * Calcule et retourne les données du widget
     *
     * @return array
     */
    protected function calculateData(): array
    {
        // Votre logique de calcul ici

        return [
            'title' => 'Titre affiché',
            'value' => '1,234', // Valeur principale
            'description' => 'Description complémentaire',
            'icon' => 'activity',
            'trend' => [
                'percentage' => 15.5,
                'direction' => 'up' // up|down|stable
            ],
            'meta' => [
                // Données additionnelles spécifiques
                'url' => '/some/url',
                'custom_field' => 'value'
            ]
        ];
    }
}
```

### Structure des données retournées

Tous les widgets doivent retourner un tableau avec cette structure :

```php
[
    'title' => string,           // Titre affiché
    'value' => string|int|float, // Valeur principale
    'description' => string,     // Description complémentaire
    'icon' => string,            // Nom de l'icône Feather
    'trend' => [
        'percentage' => float,   // Pourcentage de variation
        'direction' => string    // 'up', 'down', ou 'stable'
    ],
    'meta' => array             // Données additionnelles personnalisées
]
```

---

## 2. Types de Widgets

### stat / kpi
Widget de statistique simple avec une valeur principale, un trend et une icône.

**Exemple d'utilisation** : Nombre d'utilisateurs, total des ventes, SMS envoyés

### chart
Widget avec graphique (nécessite intégration Chart.js ou autre librairie).

**Exemple d'utilisation** : Évolution mensuelle, répartition par catégorie

### list
Widget affichant une liste d'éléments avec valeurs.

**Exemple d'utilisation** : Top 5 produits, dernières commandes

### info
Widget informatif simple sans statistique.

**Exemple d'utilisation** : Version du système, dernière sauvegarde

### alert
Widget d'alerte avec niveaux de sévérité.

**Exemple d'utilisation** : Alertes système, erreurs critiques

### trend
Widget focalisé sur l'évolution d'une métrique.

**Exemple d'utilisation** : Croissance mensuelle, variation hebdomadaire

### activity
Widget affichant une timeline d'activités récentes.

**Exemple d'utilisation** : Dernières actions, historique d'événements

---

## 3. Enregistrer et Découvrir les Widgets

### Méthode automatique (recommandée)

Le système découvre automatiquement tous les widgets dans `Modules/{Module}/Widgets/` :

```php
use App\Core\Widget\WidgetRegistry;

$registry = WidgetRegistry::getInstance();

// Découvrir les widgets d'un module
$count = $registry->discoverModuleWidgets('Users');

// Découvrir tous les widgets de tous les modules
$discovered = $registry->discoverAllWidgets();
```

### Méthode manuelle

```php
use App\Core\Widget\WidgetRegistry;

$registry = WidgetRegistry::getInstance();
$registry->register('Users', \Modules\Users\Widgets\TotalUsersWidget::class);
```

---

## 4. Afficher les Widgets dans les Vues

### Fonction helper : widget()

Affiche un widget unique :

```php
<?php echo widget('users.total'); ?>
```

Avec options :

```php
<?php echo widget('users.total', [
    'show_unauthorized' => false,
    'stat_card_class' => 'card shadow-sm'
]); ?>
```

### Fonction helper : module_widgets()

Affiche tous les widgets d'un module :

```php
<?php echo module_widgets('Users'); ?>
```

Avec options de mise en page :

```php
<?php echo module_widgets('Users', [
    'columns' => 3,          // Nombre de colonnes (1-12)
    'card_class' => 'col-lg-4 col-md-6 mb-4',
    'show_empty' => true
]); ?>
```

### Fonction helper : all_widgets()

Affiche tous les widgets accessibles à l'utilisateur :

```php
<?php echo all_widgets(); ?>
```

Avec options :

```php
<?php echo all_widgets([
    'columns' => 4,
    'show_empty' => false
]); ?>
```

### Fonction helper : widget_data()

Récupère uniquement les données sans rendu HTML :

```php
<?php
$data = widget_data('users.total');
if ($data) {
    echo "Total: " . $data['value'];
}
?>
```

---

## 5. API REST

Le système expose plusieurs endpoints REST pour interagir avec les widgets.

### GET /api/widgets

Liste tous les widgets disponibles pour l'utilisateur connecté.

**Réponse** :
```json
{
    "success": true,
    "data": [
        {
            "name": "users.total",
            "type": "stat",
            "permission": "users.view",
            "cacheable": true,
            "cacheDuration": 600
        }
    ],
    "count": 1
}
```

### GET /api/widgets/module/{module}

Liste les widgets d'un module spécifique.

**Exemple** : `/api/widgets/module/Users`

### GET /api/widgets/{widget_name}

Récupère les données d'un widget spécifique.

**Exemple** : `/api/widgets/users.total`

**Réponse** :
```json
{
    "success": true,
    "widget": "users.total",
    "config": {
        "name": "users.total",
        "type": "stat",
        "permission": "users.view"
    },
    "data": {
        "title": "Utilisateurs totaux",
        "value": "1,234",
        "description": "dont 45 nouveaux ce mois",
        "icon": "users",
        "trend": {
            "percentage": 15.5,
            "direction": "up"
        },
        "meta": {}
    }
}
```

### POST /api/widgets/batch

Récupère les données de plusieurs widgets en une seule requête.

**Body** :
```json
{
    "widgets": ["users.total", "users.active", "sms.total"]
}
```

**Réponse** :
```json
{
    "success": true,
    "data": {
        "users.total": {
            "config": {...},
            "data": {...}
        },
        "users.active": {
            "config": {...},
            "data": {...}
        }
    },
    "errors": {},
    "count": 2
}
```

### POST /api/widgets/{widget_name}/clear-cache

Invalide le cache d'un widget.

**Exemple** : `/api/widgets/users.total/clear-cache`

### POST /api/widgets/discover

Force la redécouverte de tous les widgets (action admin).

---

## 6. Système de Cache

### Configuration du cache

```php
class VotreWidget extends AbstractWidget
{
    // Activer/désactiver le cache
    protected bool $cacheable = true;

    // Durée du cache en secondes
    protected int $cacheDuration = 300; // 5 minutes
}
```

### Personnaliser la clé de cache

```php
public function getCacheKey(): string
{
    // Par défaut: 'widget:nom_widget'
    // Personnalisé avec paramètres utilisateur :
    return 'widget:' . $this->getName() . ':user:' . $this->getCurrentUserId();
}
```

### Invalider le cache manuellement

```php
$widget = $registry->get('users.total');
$widget->clearCache();
```

---

## 7. Permissions et Sécurité

### Définir une permission

```php
class VotreWidget extends AbstractWidget
{
    // Permission requise
    protected ?string $permission = 'module.action.view';

    // Null = accessible à tous les utilisateurs authentifiés
    // protected ?string $permission = null;
}
```

### Vérification automatique

Le système vérifie automatiquement les permissions via le service RBAC lors de :
- L'affichage des widgets (WidgetRenderer)
- Les appels API (WidgetController)
- Les listings de widgets (WidgetRegistry)

### Logique personnalisée

```php
public function canView($user): bool
{
    // Logique personnalisée en plus de la permission
    if (!parent::canView($user)) {
        return false;
    }

    // Vérifications additionnelles
    return $user->hasActiveSubscription();
}
```

---

## 8. Exemples Complets

### Exemple 1 : Widget de statistique simple

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
    protected bool $cacheable = true;
    protected int $cacheDuration = 600;

    protected function calculateData(): array
    {
        $total = User::count();
        $thisMonth = User::where('created_at', '>=', date('Y-m-01'))->count();

        return [
            'title' => 'Utilisateurs totaux',
            'value' => number_format($total, 0, ',', ' '),
            'description' => "{$thisMonth} nouveaux ce mois",
            'icon' => 'users',
            'trend' => [
                'percentage' => 12.5,
                'direction' => 'up'
            ],
            'meta' => [
                'url' => '/admin/users'
            ]
        ];
    }
}
```

### Exemple 2 : Widget de liste

```php
<?php

namespace Modules\SmsCore\Widgets;

use App\Core\Widget\AbstractWidget;
use App\Core\Database\Database;

class SmsStatusWidget extends AbstractWidget
{
    protected string $name = 'sms.status';
    protected string $type = 'list';
    protected ?string $permission = 'sms.view';

    protected function calculateData(): array
    {
        $db = Database::getInstance();

        $result = $db->query("
            SELECT status, COUNT(*) as count
            FROM sms_messages
            GROUP BY status
        ")->fetchAll();

        $items = [];
        foreach ($result as $row) {
            $items[] = [
                'title' => ucfirst($row['status']),
                'value' => number_format($row['count'], 0, ',', ' ')
            ];
        }

        return [
            'title' => 'Statut des SMS',
            'value' => '',
            'description' => 'Répartition par statut',
            'icon' => 'pie-chart',
            'trend' => ['percentage' => 0, 'direction' => 'stable'],
            'meta' => [
                'items' => $items
            ]
        ];
    }
}
```

### Exemple 3 : Widget d'activité récente

```php
<?php

namespace Modules\Admin\Widgets;

use App\Core\Widget\AbstractWidget;

class RecentActivityWidget extends AbstractWidget
{
    protected string $name = 'admin.activity';
    protected string $type = 'activity';
    protected ?string $permission = 'admin.access';
    protected int $cacheDuration = 60; // 1 minute

    protected function calculateData(): array
    {
        $activities = [
            [
                'title' => 'Nouvel utilisateur créé',
                'time' => 'Il y a 5 minutes',
                'icon' => 'user-plus'
            ],
            [
                'title' => 'SMS envoyé',
                'time' => 'Il y a 10 minutes',
                'icon' => 'send'
            ]
        ];

        return [
            'title' => 'Activité récente',
            'value' => count($activities) . ' événements',
            'description' => 'Dernières actions',
            'icon' => 'clock',
            'trend' => ['percentage' => 0, 'direction' => 'stable'],
            'meta' => [
                'activities' => $activities
            ]
        ];
    }
}
```

---

## 9. Intégration dans le Dashboard

### Dans votre vue de dashboard

```php
@extends('backend.layouts.master')

@section('content')

<div class="container-fluid">
    <h1>Dashboard</h1>

    <!-- Afficher tous les widgets accessibles -->
    <?php echo all_widgets(['columns' => 3]); ?>

    <!-- OU afficher les widgets par module -->
    <h2>Utilisateurs</h2>
    <?php echo module_widgets('Users', ['columns' => 4]); ?>

    <h2>SMS</h2>
    <?php echo module_widgets('SmsCore', ['columns' => 3]); ?>
</div>

@endsection
```

---

## 10. Bonnes Pratiques

### ✅ DO

- Toujours étendre `AbstractWidget`
- Utiliser des noms de widgets uniques au format `module.nom`
- Définir des permissions appropriées
- Mettre en cache les widgets avec des calculs lourds
- Documenter le widget avec un DocBlock clair
- Gérer les exceptions dans `calculateData()`
- Utiliser `number_format()` pour les grandes valeurs

### ❌ DON'T

- Ne pas faire de requêtes lourdes sans cache
- Ne pas exposer de données sensibles sans permission
- Ne pas modifier directement `AbstractWidget`
- Ne pas oublier de valider les données
- Ne pas utiliser de queries non préparées (SQL injection)

---

## 11. Dépannage

### Le widget n'apparaît pas

1. Vérifiez que le fichier est dans `Modules/{Module}/Widgets/`
2. Vérifiez que la classe implémente `WidgetInterface` (via `AbstractWidget`)
3. Vérifiez que l'utilisateur a la permission requise
4. Lancez la découverte : `$registry->discoverModuleWidgets('Module')`

### Erreur de permission

1. Vérifiez la permission dans le widget : `protected ?string $permission = 'module.action';`
2. Vérifiez que la permission existe dans la table `permissions`
3. Vérifiez que le rôle de l'utilisateur a cette permission

### Le cache ne se rafraîchit pas

1. Invalidez manuellement : `$widget->clearCache()`
2. Vérifiez `protected bool $cacheable = true`
3. Vérifiez que le répertoire `storage/cache/widgets/` est accessible en écriture

### Données incorrectes

1. Vérifiez la logique dans `calculateData()`
2. Ajoutez des logs : `error_log('Debug: ' . print_r($data, true));`
3. Désactivez temporairement le cache pour tester

---

## 12. Extension et Personnalisation

### Créer un type de widget personnalisé

1. Ajoutez le type dans `WidgetRenderer::renderWidget()`
2. Créez une méthode `renderCustomWidget()`
3. Définissez le HTML de rendu

### Ajouter des méta-données personnalisées

```php
protected function calculateData(): array
{
    return [
        // ... données standard ...
        'meta' => [
            'custom_field' => 'value',
            'chart_data' => [...],
            'actions' => [
                ['label' => 'Voir plus', 'url' => '/some/url']
            ]
        ]
    ];
}
```

### Intégration avec Chart.js

```php
'meta' => [
    'chart_type' => 'line',
    'chart_data' => [
        'labels' => ['Jan', 'Fév', 'Mar'],
        'datasets' => [[
            'label' => 'Ventes',
            'data' => [100, 150, 200]
        ]]
    ]
]
```

---

## 13. Widgets Recommandés par Module

### Module Users
- `users.total` - Nombre total d'utilisateurs
- `users.active` - Utilisateurs actifs
- `users.recent` - Nouveaux utilisateurs récents
- `users.by_role` - Répartition par rôle

### Module SmsCore
- `sms.total` - Total SMS envoyés
- `sms.status` - Répartition par statut
- `sms.sender_names` - Noms d'expéditeur disponibles
- `sms.monthly_trend` - Évolution mensuelle

### Module Admin
- `admin.system_health` - Santé du système
- `admin.recent_activity` - Activité récente
- `admin.cache_status` - État du cache
- `admin.queue_status` - État de la queue

---

## Support et Contribution

Pour toute question ou suggestion d'amélioration, contactez l'équipe de développement ou créez une issue dans le système de gestion de projet.

**Version** : 1.0.0
**Dernière mise à jour** : 2025-12-12
