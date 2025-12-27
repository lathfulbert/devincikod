# Documentation du Système de Routage

## Table des Matières

1. [Introduction](#introduction)
2. [Routes Simples](#routes-simples)
3. [Routes avec Paramètres](#routes-avec-paramètres)
4. [Middlewares](#middlewares)
5. [Routes Groupées](#routes-groupées)
6. [Routes Nommées](#routes-nommées)
7. [Génération d'URLs](#génération-durls)
8. [Exemples Pratiques](#exemples-pratiques)

---

## Introduction

Le système de routage de SunuFramework permet de définir des routes HTTP (GET, POST, PUT, DELETE) et de les associer à des contrôleurs ou des fonctions anonymes.

### Fichier de Routes

Les routes sont définies dans des fichiers dédiés :

- **Routes Web** : `routes/web.php`
- **Routes Module** : `Modules/{ModuleName}/Routes/web.php`

---

## Routes Simples

### Syntaxe de Base

```php
$router->get('/chemin', [Controller::class, 'methode']);
$router->post('/chemin', [Controller::class, 'methode']);
$router->put('/chemin', [Controller::class, 'methode']);
$router->delete('/chemin', [Controller::class, 'methode']);
```

### Exemples

```php
use Modules\Admin\Controllers\AdminController;

// Route GET simple
$router->get('/admin/dashboard', [AdminController::class, 'dashboard']);

// Route POST pour traiter un formulaire
$router->post('/admin/users/store', [UserController::class, 'store']);

// Route avec fonction anonyme
$router->get('/test', function() {
    echo "Hello World!";
});
```

---

## Routes avec Paramètres

### Paramètres Simples

```php
// {id} sera capturé et passé au contrôleur
$router->get('/users/{id}', [UserController::class, 'show']);
```

### Méthode du Contrôleur

```php
class UserController
{
    public function show(array $params = [])
    {
        $userId = $params['id'];
        $user = User::find($userId);
        echo view('users/show', ['user' => $user]);
    }
}
```

### Paramètres Multiples

```php
// Plusieurs paramètres
$router->get('/posts/{category}/{slug}', [PostController::class, 'show']);
```

```php
public function show(array $params = [])
{
    $category = $params['category']; // ex: "tech"
    $slug = $params['slug'];          // ex: "nouveau-article"
}
```

---

## Middlewares

### Middleware Simple

```php
$router->get('/admin', [AdminController::class, 'index'])
    ->middleware('auth');
```

### Middleware avec Paramètres

```php
// Vérifier une permission spécifique
$router->get('/admin/users', [UserController::class, 'index'])
    ->middleware('can:admin.users.view');

// Vérifier un rôle
$router->post('/admin/permissions/store', [PermissionController::class, 'store'])
    ->middleware('role:admin');
```

### Middlewares Multiples (Chaînés)

```php
$router->post('/admin/modules/install', [ModuleController::class, 'install'])
    ->middleware('auth')
    ->middleware('role:admin')
    ->middleware('can:admin.modules.manage');
```

### Middlewares Disponibles

| Middleware       | Description              | Exemple                                |
| ---------------- | ------------------------ | -------------------------------------- |
| `auth`           | Authentification requise | `->middleware('auth')`                 |
| `can:permission` | Permission RBAC requise  | `->middleware('can:admin.users.view')` |
| `role:role_name` | Rôle requis              | `->middleware('role:admin')`           |
| `api_auth`       | Authentification API     | `->middleware('api_auth')`             |

---

## Routes Groupées

Les groupes permettent de partager des attributs entre plusieurs routes.

### Groupe avec Préfixe

```php
$router->group(['prefix' => '/admin'], function ($router) {
    // URL finale: /admin/dashboard
    $router->get('/dashboard', [AdminController::class, 'dashboard']);

    // URL finale: /admin/users
    $router->get('/users', [UserController::class, 'index']);
});
```

### Groupe avec Middleware

```php
$router->group(['middleware' => ['auth']], function ($router) {
    $router->get('/profile', [ProfileController::class, 'show']);
    $router->post('/profile/update', [ProfileController::class, 'update']);
});
```

### Groupe Complet (Préfixe + Middleware)

```php
$router->group([
    'prefix' => '/admin/roles',
    'middleware' => ['auth', 'can:admin.roles.view']
], function ($router) {

    // GET /admin/roles
    $router->get('', [RoleController::class, 'index']);

    // GET /admin/roles/create
    $router->get('/create', [RoleController::class, 'create']);

    // POST /admin/roles/store
    $router->post('/store', [RoleController::class, 'store']);

    // POST /admin/roles/{id}/delete avec middleware supplémentaire
    $router->post('/{id}/delete', [RoleController::class, 'delete'])
        ->middleware('can:admin.roles.delete');
});
```

### Groupes Imbriqués

```php
$router->group(['prefix' => '/admin', 'middleware' => ['auth']], function ($router) {

    // Sous-groupe pour les utilisateurs
    $router->group(['prefix' => '/users'], function ($router) {
        // GET /admin/users
        $router->get('', [UserController::class, 'index']);

        // POST /admin/users/store
        $router->post('/store', [UserController::class, 'store']);
    });

    // Sous-groupe pour les rôles
    $router->group(['prefix' => '/roles'], function ($router) {
        // GET /admin/roles
        $router->get('', [RoleController::class, 'index']);
    });
});
```

---

## Routes Nommées

Les routes nommées facilitent la génération d'URLs.

### Nommer une Route

```php
$router->get('/admin/users', [UserController::class, 'index'])
    ->name('admin.users.index');

$router->get('/admin/users/{id}/edit', [UserController::class, 'edit'])
    ->name('admin.users.edit');
```

### Convention de Nommage

```
{module}.{resource}.{action}

Exemples:
- admin.users.index
- admin.users.create
- admin.users.edit
- admin.roles.index
- blog.posts.show
```

---

## Génération d'URLs

### Helper `url()`

Génère une URL complète à partir d'un chemin.

```php
// Génère: http://localhost/admin/users
echo url('/admin/users');

// Avec paramètres
echo url('/admin/users/' . $user->id . '/edit');
```

### Helper `route()` (Routes Nommées)

**Important**: Le helper `route()` nécessite l'instance du Router.

```php
// Dans un contrôleur
$app = Application::getInstance();
$url = $app->router->route('admin.users.index');

// Avec paramètres
$url = $app->router->route('admin.users.edit', ['id' => 5]);
// Génère: http://localhost/admin/users/5/edit
```

### Dans les Vues

```php
<!-- Lien vers la liste des utilisateurs -->
<a href="<?= url('/admin/users') ?>">Voir les utilisateurs</a>

<!-- Lien d'édition avec ID -->
<a href="<?= url('/admin/users/' . $user->id . '/edit') ?>">
    Éditer
</a>

<!-- Formulaire avec action -->
<form action="<?= url('/admin/users/store') ?>" method="POST">
    <?= csrf_field() ?>
    <!-- ... -->
</form>
```

---

## Exemples Pratiques

### 1. CRUD Complet pour une Ressource

```php
use Modules\Blog\Controllers\PostController;

$router->group([
    'prefix' => '/admin/posts',
    'middleware' => ['auth']
], function ($router) {

    // Liste
    $router->get('', [PostController::class, 'index'])
        ->name('admin.posts.index')
        ->middleware('can:posts.view');

    // Formulaire de création
    $router->get('/create', [PostController::class, 'create'])
        ->name('admin.posts.create')
        ->middleware('can:posts.create');

    // Enregistrement
    $router->post('/store', [PostController::class, 'store'])
        ->middleware('can:posts.create');

    // Affichage
    $router->get('/{id}', [PostController::class, 'show'])
        ->name('admin.posts.show')
        ->middleware('can:posts.view');

    // Formulaire d'édition
    $router->get('/{id}/edit', [PostController::class, 'edit'])
        ->name('admin.posts.edit')
        ->middleware('can:posts.edit');

    // Mise à jour
    $router->post('/{id}/update', [PostController::class, 'update'])
        ->middleware('can:posts.edit');

    // Suppression
    $router->post('/{id}/delete', [PostController::class, 'delete'])
        ->middleware('can:posts.delete');
});
```

### 2. Routes API avec Authentification

```php
use Modules\Api\Controllers\ApiPostController;

$router->group([
    'prefix' => '/api/v1/posts',
    'middleware' => ['api_auth']
], function ($router) {

    // GET /api/v1/posts
    $router->get('', [ApiPostController::class, 'index'])
        ->middleware('can:posts.view');

    // POST /api/v1/posts
    $router->post('', [ApiPostController::class, 'store'])
        ->middleware('can:posts.create');

    // GET /api/v1/posts/{id}
    $router->get('/{id}', [ApiPostController::class, 'show'])
        ->middleware('can:posts.view');

    // PUT /api/v1/posts/{id}
    $router->put('/{id}', [ApiPostController::class, 'update'])
        ->middleware('can:posts.edit');

    // DELETE /api/v1/posts/{id}
    $router->delete('/{id}', [ApiPostController::class, 'destroy'])
        ->middleware('can:posts.delete');
});
```

### 3. Routes Publiques vs Protégées

```php
use Modules\Shop\Controllers\ProductController;

// Routes publiques
$router->get('/products', [ProductController::class, 'index'])
    ->name('products.index');

$router->get('/products/{id}', [ProductController::class, 'show'])
    ->name('products.show');

// Routes protégées (admin)
$router->group([
    'prefix' => '/admin/products',
    'middleware' => ['auth', 'can:products.manage']
], function ($router) {

    $router->get('', [ProductController::class, 'adminIndex'])
        ->name('admin.products.index');

    $router->get('/create', [ProductController::class, 'create'])
        ->name('admin.products.create');

    $router->post('/store', [ProductController::class, 'store']);

    $router->get('/{id}/edit', [ProductController::class, 'edit'])
        ->name('admin.products.edit');

    $router->post('/{id}/update', [ProductController::class, 'update']);

    $router->post('/{id}/delete', [ProductController::class, 'delete']);
});
```

### 4. Routes avec Redirections

```php
// Redirection simple
$router->get('/', function() {
    redirect('/login');
});

// Redirection dans un contrôleur
public function store()
{
    $user = new User();
    $user->username = $_POST['username'];
    $user->save();

    redirect('/admin/users'); // Redirection après création
}
```

### 5. Routes de Fichiers (Upload/Download)

```php
use App\Core\Files\Controllers\FileController;

$router->group([
    'prefix' => '/api/files',
    'middleware' => ['secure_upload', 'api_auth']
], function ($router) {

    // Upload
    $router->post('/upload', [FileController::class, 'upload'])
        ->middleware('can:files.upload');

    // Liste
    $router->get('/list', [FileController::class, 'list'])
        ->middleware('can:files.list');

    // Suppression
    $router->delete('/delete', [FileController::class, 'delete'])
        ->middleware('can:files.delete');
});
```

### 6. Routes Module (Exemple RBAC)

**Fichier**: `Modules/RBAC/Routes/web.php`

```php
<?php

use Modules\RBAC\Controllers\RoleController;
use Modules\RBAC\Controllers\PermissionController;

/** @var \App\Core\Routing\Router $router */

// Gestion des Rôles
$router->group([
    'prefix' => '/admin/roles',
    'middleware' => ['auth', 'can:admin.roles.view']
], function ($router) {
    $router->get('', [RoleController::class, 'index'])
        ->name('admin.roles.index');

    $router->get('/create', [RoleController::class, 'create'])
        ->middleware('can:admin.roles.create')
        ->name('admin.roles.create');

    $router->post('/store', [RoleController::class, 'store'])
        ->middleware('can:admin.roles.create');

    $router->get('/{id}/edit', [RoleController::class, 'edit'])
        ->middleware('can:admin.roles.edit')
        ->name('admin.roles.edit');

    $router->post('/{id}/update', [RoleController::class, 'update'])
        ->middleware('can:admin.roles.edit');

    $router->post('/{id}/delete', [RoleController::class, 'delete'])
        ->middleware('can:admin.roles.delete');
});

// Gestion des Permissions
$router->group([
    'prefix' => '/admin/permissions',
    'middleware' => ['auth', 'role:admin']
], function ($router) {
    $router->get('', [PermissionController::class, 'index'])
        ->name('admin.permissions.index');

    $router->get('/create', [PermissionController::class, 'create'])
        ->name('admin.permissions.create');

    $router->post('/store', [PermissionController::class, 'store']);

    $router->get('/{id}/edit', [PermissionController::class, 'edit'])
        ->name('admin.permissions.edit');

    $router->post('/{id}/update', [PermissionController::class, 'update']);

    $router->post('/{id}/delete', [PermissionController::class, 'delete']);
});
```

---

## Bonnes Pratiques

### 1. **Toujours utiliser des routes nommées pour les vues**

```php
// ❌ Mauvais
$router->get('/admin/users', [UserController::class, 'index']);

// ✅ Bon
$router->get('/admin/users', [UserController::class, 'index'])
    ->name('admin.users.index');
```

### 2. **Grouper les routes par préfixe et middleware**

```php
// ✅ Facile à maintenir
$router->group(['prefix' => '/admin', 'middleware' => ['auth']], function ($router) {
    // Toutes les routes admin ici
});
```

### 3. **Utiliser des middlewares spécifiques**

```php
// ✅ Sécurité granulaire
$router->post('/admin/users/store', [UserController::class, 'store'])
    ->middleware('can:admin.users.create');
```

### 4. **Convention de nommage cohérente**

```php
// Format: {module}.{resource}.{action}
->name('admin.users.index')
->name('admin.users.create')
->name('admin.users.edit')
```

### 5. **POST pour les actions destructives**

```php
// ✅ Sécurisé (CSRF protection)
$router->post('/admin/users/{id}/delete', [UserController::class, 'delete']);

// ❌ Éviter GET pour les suppressions
$router->get('/admin/users/{id}/delete', [UserController::class, 'delete']);
```

---

## Référence Rapide

### Méthodes HTTP

- `$router->get()` - Lecture
- `$router->post()` - Création/Modification
- `$router->put()` - Mise à jour complète
- `$router->delete()` - Suppression

### Méthodes Chaînables

- `->middleware('alias')` - Ajouter un middleware
- `->name('route.name')` - Nommer la route

### Helpers Disponibles

- `url('/path')` - Générer une URL
- `redirect('/path')` - Rediriger
- `csrf_field()` - Token CSRF pour formulaires
- `csrf_token()` - Obtenir le token CSRF

---

## Dépannage

### Route 404

```php
// Vérifier que la route est bien enregistrée
$app = Application::getInstance();
$routes = $app->router->getRoutes();
var_dump($routes);
```

### Middleware qui bloque

```php
// Vérifier les permissions de l'utilisateur
$user = User::find($_SESSION['user_id']);
$hasPermission = $user->can('admin.users.view');
var_dump($hasPermission);
```

### Paramètres non capturés

```php
// S'assurer que le pattern correspond
// Pattern: /users/{id}
// URL: /users/5
// ✅ Match

// Pattern: /users/{userId}
// Paramètre: $params['userId'] (pas 'id')
```

---

**Dernière mise à jour**: 2025-12-02
