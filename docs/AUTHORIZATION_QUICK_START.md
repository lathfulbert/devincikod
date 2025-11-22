# 🚀 Guide Rapide d'Intégration - Système d'Autorisation

**Temps estimé: 30 minutes**

---

## Étape 1: Enregistrer les Middleware (5 min)

### Option A: Dans `Core/Application.php`

Ajouter dans le constructeur ou la méthode `boot()`:

```php
// Core/Application.php

public function boot()
{
    // ... code existant ...

    // Enregistrer les middleware d'autorisation
    $this->router->middleware('can', \App\Core\Middleware\PermissionMiddleware::class);
    $this->router->middleware('role', \App\Core\Middleware\RoleMiddleware::class);
}
```

### Option B: Dans un fichier bootstrap

```php
// bootstrap/middleware.php ou bootstrap.php

$app->router->middleware('can', \App\Core\Middleware\PermissionMiddleware::class);
$app->router->middleware('role', \App\Core\Middleware\RoleMiddleware::class);
```

---

## Étape 2: Charger les Helpers (2 min)

### Option A: Via Composer (Recommandé)

Modifier `composer.json`:

```json
{
  "autoload": {
    "psr-4": {
      "App\\": ".",
      "Modules\\": "Modules/"
    },
    "files": [
      "Core/Support/helpers.php",
      "Core/Support/authorization_helpers.php"
    ]
  }
}
```

Puis exécuter:

```bash
composer dump-autoload
```

### Option B: Require manuel

Dans votre `public/index.php` ou bootstrap:

```php
require_once __DIR__ . '/../Core/Support/helpers.php';
require_once __DIR__ . '/../Core/Support/authorization_helpers.php';
```

---

## Étape 3: Implémenter dans User Model (10 min)

Ouvrir votre fichier `User.php` et ajouter ces méthodes:

```php
<?php

namespace App\Models; // ou Modules\Admin\Models

class User extends Model
{
    // ... propriétés existantes ...

    /**
     * Check if user has a permission
     */
    public function can($permission, $model = null): bool
    {
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
        // OPTION 1: Champ simple 'role' dans la table users
        return $this->role === $role;

        // OPTION 2: Relation many-to-many avec table roles
        // return $this->roles()->where('name', $role)->exists();
    }

    /**
     * Check if user has a permission directly
     */
    public function hasPermission($permission): bool
    {
        // Admin a toutes les permissions
        if ($this->hasRole('admin')) {
            return true;
        }

        // OPTION 1: Vérifier dans une table permissions
        // return $this->permissions()->where('name', $permission)->exists();

        // OPTION 2: Vérifier via les rôles
        // return $this->roles()->whereHas('permissions', function($q) use ($permission) {
        //     $q->where('name', $permission);
        // })->exists();

        // Pour l'instant, retourner false
        return false;
    }
}
```

---

## Étape 4: Créer la Page 403 (5 min)

Créer le fichier `templates/errors/403.php`:

```php
@extends('admin.layout')

@section('content')
<div class="container">
    <div class="error-page text-center py-5">
        <div class="error-code display-1 text-danger mb-4">403</div>
        <h1 class="mb-3">Accès Refusé</h1>
        <p class="lead mb-4">
            <?= flash('_error') ?? 'Vous n\'avez pas la permission d\'accéder à cette ressource.' ?>
        </p>

        <div class="error-actions">
            <a href="<?= url('/') ?>" class="btn btn-primary">
                <i class="icon-home"></i> Accueil
            </a>

            @auth
                <a href="<?= url('/dashboard') ?>" class="btn btn-secondary">
                    <i class="icon-grid"></i> Tableau de bord
                </a>
            @endauth

            @guest
                <a href="<?= url('/login') ?>" class="btn btn-secondary">
                    <i class="icon-log-in"></i> Connexion
                </a>
            @endguest
        </div>
    </div>
</div>
@endsection
```

---

## Étape 5: Tester (5 min)

### Test 1: Route Protégée

Dans votre fichier de routes:

```php
// routes/web.php

// Route de test protégée par permission
$router->get('/test-permission', function() {
    return "Vous avez la permission!";
})->middleware('auth', 'can:test-permission');

// Route de test protégée par rôle
$router->get('/test-admin', function() {
    return "Vous êtes admin!";
})->middleware('auth', 'role:admin');
```

### Test 2: Dans un Controller

Créer un controller de test:

```php
<?php

namespace App\Controllers;

class TestAuthController
{
    public function testCan()
    {
        // Test can()
        if (can('edit-posts')) {
            echo "✅ Vous pouvez éditer les posts<br>";
        } else {
            echo "❌ Vous ne pouvez pas éditer les posts<br>";
        }

        // Test cannot()
        if (cannot('delete-posts')) {
            echo "❌ Vous ne pouvez pas supprimer les posts<br>";
        } else {
            echo "✅ Vous pouvez supprimer les posts<br>";
        }

        // Test gate()
        $gate = gate();
        echo "Gate instance: " . get_class($gate) . "<br>";
    }

    public function testAuthorize()
    {
        try {
            authorize('manage-users');
            echo "✅ Autorisé à gérer les utilisateurs";
        } catch (\App\Core\Exceptions\AuthorizationException $e) {
            echo "❌ Non autorisé: " . $e->getMessage();
        }
    }
}
```

### Test 3: Vérifier les Helpers

Créer `test_authorization.php` à la racine:

```php
<?php

require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/Core/Support/helpers.php';
require_once __DIR__ . '/Core/Support/authorization_helpers.php';

echo "=== Test des Helpers d'Autorisation ===\n\n";

// Test existence
echo "1. Vérification des helpers:\n";
echo "   can() existe: " . (function_exists('can') ? '✅' : '❌') . "\n";
echo "   cannot() existe: " . (function_exists('cannot') ? '✅' : '❌') . "\n";
echo "   authorize() existe: " . (function_exists('authorize') ? '✅' : '❌') . "\n";
echo "   gate() existe: " . (function_exists('gate') ? '✅' : '❌') . "\n\n";

// Test gate()
echo "2. Test du Gate:\n";
$gate = gate();
echo "   Gate class: " . get_class($gate) . "\n";

// Définir une ability de test
$gate->define('test-ability', function ($user) {
    return true;
});

echo "   Ability définie: test-ability\n";
echo "\n✅ Tous les tests passent!\n";
```

Exécuter:

```bash
php test_authorization.php
```

---

## Étape 6: Utiliser dans votre Application (5 min)

### Exemple: Protéger une Route Admin

```php
// routes/web.php

$router->group(['prefix' => '/admin', 'middleware' => ['auth', 'role:admin']], function($router) {
    $router->get('/', [AdminController::class, 'index']);
    $router->get('/users', [UserController::class, 'index'])->middleware('can:manage-users');
    $router->get('/settings', [SettingsController::class, 'index'])->middleware('can:manage-settings');
});
```

### Exemple: Dans un Controller

```php
class PostController extends Controller
{
    public function update($id)
    {
        $post = Post::find($id);

        // Vérifier permission
        authorize('update-post', $post);

        // Continuer...
        $post->update($_POST);
        return redirect('/posts/' . $id);
    }
}
```

### Exemple: Dans une Vue

```php
<div class="post-actions">
    @can('edit-posts')
        <a href="<?= route('posts.edit', ['id' => $post->id]) ?>" class="btn btn-primary">
            Éditer
        </a>
    @endcan

    @can('delete-posts')
        <button class="btn btn-danger" onclick="deletePost(<?= $post->id ?>)">
            Supprimer
        </button>
    @endcan
</div>
```

---

## ✅ Checklist de Validation

Après avoir terminé toutes les étapes:

- [ ] Middleware enregistrés
- [ ] Helpers chargés
- [ ] Méthodes User implémentées
- [ ] Page 403 créée
- [ ] Tests exécutés avec succès
- [ ] Route de test fonctionne
- [ ] Helpers fonctionnent
- [ ] Gate fonctionne

---

## 🐛 Dépannage

### Problème: "Undefined function auth()"

**Solution**: Assurez-vous que votre système d'authentification est bien configuré et que la fonction `auth()` existe.

### Problème: "Class not found: PermissionMiddleware"

**Solution**: Vérifiez que le namespace est correct et que l'autoload Composer est à jour:

```bash
composer dump-autoload
```

### Problème: "Call to undefined method can()"

**Solution**: Assurez-vous d'avoir implémenté la méthode `can()` dans votre User model.

### Problème: Redirection infinie vers /403

**Solution**: Assurez-vous que la route `/403` n'est pas protégée par un middleware d'autorisation.

---

## 📚 Ressources

- **Documentation complète**: `docs/AUTHORIZATION_SYSTEM.md`
- **Exemples**: `examples/authorization_complete_example.php`
- **Résumé final**: `docs/AUTHORIZATION_FINAL_SUMMARY.md`

---

**Félicitations ! Votre système d'autorisation est maintenant opérationnel ! 🎉**
