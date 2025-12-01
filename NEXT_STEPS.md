# Prochaines Étapes - SunuFramework2

## 🎯 Phase Actuelle : Extension des Modules RBAC

Après avoir réussi l'unification RBAC et Users, voici les prochaines étapes recommandées :

---

## 1️⃣ Compléter les Permissions des Modules (Priorité HAUTE)

### Modules à Compléter

| Module            | Statut     | Permissions Estimées | Action                         |
| ----------------- | ---------- | -------------------- | ------------------------------ |
| **Contacts**      | ⏳ À faire | ~8 permissions       | Créer `config/permissions.php` |
| **Notifications** | ⏳ À faire | ~5 permissions       | Créer `config/permissions.php` |
| **Queue**         | ⏳ À faire | ~4 permissions       | Créer `config/permissions.php` |
| **Cron**          | ⏳ À faire | ~3 permissions       | Créer `config/permissions.php` |
| **I18n**          | ⏳ À faire | ~3 permissions       | Créer `config/permissions.php` |
| **ApiKeys**       | ⏳ À faire | ~5 permissions       | Créer `config/permissions.php` |
| **Wallet**        | ⏳ À faire | ~6 permissions       | Créer `config/permissions.php` |
| **SmsCore**       | ⏳ À faire | ~5 permissions       | Créer `config/permissions.php` |

### Template de Permissions

```php
<?php
// Modules/[ModuleName]/config/permissions.php

return [
    'module.resource.view' => 'Voir les ressources',
    'module.resource.create' => 'Créer une ressource',
    'module.resource.edit' => 'Modifier une ressource',
    'module.resource.delete' => 'Supprimer une ressource',
];
```

---

## 2️⃣ Sécuriser les Routes (Priorité HAUTE)

### Routes à Protéger

Audit de toutes les routes dans :

- `Modules/*/Routes/web.php`
- `routes/web.php`

### Actions Requises

```php
// AVANT (Non sécurisé)
$router->get('/admin/users', [UserController::class, 'index']);

// APRÈS (Sécurisé)
$router->get('/admin/users', [UserController::class, 'index'])
    ->middleware('can:admin.users.view');
```

### Checklist par Module

- [ ] **Admin** : Toutes les routes protégées
- [ ] **Auth** : Routes publiques (login/register) vs protégées (profile)
- [ ] **Settings** : Protection par `settings.view` et `settings.edit`
- [ ] **Backup** : Protection complète (sensible!)
- [ ] **Contacts** : CRUD protégé
- [ ] **Autres modules** : À auditer

---

## 3️⃣ Interface d'Administration RBAC (Priorité MOYENNE)

### Écrans à Créer

#### 3.1 Gestion des Rôles

- **Route** : `/admin/rbac/roles`
- **Fichiers** :
  - `Modules/RBAC/Controllers/RoleController.php`
  - `Modules/RBAC/Views/roles/index.php`
  - `Modules/RBAC/Views/roles/create.php`
  - `Modules/RBAC/Views/roles/edit.php`
- **Fonctionnalités** :
  - Liste des rôles
  - Créer/Modifier/Supprimer un rôle
  - Assigner des permissions à un rôle

#### 3.2 Gestion des Permissions

- **Route** : `/admin/rbac/permissions`
- **Fichiers** :
  - `Modules/RBAC/Controllers/PermissionController.php`
  - `Modules/RBAC/Views/permissions/index.php`
  - `Modules/RBAC/Views/permissions/edit.php`
- **Fonctionnalités** :
  - Liste des permissions par module
  - Modifier description/nom d'une permission
  - Activer/Désactiver une permission

#### 3.3 Gestion des Utilisateurs

- **Route** : `/admin/users`
- **Fichiers** :
  - `Modules/Admin/Controllers/UserController.php` (mise à jour)
  - `Modules/Admin/Views/users/edit.php` (ajouter section rôles)
- **Fonctionnalités** :
  - Assigner des rôles à un utilisateur
  - Visualiser les permissions d'un utilisateur

---

## 4️⃣ Implémenter le Cache des Permissions (Priorité BASSE)

### Objectif

Améliorer les performances en cachant les vérifications de permissions fréquentes.

### Actions

1. **Créer le Service de Cache**

   ```php
   // Modules/RBAC/Services/PermissionCache.php
   class PermissionCache {
       public function getUserPermissions(int $userId): array
       public function invalidate(int $userId): void
   }
   ```

2. **Modifier RbacService**

   ```php
   public function userHasPermission($user, string $permission): bool
   {
       $cached = $this->cache->getUserPermissions($user->id);
       if ($cached) return in_array($permission, $cached);

       // Logique actuelle...
   }
   ```

3. **Configuration**
   ```php
   // config/rbac.php
   'permissions_cache' => true,
   'cache_ttl' => 60 * 24, // 24 heures
   ```

---

## 5️⃣ Créer des Tests Automatisés (Priorité MOYENNE)

### Tests à Créer

```php
// tests/Unit/RbacServiceTest.php
class RbacServiceTest extends TestCase
{
    public function testAssignRole() { /* ... */ }
    public function testUserHasPermission() { /* ... */ }
    public function testRoleHasPermission() { /* ... */ }
}

// tests/Feature/RbacMiddlewareTest.php
class RbacMiddlewareTest extends TestCase
{
    public function testCheckRoleMiddleware() { /* ... */ }
    public function testCheckPermissionMiddleware() { /* ... */ }
}
```

---

## 6️⃣ Améliorer les Helpers de Template (Priorité BASSE)

### Directives à Ajouter

```php
// Core/View/TemplateEngine.php

@can('permission.name')
    <!-- Contenu visible si permission -->
@endcan

@role('admin')
    <!-- Contenu visible si rôle admin -->
@endrole

@canany(['permission1', 'permission2'])
    <!-- Contenu visible si au moins une permission -->
@endcanany
```

---

## 7️⃣ Ajouter des Événements RBAC (Priorité BASSE)

### Événements à Créer

```php
// Modules/RBAC/Events/
- RoleAssigned.php
- RoleRemoved.php
- PermissionGranted.php
- PermissionRevoked.php
```

### Utilisation

```php
// Après assignation de rôle
event(new RoleAssigned($user, $role));

// Listener
class SendRoleNotification
{
    public function handle(RoleAssigned $event)
    {
        // Envoyer notification à l'utilisateur
    }
}
```

---

## 📊 Timeline Suggérée

| Phase                            | Durée Estimée | Priorité   |
| -------------------------------- | ------------- | ---------- |
| 1. Compléter permissions modules | 2-3 heures    | 🔴 HAUTE   |
| 2. Sécuriser routes              | 3-4 heures    | 🔴 HAUTE   |
| 3. Interface admin RBAC          | 1-2 jours     | 🟡 MOYENNE |
| 4. Cache permissions             | 4-6 heures    | 🟢 BASSE   |
| 5. Tests automatisés             | 1 jour        | 🟡 MOYENNE |
| 6. Helpers template              | 2-3 heures    | 🟢 BASSE   |
| 7. Événements RBAC               | 3-4 heures    | 🟢 BASSE   |

---

## ✅ Checklist de Validation

Avant de considérer le module RBAC comme "production-ready" :

- [ ] Toutes les permissions définies dans tous les modules
- [ ] Toutes les routes sensibles protégées
- [ ] Interface d'administration fonctionnelle
- [ ] Tests unitaires passent à 100%
- [ ] Documentation complète (README.md)
- [ ] Performance acceptable (< 50ms par vérification)
- [ ] Pas d'erreurs dans les logs
- [ ] Testé en production avec plusieurs utilisateurs

---

## 🚀 Recommandation Immédiate

**Commencer par la Phase 1 (Permissions) et Phase 2 (Routes)** car ce sont les fondations de la sécurité de l'application. Les autres phases peuvent être développées de manière itérative.

**Commande pour vérifier l'état actuel** :

```bash
php verify_permissions.php
```

Bonne continuation ! 🎉
