# I18n System - SunuFramework2

## 🌍 Système d'Internationalisation Complet

Système I18n professionnel inspiré de Laravel et Symfony, fully integrated dans SunuFramework2.

## ✨ Fonctionnalités

- ✅ **Multi-langue** - Support complet pour French, English, Arabic (extensible)
- ✅ **Fichiers JSON** - Format simple et lisible
- ✅ **Chargement hiérarchique** - Core → Module → Override
- ✅ **Fallback intelligent** - Gestion automatique des traductions manquantes
- ✅ **Cache performant** - Système de cache fichier avec invalidation auto
- ✅ **Détection automatique** - URL, session, préférence utilisateur, header HTTP
- ✅ **Helpers globaux** - API intuitive (`__t()`, `trans()`, etc.)
- ✅ **CLI Tools** - 7 commandes pour gérer les traductions
- ✅ **Backoffice** - Interface complète de gestion
- ✅ **Import/Export** - JSON import/export avec override

## 📦 Installation

Le système est déjà intégré dans le framework. Pour l'activer :

### 1. Activer le module

Ajouter dans `config/app.php`:

```php
'modules' => [
    // ... autres modules
    'I18n' => true,
],
```

### 2. Configuration

```php
// config/app.php
'locale' => 'fr',
'fallback_locale' => 'en',
'supported_locales' => ['fr', 'en', 'ar'],

'i18n' => [
    'cache_enabled' => true,
    'cache_path' => '/storage/cache/i18n',
    'override_path' => '/storage/i18n/overrides',
]
```

### 3. Créer les répertoires (déjà fait)

```
languages/
storage/cache/i18n/
storage/i18n/overrides/
```

## 🚀 Usage Rapide

### Dans les vues

```php
<h1><?= __t('dashboard.title') ?></h1>
<p><?= __t('auth.welcome', ['name' => $user->name]) ?></p>
```

### Avec pluralisation

```php
<?= trans_choice('users.count', 5, ['count' => 5]) ?>
// Output: "5 utilisateurs"
```

### Dans les contrôleurs

```php
class UserController
{
    public function store()
    {
        // ...
        flash('success', __t('messages.saved'));
        redirect('/users');
    }
}
```

### Changer la langue

```php
// Via helper
set_locale('en');

// Via URL
// ?lang=en

// Forcer pour une traduction specifique
echo __t('dashboard.title', [], 'ar');
```

## 📝 Helpers Disponibles

| Helper                                          | Description            | Exemple                               |
| ----------------------------------------------- | ---------------------- | ------------------------------------- |
| `__t($key, $replace, $locale)`                  | Traduction (shorthand) | `__t('auth.login')`                   |
| `trans($key, $replace, $locale)`                | Traduction complète    | `trans('messages.saved')`             |
| `trans_choice($key, $count, $replace, $locale)` | Pluralisation          | `trans_choice('users.count', 10)`     |
| `app_locale()`                                  | Locale actuelle        | `app_locale() // 'fr'`                |
| `set_locale($locale)`                           | Définir locale         | `set_locale('en')`                    |
| `supported_locales()`                           | Langues supportées     | `supported_locales() // ['fr', 'en']` |
| `is_locale_supported($locale)`                  | Vérifier support       | `is_locale_supported('es')`           |

## 🔧 CLI Commands

```bash
# Lister toutes les clés
php sunu i18n:list fr

# Trouver les traductions manquantes
php sunu i18n:missing ar

# Synchroniser et rebuild cache
php sunu i18n:sync

# Exporter les traductions
php sunu i18n:export fr export.json

# Importer des traductions
php sunu i18n:import fr import.json

# Vider le cache
php sunu i18n:cache:clear fr

# Statistiques du cache
php sunu i18n:cache:stats
```

## 💼 Backoffice

Accès: `/admin/i18n`

**Fonctionnalités:**

- Visualiser toutes les traductions
- Éditer traductions inline
- Créer nouvelles clés
- Importer/Exporter JSON
- Gérer les overrides
- Vider le cache
- Statistiques

## 📂 Structure des Fichiers

```
/languages/
    ├── fr.json          # Français (Core)
    ├── en.json          # English (Core)
    └── ar.json          # Arabic (Core)

/Modules/Blog/languages/
    ├── fr.json          # Module-specific French
    └── en.json          # Module-specific English

/storage/i18n/overrides/
    ├── fr.json          # Overrides français (Backoffice)
    └── en.json          # Overrides anglais

/storage/cache/i18n/
    ├── fr.cache         # Cache compilé français
    ├── en.cache         # Cache compilé anglais
    └── ar.cache         # Cache compilé arabe
```

## 🔄 Hiérarchie de Chargement

```
Core Languages (languages/fr.json)
         ↓
Module Languages (Modules/Blog/languages/fr.json)
         ↓
Overrides (storage/i18n/overrides/fr.json) ⭐ Priorité maximale
```

Les fichiers sont fusionnés automatiquement, les niveaux supérieurs écrasent les inférieurs.

## 📖 Format JSON

### Exemple de fichier de traduction

```json
{
  "auth": {
    "login": "Connexion",
    "welcome": "Bienvenue, :name",
    "logout_success": "Déconnexion réussie"
  },
  "users": {
    "title": "Utilisateurs",
    "count": ":count utilisateur|:count utilisateurs"
  },
  "messages": {
    "saved": "Enregistré avec succès"
  }
}
```

### Utilisation

```php
__t('auth.login')                              // "Connexion"
__t('auth.welcome', ['name' => 'Jean'])        // "Bienvenue, Jean"
trans_choice('users.count', 5, ['count' => 5]) // "5 utilisateurs"
```

## 🌐 Détection Automatique de Langue

Priorité (du plus haut au plus bas):

1. **URL Parameter** - `?lang=fr`
2. **Session** - `$_SESSION['locale']`
3. **User Preference** - Base de données (colonne `preferred_locale`)
4. **Accept-Language Header** - Navigateur
5. **Default** - Configuration (`locale` dans `app.php`)

## 🎯 Ajouter une Nouvelle Langue

1. Créer le fichier JSON :

```bash
touch languages/es.json
```

2. Ajouter les traductions :

```json
{
  "auth": {
    "login": "Iniciar sesión",
    "welcome": "Bienvenido, :name"
  }
}
```

3. Mettre à jour la config :

```php
// config/app.php
'supported_locales' => ['fr', 'en', 'ar', 'es'],
```

4. Synchroniser :

```bash
php sunu i18n:sync
```

## 🧩 Intégration Module

Pour ajouter des traductions dans votre module :

```php
// Modules/MyModule/languages/fr.json
{
  "mymodule": {
    "title": "Mon Module",
    "description": "Description du module"
  }
}
```

Utilisation :

```php
echo __t('mymodule.title'); // "Mon Module"
```

## 🏗️ Architecture

```
LanguageManager (Singleton)
    ↓
├── Translator (TranslatorInterface)
├── LanguageLoader
│   └── LanguageCache
└── SetLocaleMiddleware
```

**Classes Core:**

- `TranslatorInterface` - Contract
- `Translator` - Moteur de traduction
- `LanguageLoader` - Chargement hiérarchique
- `LanguageCache` - Cache fichier
- `LanguageManager` - Manager central
- `SetLocaleMiddleware` - Détection automatique

## 📊 Performance

- **Cache compilé** - Traductions sérialisées en cache
- **Lazy loading** - Chargement à la demande
- **TTL configurable** - Expiration automatique (défaut: 1h)
- **Invalidation intelligente** - Clear cache auto lors des mises à jour

## 🔐 Sécurité

- Validation des locales supportées
- Protection contre directory traversal
- Sanitization des clés
- Gestion sécurisée des fichiers

## 📚 Documentation Complète

- [Walkthrough.md](file:///C:/Users/akpa.lath/.gemini/antigravity/brain/4e43105f-11e6-4b16-ac66-601993fd8250/walkthrough.md) - Guide technique détaillé
- [Implementation Plan](file:///C:/Users/akpa.lath/.gemini/antigravity/brain/4e43105f-11e6-4b16-ac66-601993fd8250/implementation_plan.md) - Plan d'implémentation
- [Task.md](file:///C:/Users/akpa.lath/.gemini/antigravity/brain/4e43105f-11e6-4b16-ac66-601993fd8250/task.md) - Checklist des tâches

## 🤝 Contribution

Pour contribuer des traductions :

1. Fork le projet
2. Ajouter vos traductions dans `languages/[locale].json`
3. Tester avec `php sunu i18n:missing [locale]`
4. Submit Pull Request

## 📄 License

Même license que SunuFramework2.

---

**Développé avec ❤️ pour SunuFramework2**
