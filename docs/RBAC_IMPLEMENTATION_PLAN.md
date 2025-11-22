# 🔐 Plan d'Implémentation : Système d'Autorisation RBAC

**Date**: 2025-11-22  
**Objectif**: Implémenter un système complet d'autorisation avec rôles et permissions

---

## 📋 Composants à Créer

### 1. Base de Données

- ✅ Migration `roles` table
- ✅ Migration `permissions` table
- ✅ Migration `role_user` pivot table
- ✅ Migration `permission_role` pivot table
- ✅ Migration `permission_user` pivot table (permissions directes)

### 2. Models

- ✅ `Role` model avec relations
- ✅ `Permission` model avec relations
- ✅ Extension du `User` model avec:
  - `roles()` relation
  - `permissions()` relation
  - `can($permission, $model = null)` method
  - `cannot($permission, $model = null)` method
  - `hasRole($role)` method
  - `hasPermission($permission)` method
  - `assignRole($role)` method
  - `givePermissionTo($permission)` method

### 3. Traits

- ✅ `HasRoles` trait
- ✅ `HasPermissions` trait
- ✅ `Authorizable` trait

### 4. Middleware

- ✅ `AuthMiddleware` - Vérifier authentification
- ✅ `PermissionMiddleware` - Vérifier permissions (`can:permission`)
- ✅ `RoleMiddleware` - Vérifier rôles (`role:admin`)
- ✅ Support réponses JSON pour API

### 5. Authorization System

- ✅ `Gate` class - Vérifier permissions
- ✅ `Policy` system - Policies par modèle
- ✅ `AuthorizationServiceProvider`

### 6. Helpers & Facades

- ✅ `can()` helper (déjà créé)
- ✅ `cannot()` helper
- ✅ `authorize()` helper
- ✅ `gate()` helper

### 7. Sécurité Avancée

- ✅ CSRF protection (déjà existant)
- ✅ Rate limiting / Throttling
- ✅ JWT token support pour API
- ✅ OAuth2 support (optionnel)

### 8. Seeders

- ✅ `RolesSeeder` - Rôles par défaut
- ✅ `PermissionsSeeder` - Permissions par défaut
- ✅ `AdminUserSeeder` - Utilisateur admin

### 9. Documentation & Exemples

- ✅ Guide d'utilisation
- ✅ Exemples de controllers sécurisés
- ✅ Exemples de routes protégées
- ✅ Exemples de policies

---

## 🎯 Ordre d'Implémentation

1. **Migrations** (Base de données)
2. **Models & Traits** (Logique métier)
3. **Gate & Authorization** (Système de vérification)
4. **Middleware** (Protection des routes)
5. **Helpers** (Fonctions utilitaires)
6. **Seeders** (Données initiales)
7. **Examples** (Documentation pratique)

---

## 📝 Exemples d'Utilisation Attendus

### Routes Protégées

```php
// Protéger par authentification
Route::get('/dashboard', [DashboardController::class, 'index'])
    ->middleware('auth');

// Protéger par permission
Route::get('/admin', [AdminController::class, 'index'])
    ->middleware('auth', 'can:manage-users');

// Protéger par rôle
Route::get('/admin/settings', [SettingsController::class, 'index'])
    ->middleware('auth', 'role:admin');

// Protéger par plusieurs permissions
Route::post('/posts', [PostController::class, 'store'])
    ->middleware('auth', 'can:create-posts,publish-posts');
```

### Dans les Controllers

```php
class PostController extends Controller
{
    public function update(Request $request, $id)
    {
        $post = Post::find($id);

        // Vérifier permission
        $this->authorize('update-post', $post);

        // Ou avec can()
        if (!$this->can('update-post', $post)) {
            abort(403);
        }

        // Update logic...
    }
}
```

### Dans les Vues

```php
@can('edit-posts')
    <a href="<?= route('posts.edit', ['id' => $post->id]) ?>">Edit</a>
@endcan

@role('admin')
    <a href="<?= route('admin.index') ?>">Admin Panel</a>
@endrole
```

### Vérifications Programmatiques

```php
// Vérifier permission
if ($user->can('edit-post', $post)) {
    // Allowed
}

// Vérifier rôle
if ($user->hasRole('admin')) {
    // Is admin
}

// Assigner rôle
$user->assignRole('moderator');

// Donner permission
$user->givePermissionTo('edit-posts');
```

---

## 🔒 Sécurité

### CSRF Protection

- Déjà implémenté via `csrf_field()` et `csrf_token()`
- Middleware CSRF vérifie automatiquement

### Rate Limiting

```php
Route::post('/login', [AuthController::class, 'login'])
    ->middleware('throttle:5,1'); // 5 tentatives par minute
```

### API Authentication

```php
// JWT Token
Route::get('/api/user', [UserController::class, 'show'])
    ->middleware('auth:api');

// OAuth2
Route::get('/api/posts', [PostController::class, 'index'])
    ->middleware('auth:oauth');
```

---

Prêt à commencer l'implémentation !
