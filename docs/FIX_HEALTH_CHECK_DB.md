# Fix: Erreur "Class DB not found" - Health Check

## 🐛 Problème

```
Error: Class "App\Core\Database\DB" not found
File: C:\laragon\www\sunuframework2\Core\Services\HealthCheckService.php:137
```

## 🔍 Cause

Le framework n'a pas de classe `DB` avec une interface façade comme Laravel. Il faut utiliser directement :
- `Database::getInstance()->getConnection()` pour obtenir la connexion PDO
- Des requêtes PDO préparées directement

## ✅ Solution

Remplacement de toutes les utilisations de `DB::table()` par des requêtes PDO natives.

### Avant (❌ Ne fonctionne pas)

```php
use App\Core\Database\DB;

$service = DB::table('system_health_checks')
    ->where('service_name', $serviceName)
    ->first();
```

### Après (✅ Fonctionne)

```php
use App\Core\Database\Database;

$db = Database::getInstance()->getConnection();
$stmt = $db->prepare("SELECT * FROM system_health_checks WHERE service_name = ?");
$stmt->execute([$serviceName]);
$service = $stmt->fetch();
```

## 📝 Modifications Effectuées

**Fichier** : `Core/Services/HealthCheckService.php`

### 1. Ajout de la méthode getDb()

```php
private static function getDb(): \PDO
{
    return Database::getInstance()->getConnection();
}
```

### 2. recordHeartbeat()

**Avant** :
```php
$existing = DB::table('system_health_checks')
    ->where('service_name', $serviceName)
    ->first();
```

**Après** :
```php
$db = self::getDb();
$stmt = $db->prepare("SELECT * FROM system_health_checks WHERE service_name = ?");
$stmt->execute([$serviceName]);
$existing = $stmt->fetch();
```

### 3. recordError()

**Avant** :
```php
DB::table('system_health_checks')
    ->where('service_name', $serviceName)
    ->update([...]);
```

**Après** :
```php
$db = self::getDb();
$stmt = $db->prepare("
    UPDATE system_health_checks
    SET last_status = 'error',
        last_error = ?,
        updated_at = ?
    WHERE service_name = ?
");
$stmt->execute([$error, $now, $serviceName]);
```

### 4. checkServiceHealth()

**Avant** :
```php
$service = DB::table('system_health_checks')
    ->where('service_name', $serviceName)
    ->first();
```

**Après** :
```php
$db = self::getDb();
$stmt = $db->prepare("SELECT * FROM system_health_checks WHERE service_name = ?");
$stmt->execute([$serviceName]);
$service = $stmt->fetch();
```

### 5. checkAllServices()

**Avant** :
```php
$services = DB::table('system_health_checks')->get();
```

**Après** :
```php
$db = self::getDb();
$stmt = $db->query("SELECT * FROM system_health_checks");
$services = $stmt->fetchAll();
```

## 🧪 Test

### Via le Dashboard

```
URL: http://localhost/admin/health
```

**Résultat attendu** : Page charge sans erreur et affiche les services.

### Via l'API

```bash
curl http://localhost/api/health
```

**Résultat attendu** :

```json
{
  "status": "warning",
  "timestamp": 1733741384,
  "datetime": "2025-12-09 10:09:44",
  "services": {
    "cron_job": {
      "status": "warning",
      "last_run": null,
      "runs_today": 0
    },
    "queue_worker": {
      "status": "warning",
      "last_run": null,
      "runs_today": 0
    }
  }
}
```

## ✅ Vérification

### Test 1 : Dashboard Fonctionne

```bash
# Visiter la page
http://localhost/admin/health
```

Si la page charge → ✅ Correction réussie

### Test 2 : Enregistrer un Heartbeat

```bash
mysql -u root sunuframework -e "
UPDATE system_health_checks
SET last_run_at = NOW(),
    runs_today = 1,
    last_status = 'ok'
WHERE service_name = 'cron_job';
"
```

Recharger la page → Service devrait afficher ✅ OK

### Test 3 : API Répond

```bash
curl -s http://localhost/api/health | jq
```

Devrait retourner du JSON valide.

## 🎯 Résultat

Le système de monitoring fonctionne maintenant correctement ! 🎉

**Pages fonctionnelles** :
- ✅ `/admin/health` - Dashboard
- ✅ `/api/health` - API JSON
- ✅ `/api/health/badge` - Badge SVG

## 📚 Référence

Pour d'autres parties du code qui interagissent avec la DB, utiliser le même pattern :

```php
use App\Core\Database\Database;

$db = Database::getInstance()->getConnection();

// SELECT
$stmt = $db->prepare("SELECT * FROM table WHERE id = ?");
$stmt->execute([$id]);
$result = $stmt->fetch();

// INSERT
$stmt = $db->prepare("INSERT INTO table (name, value) VALUES (?, ?)");
$stmt->execute([$name, $value]);

// UPDATE
$stmt = $db->prepare("UPDATE table SET name = ? WHERE id = ?");
$stmt->execute([$name, $id]);

// DELETE
$stmt = $db->prepare("DELETE FROM table WHERE id = ?");
$stmt->execute([$id]);
```

---

✅ **Le système est maintenant opérationnel !**
