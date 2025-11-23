# Guide d'Installation Rapide - Module Settings

## Prérequis

- PHP >= 8.0
- SunuFramework >= 2.0
- MySQL >= 5.7 ou MariaDB >= 10.2
- Extension PHP: PDO, JSON, cURL

## Installation en 3 Étapes

### Étape 1: Installation du Module

**Option A - Via l'Interface Admin (Recommandé)**

1. Connectez-vous à votre backoffice
2. Allez dans **Admin > Modules**
3. Cliquez sur **Upload Module**
4. Sélectionnez le fichier `Settings.zip`
5. Cliquez sur **Installer**

**Option B - Installation Manuelle**

```bash
# 1. Copier le module dans le dossier Modules
cd /path/to/sunuframework
cp -r path/to/Settings Modules/

# 2. Donner les permissions appropriées
chmod -R 755 Modules/Settings
chown -R www-data:www-data Modules/Settings
```

### Étape 2: Activer et Installer

**Via l'Interface Admin**

1. Dans **Admin > Modules**, trouvez "Settings"
2. Cliquez sur **Activer**
3. Cliquez sur **Installer** pour exécuter les migrations
4. Les paramètres par défaut seront automatiquement créés

**Via la Ligne de Commande**

```bash
# Activer le module
php bin/console module:enable Settings

# Installer (exécuter les migrations et seeders)
php bin/console module:install Settings
```

### Étape 3: Vérification

1. Allez dans **Admin > Configuration Générale**
2. Vous devriez voir le dashboard des paramètres
3. Vérifiez que toutes les sections sont accessibles:
   - ✅ Paramètres du site
   - ✅ Thème & Apparence
   - ✅ API & Services
   - ✅ Configuration Mail
   - ✅ Traductions
   - ✅ Webhooks

## Configuration Post-Installation

### 1. Paramètres du Site

```
Admin > Configuration Générale > Paramètres du site
```

- Modifiez le **nom du site**
- Uploadez votre **logo** et **favicon**
- Configurez la **langue** et le **fuseau horaire**

### 2. Thème

```
Admin > Configuration Générale > Thème & Apparence
```

- Choisissez votre **mode** (clair/sombre)
- Personnalisez les **couleurs**
- Configurez le **type de sidebar**

### 3. Configuration Mail

```
Admin > Configuration Générale > Configuration Mail
```

- Configurez votre serveur **SMTP**
- Testez l'envoi d'email

### 4. Permissions

Assurez-vous que les utilisateurs ont les bonnes permissions:

```
Admin > Rôles > Modifier un rôle
```

Sélectionnez les permissions appropriées:
- `manage_settings` - Gestion complète
- `manage_site_settings` - Paramètres du site uniquement
- `view_settings` - Lecture seule

## Création de Dossiers Requis

Le module créera automatiquement les dossiers nécessaires, mais vous pouvez les créer manuellement si besoin:

```bash
# Créer le dossier pour les uploads de logos
mkdir -p public/uploads/logos
chmod 755 public/uploads/logos

# Créer le dossier de cache
mkdir -p storage/cache/settings
chmod 755 storage/cache/settings
```

## Vérification de la Base de Données

Vérifiez que les tables ont été créées:

```sql
SHOW TABLES LIKE 'settings';
SHOW TABLES LIKE 'translations';
SHOW TABLES LIKE 'translation_history';
SHOW TABLES LIKE 'webhooks';
SHOW TABLES LIKE 'webhook_logs';
```

Toutes ces tables devraient exister.

## Premier Paramètre

Testez la configuration en modifiant un paramètre via le code:

```php
<?php

use Modules\Settings\Services\SettingsService;

$settings = new SettingsService();

// Modifier le nom du site
$settings->set('site_name', 'Mon Super Site', 'string', 'site');

// Vérifier
echo $settings->get('site_name'); // Affiche: Mon Super Site
```

## Première Traduction

Ajoutez votre première traduction:

```php
<?php

use Modules\Settings\Models\Translation;

// Créer une traduction
Translation::setTranslation('welcome.message', 'Bienvenue sur mon site!', 'fr', 'general');

// Récupérer
$text = Translation::getTranslation('welcome.message', 'fr');
echo $text; // Affiche: Bienvenue sur mon site!
```

## Premier Webhook

Créez votre premier webhook via l'interface:

1. Allez dans **Admin > Configuration Générale > Webhooks**
2. Cliquez sur **Nouveau Webhook**
3. Remplissez les informations:
   - **Nom**: Mon premier webhook
   - **URL**: https://webhook.site/unique-url
   - **Événements**: user.created
4. Cliquez sur **Tester** pour vérifier la connexion

## Dépannage

### Erreur: "Tables not found"

```bash
# Réexécuter les migrations
php bin/console module:install Settings --force
```

### Erreur: "Permission denied" lors de l'upload

```bash
# Vérifier les permissions
chmod 755 public/uploads/logos
chown -R www-data:www-data public/uploads
```

### Erreur: "Settings service not found"

Vérifiez que le module est bien activé:

```bash
php bin/console module:list
```

Si "Settings" n'apparaît pas comme activé:

```bash
php bin/console module:enable Settings
```

### Les webhooks ne se déclenchent pas

1. Vérifiez que le webhook est **actif**
2. Vérifiez les **logs** dans `Admin > Webhooks > Logs`
3. Testez manuellement avec le bouton **Test**

## Mise à Jour

Pour mettre à jour le module vers une nouvelle version:

```bash
# 1. Sauvegarder la configuration actuelle
Admin > Configuration Générale > Backup

# 2. Désactiver le module
php bin/console module:disable Settings

# 3. Remplacer les fichiers
cp -r path/to/new-Settings Modules/Settings

# 4. Réactiver et migrer
php bin/console module:enable Settings
php bin/console module:migrate Settings

# 5. Restaurer la configuration si nécessaire
Admin > Configuration Générale > Import
```

## Désinstallation

**⚠️ ATTENTION**: Cette opération supprimera toutes vos données de configuration!

```bash
# Via l'interface
Admin > Modules > Settings > Désinstaller

# Via la ligne de commande
php bin/console module:uninstall Settings
```

Pour conserver les données tout en désactivant le module:

```bash
php bin/console module:disable Settings
```

## Support

Si vous rencontrez des problèmes:

1. Consultez la [Documentation complète](README.md)
2. Vérifiez les [Issues GitHub](https://github.com/sunuframework/settings/issues)
3. Contactez le support: support@sunuframework.local

## Prochaines Étapes

Maintenant que le module est installé:

1. 📖 Lisez la [Documentation complète](README.md)
2. 🎨 Personnalisez votre thème
3. 🌍 Configurez vos traductions
4. 🔗 Paramétrez vos webhooks
5. 📧 Testez l'envoi d'emails

Félicitations ! Votre module Settings est prêt à l'emploi ! 🎉
