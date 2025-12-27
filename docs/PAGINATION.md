# Système de Pagination - Documentation

## Vue d'ensemble

Le système de pagination de SunuFramework est inspiré de Laravel et offre une pagination performante et facile à utiliser pour vos modèles.

## Utilisation de base

### Dans un contrôleur

```php
use Modules\Auth\Models\User;

class UserController
{
    public function index()
    {
        // Paginer avec 15 items par page (défaut)
        $users = User::query()->paginate();

        // Paginer avec un nombre personnalisé d'items par page
        $users = User::query()->paginate(10);

        // Avec un tri et des filtres
        $users = User::query()
            ->where('active', 1)
            ->orderBy('created_at', 'DESC')
            ->paginate(20);

        return view('users.index', ['users' => $users]);
    }
}
```

### Dans une vue

```php
<!-- Afficher les items -->
<?php foreach ($users as $user): ?>
    <div><?= $user->username ?></div>
<?php endforeach; ?>

<!-- Afficher les liens de pagination -->
<?= $users->links() ?>

<!-- Ou utiliser une vue de pagination simple -->
<?= $users->links('pagination.simple') ?>
```

## Méthodes disponibles

### Sur l'objet Paginator

```php
// Obtenir les items
$users->items()              // array

// Informations de pagination
$users->total()              // Nombre total d'items
$users->perPage()            // Items par page
$users->currentPage()        // Page actuelle
$users->lastPage()           // Dernière page

// Positions
$users->firstItem()          // Position du premier item (ex: 1)
$users->lastItem()           // Position du dernier item (ex: 10)

// Navigation
$users->hasMorePages()       // bool
$users->hasPages()           // bool
$users->onFirstPage()        // bool

// URLs
$users->url(2)               // URL de la page 2
$users->previousPageUrl()    // URL de la page précédente
$users->nextPageUrl()        // URL de la page suivante

// Convertir en array/JSON
$users->toArray()
$users->toJson()
```

## Vues de pagination

### Vue par défaut (pagination.default)

Affiche une pagination complète avec les numéros de pages :

```php
<?= $users->links() ?>
```

Structure : `« 1 ... 5 6 7 ... 20 »`

### Vue simple (pagination.simple)

Affiche seulement Précédent et Suivant :

```php
<?= $users->links('pagination.simple') ?>
```

Structure : `« Précédent | Suivant »`

## Personnalisation

### Créer une vue de pagination personnalisée

Créez un fichier dans `templates/components/pagination/custom.php` :

```php
<?php
/** @var \App\Core\Pagination\LengthAwarePaginator $paginator */

if (!$paginator->hasPages()) {
    return;
}
?>

<nav>
    <ul class="custom-pagination">
        <?php if (!$paginator->onFirstPage()): ?>
            <li><a href="<?= $paginator->previousPageUrl() ?>">Précédent</a></li>
        <?php endif; ?>

        <!-- Vos éléments personnalisés ici -->

        <?php if ($paginator->hasMorePages()): ?>
            <li><a href="<?= $paginator->nextPageUrl() ?>">Suivant</a></li>
        <?php endif; ?>
    </ul>
</nav>
```

Utilisez-la avec :

```php
<?= $users->links('pagination.custom') ?>
```

### Paramètres de requête personnalisés

Le système conserve automatiquement tous les paramètres GET existants dans les liens de pagination.

Exemple : Si l'URL est `/users?search=john&page=2`, les liens de pagination conserveront `search=john`.

## API JSON

Le paginator peut être converti en JSON pour les APIs :

```php
public function apiIndex()
{
    $users = User::query()->paginate(10);

    header('Content-Type: application/json');
    echo $users->toJson();
}
```

Format de réponse :

```json
{
    "current_page": 1,
    "data": [...],
    "first_page_url": "http://localhost/users?page=1",
    "from": 1,
    "last_page": 5,
    "last_page_url": "http://localhost/users?page=5",
    "next_page_url": "http://localhost/users?page=2",
    "path": "/users",
    "per_page": 10,
    "prev_page_url": null,
    "to": 10,
    "total": 50
}
```

## Performance

Le système de pagination est optimisé :

1. **Une seule requête COUNT** : Le nombre total est calculé une seule fois
2. **LIMIT et OFFSET** : Utilise les clauses SQL natives pour la performance
3. **Mise en cache statique** : L'URL de base est mise en cache pour éviter les calculs répétés

## Exemples avancés

### Avec des relations

```php
$users = User::query()
    ->where('active', 1)
    ->orderBy('created_at', 'DESC')
    ->paginate(15);

// Dans la vue
foreach ($users as $user) {
    $roles = $user->roles()->getResults();
    // ...
}
```

### Pagination dans les modules

Chaque module peut utiliser la pagination sur ses propres modèles :

```php
// Dans BlogModule
$posts = Post::query()
    ->where('published', 1)
    ->orderBy('published_at', 'DESC')
    ->paginate(20);
```

## Notes importantes

- La pagination utilise le paramètre GET `page` par défaut
- Les pages commencent à 1, pas à 0
- Les requêtes invalides (page négative, non numérique) redirigent vers la page 1
- Le paginator implémente `Iterator`, `Countable` et `ArrayAccess` pour une utilisation flexible
