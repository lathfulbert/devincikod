# Intégration du Menu Langue I18n avec le Template AbckOffice

## Vue d'ensemble

Cette intégration fusionne le style visuel du menu langue du template AbckOffice avec le système I18n natif de SunuFramework2. Le menu permet maintenant de changer dynamiquement la langue de l'application en utilisant le système de traduction du framework.

## Modifications effectuées

### 1. Composant Language Selector
**Fichier**: [templates/backend/components/language-selector.php](templates/backend/components/language-selector.php)

Le composant a été complètement refactorisé pour :
- Utiliser les helpers I18n du framework (`app_locale()`, `supported_locales()`)
- Conserver le style visuel du template AbckOffice avec les classes `.translate_wrapper`, `.current_lang`, `.more_lang`
- Afficher dynamiquement les langues supportées configurées dans `config/app.php`
- Générer des URLs de redirection avec le paramètre `?lang={locale}`

**Configuration des langues** :
```php
$localeConfig = [
    'fr' => [
        'name' => 'Français',
        'flag' => 'flag-icon-fr',
        'short' => 'FR'
    ],
    'en' => [
        'name' => 'English',
        'flag' => 'flag-icon-us',
        'short' => 'EN',
        'suffix' => '(US)'
    ],
    'ar' => [
        'name' => 'العربية',
        'flag' => 'flag-icon-ae',
        'short' => 'AR',
        'suffix' => '(AE)'
    ]
];
```

### 2. Script JavaScript
**Fichier**: [public/assets/js/i18n-language-selector.js](public/assets/js/i18n-language-selector.js)

Un nouveau fichier JavaScript dédié a été créé pour gérer :
- L'ouverture/fermeture du dropdown de sélection de langue
- La redirection vers l'URL avec le paramètre de langue approprié
- La fermeture du dropdown lors d'un clic en dehors
- Pas de conflit avec le script.js existant du template

**Fonctionnalités** :
- Toggle du dropdown au clic sur la langue courante
- Redirection automatique vers `?lang={locale}` au choix d'une langue
- Support des événements sans dépendance jQuery (vanilla JS)
- Initialisation automatique au chargement du DOM

### 3. Intégration dans le Header
**Fichier**: [templates/backend/layouts/header.php](templates/backend/layouts/header.php:32)

Le menu hard-codé avec les langues statiques (en, de, es, fr, pt, cn, ae) a été remplacé par :
```php
<li class="language-nav">
  <?php component('language-selector') ?>
</li>
```

### 4. Chargement du Script
**Fichier**: [templates/backend/layouts/script.php](templates/backend/layouts/script.php:30)

Le script I18n a été ajouté après `script.js` principal :
```html
<!-- I18n Language Selector js-->
<script src="<?= url() ?>/assets/js/i18n-language-selector.js"></script>
```

## Fonctionnement

### Flux de changement de langue

1. **L'utilisateur clique sur le menu langue** dans le header
2. **Le dropdown s'affiche** avec les langues supportées
3. **L'utilisateur sélectionne une langue**
4. **Redirection** vers `?lang={locale}` (ex: `?lang=fr`)
5. **Le middleware `SetLocaleMiddleware`** intercepte la requête
6. **La session est mise à jour** : `$_SESSION['locale'] = 'fr'`
7. **La page se recharge** avec toutes les traductions dans la nouvelle langue
8. **Le menu affiche** la langue sélectionnée comme langue courante

### Persistance de la langue

La langue sélectionnée est persistée via :
- **Session PHP** : `$_SESSION['locale']`
- **Préférence utilisateur** (si authentifié) : `users.preferred_locale` en base de données

### Ordre de priorité de détection

Le middleware détecte la langue dans cet ordre :
1. Paramètre URL : `?lang={locale}`
2. Variable de session : `$_SESSION['locale']`
3. Préférence utilisateur en BD : `users.preferred_locale`
4. Header Accept-Language du navigateur
5. Locale par défaut : `config('app.locale')`

## Configuration

### Ajouter une nouvelle langue

1. **Mettre à jour `config/app.php`** :
```php
'supported_locales' => ['fr', 'en', 'ar', 'es'], // Ajouter 'es'
```

2. **Ajouter la configuration dans le composant** [templates/backend/components/language-selector.php](templates/backend/components/language-selector.php:6-24) :
```php
'es' => [
    'name' => 'Español',
    'flag' => 'flag-icon-es',
    'short' => 'ES'
],
```

3. **Créer le fichier de traduction** :
```
/languages/es.json
```

4. **Le menu affichera automatiquement** la nouvelle langue

### Personnaliser les drapeaux

Les classes CSS des drapeaux utilisent la librairie **flag-icons**. Pour changer un drapeau :
```php
'en' => [
    'name' => 'English',
    'flag' => 'flag-icon-gb',  // Changer de 'us' à 'gb' pour le drapeau UK
    'short' => 'EN'
],
```

## Fichiers de traduction

### Structure
```
/languages/
  ├── fr.json          # Traductions françaises
  ├── en.json          # Traductions anglaises
  └── ar.json          # Traductions arabes

/Modules/{Module}/languages/
  ├── fr.json          # Traductions spécifiques au module
  └── en.json
```

### Utilisation dans les templates
```php
// Traduction simple
<?= trans('auth.login') ?>

// Traduction avec paramètres
<?= trans('auth.welcome', ['name' => $user->name]) ?>

// Pluralisation
<?= trans_choice('users.count', 5) ?>

// Obtenir la locale actuelle
<?= app_locale() ?>  // Retourne 'fr', 'en', ou 'ar'
```

## Tests

### Test manuel

1. **Accéder au dashboard** backend
2. **Vérifier que le menu langue** affiche la langue courante (ex: FR)
3. **Cliquer sur le menu langue** - le dropdown doit s'afficher
4. **Sélectionner une langue** différente (ex: EN)
5. **La page doit recharger** avec les traductions en anglais
6. **Le menu doit afficher** EN comme langue courante
7. **Naviguer sur d'autres pages** - la langue doit persister

### Test de persistance

1. Changer la langue vers EN
2. Fermer le navigateur
3. Rouvrir et accéder au dashboard
4. La langue doit toujours être EN (session)

### Test des traductions

1. Créer une clé de test dans `/languages/fr.json` :
```json
{
  "test": {
    "message": "Ceci est un test en français"
  }
}
```

2. Créer la même clé dans `/languages/en.json` :
```json
{
  "test": {
    "message": "This is a test in English"
  }
}
```

3. Ajouter dans un template :
```php
<?= trans('test.message') ?>
```

4. Changer de langue et vérifier que le texte change

## Interface d'administration I18n

Le module I18n fournit une interface complète accessible via :
- **URL** : `/admin/i18n`
- **Fonctionnalités** :
  - Voir toutes les traductions
  - Créer/Modifier/Supprimer des clés
  - Importer/Exporter des fichiers JSON
  - Vider le cache des traductions

### Utiliser l'interface admin

1. Accéder à `/admin/i18n`
2. Sélectionner une langue dans le menu déroulant
3. Chercher une clé ou créer une nouvelle
4. Modifier les traductions pour chaque langue
5. Les modifications sont stockées dans `/storage/i18n/overrides/{locale}.json`
6. Ces overrides ont priorité sur les fichiers de base

## Commandes Console

Le framework fournit des commandes pour gérer les traductions :

```bash
# Lister toutes les traductions d'une langue
php sunu i18n:list fr

# Trouver les traductions manquantes
php sunu i18n:missing en

# Regénérer le cache
php sunu i18n:sync

# Exporter les traductions
php sunu i18n:export fr languages/export/fr.json

# Importer des traductions
php sunu i18n:import fr languages/import/fr.json

# Vider le cache
php sunu i18n:cache:clear

# Statistiques du cache
php sunu i18n:cache:stats
```

## Dépannage

### Le menu ne s'affiche pas
1. Vérifier que le fichier [public/assets/js/i18n-language-selector.js](public/assets/js/i18n-language-selector.js) existe
2. Vérifier qu'il est chargé dans [templates/backend/layouts/script.php](templates/backend/layouts/script.php:30)
3. Vérifier la console JavaScript pour les erreurs

### Le dropdown ne s'ouvre pas
1. Vérifier que les classes CSS du template sont bien chargées
2. Vérifier que `script.js` principal est chargé avant `i18n-language-selector.js`
3. Vérifier qu'il n'y a pas de conflit JavaScript dans la console

### La langue ne change pas
1. Vérifier que le middleware `SetLocaleMiddleware` est activé
2. Vérifier que la langue est dans `supported_locales` de [config/app.php](config/app.php)
3. Vérifier les sessions PHP (extension session activée)
4. Vérifier les logs dans `storage/logs/`

### Les traductions ne s'affichent pas
1. Vérifier que le fichier `/languages/{locale}.json` existe
2. Vérifier le format JSON (pas d'erreurs de syntaxe)
3. Vérifier que la clé existe : `php sunu i18n:list {locale}`
4. Vider le cache : `php sunu i18n:cache:clear`

### Conflit avec l'ancien système du template
Si l'ancien JavaScript du template interfère :
1. Rechercher dans [public/assets/js/script.js](public/assets/js/script.js:231-242) les lignes concernant `.translate_wrapper`
2. Commenter ou supprimer la fonction `translate()` si elle n'est plus nécessaire
3. Notre nouveau système utilise la redirection serveur, pas la traduction côté client

## Avantages de cette intégration

1. **Style natif du template** : Le menu conserve l'apparence visuelle d'AbckOffice
2. **Fonctionnalité I18n native** : Utilise le système de traduction robuste du framework
3. **Pas de duplication** : Les langues sont configurées une seule fois dans `config/app.php`
4. **Extensible** : Facile d'ajouter de nouvelles langues
5. **Persistance** : La langue est sauvegardée en session et en base de données
6. **Cache performant** : Les traductions sont mises en cache pour de meilleures performances
7. **Interface admin** : Gestion complète des traductions sans toucher au code

## Références

- **Core I18n** : [Core/I18n/](Core/I18n/)
- **Configuration** : [config/app.php](config/app.php)
- **Middleware** : [Core/I18n/Middleware/SetLocaleMiddleware.php](Core/I18n/Middleware/SetLocaleMiddleware.php)
- **Module I18n** : [Modules/I18n/](Modules/I18n/)
- **Helpers** : [Core/Support/helpers.php](Core/Support/helpers.php:990-1083)
