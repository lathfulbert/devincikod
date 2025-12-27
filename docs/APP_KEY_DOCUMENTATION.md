# Documentation APP_KEY - Clé de Chiffrement

## Vue d'ensemble

La clé `APP_KEY` est une clé de chiffrement **obligatoire** pour sécuriser votre application SunuFramework2. Elle est utilisée pour :

- 🔐 Chiffrer les données sensibles
- 🍪 Sécuriser les sessions et cookies
- 🔑 Générer des tokens CSRF
- 📧 Chiffrer les URLs signées
- 💳 Protéger les informations de paiement

**⚠️ IMPORTANT : Sans APP_KEY, votre application ne démarrera pas !**

---

## 🚀 Démarrage Rapide

### 1. Générer une Clé

```bash
php sunu key:generate
```

Cette commande va :
- Générer une clé sécurisée de 32 bytes
- L'encoder en base64
- L'ajouter automatiquement à votre fichier `.env`

**Résultat dans `.env` :**
```env
APP_KEY=base64:wzFGsN+UO4sV2gUHXWnDge7h8mdiBeJMFbcAPs7p5Bo=
```

### 2. Vérifier la Clé

```bash
# Afficher une clé sans modifier .env
php sunu key:generate --show
```

### 3. Regénérer une Clé Existante

```bash
# Forcer la regénération
php sunu key:generate --force
```

---

## 📋 Commandes Disponibles

### `php sunu key:generate`

Génère une nouvelle clé et l'ajoute au fichier `.env`.

**Options :**
- `--show` - Affiche une clé sans modifier le fichier `.env`
- `--force` - Force la regénération même si une clé existe déjà

**Exemples :**
```bash
# Génération normale
php sunu key:generate

# Afficher sans enregistrer
php sunu key:generate --show

# Forcer la regénération
php sunu key:generate --force
```

**Sortie de la commande :**
```
✓ Application key set successfully.

Key: base64:wzFGsN+UO4sV2gUHXWnDge7h8mdiBeJMFbcAPs7p5Bo=
```

---

## 🔒 Utilisation du Chiffrement

### Helpers Globaux

SunuFramework2 fournit des helpers simples pour chiffrer/déchiffrer des données :

#### `encrypt($value)`

Chiffre n'importe quelle valeur (string, array, object).

```php
// Chiffrer une chaîne
$encrypted = encrypt('Données sensibles');

// Chiffrer un tableau
$encrypted = encrypt(['email' => 'user@example.com', 'token' => 'secret']);

// Chiffrer un objet
$encrypted = encrypt($user);
```

#### `decrypt($payload)`

Déchiffre une valeur précédemment chiffrée.

```php
$decrypted = decrypt($encrypted);
echo $decrypted; // 'Données sensibles'

// Déchiffrer un tableau
$array = decrypt($encryptedArray);
echo $array['email']; // 'user@example.com'
```

#### `encryptString($string)`

Chiffre uniquement des chaînes de caractères (plus performant).

```php
$encrypted = encryptString('Mon message secret');
```

#### `decryptString($payload)`

Déchiffre une chaîne chiffrée.

```php
$decrypted = decryptString($encrypted);
echo $decrypted; // 'Mon message secret'
```

### Exemples Pratiques

#### Exemple 1 : Stocker un Mot de Passe Temporaire

```php
// Dans un contrôleur
public function storeTemporaryPassword($userId, $password)
{
    $encrypted = encrypt($password);

    // Stocker dans la BDD
    DB::table('password_resets')->insert([
        'user_id' => $userId,
        'temp_password' => $encrypted,
        'created_at' => date('Y-m-d H:i:s')
    ]);
}

public function retrieveTemporaryPassword($userId)
{
    $record = DB::table('password_resets')
        ->where('user_id', $userId)
        ->first();

    if ($record) {
        return decrypt($record->temp_password);
    }

    return null;
}
```

#### Exemple 2 : Chiffrer des Données d'API

```php
// Chiffrer les credentials d'API avant stockage
$apiCredentials = [
    'api_key' => 'sk-1234567890',
    'api_secret' => 'secret_key_here'
];

$encrypted = encrypt($apiCredentials);

// Sauvegarder dans la config utilisateur
$user->api_credentials = $encrypted;
$user->save();

// Récupérer et utiliser
$credentials = decrypt($user->api_credentials);
$apiClient = new ApiClient($credentials['api_key'], $credentials['api_secret']);
```

#### Exemple 3 : URLs Signées

```php
// Créer une URL avec paramètres chiffrés
function generateSecureUrl($data, $expiresIn = 3600)
{
    $payload = [
        'data' => $data,
        'expires_at' => time() + $expiresIn
    ];

    $encrypted = encrypt($payload);
    $encoded = base64_encode($encrypted);

    return url('/secure-action') . '?token=' . urlencode($encoded);
}

// Vérifier et utiliser
function verifySecureUrl($token)
{
    try {
        $encrypted = base64_decode($token);
        $payload = decrypt($encrypted);

        if (time() > $payload['expires_at']) {
            throw new Exception('Token expiré');
        }

        return $payload['data'];
    } catch (Exception $e) {
        return false;
    }
}
```

#### Exemple 4 : Cookies Sécurisés

```php
// Stocker des données sensibles dans un cookie
function setSecureCookie($name, $value, $expiry = 3600)
{
    $encrypted = encrypt($value);
    setcookie($name, $encrypted, time() + $expiry, '/', '', true, true);
}

// Récupérer et déchiffrer
function getSecureCookie($name)
{
    if (isset($_COOKIE[$name])) {
        try {
            return decrypt($_COOKIE[$name]);
        } catch (Exception $e) {
            return null;
        }
    }
    return null;
}
```

---

## 🛡️ Sécurité

### Format de la Clé

**Format requis :** `base64:XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX=`

- **Préfixe** : `base64:`
- **Longueur décodée** : 32 bytes (pour AES-256-CBC)
- **Encodage** : Base64

### Validation Automatique

L'application valide automatiquement la clé au démarrage :

1. ✅ **Vérification de présence** - La clé doit exister
2. ✅ **Vérification de format** - Doit commencer par `base64:`
3. ✅ **Vérification de longueur** - 32 bytes après décodage

**Si la validation échoue :**

```
╔══════════════════════════════════════════════════════════════╗
║                                                              ║
║  ⚠  ERREUR: APP_KEY non définie                             ║
║                                                              ║
╠══════════════════════════════════════════════════════════════╣
║                                                              ║
║  La clé d'application (APP_KEY) est requise pour la          ║
║  sécurité de votre application (chiffrement, sessions).      ║
║                                                              ║
║  Pour générer une clé, exécutez :                            ║
║                                                              ║
║      php sunu key:generate                                   ║
║                                                              ║
╚══════════════════════════════════════════════════════════════╝
```

### Bonnes Pratiques

#### ✅ À FAIRE

1. **Générer une clé unique par environnement**
   ```bash
   # Dev
   APP_KEY=base64:dev_key_here

   # Staging
   APP_KEY=base64:staging_key_here

   # Production
   APP_KEY=base64:prod_key_here
   ```

2. **Conserver la clé en sécurité**
   - Ne JAMAIS committer `.env` dans Git
   - Sauvegarder la clé de production dans un gestionnaire de secrets
   - Limiter l'accès à la clé

3. **Rotation régulière en production**
   ```bash
   # 1. Générer une nouvelle clé
   php sunu key:generate --show

   # 2. Tester avec la nouvelle clé
   # 3. Déployer
   # 4. Invalider l'ancienne clé
   ```

#### ❌ À ÉVITER

1. **Utiliser la même clé sur plusieurs environnements**
2. **Partager la clé par email ou chat**
3. **Stocker la clé en clair dans le code**
4. **Oublier de générer une clé en production**

---

## 🔧 Algorithme de Chiffrement

### AES-256-CBC

SunuFramework2 utilise **AES-256-CBC** pour le chiffrement :

- **Algorithme** : Advanced Encryption Standard
- **Longueur de clé** : 256 bits (32 bytes)
- **Mode** : Cipher Block Chaining (CBC)
- **IV** : Vecteur d'initialisation aléatoire par chiffrement
- **MAC** : HMAC-SHA256 pour l'authentification

### Structure du Payload Chiffré

```json
{
    "iv": "base64_encoded_initialization_vector",
    "value": "encrypted_data",
    "mac": "hmac_sha256_signature"
}
```

Le payload final est encodé en base64.

---

## 🚨 Dépannage

### Erreur : "APP_KEY non définie"

**Cause** : Le fichier `.env` n'a pas de valeur pour `APP_KEY`.

**Solution** :
```bash
php sunu key:generate
```

### Erreur : "APP_KEY invalide"

**Cause** : Format incorrect ou longueur incorrecte.

**Solution** :
```bash
php sunu key:generate --force
```

### Erreur : "Could not decrypt the data"

**Causes possibles** :
1. La clé a changé depuis le chiffrement
2. Le payload est corrompu
3. Le payload a été modifié

**Solution** :
- Vérifier que la clé est correcte
- Rechiffrer les données avec la nouvelle clé
- Ne jamais modifier manuellement les données chiffrées

### L'application ne démarre pas

**Cause** : Validation de `APP_KEY` échoue au boot.

**Solution** :
```bash
# 1. Vérifier que .env existe
ls -la .env

# 2. Vérifier le contenu
cat .env | grep APP_KEY

# 3. Régénérer la clé
php sunu key:generate --force
```

---

## 📝 Migration depuis un Autre Framework

### Depuis Laravel

La clé générée par SunuFramework2 est **100% compatible** avec Laravel.

Vous pouvez :
- Utiliser la même clé dans les deux frameworks
- Déchiffrer des données Laravel avec SunuFramework2
- Chiffrer avec SunuFramework2 et déchiffrer avec Laravel

```bash
# Générer une clé compatible Laravel
php sunu key:generate
```

### Depuis un Framework Custom

Si vous avez des données chiffrées avec un autre système :

1. Déchiffrez les données avec l'ancien système
2. Rechiffrez avec `encrypt()` de SunuFramework2
3. Sauvegardez les nouvelles valeurs

```php
// Script de migration
$oldData = decryptWithOldSystem($encryptedData);
$newData = encrypt($oldData); // Utilise APP_KEY
updateDatabase($id, $newData);
```

---

## 📚 Classe Encrypter

### Utilisation Directe

Si vous préférez utiliser la classe `Encrypter` directement :

```php
use App\Core\Encryption\Encrypter;

// Créer une instance
$key = base64_decode(substr(env('APP_KEY'), 7));
$encrypter = new Encrypter($key, 'AES-256-CBC');

// Chiffrer
$encrypted = $encrypter->encrypt('Secret data');

// Déchiffrer
$decrypted = $encrypter->decrypt($encrypted);

// Chiffrer sans sérialisation
$encryptedString = $encrypter->encryptString('Plain text');
$decryptedString = $encrypter->decryptString($encryptedString);
```

### Méthodes Disponibles

| Méthode | Description |
|---------|-------------|
| `encrypt($value, $serialize = true)` | Chiffre une valeur |
| `decrypt($payload, $unserialize = true)` | Déchiffre un payload |
| `encryptString($value)` | Chiffre une chaîne sans sérialisation |
| `decryptString($payload)` | Déchiffre une chaîne |
| `getKey()` | Récupère la clé de chiffrement |
| `generateKey($cipher)` | Génère une clé aléatoire (statique) |
| `supported($key, $cipher)` | Vérifie la compatibilité clé/cipher (statique) |

---

## ⚡ Performance

### Cache de l'Encrypter

Les helpers `encrypt()` et `decrypt()` utilisent un cache statique pour éviter de recréer l'instance `Encrypter` à chaque appel.

```php
// Premier appel - Crée l'encrypter
$encrypted1 = encrypt('data1');

// Appels suivants - Réutilise l'instance
$encrypted2 = encrypt('data2'); // Plus rapide
$encrypted3 = encrypt('data3'); // Plus rapide
```

### Benchmark

| Opération | Temps moyen |
|-----------|-------------|
| `encrypt()` (première fois) | ~0.5ms |
| `encrypt()` (cache) | ~0.1ms |
| `decrypt()` | ~0.1ms |

---

## 🔄 Changelog

### Version 2.0.0 (2024-12-08)

✨ **Ajout initial du système APP_KEY**

- Classe `Encrypter` (AES-256-CBC)
- Commande `php sunu key:generate`
- Validation automatique au boot
- Helpers `encrypt()`, `decrypt()`, `encryptString()`, `decryptString()`
- Documentation complète
- Messages d'erreur détaillés

---

## 📞 Support

Pour toute question ou problème :

1. Consultez cette documentation
2. Vérifiez les logs : `storage/logs/`
3. Testez avec : `php sunu key:generate --show`
4. Créez une issue sur GitHub

---

**Auteur** : SunuFramework Team
**Date** : 2024-12-08
**Version** : 2.0.0
**Licence** : MIT
