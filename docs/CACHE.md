# Système de Cache - Documentation

Le système de cache de SunuFramework offre une solution modulaire et ultra-performante pour gérer le cache de votre application avec support de quatre drivers : Filesystem, Redis, Memcached et APCu.

## Table des Matières

1. [Installation](#installation)
2. [Configuration](#configuration)
3. [Utilisation de Base](#utilisation-de-base)
4. [Drivers Disponibles](#drivers-disponibles)
5. [Opérations Avancées](#opérations-avancées)
6. [Gestion Backoffice](#gestion-backoffice)
7. [Bonnes Pratiques](#bonnes-pratiques)
8. [Dépannage](#dépannage)

---

## Installation

### Prérequis Système

- PHP 8.0 ou supérieur
- Extensions PHP selon les drivers utilisés :
  - **Filesystem** : Aucune extension requise (fonctionne partout)
  - **Redis** : composer require predis/predis
  - **Memcached** : Extension PECL `memcached`
  - **APCu** : Extension `apcu` et `apc.enabled=1` dans php.ini

### Installation via Composer

Pour utiliser Redis, installez la bibliothèque Predis :

```bash
composer require predis/predis
```

### Installation des Extensions PHP

**Memcached (sur Ubuntu/Debian) :**

```bash
sudo apt-get install php-memcached
sudo service php-fpm restart
```

**APCu (sur Ubuntu/Debian) :**

```bash
sudo apt-get install php-apcu
echo "apc.enabled=1" | sudo tee -a /etc/php/8.1/fpm/php.ini
sudo service php-fpm restart
```

### Configuration Serveur

**Redis :**

```bash
# Installation
sudo apt-get install redis-server

# Démarrer le service
sudo systemctl start redis
sudo systemctl enable redis

# Tester
redis-cli ping  # Devrait répondre "PONG"
```

**Memcached :**

```bash
# Installation
sudo apt-get install memcached

# Démarrer le service
sudo systemctl start memcached
sudo systemctl enable memcached
```

---

## Configuration

### Migration Base de Données

Exécutez la migration pour créer la table de configuration :

```bash
php sunu migrate --up
```

Cela créera la table `cache_config` avec une configuration par défaut (Filesystem).

### Configuration depuis le Backoffice

1. Accédez à `/admin/cache` dans votre navigateur
2. Sélectionnez le driver souhaité
3. Configurez les paramètres spécifiques au driver
4. Cliquez "Tester la connexion" pour vérifier
5. Sauvegardez la configuration

### Configuration Manuelle (Base de Données)

Vous pouvez également modifier directement la table `cache_config` :

```sql
UPDATE cache_config SET
    driver = 'redis',
    enabled = 1,
    redis_host = '127.0.0.1',
    redis_port = 6379
WHERE id = 1;
```

---

## Utilisation de Base

### Avec les Helpers

```php
// Stocker une valeur dans le cache (60 secondes)
cache('user:1', ['name' => 'John', 'email' => 'john@example.com'], 60);

// Récupérer une valeur du cache
$user = cache('user:1');

// Vérifier si une clé existe
if (cache_has('user:1')) {
    echo "Le cache existe!";
}

// Supprimer une clé
cache_forget('user:1');

// Vider tout le cache
cache_flush();
```

### Remember Pattern

Le pattern "remember" permet d'exécuter une fonction coûteuse uniquement si la valeur n'est pas en cache :

```php
$users = cache_remember('all_users', function() {
    // Cette requête ne s'exécute que si le cache n'existe pas
    return Database::getInstance()->query("SELECT * FROM users");
}, 3600);
```

### Avec le CacheManager

```php
use App\Core\Cache\CacheManager;

$cache = CacheManager::getInstance();

// Set/Get
$cache->set('key', 'value', 300);
$value = $cache->get('key', 'default_value');

// Multiple operations
$cache->setMultiple([
    'key1' => 'value1',
    'key2' => 'value2'
], 600);

$values = $cache->getMultiple(['key1', 'key2']);

// Increment/Decrement (atomique)
$cache->increment('page_views');
$cache->decrement('stock:product:5', 1);

// Current driver name and stats
echo $cache->getDriverName(); // "redis", "filesystem", etc.
$stats = $cache->getStats();
print_r($stats);
```

---

## Drivers Disponibles

### 1. Filesystem Driver

**Avantages :**

- Aucune dépendance externe
- Fonctionne partout
- Ideal pour développement

**Inconvénients :**

- Plus lent que les autres drivers
- Limité par les performances disque

**Configuration :**

```php
driver = 'filesystem'
filesystem_path = 'storage/cache'
```

**Cas d'usage :**

- Environnements sans Redis/Memcached
- Développement local
- Cache de fichiers volumineux

---

### 2. Redis Driver

**Avantages :**

- Très haute performance
- Support des structures de données avancées
- Persistance optionnelle
- Opérations atomiques

**Inconvénients :**

- Nécessite un serveur Redis
- Consommation mémoire à surveiller

**Configuration :**

```php
driver = 'redis'
redis_host = '127.0.0.1'
redis_port = 6379
redis_password = null  // Optionnel
redis_database = 0     // 0-15
```

**Cas d'usage :**

- Applications haute performance
- Cache de sessions
- Rate limiting
- Queues et jobs

**Exemple avancé :**

```php
cache_remember('trending_products', function() {
    return Product::where('views', '>', 1000)
                  ->orderBy('views', 'DESC')
                  ->limit(10)
                  ->all();
}, 300); // 5 minutes
```

---

### 3. Memcached Driver

**Avantages :**

- Excellente performance
- Support multi-serveurs (distribution)
- Compression automatique

**Inconvénients :**

- Nécessite extension PECL
- Pas de persistance

**Configuration :**

```php
driver = 'memcached'
memcached_servers = [
    ['host' => '127.0.0.1', 'port' => 11211],
    ['host' => '127.0.0.2', 'port' => 11211]  // Optionnel: serveur secondaire
]
```

**Cas d'usage :**

- Applications scalables
- Cache distribué
- Gros volumes de données

---

### 4. APCu Driver

**Avantages :**

- Ultra-rapide (mémoire partagée PHP)
- Aucun réseau impliqué
- Idéal pour petits volumes

**Inconvénients :**

- Mémoire limitée
- Pas de distribution
- Réinitialisé au redémarrage PHP-FPM

**Configuration :**

```php
driver = 'apcu'
apcu_enabled = true
```

**Cas d'usage :**

- Configuration applicative
- Métadonnées légères
- Compteurs de visites
- Cache de résultats de calculs

---

## Opérations Avancées

### Cache Multi-Clés

```php
// Set multiple
cache()->setMultiple([
    'user:1' => $user1,
    'user:2' => $user2,
    'user:3' => $user3
], 600);

// Get multiple
$users = cache()->getMultiple(['user:1', 'user:2', 'user:3']);

// Delete multiple
cache()->deleteMultiple(['user:1', 'user:2']);
```

### Increment/Decrement Atomique

Utile pour compteurs, rate limiting, stocks :

```php
// Incrémenter les vues d'une page
cache()->increment('page:home:views');

// Rate limiting
$requests = cache()->increment('api:user:123:requests');
if ($requests > 100) {
    throw new Exception('Rate limit exceeded');
}

// Décrémenter un stock
cache()->decrement('stock:product:42', 1);
```

### Namespacing avec Préfixes

Le préfixe global est défini dans la configuration, mais vous pouvez aussi utiliser des préfixes dans vos clés :

```php
// Cache par environnement
cache('prod:user:1', $data);
cache('staging:user:1', $data);

// Cache par module
cache('auth:sessions:xyz', $session);
cache('blog:post:5', $post);
```

---

## Gestion Backoffice

### Configuration via Interface

1. **Accès** : `/admin/cache`
2. **Onglets disponibles** :
   - Configuration générale (driver, préfixe, TTL par défaut)
   - Filesystem (chemin de stockage)
   - Redis (host, port, password, database)
   - Memcached (liste des serveurs)
   - APCu (activation simple)

### Tests de Connectivité

Sur chaque onglet driver, un bouton "Tester la connexion" permet de :

- Vérifier que le driver est disponible
- Tester une opération read/write
- Afficher les erreurs de configuration

### Statistiques

Page `/admin/cache/stats` affiche :

- Driver actif
- Nombre de clés/fichiers
- Taille mémoire utilisée
- Taux de hit (pour APCu)
- Informations spécifiques au driver

### Vidage du Cache

- **Vider tout** : Bouton "Vider le cache" (utilise `clear()`)
- **Par préfixe** : Possible via code uniquement

---

## Bonnes Pratiques

### Choix du Driver

| Critère         | Filesystem | Redis      | Memcached  | APCu       |
| --------------- | ---------- | ---------- | ---------- | ---------- |
| **Performance** | ⭐⭐       | ⭐⭐⭐⭐⭐ | ⭐⭐⭐⭐⭐ | ⭐⭐⭐⭐⭐ |
| **Scalabilité** | ⭐         | ⭐⭐⭐⭐   | ⭐⭐⭐⭐⭐ | ⭐         |
| **Simplicité**  | ⭐⭐⭐⭐⭐ | ⭐⭐⭐     | ⭐⭐       | ⭐⭐⭐⭐   |
| **Persistance** | ⭐⭐⭐⭐⭐ | ⭐⭐⭐⭐   | ⭐         | ⭐         |

### Stratégies de TTL

```php
// Court terme (1-5 minutes) : Données changeantes
cache('trending_now', $data, 60);

// Moyen terme (1-24 heures) : Résultats de requêtes
cache('user_dashboard:123', $dashboard, 3600);

// Long terme (jours/semaines) : Données quasi-statiques
cache('categories', $categories, 86400 * 7);

// Pas d'expiration : Configuration app
cache('app_settings', $settings, null);
```

### Gestion des Erreurs

Le système inclut un fallback automatique sur Filesystem si le driver échoue :

```php
// Si Redis crash, le système bascule automatiquement sur Filesystem
// Aucune erreur lancée, juste un log
cache('key', 'value'); // Continue de fonctionner
```

### Patterns d'Invalidation

```php
// 1. Invalidation manuelle
function updateUser($id, $data) {
    User::update($id, $data);
    cache_forget("user:{$id}");
}

// 2. TTL court pour données changeantes
cache('live_scores', $scores, 30); // 30 secondes

// 3. Versioning
$version = cache('data_version') ?? 1;
cache("products:v{$version}", $products, 3600);

// Quand les données changent :
cache('data_version', $version + 1);
```

---

## Dépannage

### Problèmes Courants

**1. Redis : "Connection refused"**

```bash
# Vérifier que Redis tourne
sudo systemctl status redis

# Vérifier le port
redis-cli ping

# Check la config PHP
grep redis php.ini
```

**2. Memcached : Extension not loaded**

```bash
# Installer l'extension
sudo apt-get install php-memcached

# Vérifier
php -m | grep memcached
```

**3. APCu : Cache not working**

```bash
# Vérifier l'extension
php -m | grep apcu

# Vérifier php.ini
php -i | grep apc.enabled  # Doit être "On"
```

**4. Filesystem : Permission denied**

```bash
# Donner les bonnes permissions
chmod -R 775 storage/cache
chown -R www-data:www-data storage/cache
```

### Logs et Debugging

Les erreurs de drivers sont automatiquement loguées via `error_log()`. Si un driver échoue à l'initialisation, le framework bascule sur Filesystem et logge l'erreur :

```
Cache driver 'redis' failed to initialize: Connection refused. Falling back to filesystem.
```

### Performance

**Benchmarking les drivers :**

Créez un script `benchmark_cache.php` :

```php
<?php
require 'vendor/autoload.php';

$iterations = 1000;
$start = microtime(true);

for ($i = 0; $i < $iterations; $i++) {
    cache("test:$i", "value$i", 60);
}

for ($i = 0; $i < $iterations; $i++) {
    cache("test:$i");
}

$end = microtime(true);
$duration = $end - $start;

echo "Driver: " . cache()->getDriverName() . "\n";
echo "Operations: " . ($iterations * 2) . "\n";
echo "Time: " . round($duration, 3) . "s\n";
echo "Ops/sec: " . round(($iterations * 2) / $duration) . "\n";
```

**Résultats typiques (2000 opérations) :**

- **APCu** : ~0.05s (40000 ops/sec)
- **Redis** : ~0.15s (13000 ops/sec)
- **Memcached** : ~0.18s (11000 ops/sec)
- **Filesystem** : ~2.5s (800 ops/sec)

---

## Exemples Pratiques

### 1. Cache de Résultats de Requête

```php
function getActiveUsers() {
    return cache_remember('active_users', function() {
        return User::where('status', 'active')->all();
    }, 600); // 10 minutes
}
```

### 2. Cache de Vues/Templates

```php
function renderProductPage($id) {
    return cache_remember("product_page:$id", function() use ($id) {
        $product = Product::find($id);
        return view()->render('products/show', ['product' => $product]);
    }, 1800); // 30 minutes
}
```

### 3. Cache d'API Externe

```php
function getWeatherData($city) {
    return cache_remember("weather:$city", function() use ($city) {
        $response = file_get_contents("https://api.weather.com/?city=$city");
        return json_decode($response, true);
    }, 3600); // 1 heure
}
```

### 4. Rate Limiting

```php
function checkRateLimit($userId) {
    $key = "rate_limit:$userId";
    $requests = cache()->increment($key);

    if ($requests === 1) {
        // Première requête, définir le TTL
        cache()->set($key, 1, 60); // Reset dans 60 secondes
    }

    if ($requests > 60) {
        throw new Exception('Rate limit: 60 requêtes par minute maximum');
    }
}
```

---

## Support

Pour toute question ou problème :

- Consultez la documentation du driver spécifique
- Vérifiez les logs d'erreur PHP
- Testez la connexion via le backoffice
- Vérifiez les statistiques sur `/admin/cache/stats`

Le système de cache de SunuFramework est conçu pour être robuste et tolérant aux pannes, avec fallback automatique sur Filesystem en cas de problème.
