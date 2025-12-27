# Guide de Configuration des Environnements

## Vue d'ensemble

SunuFramework2 supporte maintenant plusieurs environnements pour faciliter le développement, le test et le déploiement en production. Cette fonctionnalité vous permet de configurer différents paramètres selon l'environnement dans lequel votre application s'exécute.

## Environnements Supportés

- **development** (dev, local) - Environnement de développement local
- **staging** (stage) - Environnement de pré-production pour les tests
- **production** (prod) - Environnement de production
- **testing** (test) - Environnement pour les tests automatisés

## Configuration Rapide

### 1. Définir l'Environnement

Dans votre fichier `.env`, ajoutez ou modifiez :

```env
APP_ENV=development
```

### 2. Utiliser les Fichiers d'Exemple

Nous avons créé des fichiers d'exemple pré-configurés pour chaque environnement :

- `.env.development` - Configuration pour le développement
- `.env.staging` - Configuration pour le staging
- `.env.production` - Configuration pour la production
- `.env.example` - Template général

**Pour commencer rapidement :**

```bash
# Copier le fichier d'environnement souhaité
cp .env.development .env

# Ou pour la production
cp .env.production .env
```

## Variables d'Environnement

### Variables Principales

```env
# Application
APP_NAME=SunuFramework
APP_ENV=development          # development, staging, production, testing
APP_URL=http://localhost/sunuframework2
APP_DEBUG=true              # true en dev, false en production
APP_KEY=                    # Clé de chiffrement (générer une clé unique)

# Base de données
DB_CONNECTION=mysql
DB_HOST=localhost
DB_PORT=3306
DB_DATABASE=sunuframework2_dev
DB_USERNAME=root
DB_PASSWORD=

# Features spécifiques à l'environnement
SHOW_ERROR_DETAILS=true     # Afficher les détails des erreurs
QUERY_LOG_ENABLED=true      # Logger les requêtes SQL
FORCE_HTTPS=false           # Forcer HTTPS (true en production)
MAINTENANCE_MODE=false      # Mode maintenance
```

## Fonctions Helpers Disponibles

### Vérifier l'Environnement

```php
// Obtenir l'environnement actuel
$env = environment(); // Retourne 'development', 'production', etc.

// Vérifier si on est en développement
if (isDevelopment()) {
    // Code uniquement pour le développement
    echo "Mode développement activé";
}

// Vérifier si on est en production
if (isProduction()) {
    // Code uniquement pour la production
    error_reporting(0);
}

// Vérifier si on est en staging
if (isStaging()) {
    // Code pour le staging
}

// Vérifier si on est en test
if (isTesting()) {
    // Code pour les tests
}

// Vérifier si le mode debug est activé
if (isDebugMode()) {
    dump($data);
}
```

### Exemples d'Utilisation

#### Affichage Conditionnel d'Erreurs

```php
// Dans un contrôleur
try {
    // Votre code
} catch (Exception $e) {
    if (isDevelopment()) {
        // Afficher tous les détails en développement
        dd($e->getMessage(), $e->getTrace());
    } else {
        // Message générique en production
        echo "Une erreur s'est produite";
        error_log($e->getMessage());
    }
}
```

#### Configuration de Services

```php
// Configurer un service différemment selon l'environnement
$config = [
    'timeout' => isDevelopment() ? 30 : 10,
    'cache' => isProduction(),
    'debug' => isDebugMode()
];
```

#### Logging Conditionnel

```php
// Logger uniquement en développement et staging
if (isDevelopment() || isStaging()) {
    error_log("Query executed: " . $query);
}
```

## Configurations par Environnement

### Development (Développement)

**Caractéristiques :**
- Affichage complet des erreurs
- Logging verbeux
- Cache désactivé
- Gateway SMS en mode mock
- Email vers Mailtrap

**Configuration recommandée :**
```env
APP_ENV=development
APP_DEBUG=true
SHOW_ERROR_DETAILS=true
QUERY_LOG_ENABLED=true
SMS_DEFAULT_GATEWAY=mock
MAIL_HOST=smtp.mailtrap.io
```

### Staging (Pré-production)

**Caractéristiques :**
- Configuration similaire à la production
- Affichage des erreurs pour le debugging
- Base de données séparée
- Gateway SMS en mode test

**Configuration recommandée :**
```env
APP_ENV=staging
APP_DEBUG=true
SHOW_ERROR_DETAILS=true
DB_DATABASE=sunuframework2_staging
SMS_DEFAULT_GATEWAY=test
```

### Production

**Caractéristiques :**
- Erreurs cachées aux utilisateurs
- Logging minimal (warnings et errors uniquement)
- Cache activé
- HTTPS forcé
- Credentials sécurisés

**Configuration recommandée :**
```env
APP_ENV=production
APP_DEBUG=false
SHOW_ERROR_DETAILS=false
QUERY_LOG_ENABLED=false
FORCE_HTTPS=true
DB_PASSWORD=STRONG_PASSWORD_HERE
```

## Base de Données par Environnement

Il est **fortement recommandé** d'utiliser des bases de données différentes pour chaque environnement :

```env
# Development
DB_DATABASE=sunuframework2_dev

# Staging
DB_DATABASE=sunuframework2_staging

# Production
DB_DATABASE=sunuframework2_prod
```

**Avantages :**
- Évite de corrompre les données de production pendant le développement
- Permet de tester les migrations en toute sécurité
- Facilite le rollback en cas de problème

## Bonnes Pratiques

### 1. Ne JAMAIS Committer le Fichier .env

Le fichier `.env` contient des informations sensibles (mots de passe, clés API). Ajoutez-le à `.gitignore` :

```gitignore
.env
.env.local
.env.*.local
```

### 2. Utiliser APP_KEY Unique

Générez une clé unique pour chaque environnement :

```php
// Générer une clé sécurisée
$key = bin2hex(random_bytes(32));
```

### 3. Mots de Passe Forts en Production

```env
# ❌ Mauvais
DB_PASSWORD=password123

# ✅ Bon
DB_PASSWORD=Kx9#mL2$pR7@nQ4!vB8&
```

### 4. Désactiver le Debug en Production

```env
APP_DEBUG=false
SHOW_ERROR_DETAILS=false
```

### 5. Forcer HTTPS en Production

```env
FORCE_HTTPS=true
```

### 6. Utiliser Redis en Production pour les Queues

```env
# Development
QUEUE_CONNECTION=database

# Production
QUEUE_CONNECTION=redis
```

## Utilisation dans le Code

### Exemple Complet : Envoyer un Email

```php
class EmailService
{
    public function send($to, $subject, $message)
    {
        // Configuration selon l'environnement
        if (isDevelopment()) {
            // En dev, envoyer à Mailtrap
            $config = [
                'host' => 'smtp.mailtrap.io',
                'to' => 'test@mailtrap.io' // Intercepter tous les emails
            ];
            echo "EMAIL INTERCEPTÉ (Dev Mode): To: $to\n";
        } elseif (isStaging()) {
            // En staging, envoyer aux testeurs
            $config = [
                'host' => env('MAIL_HOST'),
                'to' => 'qa-team@company.com'
            ];
        } else {
            // En production, envoyer normalement
            $config = [
                'host' => env('MAIL_HOST'),
                'to' => $to
            ];
        }

        // Envoyer l'email
        $this->mailer->send($config);
    }
}
```

### Exemple : Configuration SMS

```php
class SmsService
{
    public function getGateway()
    {
        if (isDevelopment()) {
            // Mode mock en développement
            return new MockSmsGateway();
        } elseif (isStaging()) {
            // Gateway de test en staging
            return new TestSmsGateway();
        } else {
            // Gateway réel en production
            return new ProductionSmsGateway(
                env('SMS_API_KEY'),
                env('SMS_SENDER_ID')
            );
        }
    }
}
```

## Déploiement

### Checklist de Déploiement en Production

1. ✅ Copier `.env.production` vers `.env`
2. ✅ Modifier `APP_ENV=production`
3. ✅ Définir `APP_DEBUG=false`
4. ✅ Générer une `APP_KEY` unique
5. ✅ Configurer les credentials de base de données
6. ✅ Définir `FORCE_HTTPS=true`
7. ✅ Vérifier les credentials SMS/Email
8. ✅ Tester la connexion à la base de données
9. ✅ Exécuter les migrations
10. ✅ Tester l'application

### Script de Déploiement

```bash
#!/bin/bash

# deploy-production.sh
cp .env.production .env
echo "Veuillez configurer les credentials dans .env"
echo "Appuyez sur Entrée quand c'est fait..."
read

# Vérifier la configuration
php artisan config:check  # Si vous avez cette commande

# Exécuter les migrations
php artisan migrate --force

# Clear cache
php artisan cache:clear

echo "Déploiement terminé!"
```

## Troubleshooting

### Problème : L'environnement ne change pas

**Solution :** Vérifiez que le fichier `.env` est bien lu :

```php
echo environment();  // Affiche l'environnement actuel
var_dump(env('APP_ENV'));  // Affiche la valeur brute
```

### Problème : Les erreurs s'affichent en production

**Solution :** Vérifiez ces variables :

```env
APP_ENV=production
APP_DEBUG=false
SHOW_ERROR_DETAILS=false
```

### Problème : La base de données ne se connecte pas

**Solution :** Vérifiez les credentials dans `.env` :

```bash
# Tester la connexion MySQL
mysql -u DB_USERNAME -p -h DB_HOST DB_DATABASE
```

## Support

Pour plus d'informations ou en cas de problème, consultez :
- Documentation : `/docs`
- Issues : GitHub Issues
- Email : support@sunuframework.com
