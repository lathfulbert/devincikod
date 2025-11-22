# Eager Loading - Documentation

## Vue d'ensemble

L'eager loading (chargement anticipé) permet de charger les relations d'un modèle en une seule requête SQL optimisée, évitant ainsi le problème N+1 qui peut sérieusement dégrader les performances.

## Problème N+1

### Sans Eager Loading (❌ Lent)

```php
$users = User::query()->get(); // 1 requête

foreach ($users as $user) {
    $roles = $user->roles()->getResults(); // N requêtes (une par user)
    // Total: 1 + N requêtes
}
```

Si vous avez 100 utilisateurs, cela fait **101 requêtes SQL** !

### Avec Eager Loading (✅ Rapide)

```php
$users = User::query()->with('roles')->get(); // 2 requêtes seulement!

foreach ($users as $user) {
    $roles = $user->roles; // Déjà chargé, 0 requête
    // Total: 2 requêtes
}
```

Seulement **2 requêtes SQL** peu importe le nombre d'utilisateurs !

## Utilisation

### Avec `get()`

```php
// Charger une relation
$users = User::query()->with('roles')->get();

// Charger plusieurs relations
$users = User::query()
    ->with(['roles', 'permissions'])
    ->get();
```

### Avec `paginate()`

```php
// La pagination supporte automatiquement l'eager loading
$users = User::query()
    ->with('roles')
    ->orderBy('id', 'DESC')
    ->paginate(10);

// Dans la vue
foreach ($users as $user) {
    foreach ($user->roles as $role) {
        echo $role->name;
    }
}
```

### Avec `datatables()`

```php
// L'eager loading fonctionne aussi avec DataTables
public function datatable()
{
    $columns = ['id', 'username', 'email'];

    $data = User::query()
        ->with('roles')
        ->datatables($columns)
        ->make();

    // Les relations sont déjà chargées dans les données
    header('Content-Type: application/json');
    echo json_encode($data);
    exit;
}
```

## Accès aux relations chargées

### Dans une vue

```php
<?php foreach ($users as $user): ?>
    <div>
        <?php
        // Accès direct à la propriété (pas d'appel de méthode!)
        $roles = $user->roles ?? [];

        foreach ($roles as $role) {
            echo $role->name;
        }
        ?>
    </div>
<?php endforeach; ?>
```

### Différence importante

```php
// ❌ ANCIEN (N+1 queries)
$roles = $user->roles()->getResults();

// ✅ NOUVEAU (eager loaded)
$roles = $user->roles; // ou $user->roles ?? []
```

## Relations supportées

Actuellement, l'eager loading supporte :

### BelongsToMany

```php
// Dans le modèle User
public function roles()
{
    return $this->belongsToMany(Role::class);
}

// Utilisation
$users = User::query()->with('roles')->get();
foreach ($users as $user) {
    foreach ($user->roles as $role) {
        echo $role->name;
    }
}
```

## Exemples complets

### Liste d'utilisateurs avec leurs rôles

```php
// Contrôleur
public function index()
{
    $users = User::query()
        ->with('roles')
        ->orderBy('username', 'ASC')
        ->paginate(20);

    return view('users.index', ['users' => $users]);
}

// Vue
<?php foreach ($users as $user): ?>
    <tr>
        <td><?= $user->username ?></td>
        <td>
            <?php foreach ($user->roles ?? [] as $role): ?>
                <span class="badge"><?= $role->name ?></span>
            <?php endforeach; ?>
        </td>
    </tr>
<?php endforeach; ?>

<?= $users->links() ?>
```

### API avec relations

```php
public function apiIndex()
{
    $users = User::query()
        ->with('roles')
        ->limit(50)
        ->get();

    // Les relations sont incluses dans le JSON
    header('Content-Type: application/json');
    echo json_encode($users);
}
```

## Performance

### Comparaison

| Scénario   | Sans Eager Loading | Avec Eager Loading |
| ---------- | ------------------ | ------------------ |
| 10 users   | 11 requêtes        | 2 requêtes         |
| 100 users  | 101 requêtes       | 2 requêtes         |
| 1000 users | 1001 requêtes      | 2 requêtes         |

### Requêtes SQL Générées

```sql
-- Requête 1: Charger les users
SELECT * FROM users LIMIT 10

-- Requête 2: Charger TOUS les rôles liés en une fois
SELECT roles.*, role_user.user_id as pivot_parent_id
FROM roles
INNER JOIN role_user ON roles.id = role_user.role_id
WHERE role_user.user_id IN (1, 2, 3, 4, 5, 6, 7, 8, 9, 10)
```

## Bonnes pratiques

### 1. Toujours utiliser l'eager loading pour les listes

```php
// ✅ BON
$users = User::query()->with('roles')->paginate(20);

// ❌ MAUVAIS (N+1)
$users = User::query()->paginate(20);
```

### 2. Charger seulement ce dont vous avez besoin

```php
// Si vous n'affichez que les rôles
$users = User::query()->with('roles')->get();

// Ne chargez pas tout si vous n'en avez pas besoin
// ❌ Évitez si non utilisé
$users = User::query()->with(['roles', 'permissions', 'profile'])->get();
```

### 3. Combiner avec les filtres

```php
$users = User::query()
    ->where('active', 1)
    ->with('roles')
    ->orderBy('created_at', 'DESC')
    ->paginate(15);
```

## Débogage

### Vérifier si une relation est chargée

```php
if (isset($user->roles)) {
    echo "Roles chargés!";
} else {
    echo "Roles non chargés (N+1 possible)";
}
```

### Logger les requêtes SQL

Activez le débo Log des requêtes dans votre application pour voir le nombre de requêtes exécutées.

## Limitations actuelles

1. **Seul `BelongsToMany` est supporté** pour le moment

   - Les autres types de relations (HasMany, BelongsTo, etc.) seront ajoutés prochainement

2. **Pas de conditions sur les relations**
   - Vous ne pouvez pas encore filtrer les relations chargées
   - Ex: `->with('roles', fn($q) => $q->where('active', 1))` n'est pas encore supporté

## Prochaines améliorations

- [ ] Support de `HasMany`
- [ ] Support de `BelongsTo`
- [ ] Support de `HasOne`
- [ ] Conditions sur les relations eager loaded
- [ ] Eager loading imbriqué (`with('roles.permissions')`)
- [ ] Lazy eager loading (`load()` après récupération)
- [ ] Count des relations (`withCount('roles')`)

## Migration du code existant

Remplacez simplement :

```php
// Ancien code
$users = User::query()->paginate(10);

// Dans la vue:
$roles = $user->roles()->getResults();

// Nouveau code
$users = User::query()->with('roles')->paginate(10);

// Dans la vue:
$roles = $user->roles ?? [];
```

C'est tout ! Les performances seront automatiquement améliorées.
