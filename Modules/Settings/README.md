# Module Settings - Configuration Générale

## Description

Le module **Settings** est un module complet de gestion de configuration pour votre backoffice SunuFramework. Il permet de gérer tous les paramètres de votre application de manière centralisée, intuitive et sécurisée.

## Fonctionnalités

### 1. Paramètres du Site
- **Nom du site** : Configuration du nom affiché sur tout le site
- **Logo et Favicon** : Upload et gestion des images de marque
- **Description** : Description courte pour le SEO
- **Langue par défaut** : Sélection de la langue principale
- **Fuseau horaire** : Configuration du timezone par défaut
- **Formats** : Date, heure, pagination

### 2. Gestion des Thèmes
- **Mode clair/sombre** : Basculement entre les modes d'affichage
- **Palette de couleurs** : Personnalisation complète des couleurs
  - Couleur primaire
  - Couleur secondaire
  - Couleur de succès
- **Layout** : Configuration de la disposition
  - Type de sidebar (compact, modern, material)
  - Type d'icônes (stroke, fill)
  - Direction (LTR/RTL)
- **Typographie** : Gestion des polices et tailles

### 3. Paramètres d'API
- **OpenAI** : Configuration de la clé API pour l'IA
- **Google** : Clés pour Maps, Analytics, etc.
- **Stripe** : Intégration de paiement
- **PayPal** : Alternative de paiement
- **SMS** : Services d'envoi de SMS
- **Test de connexion** : Vérification en temps réel de chaque API

### 4. Configuration Mail (SMTP)
- **Driver** : SMTP, Sendmail, Mailgun, etc.
- **Hôte et Port** : Configuration du serveur
- **Authentification** : Username/Password
- **Chiffrement** : TLS/SSL
- **Adresse d'expéditeur** : From address et name
- **Test d'envoi** : Envoi d'email de test

### 5. Multilingue
- **Gestion des traductions** : Interface CRUD complète
- **Fichiers JSON** : Import/Export de traductions
- **Langues supportées** : Français, Anglais, Arabe, Espagnol, Allemand
- **Historique** : Suivi de toutes les modifications
- **Modules** : Organisation par module (admin, auth, blog, etc.)

### 6. Webhooks
- **Création de webhooks** : Configuration d'URLs de callback
- **Événements** : Sélection des événements déclencheurs
  - user.created, user.updated, user.deleted
  - order.created, order.completed
  - payment.success
- **Sécurité** : Secret et signature HMAC
- **Headers personnalisés** : Configuration des en-têtes HTTP
- **Retry** : Nombre de tentatives en cas d'échec
- **Logs** : Historique complet des appels
- **Test** : Test immédiat du webhook

## Structure des Fichiers

```
Modules/Settings/
├── Controllers/
│   ├── SettingsController.php          # Vue d'ensemble et import/export
│   ├── SiteSettingsController.php      # Paramètres du site
│   ├── ThemeSettingsController.php     # Gestion du thème
│   ├── ApiSettingsController.php       # Configuration des APIs
│   ├── MailSettingsController.php      # Configuration mail
│   ├── TranslationController.php       # Gestion des traductions
│   ├── WebhookController.php           # Gestion des webhooks
│   └── ApiController.php               # API REST du module
├── Models/
│   ├── Setting.php                     # Modèle principal des paramètres
│   ├── Translation.php                 # Modèle de traduction
│   ├── TranslationHistory.php          # Historique des modifications
│   ├── Webhook.php                     # Modèle webhook
│   └── WebhookLog.php                  # Logs des webhooks
├── Services/
│   └── SettingsService.php             # Service de gestion des paramètres
├── Database/
│   ├── Migrations/
│   │   ├── 001_create_settings_table.php
│   │   ├── 002_create_translations_table.php
│   │   ├── 003_create_translation_history_table.php
│   │   ├── 004_create_webhooks_table.php
│   │   └── 005_create_webhook_logs_table.php
│   └── Seeders/
│       └── DefaultSettingsSeeder.php   # Données par défaut
├── Config/
│   └── defaults.php                    # Configuration par défaut
├── SettingsModule.php                  # Fichier principal du module
├── module.json                         # Métadonnées du module
└── README.md                           # Cette documentation
```

## Installation

### 1. Installation via le gestionnaire de modules

```bash
# Depuis l'interface d'administration
Admin > Modules > Upload Module > Sélectionner le fichier ZIP du module
```

### 2. Installation manuelle

```bash
# Copier le module dans le dossier Modules
cp -r Settings /path/to/sunuframework/Modules/

# Activer le module
php bin/console module:enable Settings

# Exécuter les migrations
php bin/console module:install Settings
```

### 3. Configuration initiale

Les paramètres par défaut seront automatiquement créés lors de l'installation via le seeder `DefaultSettingsSeeder`.

## Utilisation

### Accès aux Paramètres dans le Code

```php
use Modules\Settings\Services\SettingsService;

// Récupérer une instance du service
$settings = new SettingsService();

// OU via l'application
$settings = app('settings');

// Récupérer un paramètre
$siteName = $settings->get('site_name', 'Default Name');

// Définir un paramètre
$settings->set('site_name', 'Mon Site', 'string', 'site');

// Récupérer tous les paramètres d'un groupe
$siteSettings = $settings->getByGroup('site');

// Vérifier l'existence d'un paramètre
if ($settings->has('maintenance_mode')) {
    // ...
}

// Supprimer un paramètre
$settings->remove('old_setting');
```

### Utilisation des Traductions

```php
use Modules\Settings\Models\Translation;

// Récupérer une traduction
$text = Translation::getTranslation('welcome.message', 'fr');

// Définir une traduction
Translation::setTranslation('welcome.message', 'Bienvenue!', 'fr', 'general');

// Récupérer toutes les traductions d'une langue
$translations = Translation::getByLanguage('fr');
```

### Webhooks

```php
use Modules\Settings\Models\Webhook;

// Récupérer les webhooks actifs pour un événement
$webhooks = Webhook::getByEvent('user.created');

// Envoyer une notification via webhook
foreach ($webhooks as $webhook) {
    $webhook->send([
        'event' => 'user.created',
        'data' => [
            'user_id' => 123,
            'email' => 'user@example.com'
        ]
    ]);
}
```

## API REST

Le module expose une API REST pour la gestion des paramètres:

### Endpoints Disponibles

#### Récupérer tous les paramètres
```http
GET /api/settings/all
```

**Réponse:**
```json
{
  "success": true,
  "data": {
    "site_name": "SunuFramework",
    "theme_mode": "light",
    ...
  }
}
```

#### Récupérer un paramètre spécifique
```http
GET /api/settings/{key}
```

**Exemple:**
```http
GET /api/settings/site_name
```

**Réponse:**
```json
{
  "success": true,
  "key": "site_name",
  "value": "SunuFramework"
}
```

#### Mettre à jour un paramètre
```http
POST /api/settings/{key}
Content-Type: application/json

{
  "value": "Nouveau Nom",
  "type": "string",
  "group": "site"
}
```

**Réponse:**
```json
{
  "success": true,
  "message": "Setting updated"
}
```

## Interface Utilisateur

### Navigation

Le module ajoute automatiquement un menu "Configuration Générale" dans le sidebar avec les sous-menus suivants:

- **Vue d'ensemble** : Dashboard avec statistiques
- **Paramètres du site** : Configuration générale du site
- **Thème & Apparence** : Personnalisation visuelle
- **API & Services** : Gestion des clés API
- **Configuration Mail** : Paramètres SMTP
- **Traductions** : Gestion multilingue
- **Webhooks** : Configuration des webhooks

### Routes Admin

| Route | Méthode | Description |
|-------|---------|-------------|
| `/admin/settings` | GET | Vue d'ensemble |
| `/admin/settings/site` | GET/POST | Paramètres du site |
| `/admin/settings/theme` | GET/POST | Paramètres du thème |
| `/admin/settings/api` | GET/POST | Paramètres API |
| `/admin/settings/mail` | GET/POST | Configuration mail |
| `/admin/settings/translations` | GET | Liste des traductions |
| `/admin/settings/webhooks` | GET | Liste des webhooks |
| `/admin/settings/backup` | GET | Exporter la configuration |
| `/admin/settings/import` | POST | Importer la configuration |

## Permissions

Le module définit les permissions suivantes:

- `manage_settings` : Accès complet à la configuration
- `manage_site_settings` : Gestion des paramètres du site
- `manage_theme_settings` : Gestion du thème
- `manage_api_settings` : Gestion des APIs
- `manage_translations` : Gestion des traductions
- `view_settings` : Voir les paramètres (lecture seule)

## Sécurité

### Bonnes Pratiques

1. **Clés API** : Toujours masquer les clés API dans l'interface (type password)
2. **Paramètres publics** : Utiliser le flag `is_public` pour contrôler la visibilité
3. **Validation** : Toutes les entrées sont validées côté serveur
4. **Upload** : Les fichiers (logo, favicon) sont validés par type MIME
5. **Webhooks** : Utilisation de HMAC SHA-256 pour la signature
6. **Historique** : Toutes les modifications de traductions sont enregistrées

### Chiffrement

Les paramètres sensibles (mots de passe, clés API) peuvent être chiffrés en base de données. Pour activer le chiffrement:

```php
$settings->set('mail_password', 'secret', 'string', 'mail', true); // encrypted
```

## Sauvegarde et Restauration

### Export de Configuration

```php
// Via le service
$json = $settings->export();
file_put_contents('backup.json', $json);

// Via l'interface
Admin > Settings > Backup (télécharge un fichier JSON)
```

### Import de Configuration

```php
// Via le service
$json = file_get_contents('backup.json');
$settings->import($json);

// Via l'interface
Admin > Settings > Import > Sélectionner le fichier JSON
```

## Nettoyage et Maintenance

### Nettoyer les logs de webhooks

```php
use Modules\Settings\Models\WebhookLog;

// Supprimer les logs de plus de 30 jours
WebhookLog::cleanup(30);
```

### Optimisation du Cache

Le service SettingsService met en cache les paramètres en mémoire pour optimiser les performances. Pour vider le cache:

```php
$settings->clearCache();
```

## Dépannage

### Les paramètres ne sont pas sauvegardés

- Vérifier les permissions de la base de données
- Vérifier que les migrations ont été exécutées
- Consulter les logs dans `storage/logs/`

### Les webhooks ne fonctionnent pas

- Vérifier que l'URL est accessible
- Tester la connexion avec le bouton "Test"
- Consulter les logs du webhook
- Vérifier le timeout et le nombre de retry

### Les traductions ne s'affichent pas

- Vérifier que la langue est activée
- Vider le cache des vues
- Vérifier que les fichiers JSON sont valides

## Support

Pour toute question ou problème:

- **Documentation** : https://docs.sunuframework.local
- **Issues** : https://github.com/sunuframework/settings/issues
- **Email** : support@sunuframework.local

## Licence

MIT License - Copyright (c) 2025 SunuFramework Team

## Auteurs

- SunuFramework Team
- Contributeurs : Voir CONTRIBUTORS.md

## Changelog

### Version 1.0.0 (2025-01-23)

- ✨ Release initiale
- ✅ Gestion complète des paramètres (CRUD)
- ✅ Thèmes et couleurs personnalisables
- ✅ Configuration API et Mail
- ✅ Système multilingue avec historique
- ✅ Webhooks avec logs
- ✅ Import/Export de configuration
- ✅ API REST complète
- ✅ Interface utilisateur intuitive
- ✅ Documentation complète
