# Guide d'Utilisation - Script de Migration RBAC

## 📋 Vue d'Ensemble

Le script `migrate_routes_to_rbac.php` applique automatiquement les middlewares RBAC à toutes les routes de l'application selon les recommandations de l'audit de sécurité.

## 🚀 Utilisation

### Test en Mode Dry-Run (Recommandé d'abord)

```bash
php migrate_routes_to_rbac.php --dry-run
```

**Effet** : Affiche ce qui serait modifié **sans toucher aux fichiers**.

### Migration Complète avec Sauvegardes

```bash
php migrate_routes_to_rbac.php
```

**Effet** :

- ✅ Crée des sauvegardes dans `storage/backups/routes_[date]/`
- ✅ Applique les middlewares RBAC
- ✅ Génère un rapport de migration

### Migration Sans Sauvegarde (Non recommandé)

```bash
php migrate_routes_to_rbac.php --no-backup
```

## 📊 Ce que le Script Modifie

### Routes Admin (`Modules/Admin/Routes/web.php`)

#### Avant

```php
$router->get('/admin/dashboard', [AdminController::class, 'dashboard'], [$authMiddleware]);
```

#### Après

```php
$router->get('/admin/dashboard', [AdminController::class, 'dashboard'])
    ->middleware('can:admin.access');
```

### Routes Critiques (Double Protection)

#### Avant

```php
$router->post('/admin/modules/install', [ModuleController::class, 'install'], [$authMiddleware]);
```

#### Après

```php
$router->post('/admin/modules/install', [ModuleController::class, 'install'])
    ->middleware('role:admin')
    ->middleware('can:admin.modules.manage');
```

## 🎯 Routes Concernées

| Module    | Fichier                                | Routes Modifiées  |
| --------- | -------------------------------------- | ----------------- |
| **Admin** | `Modules/Admin/Routes/web.php`         | ~35 routes        |
| **Auth**  | `Modules/Auth/Routes/web.php`          | ~8 routes         |
| **API**   | `Modules/Notifications/routes/api.php` | Révision manuelle |

## ⚙️ Détails des Modifications

### 1. Dashboard & Monitoring

- `admin.access` → Accès au dashboard
- `admin.settings.edit` → Clear monitoring

### 2. Gestion des Modules (CRITIQUE)

- `admin.modules.view` → Liste des modules
- `admin.modules.manage` + `role:admin` → Installation/Désinstallation

### 3. Gestion des Utilisateurs

- `admin.users.view` → Liste
- `admin.users.create` → Création
- `admin.users.edit` → Modification
- `admin.users.delete` → Suppression

### 4. Gestion des Rôles

- `admin.roles.view` → Liste
- `admin.roles.create` → Création
- `admin.roles.edit` → Modification
- `admin.roles.delete` → Suppression

### 5. Gestion des Permissions (TRÈS CRITIQUE)

- `role:admin` → **Tous les accès** (super-admin uniquement)

### 6. Queue

- `queue.view` → Visualisation
- `queue.retry` → Relancer un job
- `queue.manage` → Retry all
- `queue.delete` → Suppression

### 7. Cron

- `cron.view` → Visualisation
- `cron.manage` → Toggle
- `cron.execute` → Exécution manuelle

### 8. Profil & API Keys

- `auth.profile.view` / `auth.profile.edit`
- `apikeys.view` / `apikeys.create` / `apikeys.revoke`

## 📁 Fichiers de Sauvegarde

Les sauvegardes sont créées dans :

```
storage/backups/routes_2025-12-01_163000/
├── Admin_web.php
├── Auth_web.php
├── Notifications_api.php
└── web.php
```

## 📄 Rapport de Migration

Un fichier `migration_report_[date].txt` est généré contenant :

- ✅ Nombre de routes migrées par fichier
- ⚠️ Avertissements et actions manuelles requises
- 📋 Prochaines étapes

## ⚠️ Actions Manuelles Requises Après Migration

### 1. Vérifier les Fichiers Modifiés

```bash
# Voir les différences
git diff Modules/Admin/Routes/web.php
git diff Modules/Auth/Routes/web.php
```

### 2. Tester les Routes

```bash
# Accéder aux différentes sections de l'admin
# Vérifier que les permissions sont correctes
```

### 3. Assigner les Permissions aux Rôles

```php
// Dans l'admin ou via console
$rbacService->givePermissionToRole('admin', 'admin.access');
$rbacService->givePermissionToRole('admin', 'admin.users.view');
// etc...
```

### 4. Routes API (Révision Manuelle)

Les routes API nécessitent `ApiAuthMiddleware` EN PLUS des permissions RBAC :

```php
// Modules/Notifications/routes/api.php

$router->get('/api/v1/notifications/{id}', [NotificationController::class, 'show'])
    ->middleware('api_auth')  // ← Ajouter manuellement
    ->middleware('can:notifications.view');
```

## 🔄 Restauration depuis Sauvegarde

Si besoin de restaurer :

```bash
# Identifier le dossier de sauvegarde
ls storage/backups/

# Restaurer un fichier
cp storage/backups/routes_2025-12-01_163000/Admin_web.php Modules/Admin/Routes/web.php
```

## ✅ Checklist Post-Migration

- [ ] Exécuter en mode `--dry-run` d'abord
- [ ] Vérifier le rapport de dry-run
- [ ] Exécuter la migration réelle
- [ ] Vérifier les fichiers modifiés avec `git diff`
- [ ] Tester l'accès au dashboard
- [ ] Tester quelques routes CRUD (users, roles)
- [ ] Assigner les permissions aux rôles appropriés
- [ ] Réviser manuellement les routes API
- [ ] Tester avec différents rôles (admin, user, etc.)
- [ ] Vérifier les logs pour erreurs de permission
- [ ] Commit les changements

## 🆘 Dépannage

### Erreur: "Permission denied"

**Cause** : Un utilisateur n'a pas la permission requise.

**Solution** :

```php
// Assigner la permission au rôle
$rbacService = new RbacService();
$rbacService->givePermissionToRole('admin', 'admin.access');
```

### Erreur: "Middleware 'can' not found"

**Cause** : Les middlewares ne sont pas enregistrés.

**Solution** : Vérifier `Core/Application.php` lignes 123-126.

### Routes toujours accessibles sans permission

**Cause** : Le cache des routes ou l'ancienne syntaxe subsiste.

**Solution** :

```bash
rm -rf storage/cache/routes/*
rm -rf storage/cache/views/*
```

## 📚 Ressources

- **Audit Complet** : `SECURITY_AUDIT.md`
- **Permissions Définies** : `Modules/*/config/permissions.php`
- **Documentation RBAC** : `Modules/RBAC/README.md`

## 🎯 Estimation

- **Exécution du script** : < 1 minute
- **Tests post-migration** : 30 minutes
- **Assignation permissions** : 15 minutes
- **Révision API** : 30 minutes

**Total** : ~1h15 pour une migration complète et testée.
