# Système de Cache - Guide Rapide

## 🚀 Démarrage Rapide

Le système de cache est **déjà opérationnel** avec le driver Filesystem par défaut.

### Utilisation Basique

```php
// Stocker une valeur (3600 secondes = 1 heure)
cache('ma_cle', 'ma_valeur', 3600);

// Récupérer une valeur
$valeur = cache('ma_cle');

// Remember pattern (exécute le callback seulement si pas en cache)
$users = cache_remember('users_actifs', function() {
    return User::where('status', 'active')->all();
}, 600);

// Supprimer
cache_forget('ma_cle');

// Viderle cache
cache_flush();
```

## 📋 Drivers Disponibles

| Driver         | Performances | Installation                     | Usage                             |
| -------------- | ------------ | -------------------------------- | --------------------------------- |
| **Filesystem** | ⭐⭐         | ✅ Aucune                        | Développement, pas de dépendances |
| **Redis**      | ⭐⭐⭐⭐⭐   | `composer require predis/predis` | Production, haute performance     |
| **Memcached**  | ⭐⭐⭐⭐⭐   | Extension PHP `memcached`        | Applications scalables            |
| **APCu**       | ⭐⭐⭐⭐⭐   | Extension PHP `apcu`             | Config, metadata, compteurs       |

## ⚙️ Configuration (Backoffice)

1. Accédez à **`/admin/cache`**
2. Sélectionnez votre driver
3. Configurez les paramètres
4. Testez la connexion
5. Sauvegardez

## 📊 Statistiques

Consultez **`/admin/cache/stats`** pour voir :

- Driver actif
- Nombre de clés
- Mémoire utilisée
- Hit rate (APCu)

## 📚 Documentation Complète

Consultez [docs/CACHE.md](file:///c:/laragon/www/sunuframework2/docs/CACHE.md) pour :

- Guide d'installation détaillé
- Configuration de chaque driver
- Exemples avancés
- Bonnes pratiques
- Dépannage

## 🧪 Test

Testez le système :

```bash
php test_cache_filesystem.php
```

## 💡 Exemples

### Cache de Requête Base de Données

```php
$products = cache_remember('products_populaires', function() {
    return Product::where('views', '>', 1000)->all();
}, 3600);
```

### Rate Limiting

```php
$requests = cache()->increment("rate_limit:user:{$userId}");
if ($requests > 100) {
    throw new Exception('Trop de requêtes');
}
```

### Cache Multi-Clés

```php
cache()->setMultiple([
    'key1' => 'value1',
    'key2' => 'value2',
    'key3' => 'value3'
], 600);
```

---

**Le système est prêt à l'emploi !** 🎉
