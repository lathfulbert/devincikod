# Templates CRUD Génériques

Ce répertoire contient des templates réutilisables pour créer rapidement des interfaces CRUD.

## Templates Disponibles

### 1. `index.php` - Page de Liste

Template pour afficher une liste d'éléments avec DataTable.

**Variables requises**:

```php
$items = [...]; // Liste des éléments
$module_name = 'users'; // Nom du module
$module_title = 'Utilisateurs'; // Titre du module
$columns = [
    ['field' => 'id', 'label' => 'ID'],
    ['field' => 'name', 'label' => 'Nom'],
    ['field' => 'active', 'label' => 'Actif', 'type' => 'boolean'],
    ['field' => 'role', 'label' => 'Rôle', 'type' => 'badge', 'badge_class' => 'bg-primary']
];
```

**Types de colonnes supportés**:

- `text` (par défaut) - Texte simple
- `badge` - Badge coloré
- `boolean` - Oui/Non avec badge
- `date` - Format dd/mm/yyyy hh:ii

### 2. `form.php` - Formulaire Create/Edit

Template unifié pour création et édition.

**Variables requises**:

```php
$item = null; // null pour create, objet pour edit
$module_name = 'users';
$module_title = 'Utilisateurs';
$module_title_singular = 'utilisateur';
$fields = [
    [
        'name' => 'username',
        'label' => "Nom d'utilisateur",
        'type' => 'text',
        'required' => true,
        'help' => 'Caractères alphanumériques uniquement'
    ],
    [
        'name' => 'email',
        'label' => 'Email',
        'type' => 'email',
        'required' => true
    ],
    [
        'name' => 'password',
        'label' => 'Mot de passe',
        'type' => 'password',
        'required' => true, // Requis seulement en création
        'autocomplete' => 'new-password'
    ],
    [
        'name' => 'role_id',
        'label' => 'Rôle',
        'type' => 'select',
        'required' => true,
        'options' => [
            1 => 'Admin',
            2 => 'User'
        ]
    ],
    [
        'name' => 'bio',
        'label' => 'Biographie',
        'type' => 'textarea',
        'rows' => 5
    ],
    [
        'name' => 'active',
        'label' => 'Statut',
        'type' => 'checkbox',
        'checkbox_label' => 'Compte actif',
        'default' => true
    ]
];
```

**Types de champs supportés**:

- `text`, `email`, `number`, `date`, `time`, `url`
- `password` - Optionnel en édition
- `textarea` - Zone de texte multiligne
- `select` - Liste déroulante
- `checkbox` - Case à cocher (switch)
- `file` - Upload de fichier

## Utilisation Rapide

### Créer une Page de Liste

```php
<?php
// Dans votre contrôleur
$users = User::all();

// Préparer les données
$items = $users;
$module_name = 'users';
$module_title = 'Utilisateurs';
$columns = [
    ['field' => 'id', 'label' => 'ID'],
    ['field' => 'username', 'label' => 'Nom'],
    ['field' => 'email', 'label' => 'Email'],
    ['field' => 'created_at', 'label' => 'Créé le', 'type' => 'date']
];

// Inclure le template
include __DIR__ . '/_templates/index.php';
```

### Créer un Formulaire

```php
<?php
// Pour la création
$item = null;
$module_name = 'users';
$module_title = 'Utilisateurs';
$module_title_singular = 'utilisateur';
$fields = [...]; // Voir exemple ci-dessus

include __DIR__ . '/_templates/form.php';
```

## Personnalisation

Vous pouvez copier ces templates et les personnaliser pour des besoins spécifiques, ou les utiliser directement en passant les bonnes variables.
