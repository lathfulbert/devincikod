# Sécurité APP_KEY - Guide Complet

## Vue d'ensemble

La clé `APP_KEY` sécurise **automatiquement** 3 composants critiques de votre application :

1. 🔐 **Tokens CSRF** - Signés avec HMAC-SHA256
2. 🍪 **Cookies** - Chiffrés avec AES-256-CBC
3. 📦 **Sessions** - Données sensibles chiffrées

---

## 🛡️ Tokens CSRF Sécurisés

### Protection Automatique

Les tokens CSRF sont maintenant **signés** avec APP_KEY pour empêcher la falsification.

**Fonctionnement :**
```
Token CSRF = random_bytes(32)
Signature = HMAC-SHA256(Token, APP_KEY)
Stockage Session = [token, signature]
```

### Validation Automatique

1. Vérifie la signature du token stocké
2. Compare le token fourni avec le token stocké
3. Rejette si signature invalide ou token modifié

**Code** :
```php
// Génération automatique dans les vues
<?= csrf_field() ?>
// Génère: <input type="hidden" name="_csrf_token" value="...">

// Validation automatique par CSRFMiddleware
// Aucun code supplémentaire requis!
```

### Avantages

✅ **Protection contre la falsification** - La signature empêche la modification du token
✅ **Détection de tampering** - Tout changement invalide la signature
✅ **Rotation automatique** - Token regénéré si signature invalide

---

## 🍪 Cookies Sécurisés

### Helpers Disponibles

#### `setSecureCookie($name, $value, $expire)`

Crée un cookie **chiffré** avec AES-256-CBC.

```php
// Cookie simple (expire dans 1 heure)
setSecureCookie('user_prefs', ['theme' => 'dark', 'lang' => 'fr']);

// Cookie avec durée personnalisée (1 semaine)
setSecureCookie('remember_token', $tokenData, 7 * 24 * 3600);

// Cookie complexe avec options complètes
setSecureCookie(
    name: 'sensitive_data',
    value: ['api_key' => 'secret', 'user_id' => 123],
    expire: 3600,
    path: '/',
    domain: '',
    secure: true,  // HTTPS uniquement
    httponly: true  // Pas accessible via JavaScript
);
```

#### `getSecureCookie($name, $default)`

Récupère et **déchiffre** un cookie.

```php
// Récupération simple
$prefs = getSecureCookie('user_prefs');

// Avec valeur par défaut
$prefs = getSecureCookie('user_prefs', ['theme' => 'light']);

// Utilisation
if ($prefs) {
    echo "Theme: " . $prefs['theme'];
}
```

#### `deleteSecureCookie($name)`

Supprime un cookie sécurisé.

```php
deleteSecureCookie('user_prefs');
deleteSecureCookie('remember_token');
```

### Exemples Pratiques

#### Exemple 1 : Remember Me Token

```php
// Login avec "Remember Me"
public function login($email, $password, $remember = false)
{
    $user = authenticateUser($email, $password);

    if ($user && $remember) {
        $token = bin2hex(random_bytes(32));

        // Stocker le token dans la BDD
        DB::table('remember_tokens')->insert([
            'user_id' => $user->id,
            'token' => hash('sha256', $token),
            'expires_at' => date('Y-m-d H:i:s', time() + 30 * 86400)
        ]);

        // Cookie sécurisé pour 30 jours
        setSecureCookie('remember_token', [
            'user_id' => $user->id,
            'token' => $token
        ], 30 * 86400);
    }

    return $user;
}

// Vérification auto-login
public function checkRememberToken()
{
    $cookie = getSecureCookie('remember_token');

    if ($cookie) {
        $hashedToken = hash('sha256', $cookie['token']);

        $record = DB::table('remember_tokens')
            ->where('user_id', $cookie['user_id'])
            ->where('token', $hashedToken)
            ->where('expires_at', '>', date('Y-m-d H:i:s'))
            ->first();

        if ($record) {
            // Auto-login
            return User::find($cookie['user_id']);
        }
    }

    return null;
}
```

#### Exemple 2 : Préférences Utilisateur

```php
// Sauvegarder les préférences
public function savePreferences($userId, $preferences)
{
    setSecureCookie('user_prefs_' . $userId, $preferences, 365 * 86400);
}

// Charger les préférences
public function loadPreferences($userId)
{
    return getSecureCookie('user_prefs_' . $userId, [
        'theme' => 'light',
        'lang' => 'fr',
        'notifications' => true
    ]);
}
```

#### Exemple 3 : Panier d'Achat

```php
// Ajouter au panier
public function addToCart($productId, $quantity)
{
    $cart = getSecureCookie('shopping_cart', []);

    $cart[$productId] = [
        'quantity' => $quantity,
        'added_at' => time()
    ];

    setSecureCookie('shopping_cart', $cart, 7 * 86400);
}

// Récupérer le panier
public function getCart()
{
    return getSecureCookie('shopping_cart', []);
}
```

### Sécurité des Cookies

**Caractéristiques de sécurité :**

✅ **Chiffrement AES-256-CBC** - Données illisibles sans APP_KEY
✅ **HTTPS automatique en production** - Force secure=true si isProduction()
✅ **HttpOnly par défaut** - Protection XSS (pas accessible via JavaScript)
✅ **Signature MAC** - Détection de modification (intégré dans le chiffrement)

**Comparaison :**

| Feature | Cookie Normal | Cookie Sécurisé |
|---------|---------------|-----------------|
| Lisible côté client | ✅ Oui | ❌ Non (chiffré) |
| Modifiable | ✅ Oui | ❌ Non (invalide si modifié) |
| XSS Protection | ⚠️ Si HttpOnly | ✅ HttpOnly par défaut |
| HTTPS Enforcement | ⚠️ Manuel | ✅ Auto en production |
| Expiration | ✅ Oui | ✅ Oui |

---

## 📦 Sessions Sécurisées

### Helpers Disponibles

#### `sessionPut($key, $value)`

Stocke une valeur **chiffrée** dans la session.

```php
// Stocker des données sensibles
sessionPut('api_credentials', [
    'api_key' => 'sk-1234567890',
    'api_secret' => 'secret_key_here'
]);

// Stocker un token temporaire
sessionPut('temp_password', 'P@ssw0rd123');
```

#### `sessionGet($key, $default)`

Récupère et **déchiffre** une valeur de session.

```php
// Récupération simple
$credentials = sessionGet('api_credentials');

// Avec valeur par défaut
$password = sessionGet('temp_password', null);

// Utilisation
if ($credentials) {
    $api = new ApiClient($credentials['api_key'], $credentials['api_secret']);
}
```

#### `sessionForget($key)`

Supprime une valeur chiffrée de la session.

```php
sessionForget('api_credentials');
sessionForget('temp_password');
```

### Exemples Pratiques

#### Exemple 1 : Processus d'Inscription Multi-Étapes

```php
// Étape 1 : Informations de base
public function step1($data)
{
    sessionPut('registration_step1', [
        'email' => $data['email'],
        'name' => $data['name']
    ]);

    redirect('/register/step2');
}

// Étape 2 : Informations sensibles
public function step2($data)
{
    $step1 = sessionGet('registration_step1');

    if (!$step1) {
        redirect('/register/step1');
    }

    sessionPut('registration_step2', [
        'phone' => $data['phone'],
        'address' => $data['address']
    ]);

    redirect('/register/step3');
}

// Finalisation
public function finalize()
{
    $step1 = sessionGet('registration_step1');
    $step2 = sessionGet('registration_step2');

    if ($step1 && $step2) {
        $user = User::create(array_merge($step1, $step2));

        // Nettoyer la session
        sessionForget('registration_step1');
        sessionForget('registration_step2');

        return $user;
    }
}
```

#### Exemple 2 : Authentification Multi-Facteurs

```php
// Stocker l'OTP temporaire
public function sendOTP($userId, $phone)
{
    $otp = rand(100000, 999999);

    sessionPut('mfa_otp', [
        'code' => $otp,
        'user_id' => $userId,
        'expires_at' => time() + 300 // 5 minutes
    ]);

    // Envoyer SMS
    sendSMS($phone, "Votre code: $otp");
}

// Vérifier l'OTP
public function verifyOTP($code)
{
    $stored = sessionGet('mfa_otp');

    if (!$stored) {
        return false;
    }

    if (time() > $stored['expires_at']) {
        sessionForget('mfa_otp');
        return false;
    }

    if ($code == $stored['code']) {
        sessionForget('mfa_otp');
        return $stored['user_id'];
    }

    return false;
}
```

#### Exemple 3 : Panier d'Achat Temporaire

```php
// Ajouter au panier (session)
public function addToCart($productId, $quantity)
{
    $cart = sessionGet('cart', []);

    $cart[$productId] = [
        'quantity' => $quantity,
        'price' => Product::find($productId)->price,
        'added_at' => time()
    ];

    sessionPut('cart', $cart);
}

// Calculer le total
public function getCartTotal()
{
    $cart = sessionGet('cart', []);
    $total = 0;

    foreach ($cart as $item) {
        $total += $item['price'] * $item['quantity'];
    }

    return $total;
}

// Vider le panier après paiement
public function checkout()
{
    $cart = sessionGet('cart');

    if ($cart) {
        // Créer la commande
        $order = Order::create(['items' => $cart]);

        // Nettoyer
        sessionForget('cart');

        return $order;
    }
}
```

### Sécurité des Sessions

**Stockage :**
```
$_SESSION['_encrypted']['api_credentials'] = "eyJpdiI6Ijg1RGVy..."
$_SESSION['_encrypted']['temp_password'] = "eyJpdiI6IkZnSk..."
```

**Avantages :**

✅ **Données illisibles** - Même si quelqu'un accède aux fichiers de session
✅ **Protection serveur** - Sécurisé même en cas de dump mémoire
✅ **Rotation automatique** - Invalidé si APP_KEY change

**Comparaison :**

| Méthode | Session Normale | Session Sécurisée |
|---------|-----------------|-------------------|
| Lisible sur serveur | ✅ Oui | ❌ Non (chiffré) |
| Protection si dump serveur | ❌ Non | ✅ Oui |
| Nécessite APP_KEY | ❌ Non | ✅ Oui |
| Performance | Rapide | Légèrement plus lent |

---

## 🔐 Résumé de la Sécurité

### Ce qui est automatiquement sécurisé

| Composant | Méthode | Utilise APP_KEY | Status |
|-----------|---------|-----------------|--------|
| **CSRF Tokens** | HMAC-SHA256 | ✅ Oui | ✅ Actif |
| **Cookies chiffrés** | AES-256-CBC | ✅ Oui | ✅ Via helpers |
| **Sessions chiffrées** | AES-256-CBC | ✅ Oui | ✅ Via helpers |
| **Données chiffrées** | AES-256-CBC | ✅ Oui | ✅ Via helpers |

### Chiffrement vs Hachage

**Quand utiliser le chiffrement (encrypt/decrypt) :**
- ✅ Données à récupérer en clair : cookies, sessions, tokens
- ✅ Informations réversibles : préférences, API keys
- ✅ Données temporaires : OTP, passwords temporaires

**Quand utiliser le hachage (hash/password_hash) :**
- ✅ Mots de passe : `password_hash($password, PASSWORD_BCRYPT)`
- ✅ Tokens one-way : `hash('sha256', $token)`
- ✅ Vérification d'intégrité : `hash_file('sha256', $file)`

### Checklist de Sécurité

#### ✅ Obligatoire

- [ ] APP_KEY générée : `php sunu key:generate`
- [ ] APP_KEY unique par environnement
- [ ] APP_KEY jamais commitée dans Git
- [ ] HTTPS forcé en production

#### ✅ Recommandé

- [ ] Utiliser `setSecureCookie()` pour cookies sensibles
- [ ] Utiliser `sessionPut()` pour données session sensibles
- [ ] Tokens CSRF actifs sur tous les formulaires
- [ ] HttpOnly activé sur cookies (défaut)

#### ✅ Avancé

- [ ] Rotation régulière de APP_KEY (avec migration des données)
- [ ] Monitoring des échecs de déchiffrement
- [ ] Audit des cookies/sessions chiffrés
- [ ] Tests de sécurité réguliers

---

## 🚨 Que Faire si APP_KEY est Compromise

### 1. Rotation Immédiate

```bash
# 1. Générer nouvelle clé
php sunu key:generate --show
# Copier la nouvelle clé

# 2. Sauvegarder l'ancienne clé temporairement
OLD_APP_KEY=base64:ANCIENNE_CLE

# 3. Mettre la nouvelle clé dans .env
php sunu key:generate --force
```

### 2. Migration des Données

```php
// Script de migration des données chiffrées
function migrateEncryptedData($oldKey, $newKey)
{
    // 1. Cookies - Demander aux utilisateurs de se reconnecter
    // Les anciens cookies seront invalides automatiquement

    // 2. Sessions - Invalider toutes les sessions
    DB::table('sessions')->truncate();

    // 3. Données en BDD - Déchiffrer avec ancienne clé, rechiffrer avec nouvelle
    $records = DB::table('encrypted_data')->get();

    foreach ($records as $record) {
        $oldEncrypter = new Encrypter($oldKey, 'AES-256-CBC');
        $newEncrypter = new Encrypter($newKey, 'AES-256-CBC');

        $decrypted = $oldEncrypter->decrypt($record->data);
        $reencrypted = $newEncrypter->encrypt($decrypted);

        DB::table('encrypted_data')
            ->where('id', $record->id)
            ->update(['data' => $reencrypted]);
    }
}
```

### 3. Actions Post-Rotation

- ✅ Invalider tous les tokens CSRF
- ✅ Forcer reconnexion de tous les utilisateurs
- ✅ Révoquer tous les "Remember Me" tokens
- ✅ Auditer les logs pour activité suspecte
- ✅ Notifier les utilisateurs si nécessaire

---

## 📊 Performance

### Impact du Chiffrement

| Opération | Temps Moyen | Impact |
|-----------|-------------|--------|
| encrypt() | ~0.1ms | Faible |
| decrypt() | ~0.1ms | Faible |
| setSecureCookie() | ~0.15ms | Faible |
| getSecureCookie() | ~0.15ms | Faible |
| sessionPut() | ~0.12ms | Faible |
| sessionGet() | ~0.12ms | Faible |
| CSRF validation | ~0.05ms | Très faible |

**Recommandations :**
- ✅ Utiliser pour données sensibles uniquement
- ✅ Données publiques = pas de chiffrement
- ✅ Cache les résultats de decrypt() si réutilisé
- ✅ Monitoring des performances en production

---

## 📚 Références

- [Documentation APP_KEY](APP_KEY_DOCUMENTATION.md)
- [Documentation Chiffrement](APP_KEY_DOCUMENTATION.md#utilisation-du-chiffrement)
- Classe `Encrypter` : `Core/Encryption/Encrypter.php`
- Classe `CSRF` : `Core/Security/CSRF.php`
- Helpers : `Core/Support/helpers.php`

---

**Auteur** : SunuFramework Team
**Date** : 2024-12-08
**Version** : 2.0.0
