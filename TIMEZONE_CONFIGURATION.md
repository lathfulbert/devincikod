# Configuration du Fuseau Horaire (Timezone)

## Vue d'ensemble

Le système respecte maintenant la configuration du fuseau horaire définie dans les settings. Cela garantit que toutes les opérations de date/heure (notamment la programmation des campagnes SMS) utilisent le fuseau horaire correct.

## Problème résolu

**Avant :** Le système utilisait le fuseau horaire par défaut de PHP (souvent UTC), ce qui causait des erreurs dans la programmation des campagnes SMS. Les dates "futures" selon le serveur pouvaient être considérées comme "passées" selon le timezone de l'utilisateur (Africa/Abidjan).

**Après :** Le système utilise le timezone configuré dans les settings (`default_timezone`), garantissant la cohérence entre PHP et JavaScript.

## Architecture de la solution

### 1. Configuration du timezone

**Emplacement :** Table `settings` → Clé `default_timezone`

**Valeurs possibles :**
- `Africa/Abidjan` (Côte d'Ivoire, GMT+0)
- `Africa/Dakar` (Sénégal, GMT+0)
- Tout timezone PHP valide (voir [PHP Timezones](https://www.php.net/manual/en/timezones.php))

**Récupération du timezone :**

```php
// Via SettingsService
$settingsService = new \Modules\Settings\Services\SettingsService();
$timezone = $settingsService->get('default_timezone', 'Africa/Abidjan');

// Via helper function (nouveau)
$timezone = get_timezone();
// ou
$timezone = app_timezone();

// Via setting helper (nouveau)
$timezone = setting('default_timezone', 'Africa/Abidjan');
```

### 2. Helpers créés

**Fichier :** `Core/Support/helpers.php` (lignes 1824-1865)

#### `setting($key, $default = null)`
```php
/**
 * Get a setting value from the database
 */
function setting(string $key, $default = null)
{
    try {
        $settingsService = new \Modules\Settings\Services\SettingsService();
        return $settingsService->get($key, $default);
    } catch (\Exception $e) {
        return $default;
    }
}
```

#### `get_timezone()`
```php
/**
 * Get the application timezone from settings
 */
function get_timezone(): string
{
    return setting('default_timezone', 'Africa/Abidjan');
}
```

#### `app_timezone()`
```php
/**
 * Alias for get_timezone()
 */
function app_timezone(): string
{
    return get_timezone();
}
```

### 3. Application du timezone au démarrage

**Fichier :** `Core/Application.php` (lignes 127-134)

```php
// Set Timezone from Settings
try {
    $timezone = get_timezone();
    date_default_timezone_set($timezone);
} catch (\Exception $e) {
    // Fallback to Africa/Abidjan if settings not available
    date_default_timezone_set('Africa/Abidjan');
}
```

**Ordre d'exécution dans `Application::boot()` :**
1. Initialisation de la base de données
2. **Application du timezone** ← NOUVEAU
3. Initialisation I18n
4. Middleware de maintenance
5. Routes, etc.

### 4. Transmission du timezone au JavaScript

**Fichiers modifiés :**
- `Modules/SmsCore/Views/sms/campaigns/create.php` (lignes 240-242)
- `Modules/SmsCore/Views/sms/campaigns/edit.php` (lignes 244-246)

```javascript
// Server timezone from PHP
const SERVER_TIMEZONE = '<?= get_timezone() ?>';
const TIMEZONE_OFFSET = <?= (new DateTime('now', new DateTimeZone(get_timezone())))->getOffset() / 3600 ?>;
```

**Variables JavaScript disponibles :**
- `SERVER_TIMEZONE` : Nom du timezone (ex: "Africa/Abidjan")
- `TIMEZONE_OFFSET` : Décalage en heures par rapport à UTC (ex: 0 pour GMT)

## Impact sur les fonctionnalités

### 1. Campagnes SMS programmées

#### PHP (Validation serveur)
```php
// Dans SmsCampaignController@store() et @update()
$scheduledTimestamp = strtotime($scheduledAt);

// time() utilise maintenant le timezone configuré grâce à date_default_timezone_set()
if ($scheduledTimestamp <= time()) {
    flash('error', 'La date et l\'heure programmées doivent être dans le futur');
}
```

#### JavaScript (Validation client)
```javascript
// Dans create.php et edit.php
const selectedDate = new Date(scheduledInput.value);
const now = new Date(); // Utilise le timezone du navigateur

if (selectedDate <= now) {
    alert('La date et l\'heure programmées doivent être dans le futur');
}
```

**Note importante :** JavaScript utilise toujours le timezone du navigateur de l'utilisateur. Le `SERVER_TIMEZONE` et `TIMEZONE_OFFSET` sont disponibles pour des conversions si nécessaire.

### 2. Affichage des dates

Toutes les fonctions PHP de date utilisent maintenant le timezone configuré :
- `date()`
- `strtotime()`
- `time()`
- `DateTime` (avec timezone par défaut)

**Exemples :**

```php
// Timezone configuré : Africa/Abidjan (GMT+0)

// Avant (PHP timezone = UTC)
echo date('Y-m-d H:i:s'); // 2025-12-11 12:00:00 (UTC)

// Après
echo date('Y-m-d H:i:s'); // 2025-12-11 12:00:00 (Africa/Abidjan GMT+0)
// (Même heure car Africa/Abidjan est GMT+0)

// Avec un timezone différent (ex: Africa/Dakar aussi GMT+0)
// Pas de différence avec Abidjan car même offset
```

### 3. Base de données

**Important :** Les colonnes `datetime` et `timestamp` dans MySQL stockent les dates **sans** timezone. La conversion est faite par PHP :

```sql
-- Dans sms_campaigns
scheduled_at datetime NULL
```

**Flux de données :**
1. Utilisateur sélectionne : `2025-12-11 15:30` (dans l'interface)
2. JavaScript envoie : `2025-12-11T15:30` (format ISO local)
3. PHP reçoit et valide avec timezone `Africa/Abidjan`
4. MySQL stocke : `2025-12-11 15:30:00` (sans timezone)
5. PHP lit et interprète avec timezone `Africa/Abidjan`

## Configuration du timezone

### Méthode 1 : Via l'interface (si disponible)

```
/admin/settings → Site Settings → Default Timezone → Africa/Abidjan
```

### Méthode 2 : Via SQL

```sql
INSERT INTO settings (`key`, value, type, setting_group, description)
VALUES ('default_timezone', 'Africa/Abidjan', 'string', 'general', 'Default system timezone')
ON DUPLICATE KEY UPDATE value = 'Africa/Abidjan';
```

### Méthode 3 : Via SettingsService

```php
use Modules\Settings\Services\SettingsService;

$settingsService = new SettingsService();
$settingsService->set('default_timezone', 'Africa/Abidjan', 'string', 'general');
```

### Méthode 4 : Via helper (après implémentation)

```php
setting('default_timezone', 'Africa/Abidjan'); // Get
// Note: Il faudrait ajouter une fonction set_setting() pour modifier
```

## Timezones courants en Afrique de l'Ouest

| Timezone | Pays | Offset UTC | Même que |
|----------|------|------------|----------|
| `Africa/Abidjan` | Côte d'Ivoire | GMT+0 | UTC |
| `Africa/Dakar` | Sénégal | GMT+0 | UTC |
| `Africa/Bamako` | Mali | GMT+0 | UTC |
| `Africa/Conakry` | Guinée | GMT+0 | UTC |
| `Africa/Accra` | Ghana | GMT+0 | UTC |
| `Africa/Lagos` | Nigeria | GMT+1 | CET |

**Note :** Tous les pays de la zone GMT+0 (Côte d'Ivoire, Sénégal, Mali, etc.) peuvent utiliser n'importe lequel de ces timezones car ils ont le même offset. Cependant, il est recommandé d'utiliser le timezone du pays.

## Tests et vérification

### Test 1 : Vérifier le timezone PHP

```php
// Dans n'importe quel contrôleur ou script
echo "Current PHP timezone: " . date_default_timezone_get() . "\n";
echo "Current time: " . date('Y-m-d H:i:s') . "\n";
echo "Configured timezone: " . get_timezone() . "\n";
```

**Résultat attendu :**
```
Current PHP timezone: Africa/Abidjan
Current time: 2025-12-11 15:30:00
Configured timezone: Africa/Abidjan
```

### Test 2 : Vérifier la configuration

```sql
SELECT `key`, value FROM settings WHERE `key` = 'default_timezone';
```

**Résultat attendu :**
```
key               | value
------------------|------------------
default_timezone  | Africa/Abidjan
```

### Test 3 : Test de programmation de campagne

```
1. Créer une campagne
2. Définir scheduled_at = MAINTENANT + 1 heure
3. Soumettre le formulaire
4. ✓ Vérifier que la campagne est créée avec status="scheduled"
5. ✓ Vérifier que scheduled_at est correct dans la BDD
6. Définir scheduled_at = MAINTENANT - 1 heure
7. Soumettre le formulaire
8. ✓ Vérifier erreur "La date doit être dans le futur"
```

### Test 4 : Comparaison timezone

```php
// Script de test
date_default_timezone_set('UTC');
$utcTime = date('Y-m-d H:i:s');
$utcTimestamp = time();

date_default_timezone_set('Africa/Abidjan');
$abidjanTime = date('Y-m-d H:i:s');
$abidjanTimestamp = time();

echo "UTC Time: $utcTime (Timestamp: $utcTimestamp)\n";
echo "Abidjan Time: $abidjanTime (Timestamp: $abidjanTimestamp)\n";
echo "Difference: " . ($utcTimestamp - $abidjanTimestamp) . " seconds\n";
```

**Résultat attendu :**
```
UTC Time: 2025-12-11 15:30:00 (Timestamp: 1733933400)
Abidjan Time: 2025-12-11 15:30:00 (Timestamp: 1733933400)
Difference: 0 seconds
```
*(Car Africa/Abidjan est GMT+0, même offset que UTC)*

## Débogage

### Problème : Les dates programmées sont rejetées alors qu'elles semblent valides

**Solution :**
1. Vérifier le timezone PHP :
   ```php
   echo date_default_timezone_get();
   ```

2. Vérifier le timezone configuré :
   ```php
   echo get_timezone();
   ```

3. Vérifier si la base de données a bien le setting :
   ```sql
   SELECT * FROM settings WHERE `key` = 'default_timezone';
   ```

4. Vérifier le timezone JavaScript (console navigateur) :
   ```javascript
   console.log(new Date().toTimeString());
   console.log(SERVER_TIMEZONE);
   ```

### Problème : Le timezone PHP ne correspond pas à la configuration

**Solution :**
1. Vider le cache (si applicable)
2. Redémarrer le serveur web
3. Vérifier que `Application::boot()` est bien appelé
4. Vérifier les logs d'erreur PHP

### Problème : Conversion de timezone entre PHP et JavaScript

**Solution :** Utiliser les variables transmises

```javascript
// Dans les vues de campagne
const SERVER_TIMEZONE = '<?= get_timezone() ?>'; // "Africa/Abidjan"
const TIMEZONE_OFFSET = <?= (new DateTime('now', new DateTimeZone(get_timezone())))->getOffset() / 3600 ?>; // 0

// Convertir une date serveur vers le timezone du navigateur
const serverDate = new Date('2025-12-11T15:30:00'); // Interprété en timezone local du navigateur
```

## Fichiers modifiés

| Fichier | Modification | Lignes |
|---------|--------------|--------|
| `Core/Support/helpers.php` | Ajout fonctions `setting()`, `get_timezone()`, `app_timezone()` | 1824-1865 |
| `Core/Application.php` | Application du timezone au boot | 127-134 |
| `Modules/SmsCore/Views/sms/campaigns/create.php` | Transmission timezone à JavaScript | 240-242 |
| `Modules/SmsCore/Views/sms/campaigns/edit.php` | Transmission timezone à JavaScript | 244-246 |
| `TIMEZONE_CONFIGURATION.md` | Documentation complète | (ce fichier) |

## Bonnes pratiques

1. **Toujours utiliser les helpers :**
   ```php
   // ✓ Bon
   $timezone = get_timezone();

   // ✗ Moins bon
   $timezone = (new SettingsService())->get('default_timezone', 'Africa/Abidjan');
   ```

2. **Ne pas coder en dur les timezones :**
   ```php
   // ✗ Mauvais
   date_default_timezone_set('Africa/Abidjan');

   // ✓ Bon
   date_default_timezone_set(get_timezone());
   ```

3. **Utiliser DateTime pour les conversions complexes :**
   ```php
   $date = new DateTime('2025-12-11 15:30:00', new DateTimeZone(get_timezone()));
   echo $date->format('Y-m-d H:i:s');
   ```

4. **Documenter les assumptions de timezone :**
   ```php
   // This function expects $date to be in the configured timezone
   public function scheduleAt(string $date): void
   ```

## Évolutions futures

1. **Support multi-timezone par utilisateur :**
   - Permettre à chaque utilisateur de définir son timezone
   - Convertir les dates à l'affichage selon le timezone de l'utilisateur

2. **Affichage des timezones dans l'interface :**
   - Indiquer le timezone utilisé à côté des champs de date
   - Ex: "Programmer l'envoi (Africa/Abidjan GMT+0)"

3. **Logs avec timezone :**
   - Inclure le timezone dans les logs
   - Faciliter le débogage

4. **API avec timezone :**
   - Accepter et retourner les dates avec timezone explicite (ISO 8601)
   - Ex: `2025-12-11T15:30:00+00:00`

## Ressources

- [PHP Timezones](https://www.php.net/manual/en/timezones.php)
- [PHP date_default_timezone_set](https://www.php.net/manual/en/function.date-default-timezone-set.php)
- [JavaScript Date](https://developer.mozilla.org/en-US/docs/Web/JavaScript/Reference/Global_Objects/Date)
- [ISO 8601 Date Format](https://www.iso.org/iso-8601-date-and-time-format.html)
