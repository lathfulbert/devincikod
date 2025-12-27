# QueryBuilder - Documentation des Clauses OR WHERE

## Vue d'ensemble

Le `QueryBuilder` supporte maintenant les clauses OR WHERE pour des requêtes plus flexibles. Cette fonctionnalité est particulièrement utile pour DataTables et les recherches multi-colonnes.

## Méthodes disponibles

### `orWhere()`

Ajoute une condition OR à la requête.

```php
// WHERE id = 1 OR id = 2
$users = User::query()
    ->where('id', 1)
    ->orWhere('id', 2)
    ->get();
```

### `whereGroup()`

Groupe plusieurs conditions dans des parenthèses. Très utile pour combiner AND et OR correctement.

```php
// WHERE active = 1 AND (username LIKE '%admin%' OR email LIKE '%admin%')
$users = User::query()
    ->where('active', 1)
    ->whereGroup(function($q) {
        $q->where('username', 'LIKE', '%admin%')
          ->orWhere('email', 'LIKE', '%admin%');
    })
    ->get();
```

## Exemples

### Recherche simple avec OR

```php
// Chercher par nom OU email
$users = User::query()
    ->where('username', 'LIKE', '%john%')
    ->orWhere('email', 'LIKE', '%john%')
    ->get();

// SQL généré: WHERE username LIKE ? OR email LIKE ?
```

### Recherche avec groupes

```php
// (active = 1 OR verified = 1) AND (role = 'admin' OR role = 'editor')
$users = User::query()
    ->whereGroup(function($q) {
        $q->where('active', 1)->orWhere('verified', 1);
    })
    ->whereGroup(function($q) {
        $q->where('role', 'admin')->orWhere('role', 'editor');
    })
    ->get();
```

### Recherche complexe

```php
// WHERE (username LIKE '%admin%' OR email LIKE '%admin%') AND status = 'active'
$users = User::query()
    ->whereGroup(function($q) {
        $q->where('username', 'LIKE', '%admin%')
          ->orWhere('email', 'LIKE', '%admin%');
    })
    ->where('status', 'active')
    ->get();
```

## Utilisation avec DataTables

La recherche DataTables utilise maintenant automatiquement `orWhere` pour chercher sur toutes les colonnes spécifiées :

```php
// Dans le contrôleur API
public function datatable()
{
    $columns = ['id', 'username', 'email', 'created_at'];

    $data = User::query()
        ->datatables($columns)
        ->make();

    // Si l'utilisateur recherche "john", la requête sera:
    // WHERE (id LIKE '%john%' OR username LIKE '%john%' OR email LIKE '%john%' OR created_at LIKE '%john%')

    header('Content-Type: application/json');
    echo json_encode($data);
    exit;
}
```

## Limitation de la première version

La recherche avec `orWhere()` fonctionne désormais sur TOUTES les colonnes spécifiées dans `datatables($columns)`.

## Fonctionnement interne

### Structure des clauses WHERE

Chaque clause WHERE est maintenant stockée avec son type (AND ou OR) :

```php
[
    'type' => 'and',  // ou 'or'
    'column' => 'username',
    'operator' => 'LIKE',
    'value' => '%admin%'
]
```

### Génération SQL

La méthode `buildWhereClause()` construit le SQL en respectant les types :

```php
// Input: [
//     ['type' => 'and', 'column' => 'a', 'operator' => '=', 'value' => 1],
//     ['type' => 'or', 'column' => 'b', 'operator' => '=', 'value' => 2]
// ]
// Output: "a = ? OR b = ?"
```

### Groupes WHERE

Les groupes sont entourés de parenthèses automatiquement :

```php
->whereGroup(function($q) { ... })
// Génère: (conditions...)
```

## Tests

Un script de test est disponible : `test_orwhere.php`

```bash
php test_orwhere.php
```

## Exemples complets

### Filtre de produits e-commerce

```php
$products = Product::query()
    ->whereGroup(function($q) {
        // Prix entre 10 et 50 OU en promotion
        $q->where('price', '>=', 10)
          ->where('price', '<=', 50)
          ->orWhere('on_sale', 1);
    })
    ->where('in_stock', 1)
    ->orderBy('price', 'ASC')
    ->get();
```

### Recherche d'utilisateurs avancée

```php
$users = User::query()
    ->whereGroup(function($q) use ($searchTerm) {
        // Chercher dans plusieurs champs
        $q->where('username', 'LIKE', "%{$searchTerm}%")
          ->orWhere('email', 'LIKE', "%{$searchTerm}%")
          ->orWhere('first_name', 'LIKE', "%{$searchTerm}%")
          ->orWhere('last_name', 'LIKE', "%{$searchTerm}%");
    })
    ->where('active', 1)
    ->orderBy('created_at', 'DESC')
    ->paginate(20);
```

## Migration du code existant

Si vous aviez des requêtes qui ne cherchaient que sur une colonne, elles ne changeront pas :

```php
// Avant (ne cherchait que sur username)
$users = User::query()->datatables(['username', 'email'])->make();

// Maintenant (cherche sur username ET email)
// Aucun changement de code nécessaire !
```

## Bonnes pratiques

1. **Toujours grouper les OR** quand vous combinez avec des AND :

   ```php
   // ✅ CORRECT
   ->where('status', 'active')
   ->whereGroup(function($q) {
       $q->where('role', 'admin')->orWhere('role', 'editor');
   })

   // ❌ PEUTproduire des résultats inattendus
   ->where('status', 'active')
   ->where('role', 'admin')
   ->orWhere('role', 'editor')
   ```

2. **Indexer les colonnes** utilisées dans les OR WHERE pour de meilleures performances

3. **Limiter le nombre de colonnes** dans les recherches DataTables aux colonnes réellement recherchables

## Prochaines améliorations possibles

- Support de `whereIn()` et `whereNotIn()`
- Support de `whereBetween()`
- Support de `whereNull()` et `whereNotNull()`
- Support des sous-requêtes
- Support de `having()` pour les agrégations
