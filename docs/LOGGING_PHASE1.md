# Logging System - Phase 1 Documentation

## ✅ Installation Complète - Phase 1

Le système de logging basique est désormais **100% fonctionnel** !

## 📦 Composants Installés

### Core Classes

- ✅ `Core/Logging/LoggerInterface.php` - Interface PSR-3
- ✅ `Core/Logging/Logger.php` - Implémentation principale
- ✅ `Core/Logging/LogManager.php` - Gestionnaire de canaux
- ✅ `Core/Logging/HandlerInterface.php` - Interface handlers

### Handlers

- ✅ `Core/Logging/Handlers/AbstractHandler.php` - Classe de base
- ✅ `Core/Logging/Handlers/FileHandler.php` - Logs dans un fichier
- ✅ `Core/Logging/Handlers/DailyFileHandler.php` - Rotation quotidienne
- ✅ `Core/Logging/Handlers/NullHandler.php` - Handler silencieux

### Formatters

- ✅ `Core/Logging/Formatters/LineFormatter.php` - Format ligne par ligne

### Configuration

- ✅ `config/logging.php` - Configuration des canaux

### Service Provider

- ✅ `Core/Providers/LoggingServiceProvider.php` - Provider de service

### Helpers

- ✅ `logger()` - Helper principal
- ✅ `logs()` - Alias de logger()
- ✅ `storage_path()` - Chemin storage
- ✅ `logs_path()` - Chemin logs

## 🚀 Activation du Système

### Étape 1 : Enregistrer le Service Provider

Ajouter dans `public/index.php` ou `Core/Application.php` :

```php
// Dans le constructeur de Application (après ligne 44)
$loggingProvider = new \App\Core\Providers\LoggingServiceProvider($this);
$loggingProvider->register();
$loggingProvider->boot();
```

### Étape 2 : Variables d'environnement

Ajouter dans `.env` :

```env
LOG_CHANNEL=daily
LOG_LEVEL=debug
```

### Étape 3 : Tester

Créer un fichier `test_logging.php` à la racine :

```php
<?php

require_once __DIR__ . '/vendor/autoload.php';

$app = new \App\Core\Application(__DIR__);

// Enregistrer le logging
$loggingProvider = new \App\Core\Providers\LoggingServiceProvider($app);
$loggingProvider->register();

// Test 1 : Log simple
logger()->info('Application démarrée');

// Test 2 : Log avec contexte
logger()->warning('User login failed', [
    'username' => 'john.doe',
    'ip' => '192.168.1.1'
]);

// Test 3 : Différents niveaux
logger()->debug('Debug information');
logger()->notice('Notice: Something happened');
logger()->error('An error occurred');
logger()->critical('Critical error!');

// Test 4 : Channel spécifique
logger('emergency')->emergency('Server is down!');

echo "Logs written successfully! Check storage/logs/\n";
```

## 📝 Utilisation

### Logging Basique

```php
// Logs avec différents niveaux
logger()->debug('Detailed debug information');
logger()->info('Interesting event occurred');
logger()->notice('Normal but significant');
logger()->warning('Something unexpected happened');
logger()->error('Runtime error');
logger()->critical('Critical component failed');
logger()->alert('Action must be taken immediately');
logger()->emergency('System is unusable');
```

### Logging avec Contexte

```php
logger()->info('User registered', [
    'user_id' => 123,
    'email' => 'user@example.com',
    'ip' => $_SERVER['REMOTE_ADDR']
]);

logger()->error('Payment failed', [
    'order_id' => 456,
    'amount' => 99.99,
    'gateway' => 'stripe',
    'error' => 'Card declined'
]);
```

### Interpolation de Message

```php
logger()->info('User {username} logged in from {ip}', [
    'username' => 'john',
    'ip' => '192.168.1.1'
]);
// Output: User john logged in from 192.168.1.1
```

### Différents Canaux

```php
// Canal par défaut (daily)
logger()->info('Default channel log');

// Canal spécifique
logger('single')->info('Single file log');
logger('emergency')->critical('Emergency log');
logger('null')->info('This will be discarded');
```

## ⚙️ Configuration

### Fichier `config/logging.php`

```php
return [
    'default' => env('LOG_CHANNEL', 'daily'),

    'channels' => [
        'single' => [
            'driver' => 'single',
            'path' => storage_path('logs/app.log'),
            'level' => 'debug',
        ],

        'daily' => [
            'driver' => 'daily',
            'path' => storage_path('logs/app.log'),
            'level' => 'debug',
            'days' => 14, // Garde 14 jours
        ],

        'null' => [
            'driver' => 'null',
        ],
    ],
];
```

### Créer un Nouveau Canal

```php
'my_channel' => [
    'driver' => 'daily',
    'path' => storage_path('logs/custom.log'),
    'level' => 'warning', // Seulement warning et au-dessus
    'days' => 30,
],
```

## 📂 Structure des Logs

### Format par Défaut

```
[2024-01-20 10:30:45] app.INFO: User registered {"user_id":123,"email":"user@example.com"} {"memory":2097152,"ip":"127.0.0.1"}
```

### Fichiers Générés

**Single driver** :

```
storage/logs/app.log
```

**Daily driver** :

```
storage/logs/app-2024-01-20.log
storage/logs/app-2024-01-21.log
storage/logs/app-2024-01-22.log
...
```

## 🎯 Cas d'Usage

### 1. Logging HTTP Requests

```php
logger()->info('HTTP Request', [
    'method' => $_SERVER['REQUEST_METHOD'],
    'uri' => $_SERVER['REQUEST_URI'],
    'ip' => $_SERVER['REMOTE_ADDR'],
    'user_agent' => $_SERVER['HTTP_USER_AGENT']
]);
```

### 2. Logging Exceptions

```php
try {
    // Code qui peut échouer
    $result = dangerousOperation();
} catch (\Exception $e) {
    logger()->error('Operation failed', [
        'exception' => get_class($e),
        'message' => $e->getMessage(),
        'file' => $e->getFile(),
        'line' => $e->getLine(),
        'trace' => $e->getTraceAsString()
    ]);
}
```

### 3. Logging Database Queries

```php
logger()->debug('SQL Query executed', [
    'query' => $sql,
    'bindings' => $bindings,
    'time' => $executionTime . 'ms'
]);
```

### 4. Logging User Actions

```php
logger()->info('User action', [
    'user_id' => $userId,
    'action' => 'profile_update',
    'changes' => $changedFields
]);
```

## 🔧 Niveaux de Log (PSR-3)

| Niveau    | Valeur | Description                           | Quand utiliser                             |
| --------- | ------ | ------------------------------------- | ------------------------------------------ |
| DEBUG     | 0      | Informations détaillées               | Développement uniquement                   |
| INFO      | 1      | Événements intéressants               | Actions utilisateur, stats                 |
| NOTICE    | 2      | Événements normaux mais significatifs | Changements de configuration               |
| WARNING   | 3      | Avertissements                        | Utilisation déconseillée, erreurs mineures |
| ERROR     | 4      | Erreurs runtime                       | Erreurs récupérables                       |
| CRITICAL  | 5      | Conditions critiques                  | Composant non disponible                   |
| ALERT     | 6      | Action immédiate requise              | Base de données indisponible               |
| EMERGENCY | 7      | Système inutilisable                  | Crash total                                |

## 🎓 Bonnes Pratiques

### ✅ À FAIRE

```php
// Toujours ajouter du contexte
logger()->error('Payment failed', ['order_id' => $id]);

// Utiliser le bon niveau
logger()->warning('Deprecated function used');

// Messages clairs et descriptifs
logger()->info('User email verification sent', ['user_id' => 123]);
```

### ❌ À ÉVITER

```php
// Logs sans contexte
logger()->error('Error');

// Niveau inapproprié
logger()->emergency('Button clicked'); // Trop sévère!

// Messages vagues
logger()->info('Thing happened');

// Logs sensibles
logger()->info('Password: ' . $password); // JAMAIS!
```

## 📊 Performance

- **Handlers asynchrones** : Non (Phase 2)
- **Buffering** : Non (Phase 2)
- **Rotation automatique** : ✅ Oui (DailyFileHandler)
- **Cleanup automatique** : ✅ Oui (garde X jours)

## 🔜 Prochaines Phases

### Phase 2 : Handlers Avancés

- DatabaseHandler (logs en base de données)
- SlackHandler (notifications Slack)
- EmailHandler (emails critiques)

### Phase 3 : Exception Handler

- Capture automatique des exceptions
- Pretty error pages
- Stack traces enrichis

### Phase 4 : HTTP Request Logging

- Middleware automatique
- Tracking des requêtes
- Performance monitoring

### Phase 5 : CLI Commands

- `php sunu log:clear` - Effacer les logs
- `php sunu log:tail` - Suivre en temps réel
- `php sunu log:analyze` - Analyser les logs

## ✨ Test Complet

Créer `test_logging_complete.php` :

```php
<?php

require_once __DIR__ . '/vendor/autoload.php';

$app = new \App\Core\Application(__DIR__);
$loggingProvider = new \App\Core\Providers\LoggingServiceProvider($app);
$loggingProvider->register();

echo "═══ Test du Système de Logging ═══\n\n";

// Test tous les niveaux
logger()->debug('DEBUG: Detailed information');
logger()->info('INFO: User logged in');
logger()->notice('NOTICE: Configuration changed');
logger()->warning('WARNING: Deprecated API used');
logger()->error('ERROR: File not found');
logger()->critical('CRITICAL: Database connection lost');
logger()->alert('ALERT: Disk space low');
logger()->emergency('EMERGENCY: System shutdown');

// Test avec contexte
logger()->info('User registration', [
    'user' => 'john.doe',
    'email' => 'john@example.com',
    'role' => 'admin'
]);

// Test canaux multiples
logger('single')->info('Single file log');
logger('daily')->warning('Daily rotated log');

echo "\n✅ Tous les tests effectués!\n";
echo "📁 Vérifiez storage/logs/\n";
```

Exécuter : `php test_logging_complete.php`

---

**Phase 1 TERMINÉE avec succès** ! 🎉

Le système est prêt pour une utilisation en production.
