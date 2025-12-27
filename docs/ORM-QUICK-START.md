# ORM Quick Start - SunuFramework

Guide de démarrage rapide pour l'ORM de SunuFramework.

## Installation

L'ORM est inclus par défaut dans SunuFramework. Aucune installation supplémentaire n'est nécessaire.

## Créer un modèle

```php
<?php

namespace App\Models;

use App\Core\Database\Model;

class User extends Model
{
    protected static string $table = 'users';
}
```

## Opérations CRUD de base

### Create (Créer)
```php
$user = new User();
$user->name = 'John Doe';
$user->email = 'john@example.com';
$user->save();
```

### Read (Lire)
```php
// Tous les enregistrements
$users = User::all();

// Un seul enregistrement
$user = User::find(1);

// Avec conditions
$users = User::where('status', 'active')->get();
```

### Update (Mettre à jour)
```php
$user = User::find(1);
$user->name = 'Jane Doe';
$user->save();
```

### Delete (Supprimer)
```php
$user = User::find(1);
$user->delete();
```

## Relations

### One-to-Many (Un à plusieurs)

```php
// User.php
public function posts()
{
    return $this->hasMany(Post::class);
}

// Utilisation
$user = User::find(1);
$posts = $user->posts()->getResults();
```

### BelongsTo (Appartient à)

```php
// Post.php
public function user()
{
    return $this->belongsTo(User::class);
}

// Utilisation
$post = Post::find(1);
$author = $post->user()->getResults();
```

### Many-to-Many (Plusieurs à plusieurs)

```php
// User.php
public function roles()
{
    return $this->belongsToMany(Role::class, 'role_user');
}

// Utilisation
$user = User::find(1);
$roles = $user->roles()->getResults();
```

## QueryBuilder

### WHERE clauses
```php
User::where('status', 'active')->get();
User::where('age', '>', 18)->get();
User::whereIn('id', [1, 2, 3])->get();
User::whereNotIn('status', ['banned'])->get();
```

### JOINs
```php
Permission::query()
    ->leftJoin('modules', 'permissions.module_id', '=', 'modules.id')
    ->select(['permissions.*', 'modules.name as module_name'])
    ->get();
```

### ORDER BY
```php
User::orderBy('name', 'ASC')->get();
User::orderBy('created_at', 'DESC')->get();
```

### LIMIT & OFFSET
```php
User::limit(10)->get();
User::limit(10)->offset(20)->get();
```

### Pagination
```php
$users = User::paginate(15);

foreach ($users->items() as $user) {
    echo $user->name;
}

echo $users->links();
```

## Exemples courants

### Récupérer avec jointure
```php
$permissions = Permission::query()
    ->leftJoin('modules', 'permissions.module_id', '=', 'modules.id')
    ->select(['permissions.*', 'modules.name as module_name'])
    ->where('permissions.status', 'active')
    ->orderBy('modules.name')
    ->get();
```

### Recherche
```php
$searchTerm = 'Laravel';
$posts = Post::query()
    ->whereRaw("(title LIKE ? OR content LIKE ?)", [
        "%{$searchTerm}%",
        "%{$searchTerm}%"
    ])
    ->get();
```

### Statistiques
```php
$activeUsers = User::where('status', 'active')->count();
$exists = User::where('email', 'john@example.com')->exists();
```

## Documentation complète

Pour plus de détails, consultez la [documentation complète de l'ORM](ORM.md).

## Support des relations

| Type | Méthode | Exemple |
|------|---------|---------|
| One-to-One | `hasOne()` | User → Profile |
| One-to-Many | `hasMany()` | User → Posts |
| Inverse | `belongsTo()` | Post → User |
| Many-to-Many | `belongsToMany()` | User ↔ Roles |

## Méthodes principales

| Catégorie | Méthodes |
|-----------|----------|
| **Récupération** | `all()`, `find()`, `get()`, `first()` |
| **WHERE** | `where()`, `orWhere()`, `whereIn()`, `whereNotIn()`, `whereRaw()` |
| **JOIN** | `join()`, `leftJoin()`, `rightJoin()` |
| **Tri** | `orderBy()` |
| **Limitation** | `limit()`, `offset()`, `paginate()` |
| **Agrégation** | `count()`, `exists()` |
| **Modification** | `create()`, `update()`, `delete()` |
| **Relations** | `hasOne()`, `hasMany()`, `belongsTo()`, `belongsToMany()` |

---

© 2024 SunuFramework
