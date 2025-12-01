# Module RBAC - Documentation Complète

## Vue d'Ensemble

Le module RBAC (Role-Based Access Control) fournit un système centralisé et modulaire pour la gestion des utilisateurs, rôles et permissions dans SunuFramework2.

### Principes Fondamentaux

- **Source Unique de Vérité** : Tous les modèles RBAC sont centralisés dans `Modules/RBAC`
- **Découplage** : Utilisation de contrats (interfaces) pour isoler les dépendances
- **Modularité** : Chaque module peut définir ses propres permissions
- **Extensibilité** : Auto-chargement des permissions depuis tous les modules

---

## Architecture

### 1. Modèles Unifiés

Tous situés dans `Modules/RBAC/Models/` :

- **Role** : Représente un rôle (admin, user, etc.)
- **Permission** : Représente une permission spécifique
- **RolePermission** : Table pivot Role ↔ Permission
- **UserRole** : Table pivot User ↔ Role

### 2. Services

#### RbacService (`Modules/RBAC/Services/RbacService.php`)

Implémente `RbacProviderInterface` et fournit :

```php
// Gestion des rôles
assignRole(User $user, string $role)
removeRole(User $user, string $role)
syncRoles(User $user, array $roles)
hasRole(User $user, string $role): bool

// Gestion des permissions
givePermissionToRole(string $role, string $permission)
roleHasPermission(string $role, string $permission): bool
userHasPermission(User $user, string $permission): bool
```

#### PermissionLoader (`Modules/RBAC/Services/PermissionLoader.php`)

Auto-charge les permissions depuis tous les modules :

```php
scanAndLoad()         // Scan tous les modules et charge les permissions
seedDefaultRoles()    // Crée les rôles par défaut
```

### 3. Middlewares

Trois middlewares pour protéger les routes :

- **CheckRole** : Vérifie qu'un utilisateur a un rôle spécifique
- **CheckPermission** : Vérifie qu'un utilisateur a une permission spécifique
- **CheckAnyPermission** : Vérifie qu'un utilisateur a au moins une des permissions

Enregistrés dans `Core/Application.php` :

```php
$router->middleware('role', \Modules\RBAC\Middleware\CheckRole::class);
$router->middleware('can', \Modules\RBAC\Middleware\CheckPermission::class);
$router->middleware('can_any', \Modules\RBAC\Middleware\CheckAnyPermission::class);
```

---

## Utilisation

### 1. Définir des Permissions pour un Module

Créer `Modules/VotreModule/config/permissions.php` :

```php
<?php

return [
    'module.action' => 'Description de la permission',
    'module.users.view' => 'Voir les utilisateurs du module',
    'module.users.create' => 'Créer des utilisateurs',
    'module.users.edit' => 'Modifier des utilisateurs',
    'module.users.delete' => 'Supprimer des utilisateurs',
];
```

**Conventions de nommage** :

- Format : `module.ressource.action`
- Exemples :
  - `admin.users.view`
  - `settings.sms.manage`
  - `backup.create`

### 2. Protéger des Routes avec des Middlewares

```php
// Vérifier un rôle
$router->get('/admin/dashboard', [AdminController::class, 'index'])
    ->middleware('role:admin');

// Vérifier une permission
$router->get('/admin/users', [UserController::class, 'index'])
    ->middleware('can:admin.users.view');

// Vérifier plusieurs permissions (OR)
$router->get('/admin/settings', [SettingsController::class, 'index'])
    ->middleware('can_any:admin.settings.view,settings.view');
```

### 3. Utiliser RbacService dans un Contrôleur

```php
use Modules\RBAC\Services\RbacService;
use App\Core\Container\Container;

class UserController
{
    protected RbacService $rbac;

    public function __construct()
    {
        $this->rbac = Container::getInstance()->make(RbacService::class);
    }

    public function assignRole($userId, $roleName)
    {
        $user = User::find($userId);
        $this->rbac->assignRole($user, $roleName);
    }

    public function checkPermission($userId, $permission)
    {
        $user = User::find($userId);
        return $this->rbac->userHasPermission($user, $permission);
    }
}
```

### 4. Vérifier les Permissions dans les Vues

```php
<?php if (can('admin.users.create')): ?>
    <a href="/admin/users/create">Créer un utilisateur</a>
<?php endif; ?>
```

---

## Règles et Bonnes Pratiques

### ✅ À FAIRE

1. **Toujours utiliser les interfaces** (`UserProviderInterface`, `RbacProviderInterface`)
2. **Définir les permissions par module** dans `config/permissions.php`
3. **Utiliser des noms de permissions descriptifs** et cohérents
4. **Documenter chaque permission** avec une description claire
5. **Protéger toutes les routes sensibles** avec des middlewares
6. **Utiliser `RbacService`** pour toute logique métier liée aux permissions
7. **Tester les permissions** après chaque modification

### ❌ À ÉVITER

1. **Ne jamais créer de modèles User/Role/Permission redondants** dans d'autres modules
2. **Ne pas hardcoder les vérifications de permissions** dans le code
3. **Ne pas utiliser directement les modèles RBAC** sans passer par `RbacService`
4. **Ne pas créer de permissions trop génériques** (ex: 'admin' au lieu de 'admin.users.view')
5. **Ne pas oublier de synchroniser les rôles** lors de la création/mise à jour d'utilisateurs

---

## Configuration

### Fichier `config/rbac.php`

```php
return [
    'permissions_cache' => true,           // Activer le cache des permissions
    'cache_ttl' => 60 * 24,               // Durée du cache (24h)
    'default_roles' => ['admin', 'user'], // Rôles créés automatiquement
    'super_admin_role' => 'super-admin',  // Rôle super administrateur
];
```

---

## Exemples Pratiques

### Créer un Utilisateur avec des Rôles

```php
use Modules\Users\Services\UserService;

$userService = new UserService();

$user = $userService->createUser([
    'username' => 'john.doe',
    'email' => 'john@example.com',
    'password' => 'secret123',
    'roles' => ['admin', 'editor'] // Assignation automatique
]);
```

### Donner une Permission à un Rôle

```php
$rbacService = new RbacService();

$rbacService->givePermissionToRole('editor', 'blog.posts.edit');
$rbacService->givePermissionToRole('admin', 'admin.users.delete');
```

### Vérifier les Permissions d'un Utilisateur

```php
$user = User::find(1);

if ($rbacService->hasRole($user, 'admin')) {
    // L'utilisateur est admin
}

if ($rbacService->userHasPermission($user, 'blog.posts.edit')) {
    // L'utilisateur peut éditer les posts
}
```

---

## Migration et Base de Données

### Tables Créées

- `users` : Utilisateurs (dans Users module)
- `roles` : Rôles disponibles
- `permissions` : Permissions disponibles
- `user_roles` : Association User ↔ Role
- `role_permissions` : Association Role ↔ Permission

### Structure de `permissions`

```sql
CREATE TABLE permissions (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    slug VARCHAR(255) NOT NULL UNIQUE,
    name VARCHAR(255) NOT NULL,
    description TEXT,
    module VARCHAR(255) NULL, -- Ajouté par AddModuleToPermissionsTable
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);
```

---

## Dépannage

### Les permissions ne se chargent pas

1. Vérifier que le fichier `config/permissions.php` existe dans le module
2. Vérifier que `RBACModule::boot()` est bien appelé
3. Exécuter manuellement : `php verify_permissions.php`

### Erreur "Column 'module' not found"

La migration n'a pas été exécutée. Corriger :

```bash
php sunu migrate
```

### Les middlewares ne fonctionnent pas

Vérifier l'enregistrement dans `Core/Application.php` ligne 123-126.

---

## Prochaines Étapes

### Phase 1 : Tests et Validation ✅ FAIT

- [x] Tester Login/Register avec UserService
- [x] Vérifier Admin Dashboard avec modèles unifiés
- [x] Valider le chargement des permissions

### Phase 2 : Extension des Modules 🔄 EN COURS

- [ ] Ajouter `config/permissions.php` aux modules restants :
  - [ ] Contacts
  - [ ] Notifications
  - [ ] Queue
  - [ ] Cron
  - [ ] I18n
  - [ ] ApiKeys
- [ ] Protéger toutes les routes avec middlewares appropriés

### Phase 3 : Interface Utilisateur 📋 À FAIRE

- [ ] Créer une interface d'administration pour :
  - [ ] Gestion des rôles (CRUD)
  - [ ] Gestion des permissions (CRUD)
  - [ ] Assignation Role ↔ Permission
  - [ ] Assignation User ↔ Role
- [ ] Ajouter des vues pour visualiser les permissions par module

### Phase 4 : Optimisations 🚀 FUTUR

- [ ] Implémenter le cache des permissions
- [ ] Ajouter des événements (UserRoleAssigned, PermissionGranted, etc.)
- [ ] Créer des helpers supplémentaires (`@can`, `@role` pour templates)
- [ ] Ajouter la gestion des permissions par utilisateur (en plus des rôles)

### Phase 5 : Documentation Avancée 📚 FUTUR

- [ ] Ajouter des tests unitaires pour RbacService
- [ ] Créer un guide de migration depuis d'autres systèmes RBAC
- [ ] Documenter les patterns avancés (permissions hiérarchiques, etc.)

---

## Support et Contribution

Pour toute question ou contribution, consulter :

- Code source : `Modules/RBAC/`
- Walkthrough : `walkthrough.md`
- Implementation plan : `implementation_plan.md`

**Dernière mise à jour** : 2025-12-01
