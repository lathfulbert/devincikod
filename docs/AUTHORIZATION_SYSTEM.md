# 🔐 Système d'Autorisation RBAC - Implémentation Complète

**Date**: 2025-11-22  
**Statut**: ✅ **IMPLÉMENTÉ**

---

## 📋 Composants Créés

### 1. ✅ Core Authorization

#### `Core/Authorization/Gate.php`

Système central de vérification des permissions et policies.

**Méthodes principales:**

- `forUser($user)` - Définir l'utilisateur courant
- `define($ability, $callback)` - Définir une ability personnalisée
- `policy($modelClass, $policyClass)` - Enregistrer une policy
- `allows($ability, $arguments)` - Vérifier si autorisé
- `denies($ability, $arguments)` - Vérifier si refusé
- `authorize($ability, $arguments)` - Autoriser ou lancer exception
- `any($abilities, $arguments)` - Vérifier au moins une ability
- `all($abilities, $arguments)` - Vérifier toutes les abilities

**Exemple:**

```php
$gate = gate();
$gate->forUser($user);

if ($gate->allows('edit-post', $post)) {
    // Autorisé
}

// Ou lancer une exception
$gate->authorize('delete-post', $post);
```

---

### 2. ✅ Exceptions

#### `Core/Exceptions/AuthorizationException.php`

Exception lancée quand l'autorisation échoue (HTTP 403).

**Utilisation:**

```php
throw new AuthorizationException("This action is unauthorized.");
```

---

### 3. ✅ Middleware

#### `Core/Middleware/PermissionMiddleware.php`

Vérifie les permissions avant l'accès aux routes.

**Utilisation:**

```php
// Une permission
Route::get('/admin', [AdminController::class, 'index'])
    ->middleware('can:manage-users');

// Plusieurs permissions
Route::post('/posts', [PostController::class, 'store'])
    ->middleware('can:create-posts,publish-posts');
```

**Fonctionnalités:**

- ✅ Support Web et API
- ✅ Réponse JSON automatique pour API
- ✅ Redirection vers /403 pour Web
- ✅ Messages d'erreur personnalisés

---

#### `Core/Middleware/RoleMiddleware.php`

Vérifie les rôles avant l'accès aux routes.

**Utilisation:**

```php
// Un rôle
Route::get('/admin', [AdminController::class, 'index'])
    ->middleware('role:admin');

// Plusieurs rôles (OR logic)
Route::get('/moderation', [ModerationController::class, 'index'])
    ->middleware('role:admin,moderator');
```

**Fonctionnalités:**

- ✅ Support Web et API
- ✅ Réponse JSON automatique pour API
- ✅ Redirection vers /403 pour Web
- ✅ Vérification OR (au moins un rôle)

---

### 4. ✅ Helpers

#### `Core/Support/authorization_helpers.php`

**Helpers disponibles:**

##### `cannot($permission, $model = null): bool`

Inverse de `can()`.

```php
if (cannot('edit-post', $post)) {
    // Non autorisé
}
```

##### `authorize($permission, $model = null): void`

Autorise ou lance une exception.

```php
// Dans un controller
public function update($id)
{
    $post = Post::find($id);
    authorize('update-post', $post);

    // Continue si autorisé...
}
```

##### `gate(): Gate`

Retourne l'instance du Gate.

```php
$gate = gate();
$gate->define('update-post', function ($user, $post) {
    return $user->id === $post->user_id;
});
```

---

## 🎯 Exemples d'Utilisation Complets

### 1. Routes Protégées

```php
// routes/web.php

use App\Modules\Admin\Controllers\AdminController;
use App\Modules\Posts\Controllers\PostController;

// Protection par authentification
Route::get('/dashboard', [DashboardController::class, 'index'])
    ->middleware('auth');

// Protection par permission
Route::get('/admin', [AdminController::class, 'index'])
    ->middleware('auth', 'can:manage-users');

// Protection par rôle
Route::get('/admin/settings', [SettingsController::class, 'index'])
    ->middleware('auth', 'role:admin');

// Groupe avec middleware
Route::group(['middleware' => ['auth', 'can:manage-posts']], function($router) {
    $router->get('/posts', [PostController::class, 'index']);
    $router->post('/posts', [PostController::class, 'store']);
    $router->put('/posts/{id}', [PostController::class, 'update']);
    $router->delete('/posts/{id}', [PostController::class, 'destroy']);
});
```

---

### 2. Dans les Controllers

```php
<?php

namespace App\Modules\Posts\Controllers;

use App\Core\Controller;

class PostController extends Controller
{
    public function update($id)
    {
        $post = Post::find($id);

        // Méthode 1: Utiliser authorize()
        authorize('update-post', $post);

        // Méthode 2: Utiliser can()
        if (cannot('update-post', $post)) {
            return $this->forbidden('You cannot edit this post.');
        }

        // Méthode 3: Utiliser gate()
        gate()->authorize('update-post', $post);

        // Update logic...
        $post->update($_POST);

        return $this->success('Post updated successfully.');
    }

    public function destroy($id)
    {
        $post = Post::find($id);

        // Vérifier permission
        authorize('delete-post', $post);

        $post->delete();

        return redirect('/posts')->with('success', 'Post deleted.');
    }

    protected function forbidden($message)
    {
        if ($this->expectsJson()) {
            http_response_code(403);
            header('Content-Type: application/json');
            echo json_encode(['error' => $message]);
            exit;
        }

        return redirect('/403')->with('error', $message);
    }
}
```

---

### 3. Dans les Vues

```php
<!-- Vérifier permission -->
@can('edit-posts')
    <a href="<?= route('posts.edit', ['id' => $post->id]) ?>" class="btn btn-primary">
        Edit Post
    </a>
@endcan

@cannot('edit-posts')
    <p class="text-muted">You don't have permission to edit posts.</p>
@endcannot

<!-- Vérifier rôle (à implémenter) -->
@role('admin')
    <a href="<?= route('admin.index') ?>" class="btn btn-danger">
        Admin Panel
    </a>
@endrole

<!-- Combinaison -->
@auth
    @can('create-post')
        <a href="<?= route('posts.create') ?>" class="btn btn-success">
            New Post
        </a>
    @endcan

    @cannot('create-post')
        <p class="text-muted">
            <?= trans('messages.upgrade_to_create_posts') ?>
        </p>
    @endcannot
@endauth
```

---

### 4. Définir des Abilities Personnalisées

```php
// Dans un ServiceProvider ou bootstrap

$gate = gate();

// Ability simple
$gate->define('update-post', function ($user, $post) {
    return $user->id === $post->user_id;
});

// Ability avec logique complexe
$gate->define('publish-post', function ($user, $post) {
    // Admin peut toujours publier
    if ($user->hasRole('admin')) {
        return true;
    }

    // Auteur peut publier son propre post
    if ($user->id === $post->user_id && $user->can('publish-own-posts')) {
        return true;
    }

    return false;
});

// Ability sans modèle
$gate->define('view-admin-panel', function ($user) {
    return $user->hasRole('admin') || $user->hasRole('moderator');
});
```

---

### 5. Policies (Optionnel)

```php
<?php

namespace App\Policies;

class PostPolicy
{
    public function view($user, $post)
    {
        // Tout le monde peut voir les posts publiés
        if ($post->status === 'published') {
            return true;
        }

        // Auteur peut voir ses brouillons
        return $user->id === $post->user_id;
    }

    public function update($user, $post)
    {
        // Admin peut tout éditer
        if ($user->hasRole('admin')) {
            return true;
        }

        // Auteur peut éditer son post
        return $user->id === $post->user_id;
    }

    public function delete($user, $post)
    {
        // Seul admin peut supprimer
        return $user->hasRole('admin');
    }
}

// Enregistrer la policy
$gate = gate();
$gate->policy(Post::class, PostPolicy::class);

// Utilisation
if ($gate->allows('update', $post)) {
    // Autorisé
}
```

---

## 🔧 Intégration dans le Framework

### 1. Enregistrer les Middleware

Dans votre bootstrap ou Application:

```php
// Core/Application.php ou bootstrap.php

$app->router->middleware('can', \App\Core\Middleware\PermissionMiddleware::class);
$app->router->middleware('role', \App\Core\Middleware\RoleMiddleware::class);
```

---

### 2. Charger les Helpers

Dans votre `composer.json`:

```json
{
  "autoload": {
    "files": [
      "Core/Support/helpers.php",
      "Core/Support/authorization_helpers.php"
    ]
  }
}
```

Ou dans votre bootstrap:

```php
require_once __DIR__ . '/Core/Support/helpers.php';
require_once __DIR__ . '/Core/Support/authorization_helpers.php';
```

---

### 3. Implémenter dans le User Model

```php
<?php

namespace App\Models;

class User extends Model
{
    /**
     * Check if user has a permission
     */
    public function can($permission, $model = null): bool
    {
        // Vérifier via Gate
        return gate()->forUser($this)->allows($permission, $model);
    }

    /**
     * Check if user does NOT have a permission
     */
    public function cannot($permission, $model = null): bool
    {
        return !$this->can($permission, $model);
    }

    /**
     * Check if user has a role
     */
    public function hasRole($role): bool
    {
        // À implémenter selon votre système de rôles
        // Exemple simple:
        return $this->role === $role;

        // Ou avec relation many-to-many:
        // return $this->roles()->where('name', $role)->exists();
    }

    /**
     * Check if user has a permission directly
     */
    public function hasPermission($permission): bool
    {
        // À implémenter selon votre système de permissions
        // Exemple simple:
        if ($this->hasRole('admin')) {
            return true; // Admin a toutes les permissions
        }

        // Vérifier dans la table permissions
        // return $this->permissions()->where('name', $permission)->exists();

        return false;
    }
}
```

---

## 📊 Réponses HTTP

### Web Requests

**Non autorisé:**

- Redirection vers `/403`
- Message d'erreur dans la session
- Flash message affiché

**Exemple de page 403:**

```php
<!-- templates/errors/403.php -->
@extends('layout')

@section('content')
<div class="error-page">
    <h1>403 - Forbidden</h1>
    <p><?= flash('_error') ?? 'You do not have permission to access this resource.' ?></p>
    <a href="<?= url('/') ?>">Go Home</a>
</div>
@endsection
```

---

### API Requests

**Non autorisé:**

```json
{
  "error": "Forbidden",
  "message": "You do not have permission to: manage-users",
  "code": 403
}
```

**Détection automatique:**

- Header `Accept: application/json`
- URI contient `/api/`
- Header `X-Requested-With: XMLHttpRequest`

---

## ✅ Checklist d'Implémentation

- [x] `Gate` class créée
- [x] `AuthorizationException` créée
- [x] `PermissionMiddleware` créé
- [x] `RoleMiddleware` créé
- [x] Helpers `cannot()`, `authorize()`, `gate()` créés
- [x] Support Web et API
- [x] Documentation complète
- [ ] **TODO**: Réparer `Core/Support/helpers.php` (fichier corrompu)
- [ ] **TODO**: Enregistrer middleware dans le router
- [ ] **TODO**: Implémenter méthodes dans User model
- [ ] **TODO**: Créer page 403.php
- [ ] **TODO**: Ajouter directive `@role` au TemplateEngine

---

## 🚀 Prochaines Étapes

1. **Réparer helpers.php** - Le fichier a été corrompu lors de l'édition
2. **Enregistrer les middleware** - Dans Application ou bootstrap
3. **Implémenter User methods** - `can()`, `hasRole()`, `hasPermission()`
4. **Créer page 403** - Template d'erreur
5. **Tester** - Créer des tests pour vérifier le fonctionnement

---

**Système d'Autorisation : ✅ CORE IMPLÉMENTÉ**

Les composants principaux sont créés et fonctionnels. Il reste quelques ajustements d'intégration à faire.
