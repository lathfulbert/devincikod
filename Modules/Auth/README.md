# Module d'Authentification et MFA

## Vue d'ensemble

Le module **Auth** est un système d'authentification complet et sécurisé pour le framework LathDevinci. Il prend en charge l'authentification classique par mot de passe ainsi que l'authentification multi-facteurs (MFA) avec plusieurs méthodes.

## Caractéristiques principales

✅ **Authentification par mot de passe**

- Hachage Argon2id (ou bcrypt en fallback)
- Politique de mot de passe configurable
- Protection contre les attaques par force brute

✅ **Authentification multi-facteurs (MFA)**

- TOTP (Google Authenticator, Authy)
- OTP par SMS
- OTP par Email
- Choix utilisateur (activation optionnelle)

✅ **Sécurité**

- Limitation du taux de tentatives (rate limiting)
- Journal d'audit complet
- Tokens JWT pour API
- Détection d'activité suspecte

## Installation

### 1. Exécuter les migrations

```bash
php run_auth_migration.php
```

Cela créera les tables suivantes :

- `mfa_methods` - Méthodes MFA disponibles
- `user_mfa_setup` - Configuration MFA des utilisateurs
- `oauth_accounts` - Comptes OAuth liés
- `auth_logs` - Journal des événements d'authentification

### 2. Synchroniser le module

```bash
php sync_modules.php
```

### 3. Configuration

Éditez `Modules/Auth/Config/auth.php` selon vos besoins :

```php
return [
    'mfa' => [
        'enabled' => true,        // Activer le MFA
        'required' => false,      // false = optionnel, true = obligatoire
        'methods' => [
            'totp' => true,       // Google Authenticator
            'sms' => true,        // OTP par SMS
            'email' => true,      // OTP par Email
        ],
    ],

    'password' => [
        'min_length' => 8,
        'require_uppercase' => true,
        'require_numbers' => true,
    ],

    'rate_limiting' => [
        'max_attempts' => 5,      // Tentatives max
        'lockout_duration' => 900, // 15 minutes
    ],
];
```

## Utilisation

### Connexion simple

**Route** : `/auth/login`

Les utilisateurs se connectent avec leur email et mot de passe. Si le MFA est activé, ils seront redirigés vers la page de vérification.

### Activer le MFA pour un utilisateur

#### Méthode 1 : Via l'interface utilisateur

1. Connectez-vous
2. Accédez à `/auth/mfa/settings`
3. Choisissez une méthode (TOTP, SMS, Email)
4. Suivez les instructions

#### Méthode 2 : Par code

```php
use Modules\Auth\Services\MfaManager;

$mfaManager = new MfaManager();

// Activer TOTP
$result = $mfaManager->setupMethod($userId, 'totp');
// Retourne : QR code URL, secret, codes de récupération

// Activer SMS
$result = $mfaManager->setupMethod($userId, 'sms', [
    'phone' => '+33612345678'
]);

// Activer Email
$result = $mfaManager->setupMethod($userId, 'email');
```

### Vérifier le MFA

```php
$mfaManager = new MfaManager();

// Vérifier un code TOTP
$isValid = $mfaManager->verify($userId, 'totp', '123456');

// Envoyer un OTP par SMS
$mfaManager->sendOtp($userId, 'sms');

// Vérifier l'OTP SMS
$isValid = $mfaManager->verify($userId, 'sms', '123456');
```

### Protéger une route avec MFA

```php
// Dans vos routes
$router->get('/admin/sensitive-data', [Controller::class, 'method'], ['mfa']);
```

Le middleware `RequireMfa` vérifiera automatiquement si l'utilisateur a complété la vérification MFA.

## Architecture du module

### Fournisseurs d'authentification (Providers)

Tous les fournisseurs implémentent `AuthProviderInterface` :

```php
interface AuthProviderInterface
{
    public function getName(): string;
    public function verify($identifier, $credential);
    public function setup(int $userId, array $data): array;
    public function isAvailable(): bool;
}
```

**Fournisseurs disponibles** :

- `PasswordProvider` - Authentification par mot de passe
- `TotpProvider` - TOTP (Google Authenticator)
- `SmsOtpProvider` - OTP par SMS
- `EmailOtpProvider` - OTP par Email

### Services

#### MfaManager

Orchestre les opérations MFA.

```php
$mfaManager = new MfaManager();

// Vérifier si MFA est requis
$required = $mfaManager->isRequired($userId);

// Obtenir les méthodes activées
$methods = $mfaManager->getAvailableMethods($userId);

// Configurer une méthode
$result = $mfaManager->setupMethod($userId, 'totp');

// Désactiver une méthode
$mfaManager->disableMethod($userId, 'totp');
```

#### TokenManager

Gère les tokens JWT pour les API.

```php
$tokenManager = new TokenManager();

// Générer des tokens
$tokens = $tokenManager->generateTokenPair($userId);
// Retourne: access_token, refresh_token, expires_in

// Vérifier un token
$payload = $tokenManager->verifyToken($accessToken);

// Rafraîchir l'access token
$newTokens = $tokenManager->refreshAccessToken($refreshToken);
```

#### AuditLogger

Journalise tous les événements d'authentification.

```php
$auditLogger = new AuditLogger();

// Journaliser une connexion réussie
$auditLogger->logLoginSuccess($userId);

// Journaliser une tentative échouée
$auditLogger->logLoginFailed($userId, 'invalid_password');

// Journaliser un événement MFA
$auditLogger->logMfaSuccess($userId, 'totp');
$auditLogger->logMfaFailed($userId, 'sms', 'code_expired');

// Vérifier le rate limiting
$shouldLimit = $auditLogger->shouldRateLimit($userId);
```

## Flux d'authentification

### Connexion sans MFA

```
1. Utilisateur → /auth/login (email + password)
2. PasswordProvider vérifie les credentials
3. Session créée
4. Redirection → /admin/dashboard
```

### Connexion avec MFA

```
1. Utilisateur → /auth/login (email + password)
2. PasswordProvider vérifie les credentials
3. MfaManager vérifie si MFA est activé
4. Si oui → Redirection vers /auth/mfa/challenge
5. Utilisateur saisit le code MFA
6. MfaManager vérifie le code
7. Session créée
8. Redirection → /admin/dashboard
```

### Configuration TOTP (Google Authenticator)

```
1. Utilisateur → /auth/mfa/settings
2. Sélectionne "TOTP"
3. TotpProvider génère un secret
4. QR Code affiché
5. Utilisateur scanne avec son app
6. Saisit un code de test
7. TotpProvider vérifie le code
8. Configuration enregistrée ✅
```

## Sécurité

### Hachage des mots de passe

Les mots de passe sont hashés avec **Argon2id** (ou bcrypt si indisponible) :

```php
$hashedPassword = password_hash($password, PASSWORD_ARGON2ID);
```

### Rate Limiting

- **5 tentatives** maximum en **15 minutes**
- Verrouillage par utilisateur ET par IP
- Événements journalisés dans `auth_logs`

### OTP Sécurisé

- Les codes OTP ne sont **jamais stockés en clair**
- Stockage du hash SHA-256 en session
- Expiration : 2 minutes (SMS/Email)
- 3 tentatives maximum

### Tokens JWT

- **Access Token** : 1 heure
- **Refresh Token** : 30 jours
- Signature HMAC SHA-256
- Clé secrète configurable via `.env`

### Journal d'audit

Tous les événements sont journalisés :

```php
// Types d'événements
- login_success
- login_failed
- mfa_success
- mfa_failed
- logout
- password_reset
- account_locked
```

Chaque entrée contient :

- `user_id`
- `event_type`
- `ip_address`
- `user_agent`
- `details` (JSON)
- `created_at`

## API REST (pour SPA/Mobile)

### Connexion

**POST** `/auth/login`

```json
{
  "email": "user@example.com",
  "password": "secretpass"
}
```

**Réponse** :

```json
{
  "access_token": "eyJ0eXAiOiJKV1Qi...",
  "refresh_token": "eyJ0eXAiOiJKV1Qi...",
  "expires_in": 3600,
  "mfa_required": false
}
```

Si MFA requis :

```json
{
  "mfa_required": true,
  "available_methods": ["totp", "sms"]
}
```

### Vérification MFA

**POST** `/auth/mfa/verify`

```json
{
  "method": "totp",
  "code": "123456"
}
```

### Rafraîchir le token

**POST** `/auth/refresh`

```json
{
  "refresh_token": "eyJ0eXAiOiJKV1Qi..."
}
```

## Exemples d'utilisation

### Exemple 1 : Forcer le MFA pour les administrateurs

```php
use Modules\Auth\Services\MfaManager;
use Modules\Users\Models\User;

// Dans un script ou contrôleur
$admins = User::query()
    ->join('user_roles', 'users.id', '=', 'user_roles.user_id')
    ->join('roles', 'user_roles.role_id', '=', 'roles.id')
    ->where('roles.slug', 'admin')
    ->get();

$mfaManager = new MfaManager();

foreach ($admins as $admin) {
    if (!$admin->hasMfaEnabled()) {
        // Forcer l'activation du TOTP
        $result = $mfaManager->setupMethod($admin->id, 'totp');

        // Envoyer par email les instructions
        // mail($admin->email, "Activer MFA", ...);
    }
}
```

### Exemple 2 : Désactiver temporairement le MFA

```php
$mfaManager = new MfaManager();

// Désactiver toutes les méthodes
$mfaManager->disableMethod($userId, 'totp');
$mfaManager->disableMethod($userId, 'sms');
$mfaManager->disableMethod($userId, 'email');
```

### Exemple 3 : Consulter le journal d'audit

```php
use Modules\Auth\Models\AuthLog;

// Dernières tentatives échouées
$failedAttempts = AuthLog::query()
    ->where('user_id', $userId)
    ->where('event_type', AuthLog::EVENT_LOGIN_FAILED)
    ->orderBy('created_at', 'DESC')
    ->limit(10)
    ->get();

foreach ($failedAttempts as $attempt) {
    echo "IP: {$attempt->ip_address} - {$attempt->created_at}\n";
}
```

## Dépannage

### Le QR Code TOTP ne s'affiche pas

Vérifiez que l'URL du QR code est accessible :

```php
// Dans TotpProvider.php, la méthode getQrCodeUrl()
// utilise l'API Google Charts
'https://chart.googleapis.com/chart?...'
```

Alternative : utilisez une bibliothèque PHP pour générer le QR code localement.

### Les OTP SMS ne sont pas envoyés

Vérifiez que le module `SmsCore` est actif :

```php
$isAvailable = class_exists('\Modules\SmsCore\Services\SmsService');
```

En développement, les codes sont journalisés dans le fichier de log PHP.

### Rate limiting trop strict

Ajustez la configuration :

```php
// Config/auth.php
'rate_limiting' => [
    'max_attempts' => 10,      // Au lieu de 5
    'lockout_duration' => 600, // Au lieu de 900
],
```

## Roadmap (fonctionnalités futures)

- [ ] OAuth2 (Google, Microsoft, GitHub)
- [ ] LDAP / Active Directory
- [ ] WebAuthn / FIDO2 (clés de sécurité)
- [ ] Codes de récupération (backup codes)
- [ ] Notifications d'activité suspecte
- [ ] Gestion des appareils de confiance

## Support

Pour toute question ou problème, consultez :

- Le code source dans `Modules/Auth/`
- Les logs dans `auth_logs` (table)
- La documentation technique dans `walkthrough.md`

## Licence

Ce module fait partie du framework LathDevinci.

---

**Développé avec ❤️ pour la sécurité de vos applications**
