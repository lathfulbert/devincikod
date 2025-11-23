# Structure Complète du Module Settings

## Vue d'Ensemble

Ce document présente la structure complète du module Settings avec tous les fichiers créés et leur rôle.

## Arborescence Complète

```
Modules/Settings/
│
├── 📄 SettingsModule.php              # Fichier principal du module
├── 📄 module.json                     # Métadonnées et configuration
├── 📄 README.md                       # Documentation complète
├── 📄 INSTALL.md                      # Guide d'installation
├── 📄 STRUCTURE.md                    # Ce fichier
│
├── 📁 Controllers/                    # Contrôleurs du module
│   ├── SettingsController.php        # Contrôleur principal (Dashboard, Import/Export)
│   ├── SiteSettingsController.php    # Gestion des paramètres du site
│   ├── ThemeSettingsController.php   # Gestion du thème et apparence
│   ├── ApiSettingsController.php     # Configuration des APIs externes
│   ├── MailSettingsController.php    # Configuration SMTP et mail
│   ├── TranslationController.php     # Gestion des traductions (CRUD)
│   ├── WebhookController.php         # Gestion des webhooks (CRUD)
│   └── ApiController.php             # API REST du module
│
├── 📁 Models/                         # Modèles de données
│   ├── Setting.php                   # Modèle principal pour les paramètres
│   ├── Translation.php               # Modèle pour les traductions
│   ├── TranslationHistory.php        # Historique des modifications de traductions
│   ├── Webhook.php                   # Modèle pour les webhooks
│   └── WebhookLog.php                # Logs des appels webhook
│
├── 📁 Services/                       # Services métier
│   └── SettingsService.php           # Service de gestion centralisée des paramètres
│
├── 📁 Database/                       # Base de données
│   ├── 📁 Migrations/                # Scripts de migration
│   │   ├── 001_create_settings_table.php
│   │   ├── 002_create_translations_table.php
│   │   ├── 003_create_translation_history_table.php
│   │   ├── 004_create_webhooks_table.php
│   │   └── 005_create_webhook_logs_table.php
│   │
│   └── 📁 Seeders/                   # Données initiales
│       └── DefaultSettingsSeeder.php # Paramètres par défaut
│
└── 📁 Config/                        # Configuration
    └── defaults.php                  # Valeurs par défaut
```

## Détails des Fichiers

### Fichiers Principaux

#### SettingsModule.php
- **Rôle**: Point d'entrée du module
- **Contenu**:
  - Déclaration des routes (30+ routes)
  - Configuration du menu (7 sous-menus)
  - Routes API REST (3 endpoints)
  - Méthode boot() pour enregistrer le service

#### module.json
- **Rôle**: Métadonnées du module
- **Contenu**:
  - Informations (nom, version, description, auteur)
  - Dépendances (PHP >= 8.0)
  - Configuration autoload
  - Permissions (6 permissions)
  - Paramètres par défaut

### Contrôleurs (8 fichiers)

#### 1. SettingsController.php
```php
- index()          # Dashboard avec statistiques
- backup()         # Export de toute la configuration en JSON
- import()         # Import de configuration depuis JSON
```

#### 2. SiteSettingsController.php
```php
- index()          # Formulaire des paramètres du site
- update()         # Mise à jour des paramètres
- uploadLogo()     # Upload du logo
- uploadFavicon()  # Upload du favicon
```

#### 3. ThemeSettingsController.php
```php
- index()          # Formulaire du thème
- update()         # Mise à jour du thème
- preview()        # Prévisualisation (AJAX)
- reset()          # Réinitialisation aux valeurs par défaut
```

#### 4. ApiSettingsController.php
```php
- index()              # Formulaire des clés API
- update()             # Mise à jour des clés
- testConnection()     # Test de connexion API (AJAX)
- generateKey()        # Génération de clé aléatoire
```

#### 5. MailSettingsController.php
```php
- index()          # Formulaire de configuration mail
- update()         # Mise à jour de la config SMTP
- sendTest()       # Envoi d'email de test
```

#### 6. TranslationController.php
```php
- index()          # Liste des traductions
- create()         # Formulaire de création
- store()          # Enregistrement
- edit()           # Formulaire d'édition
- update()         # Mise à jour
- delete()         # Suppression
- import()         # Import depuis JSON
- export()         # Export vers JSON
- history()        # Historique des modifications
```

#### 7. WebhookController.php
```php
- index()          # Liste des webhooks
- create()         # Formulaire de création
- store()          # Enregistrement
- edit()           # Formulaire d'édition
- update()         # Mise à jour
- delete()         # Suppression
- test()           # Test du webhook (AJAX)
- logs()           # Logs des appels
```

#### 8. ApiController.php
```php
- getAllSettings()     # GET /api/settings/all
- getSetting()         # GET /api/settings/{key}
- updateSetting()      # POST /api/settings/{key}
```

### Modèles (5 fichiers)

#### 1. Setting.php
**Table**: `settings`

**Méthodes principales**:
```php
- get($key, $default)           # Récupérer un paramètre
- set($key, $value, $type, $group) # Définir un paramètre
- getByGroup($group)            # Récupérer par groupe
- getAll()                      # Tous les paramètres
- has($key)                     # Vérifier l'existence
- remove($key)                  # Supprimer
- prepareValue($value, $type)   # Préparer pour stockage
- castValue($value, $type)      # Convertir au bon type
```

**Types supportés**: string, integer, float, boolean, json, array

#### 2. Translation.php
**Table**: `translations`

**Méthodes principales**:
```php
- getTranslation($key, $language)
- getByLanguage($language)
- setTranslation($key, $value, $language, $module)
- history()
- saveToHistory($userId)
```

#### 3. TranslationHistory.php
**Table**: `translation_history`

**Méthodes principales**:
```php
- user()           # Relation avec User
- translation()    # Relation avec Translation
```

#### 4. Webhook.php
**Table**: `webhooks`

**Méthodes principales**:
```php
- getByEvent($event)           # Webhooks pour un événement
- send($payload)               # Envoyer une requête
- logCall(...)                 # Logger l'appel
- logs()                       # Récupérer les logs
- test()                       # Test du webhook
```

**Fonctionnalités**:
- Signature HMAC SHA-256
- Headers personnalisés
- Retry automatique
- Timeout configurable

#### 5. WebhookLog.php
**Table**: `webhook_logs`

**Méthodes principales**:
```php
- webhook()                    # Relation avec Webhook
- isSuccessful()               # Vérifier le succès
- cleanup($days)               # Nettoyage des vieux logs
```

### Services (1 fichier)

#### SettingsService.php
**Rôle**: Service centralisé pour la gestion des paramètres

**Méthodes**:
```php
// Gestion générale
- get($key, $default)
- set($key, $value, $type, $group)
- all()
- getByGroup($group)
- has($key)
- remove($key)

// Cache
- loadCache()
- clearCache()

// Import/Export
- export()
- import($json)

// Helpers par groupe
- getSiteSettings()
- getThemeSettings()
- getApiSettings()
- getMailSettings()
```

**Fonctionnalités**:
- Cache en mémoire pour les performances
- Type casting automatique
- Gestion par groupe
- Import/Export JSON

### Migrations (5 fichiers)

#### 1. 001_create_settings_table.php
**Table**: `settings`

**Colonnes**:
- id (INT, PRIMARY KEY, AUTO_INCREMENT)
- key (VARCHAR(255), UNIQUE, NOT NULL)
- value (TEXT)
- type (ENUM: string, integer, float, boolean, json, array)
- group (VARCHAR(100), DEFAULT 'general')
- description (TEXT, NULL)
- is_public (BOOLEAN, DEFAULT FALSE)
- created_at (TIMESTAMP)
- updated_at (TIMESTAMP)

**Index**:
- idx_settings_group
- idx_settings_key

#### 2. 002_create_translations_table.php
**Table**: `translations`

**Colonnes**:
- id (INT, PRIMARY KEY, AUTO_INCREMENT)
- language (VARCHAR(10), NOT NULL)
- key (VARCHAR(255), NOT NULL)
- value (TEXT, NOT NULL)
- module (VARCHAR(100), DEFAULT 'general')
- updated_by (INT, NULL)
- created_at (TIMESTAMP)
- updated_at (TIMESTAMP)

**Index**:
- UNIQUE: (language, key)
- idx_translations_language
- idx_translations_module

#### 3. 003_create_translation_history_table.php
**Table**: `translation_history`

**Colonnes**:
- id (INT, PRIMARY KEY, AUTO_INCREMENT)
- translation_id (INT, NOT NULL)
- old_value (TEXT)
- new_value (TEXT)
- changed_by (INT, NULL)
- created_at (TIMESTAMP)

**Index**:
- idx_translation_history_translation_id
- idx_translation_history_changed_by

#### 4. 004_create_webhooks_table.php
**Table**: `webhooks`

**Colonnes**:
- id (INT, PRIMARY KEY, AUTO_INCREMENT)
- name (VARCHAR(255), NOT NULL)
- url (VARCHAR(500), NOT NULL)
- events (JSON, NOT NULL)
- secret (VARCHAR(255), NULL)
- is_active (BOOLEAN, DEFAULT TRUE)
- headers (JSON, NULL)
- retry_count (INT, DEFAULT 3)
- timeout (INT, DEFAULT 30)
- created_at (TIMESTAMP)
- updated_at (TIMESTAMP)

**Index**:
- idx_webhooks_is_active

#### 5. 005_create_webhook_logs_table.php
**Table**: `webhook_logs`

**Colonnes**:
- id (INT, PRIMARY KEY, AUTO_INCREMENT)
- webhook_id (INT, NOT NULL)
- payload (TEXT)
- response (TEXT)
- http_code (INT)
- error (TEXT, NULL)
- duration (FLOAT)
- created_at (TIMESTAMP)

**Index**:
- idx_webhook_logs_webhook_id
- idx_webhook_logs_created_at

### Configuration

#### defaults.php
**Sections**:
1. **Site** (11 paramètres)
2. **Theme** (10 paramètres)
3. **API** (10 clés différentes)
4. **Mail** (8 paramètres SMTP)
5. **Languages** (8 langues supportées)
6. **Timezones** (9 zones recommandées)
7. **Webhook Events** (13 événements)
8. **Sidebar Types** (8 types)
9. **Date Formats** (6 formats)
10. **Time Formats** (4 formats)
11. **Mail Drivers** (6 drivers)

## Routes du Module

### Routes Admin (33 routes)

| Méthode | Route | Contrôleur | Action |
|---------|-------|------------|--------|
| GET | /admin/settings | SettingsController | index |
| GET | /admin/settings/site | SiteSettingsController | index |
| POST | /admin/settings/site/update | SiteSettingsController | update |
| POST | /admin/settings/site/upload-logo | SiteSettingsController | uploadLogo |
| POST | /admin/settings/site/upload-favicon | SiteSettingsController | uploadFavicon |
| GET | /admin/settings/theme | ThemeSettingsController | index |
| POST | /admin/settings/theme/update | ThemeSettingsController | update |
| POST | /admin/settings/theme/preview | ThemeSettingsController | preview |
| POST | /admin/settings/theme/reset | ThemeSettingsController | reset |
| GET | /admin/settings/api | ApiSettingsController | index |
| POST | /admin/settings/api/update | ApiSettingsController | update |
| POST | /admin/settings/api/test-connection | ApiSettingsController | testConnection |
| GET | /admin/settings/api/generate-key | ApiSettingsController | generateKey |
| GET | /admin/settings/mail | MailSettingsController | index |
| POST | /admin/settings/mail/update | MailSettingsController | update |
| POST | /admin/settings/mail/test | MailSettingsController | sendTest |
| GET | /admin/settings/translations | TranslationController | index |
| GET | /admin/settings/translations/create | TranslationController | create |
| POST | /admin/settings/translations/store | TranslationController | store |
| GET | /admin/settings/translations/{id}/edit | TranslationController | edit |
| POST | /admin/settings/translations/{id}/update | TranslationController | update |
| POST | /admin/settings/translations/{id}/delete | TranslationController | delete |
| POST | /admin/settings/translations/import | TranslationController | import |
| GET | /admin/settings/translations/export | TranslationController | export |
| GET | /admin/settings/translations/history | TranslationController | history |
| GET | /admin/settings/webhooks | WebhookController | index |
| GET | /admin/settings/webhooks/create | WebhookController | create |
| POST | /admin/settings/webhooks/store | WebhookController | store |
| GET | /admin/settings/webhooks/{id}/edit | WebhookController | edit |
| POST | /admin/settings/webhooks/{id}/update | WebhookController | update |
| POST | /admin/settings/webhooks/{id}/delete | WebhookController | delete |
| POST | /admin/settings/webhooks/{id}/test | WebhookController | test |
| GET | /admin/settings/webhooks/{id}/logs | WebhookController | logs |
| GET | /admin/settings/backup | SettingsController | backup |
| POST | /admin/settings/import | SettingsController | import |

### Routes API (3 routes)

| Méthode | Route | Contrôleur | Description |
|---------|-------|------------|-------------|
| GET | /api/settings/all | ApiController | Tous les paramètres |
| GET | /api/settings/{key} | ApiController | Un paramètre spécifique |
| POST | /api/settings/{key} | ApiController | Mettre à jour un paramètre |

## Permissions (6 permissions)

1. `manage_settings` - Accès complet
2. `manage_site_settings` - Gestion des paramètres du site
3. `manage_theme_settings` - Gestion du thème
4. `manage_api_settings` - Gestion des APIs
5. `manage_translations` - Gestion des traductions
6. `view_settings` - Lecture seule

## Statistiques du Module

- **Total de fichiers**: 27 fichiers
- **Lignes de code**: ~3500 lignes
- **Contrôleurs**: 8
- **Modèles**: 5
- **Services**: 1
- **Migrations**: 5
- **Routes**: 36
- **Permissions**: 6
- **Tables BDD**: 5

## Points d'Entrée

### Pour les Développeurs

```php
// Utiliser le service
$settings = app('settings');
$value = $settings->get('site_name');

// Utiliser le modèle directement
use Modules\Settings\Models\Setting;
$value = Setting::get('site_name', 'Default');

// Traductions
use Modules\Settings\Models\Translation;
$text = Translation::getTranslation('key', 'fr');

// Webhooks
use Modules\Settings\Models\Webhook;
$webhooks = Webhook::getByEvent('user.created');
```

### Pour les Administrateurs

```
Menu: Admin > Configuration Générale
Sous-menus:
- Vue d'ensemble
- Paramètres du site
- Thème & Apparence
- API & Services
- Configuration Mail
- Traductions
- Webhooks
```

## Prochaines Améliorations Possibles

1. **Interface de vues** (templates backend)
2. **Assets JS/CSS** pour l'interactivité
3. **Validation avancée** des formulaires
4. **Cache Redis** pour les paramètres
5. **Multi-tenant** support
6. **API GraphQL** en complément du REST
7. **Webhooks retry queue** avec système de queue
8. **Chiffrement** des paramètres sensibles
9. **Audit log** pour toutes les modifications
10. **Tests unitaires** et d'intégration

---

**Version**: 1.0.0
**Date**: 23 Janvier 2025
**Auteur**: SunuFramework Team
