# 📊 Système de Widgets Modulaires - Récapitulatif

## 🎯 Vue d'ensemble

Un système complet et standardisé de widgets modulaires a été intégré dans le framework. Ce système permet à chaque module de créer facilement des widgets réutilisables pour afficher des statistiques, KPIs, graphiques et informations dans les dashboards.

---

## ✅ Ce qui a été créé

### 1. Architecture Core (`Core/Widget/`)

| Fichier | Description |
|---------|-------------|
| `WidgetInterface.php` | Interface que tous les widgets doivent implémenter |
| `AbstractWidget.php` | Classe de base avec fonctionnalités communes (cache, permissions, etc.) |
| `WidgetRegistry.php` | Registre central pour découvrir et gérer les widgets |
| `WidgetController.php` | Contrôleur API REST pour interagir avec les widgets |
| `WidgetRenderer.php` | Moteur de rendu HTML des widgets |
| `WidgetServiceProvider.php` | Service provider pour initialiser le système |
| `helpers.php` | Fonctions helper (`widget()`, `module_widgets()`, etc.) |
| `routes.php` | Routes API prêtes à inclure |
| `config.php` | Configuration centralisée du système |

### 2. Documentation

| Fichier | Contenu |
|---------|---------|
| `README.md` | Documentation complète (50+ pages) |
| `QUICK_START.md` | Guide de démarrage rapide (15 minutes) |
| `INSTALLATION.md` | Guide d'installation pas à pas |
| `WIDGET_TEMPLATE.php` | Template prêt à copier pour créer un widget |

### 3. Widgets d'exemple

#### Module Users
- **TotalUsersWidget** (`users.total`) : Nombre total d'utilisateurs avec évolution
- **ActiveUsersWidget** (`users.active`) : Utilisateurs actifs vs total

#### Module SmsCore
- **TotalSmsWidget** (`sms.total`) : Total SMS envoyés avec tendance mensuelle
- **SmsStatusWidget** (`sms.status`) : Répartition des SMS par statut (envoyé, échec, en attente)
- **SenderNamesWidget** (`sms.sender_names`) : Nombre de sender names disponibles

#### Module Admin
- **SystemHealthWidget** (`admin.system_health`) : Santé globale du système (DB, cache, storage, memory)
- **RecentActivityWidget** (`admin.recent_activity`) : Activité récente (derniers utilisateurs, SMS, etc.)

### 4. Page Dashboard d'exemple

- **`Modules/Admin/Views/dashboard_widgets.php`** : Dashboard complet utilisant le système de widgets
  - Welcome card avec horloge
  - Widgets par module en grille
  - Onglets pour visualiser les widgets par module
  - Section de test de l'API REST

---

## 🚀 Démarrage Rapide

### Installation (5 minutes)

```php
// 1. Charger les helpers (dans votre bootstrap)
require_once __DIR__ . '/../Core/Widget/helpers.php';

// 2. Initialiser le système
use App\Core\Widget\WidgetServiceProvider;
WidgetServiceProvider::boot();

// 3. Créer le répertoire de cache
WidgetServiceProvider::ensureCacheDirectory();

// 4. Inclure les routes API
require_once __DIR__ . '/../Core/Widget/routes.php';
```

### Créer un widget (5 minutes)

```php
<?php
namespace Modules\VotreModule\Widgets;

use App\Core\Widget\AbstractWidget;

class MonWidget extends AbstractWidget
{
    protected string $name = 'votre_module.mon_widget';
    protected string $type = 'stat';
    protected ?string $permission = null; // Public

    protected function calculateData(): array
    {
        return [
            'title' => 'Mon Widget',
            'value' => '42',
            'icon' => 'star',
            'trend' => ['percentage' => 15, 'direction' => 'up']
        ];
    }
}
```

### Afficher dans une vue

```php
<!-- Un seul widget -->
<?php echo widget('votre_module.mon_widget'); ?>

<!-- Tous les widgets d'un module -->
<?php echo module_widgets('VotreModule', ['columns' => 3]); ?>

<!-- Tous les widgets -->
<?php echo all_widgets(['columns' => 4]); ?>
```

---

## 🎨 Types de Widgets Supportés

| Type | Usage | Exemple |
|------|-------|---------|
| **stat / kpi** | Statistique avec valeur et tendance | Total utilisateurs: 1,234 ↑ 15% |
| **chart** | Graphique (avec Chart.js) | Évolution mensuelle |
| **list** | Liste d'éléments | Top 5 produits |
| **info** | Information simple | Version système |
| **alert** | Alerte avec sévérité | Erreur critique |
| **trend** | Tendance focalisée | Croissance +25% |
| **activity** | Timeline d'activités | Dernières actions |

---

## 🔌 API REST

### Endpoints disponibles

| Méthode | Endpoint | Description |
|---------|----------|-------------|
| GET | `/api/widgets` | Liste tous les widgets accessibles |
| GET | `/api/widgets/module/{module}` | Widgets d'un module spécifique |
| GET | `/api/widgets/{widget}` | Données d'un widget |
| POST | `/api/widgets/batch` | Charger plusieurs widgets en une requête |
| POST | `/api/widgets/{widget}/clear-cache` | Invalider le cache |
| POST | `/api/widgets/discover` | Redécouvrir les widgets (admin) |

### Exemple d'utilisation

```javascript
// Charger un widget via JavaScript
fetch('/api/widgets/users.total')
    .then(response => response.json())
    .then(data => {
        console.log(data.data.value); // "1,234"
        console.log(data.data.trend); // {percentage: 15, direction: 'up'}
    });
```

---

## 🔐 Permissions RBAC

### Système intégré

- Chaque widget peut définir une permission : `protected ?string $permission = 'module.action.view';`
- Vérification automatique via `RbacService`
- Widgets sans permission (`null`) accessibles à tous les utilisateurs authentifiés

### Exemples

```php
// Public (tous les utilisateurs)
protected ?string $permission = null;

// Réservé aux admins
protected ?string $permission = 'admin.access';

// Permission spécifique
protected ?string $permission = 'sms.view';
```

---

## ⚡ Système de Cache

### Fonctionnalités

- **Cache automatique** configurable par widget
- **Durée personnalisable** : `protected int $cacheDuration = 300;` (secondes)
- **Invalidation manuelle** : `$widget->clearCache()`
- **Nettoyage global** : `WidgetServiceProvider::clearAllCache()`

### Configuration

```php
class MonWidget extends AbstractWidget
{
    protected bool $cacheable = true;
    protected int $cacheDuration = 600; // 10 minutes
}
```

---

## 📊 Statistiques Système

```php
use App\Core\Widget\WidgetServiceProvider;

$stats = WidgetServiceProvider::getStats();
/*
[
    'total_widgets' => 7,
    'by_type' => ['stat' => 5, 'list' => 1, 'activity' => 1],
    'by_module' => ['Users' => 2, 'SmsCore' => 3, 'Admin' => 2],
    'cacheable' => 7,
    'with_permission' => 5
]
*/
```

---

## 🎯 Avantages du Système

### ✅ Pour les développeurs

- **Uniformisation** : Architecture et conventions communes
- **Gain de temps** : Template prêt à l'emploi
- **Flexibilité** : 8 types de widgets différents
- **Extensibilité** : Facile d'ajouter de nouveaux widgets
- **Documentation** : Guide complet + exemples

### ✅ Pour le système

- **Modularité** : Chaque module a ses propres widgets
- **Performance** : Système de cache intégré
- **Sécurité** : Permissions RBAC automatiques
- **API REST** : Widgets accessibles via JSON
- **Dashboard dynamique** : Chargement automatique selon les permissions

### ✅ Pour les utilisateurs

- **Dashboards personnalisés** : Widgets selon les permissions
- **Informations en temps réel** : Statistiques à jour
- **Interface cohérente** : Même style pour tous les widgets
- **Responsive** : Compatible mobile et desktop

---

## 📁 Structure des Fichiers

```
sunuframework2/
├── Core/Widget/                          # Système core
│   ├── Contracts/
│   │   └── WidgetInterface.php
│   ├── AbstractWidget.php
│   ├── WidgetRegistry.php
│   ├── WidgetController.php
│   ├── WidgetRenderer.php
│   ├── WidgetServiceProvider.php
│   ├── helpers.php
│   ├── routes.php
│   ├── config.php
│   ├── README.md                         # Doc complète
│   ├── QUICK_START.md                    # Démarrage rapide
│   ├── INSTALLATION.md                   # Installation
│   └── WIDGET_TEMPLATE.php               # Template
│
├── Modules/
│   ├── Users/Widgets/                    # Widgets Users
│   │   ├── TotalUsersWidget.php
│   │   └── ActiveUsersWidget.php
│   │
│   ├── SmsCore/Widgets/                  # Widgets SMS
│   │   ├── TotalSmsWidget.php
│   │   ├── SmsStatusWidget.php
│   │   └── SenderNamesWidget.php
│   │
│   └── Admin/
│       ├── Widgets/                      # Widgets Admin
│       │   ├── SystemHealthWidget.php
│       │   └── RecentActivityWidget.php
│       └── Views/
│           └── dashboard_widgets.php     # Dashboard exemple
│
├── storage/cache/widgets/                # Cache des widgets
│
└── WIDGETS_SYSTEM_SUMMARY.md            # Ce fichier
```

---

## 🛠️ Commandes Utiles

### Découvrir les widgets

```php
use App\Core\Widget\WidgetRegistry;

$registry = WidgetRegistry::getInstance();
$discovered = $registry->discoverAllWidgets();
// Retourne: ['Users' => 2, 'SmsCore' => 3, 'Admin' => 2]
```

### Obtenir un widget

```php
$widget = $registry->get('users.total');
$data = $widget->getData();
```

### Nettoyer les caches

```php
use App\Core\Widget\WidgetServiceProvider;

$count = WidgetServiceProvider::clearAllCache();
echo "Cleared {$count} cache files";
```

### Tester un widget

```php
$widget = $registry->get('users.total');
if ($widget) {
    print_r($widget->getData());
    print_r($widget->getConfig());
}
```

---

## 📖 Documentation

| Document | Contenu | Durée de lecture |
|----------|---------|------------------|
| **QUICK_START.md** | Créer votre premier widget en 5 minutes | 15 min |
| **INSTALLATION.md** | Installation et configuration complète | 30 min |
| **README.md** | Documentation technique exhaustive | 2 heures |
| **WIDGET_TEMPLATE.php** | Template commenté ligne par ligne | 10 min |

---

## 🎓 Prochaines Étapes

### 1. Installer et tester (15 minutes)
- Suivre `INSTALLATION.md`
- Tester les widgets d'exemple
- Vérifier l'API REST

### 2. Créer vos premiers widgets (30 minutes)
- Utiliser `WIDGET_TEMPLATE.php`
- Créer 2-3 widgets pour votre module principal
- Les afficher dans une vue de test

### 3. Intégrer dans votre dashboard (1 heure)
- Créer ou modifier votre page dashboard
- Utiliser `module_widgets()` pour chaque module
- Ajouter les permissions nécessaires

### 4. Utiliser l'API REST (optionnel, 30 minutes)
- Tester les endpoints avec cURL ou Postman
- Créer un dashboard dynamique en JavaScript
- Implémenter le chargement asynchrone

---

## 💡 Exemples de Widgets à Créer

### Pour différents modules

- **CRM** : Nouveaux leads, pipeline de ventes, contacts actifs
- **E-commerce** : Ventes du jour, produits populaires, panier moyen
- **Facturation** : Factures impayées, revenus mensuels, clients débiteurs
- **Support** : Tickets ouverts, temps de réponse moyen, satisfaction client
- **Logs** : Erreurs critiques, avertissements, activité système
- **Technique** : Utilisation CPU, espace disque, requêtes lentes

---

## 🎉 Conclusion

Le système de widgets modulaires est maintenant **opérationnel** et **prêt à l'emploi** !

### Résumé en chiffres

- ✅ **9 fichiers core** créés
- ✅ **7 widgets d'exemple** fonctionnels
- ✅ **6 routes API** exposées
- ✅ **4 documents** de documentation
- ✅ **8 types de widgets** supportés
- ✅ **3 modules** avec widgets (Users, SmsCore, Admin)
- ✅ **1 dashboard** d'exemple complet

### 🚀 Commencez maintenant !

1. Lisez `Core/Widget/QUICK_START.md`
2. Créez votre premier widget
3. Intégrez-le dans votre dashboard
4. Partagez avec votre équipe !

---

**Date de création** : 2025-12-12
**Version** : 1.0.0
**Framework** : Sunu Framework 2
**Status** : ✅ Production Ready

**Questions ou suggestions ?** Consultez la documentation complète dans `Core/Widget/README.md`
