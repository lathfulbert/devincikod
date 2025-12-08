# Intégration des Settings dans les Vues

Ce document explique comment le système de settings a été intégré dans l'application et comment l'utiliser pour personnaliser votre application.

## Vue d'ensemble

Le système de settings permet de personnaliser dynamiquement l'application sans modifier le code. Les paramètres sont stockés en base de données et peuvent être modifiés via l'interface d'administration.

## Composants du Système

### 1. SettingsService

**Fichier** : `Modules/Settings/Services/SettingsService.php`

Le service principal qui gère tous les settings de l'application.

**Nouvelles méthodes ajoutées** :
- `getLogoUrl(?string $default = null)` - Récupère l'URL du logo
- `getDarkLogoUrl(?string $default = null)` - Récupère l'URL du logo en mode sombre
- `getLogoIconUrl(?string $default = null)` - Récupère l'URL de l'icône du logo
- `getFaviconUrl(?string $default = null)` - Récupère l'URL du favicon
- `getSiteName(?string $default = null)` - Récupère le nom du site
- `getSiteDescription(?string $default = null)` - Récupère la description du site

### 2. Helpers Globaux

**Fichier** : `Core/Support/helpers.php`

Des fonctions helpers ont été ajoutées pour faciliter l'accès aux settings dans les vues.

#### Fonctions disponibles :

```php
// Récupérer le service settings ou une valeur spécifique
settings(?string $key = null, $default = null)

// Récupérer le nom du site
site_name(?string $default = null): string

// Récupérer l'URL du logo
site_logo(?string $default = null): string

// Récupérer l'URL du logo en mode sombre
site_logo_dark(?string $default = null): string

// Récupérer l'URL de l'icône du logo
site_logo_icon(?string $default = null): string

// Récupérer l'URL du favicon
site_favicon(?string $default = null): string

// Récupérer la description du site
site_description(?string $default = null): string
```

### 3. FileManager Integration

**Fichier** : `Core/Files/Services/FileManager.php`

Le système utilise le FileManager du core pour gérer les uploads d'images. Les images sont automatiquement :
- Validées (type, taille)
- Stockées dans des dossiers organisés
- Optimisées avec création de miniatures (thumbnails)

**Helper pour les fichiers** :
```php
file_manager() // Récupère l'instance du FileManager
file_url(string $path) // Convertit un chemin de fichier en URL
```

## Utilisation dans les Vues

### Dans les layouts PHP

Les layouts suivants ont été mis à jour pour utiliser les settings :

#### 1. Layout Master (`resources/views/backend/layouts/master.php`)

```php
<!-- Title dynamique -->
<title><?= htmlspecialchars(site_name()) ?> - @yield('title')</title>

<!-- Favicon dynamique -->
<link rel="icon" href="<?= site_favicon() ?>" type="image/x-icon">

<!-- Description dynamique -->
<meta name="description" content="<?= htmlspecialchars(site_description('Description par défaut')) ?>">

<!-- Auteur dynamique -->
<meta name="author" content="<?= htmlspecialchars(settings('site_author', 'LathDevinci')) ?>">
```

#### 2. Sidebar (`resources/views/backend/layouts/sidebar.php`)

```php
<!-- Logo principal -->
<img class="img-fluid for-light"
     src="<?= site_logo() ?>"
     alt="<?= htmlspecialchars(site_name()) ?>">

<!-- Logo mode sombre -->
<img class="img-fluid for-dark"
     src="<?= site_logo_dark() ?>"
     alt="<?= htmlspecialchars(site_name()) ?>">

<!-- Logo icône -->
<img class="img-fluid"
     src="<?= site_logo_icon() ?>"
     alt="<?= htmlspecialchars(site_name()) ?>">
```

#### 3. Header (`resources/views/backend/layouts/header.php`)

```php
<!-- Logo dans le header -->
<img class="img-fluid"
     src="<?= site_logo() ?>"
     alt="<?= htmlspecialchars(site_name()) ?>">
```

### Exemples d'utilisation

#### Afficher le nom du site
```php
<h1><?= site_name() ?></h1>
```

#### Afficher le logo avec fallback
```php
<img src="<?= site_logo('/assets/images/default-logo.png') ?>" alt="Logo">
```

#### Récupérer un setting personnalisé
```php
<?php
$customSetting = settings('custom_key', 'valeur par défaut');
echo $customSetting;
?>
```

#### Vérifier si un setting existe
```php
<?php if (settings()->has('custom_key')): ?>
    <p><?= settings('custom_key') ?></p>
<?php endif; ?>
```

## Gestion des Settings via l'Interface Admin

### Accès à la page de settings

1. Connectez-vous à l'administration
2. Allez dans **Settings** > **Site Settings**
3. Vous pouvez modifier :
   - Nom du site
   - Description
   - Logos (light, dark, icon)
   - Favicon

### Upload des logos

Le système accepte les formats suivants :
- **Logos** : JPG, PNG, GIF, SVG
- **Favicon** : ICO, PNG

**Processus d'upload** :
1. Sélectionnez un fichier via le formulaire
2. Le fichier est validé (type, taille)
3. Il est uploadé via le FileManager
4. Le chemin est automatiquement stocké dans les settings
5. Une miniature est créée (si applicable)

### Structure de stockage

Les fichiers uploadés sont stockés dans :
```
storage/uploads/logos/
├── YYYY/MM/DD/           # Organisation par date
│   ├── logo.png
│   ├── logo-dark.png
│   ├── logo-icon.png
│   └── favicon.png
└── thumbs/               # Miniatures
    └── YYYY/MM/DD/
```

## Settings Disponibles

### Settings du Site
- `site_name` - Nom de l'application
- `site_description` - Description
- `site_author` - Auteur/Créateur
- `site_logo` - Chemin du logo (light mode)
- `site_logo_dark` - Chemin du logo (dark mode)
- `site_logo_icon` - Chemin de l'icône
- `site_favicon` - Chemin du favicon

### Settings du Thème
- `theme_mode` - Mode du thème (light/dark)
- `primary_color` - Couleur primaire
- `secondary_color` - Couleur secondaire
- `sidebar_type` - Type de sidebar
- `layout_type` - Direction (LTR/RTL)

## Fonctionnement Technique

### 1. Chargement des Images

Les images sont chargées via le helper `file_url()` qui :
- Prend le chemin relatif stocké en BDD
- Le convertit en URL accessible publiquement
- Gère les chemins `storage/` automatiquement

```php
// En BDD : storage/uploads/logos/2024/12/08/logo.png
// Converti en : /storage/uploads/logos/2024/12/08/logo.png
```

### 2. Cache des Settings

Le `SettingsService` utilise un cache statique pour optimiser les performances :
- Les settings sont chargés une seule fois par requête
- Le cache est invalidé lors de la mise à jour
- Pas d'accès BDD répétés

### 3. Valeurs par Défaut

Chaque helper accepte une valeur par défaut optionnelle :
```php
site_logo('/assets/images/default-logo.png')
```

Si le setting n'existe pas, la valeur par défaut est retournée.

## Bonnes Pratiques

### 1. Toujours échapper les outputs
```php
<!-- Bon -->
<title><?= htmlspecialchars(site_name()) ?></title>

<!-- Mauvais -->
<title><?= site_name() ?></title>
```

### 2. Fournir des fallbacks
```php
<!-- Bon -->
<img src="<?= site_logo('/assets/images/logo.png') ?>" alt="Logo">

<!-- Acceptable mais moins robuste -->
<img src="<?= site_logo() ?>" alt="Logo">
```

### 3. Utiliser les helpers plutôt que le service directement
```php
<!-- Bon -->
<?= site_name() ?>

<!-- Moins recommandé -->
<?= settings()->getSiteName() ?>
```

### 4. Vérifier l'existence des fichiers pour les aperçus
```php
<?php if (!empty($settings['site_logo'])): ?>
    <img src="<?= site_logo() ?>" alt="Logo">
<?php endif; ?>
```

## Extension du Système

### Ajouter un nouveau setting

1. **Ajouter dans la BDD** (via interface ou migration)
```php
Setting::set('custom_setting', 'valeur', 'string', 'custom_group');
```

2. **Créer un helper** (optionnel) dans `helpers.php`
```php
if (!function_exists('custom_setting')) {
    function custom_setting(?string $default = null): string
    {
        return settings('custom_setting', $default);
    }
}
```

3. **Utiliser dans les vues**
```php
<div><?= custom_setting('Valeur par défaut') ?></div>
```

### Ajouter un type d'image

1. **Ajouter une méthode dans SettingsService**
```php
public function getCustomImageUrl(?string $default = null): string
{
    $path = $this->get('custom_image', '');

    if (empty($path)) {
        return $default ?? url('assets/images/default.png');
    }

    if (str_starts_with($path, 'storage/')) {
        return file_url($path);
    }

    return url($path);
}
```

2. **Créer un helper**
```php
if (!function_exists('custom_image')) {
    function custom_image(?string $default = null): string
    {
        return settings()->getCustomImageUrl($default);
    }
}
```

3. **Ajouter le formulaire d'upload**
```php
<input type="file" name="custom_image" class="form-control">
```

4. **Gérer l'upload dans le controller**
```php
if (isset($_FILES['custom_image']) && $_FILES['custom_image']['error'] === UPLOAD_ERR_OK) {
    $results = file_manager()->upload(['custom_image' => $_FILES['custom_image']], 'custom');
    if (!empty($results) && $results[0]['success']) {
        $this->settingsService->set('custom_image', $results[0]['path'], 'string', 'site');
    }
}
```

## Dépannage

### Les images ne s'affichent pas
1. Vérifiez que le dossier `storage/uploads` est accessible publiquement
2. Vérifiez les permissions (755 pour les dossiers, 644 pour les fichiers)
3. Vérifiez que le lien symbolique `public/storage -> storage` existe

### Les settings ne se mettent pas à jour
1. Vérifiez que le cache est bien invalidé : `settings()->clearCache()`
2. Vérifiez les permissions d'écriture en BDD
3. Vérifiez les logs pour les erreurs SQL

### Erreur lors de l'upload
1. Vérifiez `upload_max_filesize` et `post_max_size` dans php.ini
2. Vérifiez les permissions du dossier `storage/uploads`
3. Vérifiez que le FileManager est correctement configuré

## Conclusion

Le système de settings permet une personnalisation complète de l'application sans toucher au code. Tous les éléments visuels (logos, couleurs, nom) peuvent être modifiés via l'interface d'administration, et les changements sont immédiatement visibles dans toute l'application grâce aux helpers globaux et au système de cache.
