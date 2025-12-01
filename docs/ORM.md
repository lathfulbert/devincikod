# Documentation ORM - SunuFramework

## Table des matières

1. [Introduction](#introduction)
2. [Configuration de base](#configuration-de-base)
3. [Requêtes de base](#requêtes-de-base)
4. [Relations](#relations)
5. [QueryBuilder avancé](#querybuilder-avancé)
6. [Exemples pratiques](#exemples-pratiques)

---

## Introduction

L'ORM (Object-Relational Mapping) de SunuFramework est un système léger et puissant inspiré de Laravel Eloquent, permettant d'interagir avec votre base de données de manière orientée objet.

### Caractéristiques principales

- ✅ Modèles éloquents avec héritage
- ✅ Relations (HasOne, HasMany, BelongsTo, BelongsToMany)
- ✅ QueryBuilder fluide avec méthode chaînée
- ✅ Support des JOINs (INNER, LEFT, RIGHT)
- ✅ WHERE avancés (whereRaw, whereIn, whereNotIn)
- ✅ Pagination intégrée
- ✅ Soft Deletes
- ✅ Eager Loading

---

## Configuration de base

### Créer un modèle

```php
<?php

namespace App\Models;

use App\Core\Database\Model;

class User extends Model
{
    // Nom de la table (optionnel, sera déduit automatiquement)
    protected static string $table = 'users';

    // Clé primaire (optionnel, 'id' par défaut)
    protected static string $primaryKey = 'id';
}
```

### Convention de nommage

Si vous ne spécifiez pas `$table`, l'ORM utilisera le nom de la classe au pluriel en minuscules :

```php
class User extends Model {} // Table: users
class Post extends Model {} // Table: posts
class Category extends Model {} // Table: categories
```

---

## Requêtes de base

### Récupérer tous les enregistrements

```php
$users = User::all();

foreach ($users as $user) {
    echo $user->name;
}
```

### Trouver un enregistrement par ID

```php
$user = User::find(1);

if ($user) {
    echo $user->email;
}
```

### Créer un nouvel enregistrement

```php
$user = new User();
$user->name = 'John Doe';
$user->email = 'john@example.com';
$user->password = password_hash('secret', PASSWORD_DEFAULT);
$user->save();

// Ou en une seule ligne
$user = User::create([
    'name' => 'John Doe',
    'email' => 'john@example.com',
    'password' => password_hash('secret', PASSWORD_DEFAULT)
]);
```

### Mettre à jour un enregistrement

```php
$user = User::find(1);
$user->name = 'Jane Doe';
$user->save();

// Ou via update()
$user->update([
    'name' => 'Jane Doe',
    'email' => 'jane@example.com'
]);
```

### Supprimer un enregistrement

```php
$user = User::find(1);
$user->delete();

// Ou directement
User::where('id', 1)->delete();
```

---

## Relations

### 1. HasOne (One-to-One)

Un utilisateur a un profil.

**Modèle User:**
```php
class User extends Model
{
    public function profile()
    {
        return $this->hasOne(Profile::class);
        // Équivalent à : return $this->hasOne(Profile::class, 'user_id', 'id');
    }
}
```

**Utilisation:**
```php
$user = User::find(1);
$profile = $user->profile()->getResults();

echo $profile->bio;

// Créer un profil pour l'utilisateur
$user->profile()->create([
    'bio' => 'Developer',
    'avatar' => 'avatar.jpg'
]);
```

### 2. HasMany (One-to-Many)

Un utilisateur a plusieurs articles.

**Modèle User:**
```php
class User extends Model
{
    public function posts()
    {
        return $this->hasMany(Post::class);
        // Équivalent à : return $this->hasMany(Post::class, 'user_id', 'id');
    }
}
```

**Utilisation:**
```php
$user = User::find(1);
$posts = $user->posts()->getResults();

foreach ($posts as $post) {
    echo $post->title;
}

// Créer un article pour l'utilisateur
$user->posts()->create([
    'title' => 'Mon article',
    'content' => 'Contenu de l\'article'
]);

// Créer plusieurs articles
$user->posts()->createMany([
    ['title' => 'Article 1', 'content' => 'Contenu 1'],
    ['title' => 'Article 2', 'content' => 'Contenu 2']
]);
```

### 3. BelongsTo (Inverse de One-to-Many)

Un article appartient à un utilisateur.

**Modèle Post:**
```php
class Post extends Model
{
    public function user()
    {
        return $this->belongsTo(User::class);
        // Équivalent à : return $this->belongsTo(User::class, 'user_id', 'id');
    }
}
```

**Utilisation:**
```php
$post = Post::find(1);
$user = $post->user()->getResults();

echo $user->name;

// Associer un utilisateur
$post->user()->associate($user);
$post->save();

// Dissocier
$post->user()->dissociate();
$post->save();
```

### 4. BelongsToMany (Many-to-Many)

Un utilisateur a plusieurs rôles, et un rôle a plusieurs utilisateurs.

**Modèle User:**
```php
class User extends Model
{
    public function roles()
    {
        return $this->belongsToMany(
            Role::class,           // Modèle lié
            'role_user',          // Table pivot (optionnel)
            'user_id',            // Clé étrangère (optionnel)
            'role_id'             // Clé liée (optionnel)
        );
    }
}
```

**Utilisation:**
```php
$user = User::find(1);
$roles = $user->roles()->getResults();

foreach ($roles as $role) {
    echo $role->name;
}

// Attacher un rôle
$user->roles()->attach($roleId);

// Attacher plusieurs rôles
$user->roles()->attach([1, 2, 3]);

// Détacher un rôle
$user->roles()->detach($roleId);

// Synchroniser (remplace tous les rôles)
$user->roles()->sync([1, 2, 3]);
```

### Convention de nommage des clés étrangères

| Relation | Clé étrangère par défaut | Clé locale/owner par défaut |
|----------|-------------------------|---------------------------|
| hasOne | `parent_id` (ex: `user_id`) | `id` |
| hasMany | `parent_id` (ex: `user_id`) | `id` |
| belongsTo | `related_id` (ex: `user_id`) | `id` |
| belongsToMany | `model_id` | `model_id` |

---

## QueryBuilder avancé

### WHERE clauses

```php
// WHERE simple
$users = User::where('status', 'active')->get();

// WHERE avec opérateur
$users = User::where('age', '>', 18)->get();

// WHERE multiples (AND)
$users = User::where('status', 'active')
             ->where('age', '>', 18)
             ->get();

// OR WHERE
$users = User::where('status', 'active')
             ->orWhere('role', 'admin')
             ->get();
```

### WHERE avancés

```php
// WHERE RAW (SQL brut)
$permissions = Permission::whereRaw(
    "id IN (SELECT permission_id FROM role_permissions WHERE role_id = ?)",
    [1]
)->get();

// WHERE IN
$users = User::whereIn('id', [1, 2, 3, 4, 5])->get();

// WHERE NOT IN
$users = User::whereNotIn('status', ['banned', 'deleted'])->get();

// WHERE Group (parenthèses)
$users = User::where('status', 'active')
             ->whereGroup(function($q) {
                 $q->where('role', 'admin')
                   ->orWhere('role', 'moderator');
             })
             ->get();
// SQL: WHERE status = 'active' AND (role = 'admin' OR role = 'moderator')
```

### JOINs

```php
// INNER JOIN
$permissions = Permission::query()
    ->join('modules', 'permissions.module_id', '=', 'modules.id')
    ->select(['permissions.*', 'modules.name as module_name'])
    ->get();

// LEFT JOIN
$permissions = Permission::query()
    ->leftJoin('modules', 'permissions.module_id', '=', 'modules.id')
    ->get();

// RIGHT JOIN
$permissions = Permission::query()
    ->rightJoin('modules', 'permissions.module_id', '=', 'modules.id')
    ->get();

// Plusieurs JOINs
$posts = Post::query()
    ->leftJoin('users', 'posts.user_id', '=', 'users.id')
    ->leftJoin('categories', 'posts.category_id', '=', 'categories.id')
    ->select([
        'posts.*',
        'users.name as author_name',
        'categories.name as category_name'
    ])
    ->get();
```

### SELECT personnalisé

```php
// Sélectionner des colonnes spécifiques
$users = User::query()
    ->select(['id', 'name', 'email'])
    ->get();

// Avec alias
$users = User::query()
    ->select(['id', 'name', 'email as user_email'])
    ->get();
```

### ORDER BY

```php
// Ordre croissant
$users = User::orderBy('name', 'ASC')->get();

// Ordre décroissant
$users = User::orderBy('created_at', 'DESC')->get();

// Plusieurs ORDER BY
$users = User::query()
    ->orderBy('status', 'ASC')
    ->orderBy('name', 'ASC')
    ->get();
```

### LIMIT et OFFSET

```php
// Les 10 premiers
$users = User::limit(10)->get();

// 10 utilisateurs après les 20 premiers
$users = User::limit(10)->offset(20)->get();
```

### Agrégation

```php
// Compter
$count = User::where('status', 'active')->count();

// Premier résultat
$user = User::where('email', 'john@example.com')->first();

// Vérifier l'existence
$exists = User::where('email', 'john@example.com')->exists();
```

### Pagination

```php
// Paginer avec 15 éléments par page
$users = User::query()
    ->where('status', 'active')
    ->orderBy('name')
    ->paginate(15);

// Dans la vue
foreach ($users->items() as $user) {
    echo $user->name;
}

// Liens de pagination
echo $users->links();

// Informations de pagination
echo "Page {$users->currentPage()} sur {$users->lastPage()}";
echo "Total: {$users->total()} utilisateurs";
```

---

## Exemples pratiques

### Exemple 1: Système de permissions avec modules

**Modèles:**

```php
// Permission.php
class Permission extends Model
{
    protected static string $table = 'permissions';

    public function module()
    {
        return $this->belongsTo(Module::class, 'module_id');
    }

    public function roles()
    {
        return $this->belongsToMany(
            Role::class,
            'role_permissions',
            'permission_id',
            'role_id'
        );
    }
}

// Module.php
class Module extends Model
{
    protected static string $table = 'modules';

    public function permissions()
    {
        return $this->hasMany(Permission::class, 'module_id');
    }
}

// Role.php
class Role extends Model
{
    protected static string $table = 'roles';

    public function permissions()
    {
        return $this->belongsToMany(
            Permission::class,
            'role_permissions',
            'role_id',
            'permission_id'
        );
    }
}
```

**Utilisation:**

```php
// Récupérer toutes les permissions avec leur module
$permissions = Permission::query()
    ->leftJoin('modules', 'permissions.module_id', '=', 'modules.id')
    ->select(['permissions.*', 'modules.name as module_name'])
    ->orderBy('modules.name')
    ->get();

// Filtrer par module
$moduleId = 1;
$permissions = Permission::query()
    ->where('module_id', $moduleId)
    ->get();

// Filtrer par rôle
$roleId = 2;
$permissions = Permission::query()
    ->whereRaw(
        "id IN (SELECT permission_id FROM role_permissions WHERE role_id = ?)",
        [$roleId]
    )
    ->get();

// Créer une permission pour un module
$module = Module::find(1);
$permission = $module->permissions()->create([
    'name' => 'Créer des utilisateurs',
    'slug' => 'users.create',
    'description' => 'Permet de créer de nouveaux utilisateurs'
]);

// Attacher des permissions à un rôle
$role = Role::find(1);
$role->permissions()->attach([1, 2, 3, 4]);

// Récupérer toutes les permissions d'un rôle
$rolePermissions = $role->permissions()->getResults();
```

### Exemple 2: Blog avec utilisateurs et catégories

**Modèles:**

```php
// User.php
class User extends Model
{
    public function posts()
    {
        return $this->hasMany(Post::class, 'user_id');
    }
}

// Post.php
class Post extends Model
{
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function category()
    {
        return $this->belongsTo(Category::class, 'category_id');
    }

    public function comments()
    {
        return $this->hasMany(Comment::class, 'post_id');
    }
}

// Category.php
class Category extends Model
{
    public function posts()
    {
        return $this->hasMany(Post::class, 'category_id');
    }
}

// Comment.php
class Comment extends Model
{
    public function post()
    {
        return $this->belongsTo(Post::class, 'post_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
```

**Utilisation:**

```php
// Récupérer tous les posts avec auteur et catégorie
$posts = Post::query()
    ->leftJoin('users', 'posts.user_id', '=', 'users.id')
    ->leftJoin('categories', 'posts.category_id', '=', 'categories.id')
    ->select([
        'posts.*',
        'users.name as author_name',
        'categories.name as category_name'
    ])
    ->where('posts.status', 'published')
    ->orderBy('posts.created_at', 'DESC')
    ->paginate(10);

// Créer un post pour un utilisateur
$user = User::find(1);
$post = $user->posts()->create([
    'title' => 'Mon premier article',
    'content' => 'Contenu de l\'article...',
    'category_id' => 2,
    'status' => 'draft'
]);

// Récupérer tous les commentaires d'un post
$post = Post::find(1);
$comments = $post->comments()->getResults();

// Recherche avancée
$searchTerm = 'Laravel';
$posts = Post::query()
    ->whereRaw("(title LIKE ? OR content LIKE ?)", [
        "%{$searchTerm}%",
        "%{$searchTerm}%"
    ])
    ->where('status', 'published')
    ->orderBy('created_at', 'DESC')
    ->get();
```

### Exemple 3: E-commerce avec produits et commandes

```php
// Product.php
class Product extends Model
{
    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function orders()
    {
        return $this->belongsToMany(
            Order::class,
            'order_items',
            'product_id',
            'order_id'
        );
    }
}

// Order.php
class Order extends Model
{
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function products()
    {
        return $this->belongsToMany(
            Product::class,
            'order_items',
            'order_id',
            'product_id'
        );
    }
}

// Utilisation
// Récupérer les commandes d'un utilisateur
$user = User::find(1);
$orders = $user->orders()->getResults();

// Récupérer les produits d'une commande
$order = Order::find(1);
$products = $order->products()->getResults();

// Statistiques de ventes
$topProducts = Product::query()
    ->leftJoin('order_items', 'products.id', '=', 'order_items.product_id')
    ->select([
        'products.*',
        'COUNT(order_items.id) as total_sales',
        'SUM(order_items.quantity) as total_quantity'
    ])
    ->orderBy('total_sales', 'DESC')
    ->limit(10)
    ->get();
```

---

## Méthodes de requête disponibles

### Méthodes statiques (sur le modèle)

| Méthode | Description |
|---------|-------------|
| `Model::all()` | Récupère tous les enregistrements |
| `Model::find($id)` | Trouve un enregistrement par ID |
| `Model::where(...)` | Début d'une requête avec WHERE |
| `Model::create($data)` | Crée un nouvel enregistrement |
| `Model::query()` | Début d'une nouvelle requête |

### Méthodes de QueryBuilder

| Méthode | Description |
|---------|-------------|
| `where($column, $operator, $value)` | Ajoute une clause WHERE |
| `orWhere($column, $operator, $value)` | Ajoute une clause OR WHERE |
| `whereRaw($sql, $bindings)` | WHERE avec SQL brut |
| `whereIn($column, $array)` | WHERE IN |
| `whereNotIn($column, $array)` | WHERE NOT IN |
| `whereGroup($callback)` | WHERE groupé avec parenthèses |
| `join($table, $first, $operator, $second)` | INNER JOIN |
| `leftJoin($table, ...)` | LEFT JOIN |
| `rightJoin($table, ...)` | RIGHT JOIN |
| `select($columns)` | Sélectionne des colonnes |
| `orderBy($column, $direction)` | Trie les résultats |
| `limit($number)` | Limite le nombre de résultats |
| `offset($number)` | Décale les résultats |
| `get()` | Exécute et récupère les résultats |
| `first()` | Récupère le premier résultat |
| `count()` | Compte les résultats |
| `exists()` | Vérifie si des résultats existent |
| `paginate($perPage)` | Pagine les résultats |
| `update($data)` | Met à jour les enregistrements |
| `delete()` | Supprime les enregistrements |

### Méthodes de modèle (sur une instance)

| Méthode | Description |
|---------|-------------|
| `$model->save()` | Sauvegarde le modèle |
| `$model->update($data)` | Met à jour et sauvegarde |
| `$model->delete()` | Supprime le modèle |
| `$model->toArray()` | Convertit en tableau |
| `$model->hasOne(...)` | Définit une relation HasOne |
| `$model->hasMany(...)` | Définit une relation HasMany |
| `$model->belongsTo(...)` | Définit une relation BelongsTo |
| `$model->belongsToMany(...)` | Définit une relation BelongsToMany |

---

## Bonnes pratiques

### 1. Toujours utiliser les paramètres bindés

❌ **Mauvais:**
```php
$email = $_POST['email'];
$user = User::whereRaw("email = '$email'")->first(); // SQL Injection!
```

✅ **Bon:**
```php
$email = $_POST['email'];
$user = User::whereRaw("email = ?", [$email])->first();
```

### 2. Utiliser les relations plutôt que les JOINs manuels

❌ **Mauvais:**
```php
$posts = Post::query()
    ->leftJoin('users', 'posts.user_id', '=', 'users.id')
    ->select(['posts.*', 'users.name'])
    ->get();

// Puis dans la vue: $post['name'] ??
```

✅ **Bon:**
```php
$posts = Post::all();

foreach ($posts as $post) {
    $author = $post->user()->getResults();
    echo $author->name;
}
```

### 3. Utiliser whereIn pour les listes

❌ **Mauvais:**
```php
$users = [];
foreach ([1, 2, 3] as $id) {
    $users[] = User::find($id);
}
```

✅ **Bon:**
```php
$users = User::whereIn('id', [1, 2, 3])->get();
```

### 4. Pagination pour les grandes listes

❌ **Mauvais:**
```php
$users = User::all(); // Peut charger des milliers d'enregistrements
```

✅ **Bon:**
```php
$users = User::paginate(25);
```

---

## Support et contribution

Pour toute question ou problème concernant l'ORM, veuillez consulter le code source dans :
- `Core/Database/Model.php`
- `Core/Database/QueryBuilder.php`
- `Core/Database/ORM/Relations/`

© 2024 SunuFramework - ORM Documentation
