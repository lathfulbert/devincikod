# Admin Design System - Guide d'Utilisation

Ce document explique comment utiliser le nouveau système de design de l'interface d'administration de SunuFramework2.

## Table des Matières

1. [Vue d'ensemble](#vue-densemble)
2. [Structure du Layout](#structure-du-layout)
3. [Composants Réutilisables](#composants-réutilisables)
4. [Exemples d'Utilisation](#exemples-dutilisation)
5. [Thèmes et Personnalisation](#thèmes-et-personnalisation)

## Vue d'Ensemble

L'interface admin utilise **Bootstrap 5** avec un thème personnalisé professionnellement conçu. Les assets proviennent d'un template Laravel moderne et ont été adaptés pour notre framework PHP natif.

### Technologies Utilisées

- **Bootstrap 5** - Framework CSS
- **Feather Icons** - Icônes modernes et légères
- **DataTables** - Tables interactives avec tri/recherche
- **jQuery** - Manipulation DOM
- **Select2** - Sélecteurs avancés

### Fichiers Clés

| Fichier                         | Description                                   |
| ------------------------------- | --------------------------------------------- |
| `templates/admin/layout.php`    | Layout principal avec header, sidebar, footer |
| `templates/admin/components/`   | Composants réutilisables                      |
| `public/assets/css/custom.css`  | Styles personnalisés                          |
| `public/assets/css/color-1.css` | Thème de couleur (6 variantes disponibles)    |

## Structure du Layout

### Template de Base

```php
@extends('admin.layout')

@section('styles')
<!-- CSS additionnels pour cette page -->
@endsection

@section('content')
<!-- Votre contenu ici -->
@endsection

@section('scripts')
<!-- JavaScript additionnels pour cette page -->
@endsection
```

### Variables Disponibles

Le layout attend certaines variables:

```php
$title = "Titre de la Page"; // Titre affiché dans <title>
$datatable = true; // Charge automatiquement DataTables si true
```

## Composants Réutilisables

### 1. Breadcrumb (Fil d'Ariane)

**Fichier**: `templates/admin/components/breadcrumb.php`

**Utilisation**:

```php
<?php
$breadcrumb = [
    ['label' => 'Dashboard', 'url' => '/admin/dashboard'],
    ['label' => 'Users', 'url' => '/admin/users'],
    ['label' => 'Edit User'] // Dernier = actif (sans URL)
];
include __DIR__ . '/../components/breadcrumb.php';
?>
```

**Rendu**:

```
🏠 > Dashboard > Users > Edit User
```

### 2. Alerts (Messages Flash)

**Fichier**: `templates/admin/components/alerts.php`

**Utilisation**:

Dans votre contrôleur:

```php
$_SESSION['flash']['success'] = 'Opération réussie!';
$_SESSION['flash']['danger'] = 'Une erreur est survenue';
$_SESSION['flash']['warning'] = 'Attention!';
$_SESSION['flash']['info'] = 'Information';
redirect('/admin/users');
```

Dans votre vue:

```php
<?php include __DIR__ . '/../components/alerts.php'; ?>
```

**Styles disponibles**: `success`, `danger`, `warning`, `info`

### 3. Card (Conteneur de Contenu)

**Fichiers**: `card-start.php` et `card-end.php`

**Utilisation**:

```php
<?php
$card_title = "Liste des Utilisateurs";
$card_actions = '<a href="/admin/users/create" class="btn btn-primary">Nouveau</a>';
include __DIR__ . '/../components/card-start.php';
?>

<!-- Contenu de la carte -->
<table class="table">
    <thead>
        <tr>
            <th>ID</th>
            <th>Nom</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        <!-- Lignes -->
    </tbody>
</table>

<?php
$card_footer = "Total: 25 utilisateurs";
include __DIR__ . '/../components/card-end.php';
?>
```

### 4. DataTable Initialization

**Fichier**: `templates/admin/components/datatable-init.php`

**Utilisation**:

Dans votre HTML:

```html
<table id="usersTable" class="table table-striped">
  <!-- Contenu -->
</table>
```

Dans la section `@section('scripts')`:

```php
<?php
$datatable_id = 'usersTable';
$datatable_config = [
    'order' => [[0, 'desc']],
    'pageLength' => 25,
    'responsive' => true
];
include __DIR__ . '/../components/datatable-init.php';
?>
```

## Exemples d'Utilisation

### Page de Liste (Index)

```php
@extends('admin.layout')

@section('content')

<?php
$breadcrumb = [
    ['label' => 'Dashboard', 'url' => '/admin/dashboard'],
    ['label' => 'Utilisateurs']
];
include __DIR__ . '/../components/breadcrumb.php';
?>

<?php include __DIR__ . '/../components/alerts.php'; ?>

<div class="row">
    <div class="col-12">
        <?php
        $card_title = "Liste des Utilisateurs";
        $card_actions = '<a href="' . url('/admin/users/create') . '" class="btn btn-primary">
            <i data-feather="plus"></i> Nouveau
        </a>';
        include __DIR__ . '/../components/card-start.php';
        ?>

        <table id="usersTable" class="table table-striped">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nom</th>
                    <th>Email</th>
                    <th>Rôle</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($users as $user): ?>
                <tr>
                    <td><?= $user->id ?></td>
                    <td><?= htmlspecialchars($user->username) ?></td>
                    <td><?= htmlspecialchars($user->email) ?></td>
                    <td><span class="badge bg-primary"><?= $user->role ?></span></td>
                    <td>
                        <a href="<?= url('/admin/users/edit/' . $user->id) ?>" class="btn btn-sm btn-warning">
                            <i data-feather="edit"></i>
                        </a>
                        <a href="<?= url('/admin/users/delete/' . $user->id) ?>" class="btn btn-sm btn-danger" onclick="return confirm('Supprimer?')">
                            <i data-feather="trash-2"></i>
                        </a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <?php include __DIR__ . '/../components/card-end.php'; ?>
    </div>
</div>

@endsection

@section('scripts')
<?php
$datatable_id = 'usersTable';
include __DIR__ . '/../components/datatable-init.php';
?>
@endsection
```

### Page de Formulaire (Create/Edit)

```php
@extends('admin.layout')

@section('content')

<?php
$breadcrumb = [
    ['label' => 'Dashboard', 'url' => '/admin/dashboard'],
    ['label' => 'Utilisateurs', 'url' => '/admin/users'],
    ['label' => isset($user) ? 'Éditer' : 'Créer']
];
include __DIR__ . '/../components/breadcrumb.php';
?>

<?php include __DIR__ . '/../components/alerts.php'; ?>

<div class="row">
    <div class="col-lg-8">
        <?php
        $card_title = isset($user) ? "Éditer l'utilisateur" : "Créer un utilisateur";
        include __DIR__ . '/../components/card-start.php';
        ?>

        <form method="POST" action="<?= isset($user) ? url('/admin/users/update/' . $user->id) : url('/admin/users/store') ?>">
            <input type="hidden" name="_csrf_token" value="<?= csrf_token() ?>">

            <div class="mb-3">
                <label for="username" class="form-label">Nom d'utilisateur <span class="text-danger">*</span></label>
                <input type="text" name="username" id="username" class="form-control" value="<?= $user->username ?? '' ?>" required>
            </div>

            <div class="mb-3">
                <label for="email" class="form-label">Email <span class="text-danger">*</span></label>
                <input type="email" name="email" id="email" class="form-control" value="<?= $user->email ?? '' ?>" required>
            </div>

            <div class="mb-3">
                <label for="role_id" class="form-label">Rôle <span class="text-danger">*</span></label>
                <select name="role_id" id="role_id" class="form-select" required>
                    <option value="">-- Sélectionnez --</option>
                    <?php foreach ($roles as $role): ?>
                    <option value="<?= $role->id ?>" <?= isset($user) && $user->role_id == $role->id ? 'selected' : '' ?>>
                        <?= htmlspecialchars($role->name) ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="mb-3">
                <label for="password" class="form-label">
                    Mot de passe <?= isset($user) ? '(laisser vide pour ne pas changer)' : '<span class="text-danger">*</span>' ?>
                </label>
                <input type="password" name="password" id="password" class="form-control" <?= !isset($user) ? 'required' : '' ?>>
            </div>

            <div class="mt-4">
                <button type="submit" class="btn btn-primary">
                    <i data-feather="save"></i> Enregistrer
                </button>
                <a href="<?= url('/admin/users') ?>" class="btn btn-secondary">
                    <i data-feather="x"></i> Annuler
                </a>
            </div>
        </form>

        <?php include __DIR__ . '/../components/card-end.php'; ?>
    </div>
</div>

@endsection
```

## Thèmes et Personnalisation

### Changer de Thème

6 thèmes de couleurs sont disponibles (`color-1.css` à `color-6.css`). Pour changer le thème, modifiez dans `layout.php`:

```php
<!-- Thème par défaut (violet) -->
<link rel="stylesheet" href="<?= url('/assets/css/color-1.css') ?>" id="color">

<!-- Autres options -->
<!-- color-2: cyan -->
<!-- color-3: vert -->
<!-- color-4: rose -->
<!-- color-5: orange -->
<!-- color-6: bleu -->
```

### Variables CSS Personnalisées

Le fichier `public/assets/css/custom.css` contient des variables CSS que vous pouvez modifier:

```css
:root {
  --primary-color: #7366ff;
  --secondary-color: #f73164;
  --success-color: #51bb25;
  --danger-color: #dc3545;
  /* etc... */
}
```

### Classes Utilitaires

Bootstrap 5 + classes custom disponibles:

```html
<!-- Couleurs de texte -->
<span class="text-primary">Texte principal</span>
<span class="text-success">Texte succès</span>
<span class="text-danger">Texte danger</span>

<!-- Backgrounds -->
<div class="bg-primary-light">Fond primaire léger</div>
<div class="bg-success-light">Fond succès léger</div>

<!-- Ombres -->
<div class="shadow-sm">Petite ombre</div>
<div class="shadow">Ombre normale</div>
<div class="shadow-lg">Grande ombre</div>

<!-- Badges -->
<span class="badge bg-primary">Badge</span>
<span class="badge bg-success">Succès</span>
```

## Icônes Feather

Utilisation des icônes Feather:

```html
<!-- Icône simple -->
<i data-feather="home"></i>
<i data-feather="user"></i>
<i data-feather="save"></i>

<!-- Icône redimensionnée -->
<i data-feather="edit" style="width: 16px; height: 16px;"></i>
```

**Icônes courantes**:

- `home`, `user`, `users`, `settings`
- `edit`, `trash-2`, `save`, `x`, `check`
- `plus`, `minus`, `search`, `filter`
- `folder`, `file`, `database`, `server`
- `alert-triangle`, `info`, `help-circle`

Liste complète: [Feather Icons](https://feathericons.com/)

## Bonnes Pratiques

1. **Toujours utiliser les composants**: Ne recréez pas les alerts, breadcrumb, cards manuellement
2. **Icônes partout**: Ajoutez des icônes aux boutons pour une meilleure UX
3. **Messages flash**: Utilisez toujours les messages flash pour le feedback utilisateur
4. **DataTables**: Pour les listes avec plus de 10 items
5. **CSRF Protection**: Toujours inclure `<input type="hidden" name="_csrf_token" value="<?= csrf_token() ?>">`
6. **Validation**: Utilisez `required` et autres attributs HTML5
7. **Responsive**: Testez sur mobile (sidebar se rétracte automatiquement)

## Support et Questions

Pour toute question ou amélioration, consultez le [`walkthrough.md`](file:///C:/Users/akpa.lath/.gemini/antigravity/brain/7acee218-6a62-4f21-821e-b1738984f4ae/walkthrough.md) pour plus de détails techniques.
