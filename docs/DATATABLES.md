# DataTables - Documentation

## Vue d'ensemble

Le système DataTables de SunuFramework permet de gérer efficacement de grandes quantités de données avec pagination, tri et recherche côté serveur. Il est basé sur la bibliothèque [DataTables](https://datatables.net/) et s'intègre parfaitement avec le système ORM.

## Mise en place

### 1. Créer un endpoint API

Dans votre contrôleur, créez une méthode pour gérer les requêtes DataTables :

```php
namespace Modules\Admin\Controllers;

use Modules\Auth\Models\User;

class UserApiController
{
    public function datatable()
    {
        // Définir les colonnes recherchables et triables
        $columns = ['id', 'username', 'email', 'created_at'];

        // Construire la réponse DataTables
        $data = User::query()
            ->datatables($columns)
            ->make();

        // Retourner le JSON
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }
}
```

### 2. Enregistrer la route API

Dans votre module, ajoutez la route dans `getApiRoutes()` :

```php
public function getApiRoutes(): array
{
    return [
        ['GET', '/users/datatable', [UserApiController::class, 'datatable']],
    ];
}
```

L'URL sera automatiquement : `/api/users/datatable`

### 3. Créer la vue

```php
<table id="usersTable" class="table table-striped">
    <thead>
        <tr>
            <th>ID</th>
            <th>Username</th>
            <th>Email</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody></tbody>
</table>

@section('scripts')
<!-- DataTables CSS & JS -->
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>

<script>
$(document).ready(function() {
    $('#usersTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: '<?= url('/api/users/datatable') ?>',
        columns: [
            { data: 'id' },
            { data: 'username' },
            { data: 'email' },
            {
                data: 'id',
                orderable: false,
                searchable: false,
                render: function(data) {
                    return `<a href="/admin/users/${data}/edit">Edit</a>`;
                }
            }
        ],
        language: {
            url: '//cdn.datatables.net/plug-ins/1.13.6/i18n/fr-FR.json'
        }
    });
});
</script>
@endsection
```

## Utilisation avancée

### Avec des filtres personnalisés

```php
public function datatable()
{
    $columns = ['id', 'username', 'email'];

    $data = User::query()
        ->where('active', 1)           // Filtre personnalisé
        ->where('role', 'admin')       // Autre filtre
        ->datatables($columns)
        ->make();

    header('Content-Type: application/json');
    echo json_encode($data);
    exit;
}
```

### Avec relations

```php
// À implémenter : support des relations dans DatatablesBuilder
// Pour l'instant, utilisez les données brutes du model
```

### Colonnes personnalisées

```php
$columns = [
    'id',
    'username',
    'email',
    'created_at'
];

// Dans le DataTable JS
columns: [
    { data: 'id', name: 'id' },
    {
        data: 'username',
        name: 'username',
        render: function(data, type, row) {
            return `<strong>${data}</strong>`;
        }
    },
    {
        data: 'email',
        defaultContent: '<em>Pas d\'email</em>'
    },
    {
        data: 'created_at',
        render: function(data) {
            return new Date(data).toLocaleDateString('fr-FR');
        }
    }
]
```

## Options DataTables courantes

### Configuration de base

```javascript
$("#table").DataTable({
  processing: true, // Affiche "Processing..." pendant le chargement
  serverSide: true, // Active le traitement côté serveur
  ajax: "/api/endpoint", // URL de l'endpoint
  pageLength: 25, // Nombre d'items par page
  order: [[0, "desc"]], // Tri par défaut (colonne 0, descendant)
  searching: true, // Active la recherche
  ordering: true, // Active le tri
  lengthChange: true, // Permet de changer le nombre d'items
  language: {
    url: "//cdn.datatables.net/plug-ins/1.13.6/i18n/fr-FR.json",
  },
});
```

### Callbacks utiles

```javascript
$("#table").DataTable({
  // ... autres options

  drawCallback: function (settings) {
    // Appelé après chaque dessin du tableau
    feather.replace(); // Réinitialiser les icônes
  },

  initComplete: function (settings, json) {
    // Appelé une fois l'initialisation terminée
    console.log("DataTable initialisé");
  },

  preDrawCallback: function (settings) {
    // Appelé avant le dessin
    return true; // Return false pour annuler
  },
});
```

### Boutons d'export

```javascript
// Inclure les bibliothèques DataTables Buttons
$("#table").DataTable({
  dom: "Bfrtip",
  buttons: ["copy", "csv", "excel", "pdf", "print"],
});
```

## Format de requête/réponse

### Requête envoyée par DataTables

```
GET /api/users/datatable?draw=1&start=0&length=10&search[value]=john&order[0][column]=0&order[0][dir]=asc
```

Paramètres :

- `draw` : Compteur de requête (pour éviter les problèmes d'async)
- `start` : Position de départ (offset)
- `length` : Nombre d'items à retourner
- `search[value]` : Terme de recherche
- `order[0][column]` : Index de la colonne à trier
- `order[0][dir]` : Direction du tri (asc/desc)

### Réponse attendue

```json
{
  "draw": 1,
  "recordsTotal": 100,
  "recordsFiltered": 50,
  "data": [
    {
      "id": 1,
      "username": "john",
      "email": "john@example.com",
      "created_at": "2024-01-01 10:00:00"
    }
  ]
}
```

- `draw` : Même valeur que dans la requête
- `recordsTotal` : Nombre total d'enregistrements (sans filtre)
- `recordsFiltered` : Nombre d'enregistrements après filtrage
- `data` : Tableau des données

## Performances

### Optimisation de la requête

1. **Indexez les colonnes** recherchables et triables
2. **Limitez les colonnes** retournées avec `select()`
3. **Utilisez le cache** pour les données statiques

```php
public function datatable()
{
    $columns = ['id', 'username', 'email'];

    $data = User::query()
        ->select(['id', 'username', 'email', 'created_at']) // Seulement les colonnes nécessaires
        ->datatables($columns)
        ->make();

    header('Content-Type: application/json');
    echo json_encode($data);
    exit;
}
```

### Cache des résultats

Pour des données qui changent peu :

```php
public function datatable()
{
    $cacheKey = 'users_datatable_' . md5(json_encode($_GET));

    $data = cache_remember($cacheKey, function() {
        return User::query()
            ->datatables(['id', 'username', 'email'])
            ->make();
    }, 300); // 5 minutes

    header('Content-Type: application/json');
    echo json_encode($data);
    exit;
}
```

## Troubleshooting

### Les données ne s'affichent pas

1. Vérifiez la console du navigateur pour les erreurs JavaScript
2. Vérifiez que l'endpoint retourne bien du JSON valide
3. Vérifiez que les noms de colonnes correspondent entre la vue et l'API

### La recherche ne fonctionne pas

1. Vérifiez que les colonnes sont bien passées à `datatables()`
2. Vérifiez que `searchable: true` est défini dans les colonnes JS
3. Note : La recherche actuelle ne fonctionne que sur la première colonne (limitation à améliorer)

### Les icônes Feather ne s'affichent pas

Ajoutez le callback `drawCallback` :

```javascript
drawCallback: function() {
    feather.replace();
}
```

## Exemple complet

Voir le fichier `templates/admin/users/index-datatable.php` pour un exemple complet et fonctionnel.

## Prochaines améliorations

- [ ] Support des clauses OR WHERE pour la recherche multi-colonnes
- [ ] Support des relations (eager loading)
- [ ] Export Excel/PDF côté serveur
- [ ] Filtres avancés par colonne
- [ ] Agrégations et totaux
