# Guide d'Installation des Permissions SMS Core

## Résumé

Ce document décrit l'installation réussie des 24 permissions SMS Core dans la base de données `sunuframework2`.

## État Actuel

### ✅ Module SMS Core
- **ID**: 24
- **Nom**: SMS Core
- **Slug**: `sms-core`
- **Version**: 1.0.0
- **Statut**: Actif et installé
- **Icône**: message-square

### ✅ Permissions Créées (24)
Toutes les 24 permissions SMS Core ont été insérées avec succès dans la table `permissions`:

| Catégorie | Nombre | Exemples |
|-----------|--------|----------|
| Dashboard | 1 | `sms.dashboard.view` |
| Envoi SMS | 2 | `sms.send`, `sms.send.view` |
| Envoi Masse | 2 | `sms.bulk`, `sms.bulk.view` |
| Campagnes | 4 | `sms.campaigns.view/create/edit/delete` |
| Historique | 2 | `sms.history.view`, `sms.history.view_all` |
| Statistiques | 2 | `sms.statistics.view`, `sms.statistics.view_all` |
| Noms Expéditeur | 3 | `sms.sender_names.view/manage/assign` |
| Tarification | 2 | `sms.pricing.view/manage` |
| Facturation | 2 | `sms.billing.view`, `sms.billing.view_all` |
| Fournisseurs | 1 | `sms.providers.view` |
| API | 3 | `sms.api.docs/keys.view/keys.manage` |

### ✅ Assignations aux Rôles (64 assignations)

| Rôle | Permissions | Couverture | Description |
|------|-------------|------------|-------------|
| **Administrateur** | 24/24 | 100% | Accès complet à toutes les fonctionnalités |
| **Manager** | 17/24 | 70.8% | Gestion et envoi, sans gestion tarifs |
| **Éditeur** | 13/24 | 54.2% | Utilisation et consultation |
| **Utilisateur** | 6/24 | 25% | Envoi basique et consultation personnelle |
| **Rédacteur** | 4/24 | 16.7% | Lecture seule (dashboard, historique, stats) |

## Structure de la Table Permissions

Chaque permission SMS possède les champs suivants:

```sql
- id: Identifiant unique
- name: Nom de la permission (ex: 'sms.dashboard.view')
- slug: Slug de la permission (ex: 'sms-dashboard-view')
- module: Nom lisible du module ('SMS')
- module_slug: Slug du module ('sms-core')
- module_id: ID du module dans la table modules (24)
- description: Description de la permission
- created_at, updated_at: Timestamps
```

## Vérifications

### Compter les permissions SMS
```sql
SELECT COUNT(*) as total
FROM permissions
WHERE module_slug = 'sms-core';
-- Résultat attendu: 24
```

### Voir toutes les permissions SMS
```sql
SELECT name, description
FROM permissions
WHERE module_slug = 'sms-core'
ORDER BY name;
```

### Vérifier les assignations d'un rôle
```sql
SELECT p.name, p.description
FROM permissions p
JOIN role_permissions rp ON p.id = rp.permission_id
JOIN roles r ON rp.role_id = r.id
WHERE r.name = 'Administrateur'
AND p.module_slug = 'sms-core'
ORDER BY p.name;
```

### Statistiques par rôle
```sql
SELECT
    r.name as role_name,
    COUNT(DISTINCT p.id) as sms_permissions_count
FROM roles r
LEFT JOIN role_permissions rp ON r.id = rp.role_id
LEFT JOIN permissions p ON rp.permission_id = p.id
WHERE p.module_slug = 'sms-core'
GROUP BY r.id, r.name
ORDER BY sms_permissions_count DESC;
```

## Utilisation dans le Code

### Vérifier une permission
```php
use App\Core\Auth\Auth;

// Dans un contrôleur
if (!Auth::hasPermission('sms.dashboard.view')) {
    redirect('/admin/dashboard');
    return;
}
```

### Vérifier plusieurs permissions
```php
// Vérifier si l'utilisateur peut voir toutes les statistiques
if (Auth::hasPermission('sms.statistics.view_all')) {
    // Afficher stats globales
    $stats = SmsMessage::all();
} else if (Auth::hasPermission('sms.statistics.view')) {
    // Afficher seulement ses stats
    $stats = SmsMessage::where('user_id', Auth::id())->get();
}
```

### Dans les vues
```php
@can('sms.campaigns.create')
    <a href="/admin/sms/campaigns/create" class="btn btn-primary">
        <i data-feather="plus"></i> Nouvelle campagne
    </a>
@endcan

@can('sms.send')
    <button type="submit" class="btn btn-success">
        <i data-feather="send"></i> Envoyer SMS
    </button>
@endcan
```

## Interface d'Administration

Pour voir les permissions SMS dans l'interface:

1. Accédez à: `/admin/permissions`
2. Utilisez le filtre "Module" et sélectionnez "SMS Core"
3. Vous verrez les 24 permissions avec leurs descriptions

## Permissions par Catégorie

### 📊 Dashboard & Consultation
- `sms.dashboard.view` - Voir le tableau de bord SMS

### 📤 Envoi SMS
- `sms.send` - Envoyer des SMS
- `sms.send.view` - Voir la page d'envoi SMS
- `sms.bulk` - Envoyer des SMS en masse
- `sms.bulk.view` - Voir la page d'envoi en masse

### 📋 Campagnes
- `sms.campaigns.view` - Voir les campagnes SMS
- `sms.campaigns.create` - Créer des campagnes SMS
- `sms.campaigns.edit` - Modifier les campagnes SMS
- `sms.campaigns.delete` - Supprimer les campagnes SMS

### 📜 Historique
- `sms.history.view` - Voir son historique des SMS
- `sms.history.view_all` - Voir l'historique de tous les utilisateurs

### 📈 Statistiques
- `sms.statistics.view` - Voir ses statistiques SMS
- `sms.statistics.view_all` - Voir les statistiques de tous les utilisateurs

### 👤 Noms d'Expéditeur
- `sms.sender_names.view` - Voir les noms d'expéditeur
- `sms.sender_names.manage` - Gérer les noms d'expéditeur
- `sms.sender_names.assign` - Assigner les noms d'expéditeur aux utilisateurs

### 💰 Tarification
- `sms.pricing.view` - Voir les tarifs SMS
- `sms.pricing.manage` - Gérer les tarifs SMS (Admin uniquement)

### 🧾 Facturation
- `sms.billing.view` - Voir sa facturation SMS
- `sms.billing.view_all` - Voir la facturation de tous les utilisateurs

### 🏢 Fournisseurs
- `sms.providers.view` - Voir les statistiques des fournisseurs

### 🔌 API
- `sms.api.docs` - Voir la documentation API SMS
- `sms.api.keys.view` - Voir ses clés API
- `sms.api.keys.manage` - Gérer ses clés API

## Notes Importantes

1. **Base de données**: Toutes les permissions sont dans la base `sunuframework2`
2. **Module ID**: Le module SMS Core a l'ID `24`
3. **Convention**: Toutes les permissions suivent le format `sms.{resource}.{action}`
4. **Hiérarchie**: Les permissions `_all` nécessitent généralement la permission de base

## Historique

- **2025-12-12**: Installation initiale des 24 permissions SMS Core
- **2025-12-12**: Création du module SMS Core (ID: 24)
- **2025-12-12**: Assignation aux 5 rôles (64 assignations au total)

## Support

Pour toute question ou problème concernant les permissions SMS Core, consultez:
- [README_SMS_PERMISSIONS.md](README_SMS_PERMISSIONS.md) - Documentation complète
- [SmsPermissionsSeeder.php](SmsPermissionsSeeder.php) - Seeder pour réinstaller si nécessaire
