# Permissions SMS Core

## Vue d'ensemble

Ce document décrit les 24 permissions créées pour le module SMS Core et leur assignation aux différents rôles.

## Structure des Permissions

Chaque permission SMS utilise la structure harmonisée des 3 champs de module:

- **module**: `"SMS"` - Nom lisible du module
- **module_slug**: `"sms-core"` - Identifiant unique du module
- **module_id**: `NULL` - Pour référence future à une table modules

## Liste des Permissions (24 au total)

### 1. Dashboard (1 permission)
- `sms.dashboard.view` - Voir le tableau de bord SMS

### 2. Envoi SMS (2 permissions)
- `sms.send` - Envoyer des SMS
- `sms.send.view` - Voir la page d'envoi SMS

### 3. Envoi en Masse (2 permissions)
- `sms.bulk` - Envoyer des SMS en masse
- `sms.bulk.view` - Voir la page d'envoi en masse

### 4. Campagnes (4 permissions)
- `sms.campaigns.view` - Voir les campagnes SMS
- `sms.campaigns.create` - Créer des campagnes SMS
- `sms.campaigns.edit` - Modifier les campagnes SMS
- `sms.campaigns.delete` - Supprimer les campagnes SMS

### 5. Historique (2 permissions)
- `sms.history.view` - Voir l'historique des SMS
- `sms.history.view_all` - Voir l'historique de tous les utilisateurs

### 6. Statistiques (2 permissions)
- `sms.statistics.view` - Voir les statistiques SMS
- `sms.statistics.view_all` - Voir les statistiques de tous les utilisateurs

### 7. Noms d'Expéditeur (3 permissions)
- `sms.sender_names.view` - Voir les noms d'expéditeur
- `sms.sender_names.manage` - Gérer les noms d'expéditeur
- `sms.sender_names.assign` - Assigner les noms d'expéditeur aux utilisateurs

### 8. Tarification (2 permissions)
- `sms.pricing.view` - Voir les tarifs SMS
- `sms.pricing.manage` - Gérer les tarifs SMS

### 9. Facturation (2 permissions)
- `sms.billing.view` - Voir la facturation SMS
- `sms.billing.view_all` - Voir la facturation de tous les utilisateurs

### 10. Fournisseurs (1 permission)
- `sms.providers.view` - Voir les statistiques des fournisseurs

### 11. API Documentation (1 permission)
- `sms.api.docs` - Voir la documentation API SMS

### 12. Clés API (2 permissions)
- `sms.api.keys.view` - Voir ses clés API
- `sms.api.keys.manage` - Gérer ses clés API

## Assignation aux Rôles

### Administrateur (24 permissions - Accès complet)
Toutes les permissions SMS sont assignées au rôle Administrateur.

**Permissions uniques à l'Administrateur:**
- `sms.campaigns.create`
- `sms.campaigns.edit`
- `sms.campaigns.delete`
- `sms.history.view_all`
- `sms.statistics.view_all`
- `sms.sender_names.manage`
- `sms.sender_names.assign`
- `sms.pricing.view`
- `sms.pricing.manage`
- `sms.billing.view_all`
- `sms.providers.view`

### Éditeur (13 permissions - Utilisation et lecture)
Permissions pour utiliser le système SMS et consulter ses données:

- `sms.dashboard.view`
- `sms.send`
- `sms.send.view`
- `sms.bulk`
- `sms.bulk.view`
- `sms.campaigns.view`
- `sms.history.view`
- `sms.statistics.view`
- `sms.sender_names.view`
- `sms.billing.view`
- `sms.api.docs`
- `sms.api.keys.view`
- `sms.api.keys.manage`

### Rédacteur (4 permissions - Lecture seule)
Permissions de consultation uniquement:

- `sms.dashboard.view`
- `sms.history.view`
- `sms.statistics.view`
- `sms.api.docs`

## Installation

### Option 1: Via le Seeder PHP (Recommandé)

```bash
# Exécuter le seeder complet (création + assignation)
php Modules/SmsCore/Database/Seeds/SmsPermissionsSeeder.php
```

### Option 2: Via SQL Direct

```sql
-- 1. Créer les permissions (si pas déjà fait)
-- Voir insert_sms_permissions.sql pour le script complet

-- 2. Assigner au rôle Administrateur
INSERT IGNORE INTO role_permissions (role_id, permission_id, created_at)
SELECT 1, id, NOW()
FROM permissions
WHERE name LIKE 'sms.%';

-- 3. Assigner aux autres rôles selon besoin
-- Voir assign_sms_permissions.sql pour le script complet
```

## Vérification

### Compter les permissions SMS
```sql
SELECT COUNT(*) as total
FROM permissions
WHERE module_slug = 'sms-core';
-- Résultat attendu: 24
```

### Voir les assignations par rôle
```sql
SELECT
    r.name as role,
    COUNT(*) as permissions_count
FROM role_permissions rp
JOIN permissions p ON rp.permission_id = p.id
JOIN roles r ON rp.role_id = r.id
WHERE p.module_slug = 'sms-core'
GROUP BY r.name
ORDER BY r.name;
```

### Lister toutes les permissions d'un rôle
```sql
SELECT p.name, p.description
FROM permissions p
JOIN role_permissions rp ON p.id = rp.permission_id
JOIN roles r ON rp.role_id = r.id
WHERE r.name = 'Administrateur'
  AND p.module_slug = 'sms-core'
ORDER BY p.name;
```

## Utilisation dans le Code

### Vérifier une permission
```php
// Dans un contrôleur
use App\Core\Auth\Auth;

class SmsController
{
    public function dashboard()
    {
        // Vérifier si l'utilisateur a la permission
        if (!Auth::hasPermission('sms.dashboard.view')) {
            redirect('/admin/dashboard');
            return;
        }

        // Code du dashboard...
    }
}
```

### Vérifier plusieurs permissions
```php
// Vérifier si admin ou owner (peut voir toutes les stats)
if (Auth::hasPermission('sms.statistics.view_all')) {
    // Afficher stats de tous les utilisateurs
} else if (Auth::hasPermission('sms.statistics.view')) {
    // Afficher seulement ses propres stats
}
```

### Dans les vues (Blade-style)
```php
@can('sms.campaigns.create')
    <a href="/admin/sms/campaigns/create" class="btn btn-primary">
        Créer une campagne
    </a>
@endcan

@can('sms.send')
    <button type="submit" class="btn btn-success">Envoyer SMS</button>
@endcan
```

## Maintenance

### Ajouter une nouvelle permission
1. Ajouter dans le seeder `SmsPermissionsSeeder.php`
2. Définir les rôles qui doivent l'avoir
3. Exécuter le seeder

### Modifier les assignations
```sql
-- Retirer une permission d'un rôle
DELETE FROM role_permissions
WHERE role_id = (SELECT id FROM roles WHERE name = 'Rédacteur')
  AND permission_id = (SELECT id FROM permissions WHERE name = 'sms.send');

-- Ajouter une permission à un rôle
INSERT INTO role_permissions (role_id, permission_id, created_at)
SELECT
    (SELECT id FROM roles WHERE name = 'Rédacteur'),
    (SELECT id FROM permissions WHERE name = 'sms.send'),
    NOW();
```

## Notes Importantes

1. **Convention de nommage**: Toutes les permissions SMS suivent le format `sms.{resource}.{action}`
2. **Module slug**: Toujours utiliser `sms-core` comme module_slug
3. **Hiérarchie**: Les permissions `view_all` nécessitent généralement la permission `view` de base
4. **API**: Les permissions API sont séparées pour permettre un contrôle granulaire

## Historique

- **2025-12-12**: Création initiale des 24 permissions SMS
- **2025-12-12**: Harmonisation avec les champs `module`, `module_slug`, `module_id`
- **2025-12-12**: Assignation aux rôles Administrateur, Éditeur, Rédacteur
