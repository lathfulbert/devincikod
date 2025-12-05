# Guide de démarrage rapide - Module Auth & MFA

## Installation en 3 étapes

### Étape 1 : Exécuter les migrations

```bash
cd C:\laragon\www\sunuframework2
php run_auth_migration.php
```

✅ Cela créera 4 nouvelles tables et mettra à jour la table `users`.

### Étape 2 : Synchroniser les modules

```bash
php sync_modules.php
```

✅ Le module Auth sera enregistré et activé.

### Étape 3 : Tester la connexion

1. Accédez à : `http://localhost:81/sunuframework2/auth/login`
2. Connectez-vous avec un compte existant
3. Si tout fonctionne, vous serez redirigé vers le dashboard

## Activer le MFA (optionnel)

### Pour activer TOTP (Google Authenticator)

1. Connectez-vous
2. Accédez à `/auth/mfa/settings`
3. Cliquez sur "Activer TOTP"
4. Scannez le QR code avec Google Authenticator
5. Entrez le code de vérification
6. ✅ TOTP activé !

À la prochaine connexion, un code sera demandé.

### Pour activer SMS OTP

1. Allez dans `/auth/mfa/settings`
2. Cliquez sur "Activer SMS"
3. Entrez votre numéro de téléphone
4. Vous recevrez un code par SMS
5. ✅ SMS OTP activé !

**Note** : Assurez-vous que le module `SmsCore` est configuré.

## Exemples de code

### Vérifier si un utilisateur a activé le MFA

```php
use Modules\Users\Models\User;

$user = User::find($userId);

if ($user->hasMfaEnabled()) {
    echo "MFA activé";
    $methods = $user->getVerifiedMfaMethods();
    print_r($methods); // ['totp', 'sms']
}
```

### Forcer le MFA pour un rôle spécifique

```php
use Modules\Auth\Services\MfaManager;

$mfaManager = new MfaManager();

// Activer TOTP pour tous les admins
$admins = User::query()
    ->whereHas('roles', function($q) {
        $q->where('slug', 'admin');
    })
    ->get();

foreach ($admins as $admin) {
    if (!$admin->hasMfaEnabled()) {
        $mfaManager->setupMethod($admin->id, 'totp');
    }
}
```

### Générer des tokens JWT

```php
use Modules\Auth\Services\TokenManager;

$tokenManager = new TokenManager();

$tokens = $tokenManager->generateTokenPair($userId);

echo $tokens['access_token'];  // Pour l'API
echo $tokens['refresh_token']; // Pour renouveler
echo $tokens['expires_in'];    // 3600 secondes
```

## Configuration rapide

Éditez `Modules/Auth/Config/auth.php` :

```php
return [
    // Rendre le MFA obligatoire pour tous
    'mfa' => [
        'required' => true, // Changez false → true
    ],

    // Augmenter la sécurité du mot de passe
    'password' => [
        'min_length' => 12,            // Au lieu de 8
        'require_special_chars' => true, // Activer
    ],

    // Assouplir le rate limiting en dev
    'rate_limiting' => [
        'max_attempts' => 10, // Au lieu de 5
    ],
];
```

## Commandes utiles

### Voir les logs d'authentification

```php
use Modules\Auth\Models\AuthLog;

// Dernières connexions
$logs = AuthLog::query()
    ->orderBy('created_at', 'DESC')
    ->limit(20)
    ->get();

foreach ($logs as $log) {
    echo "{$log->event_type} - {$log->ip_address} - {$log->created_at}\n";
}
```

### Désactiver le MFA pour un utilisateur

```php
use Modules\Auth\Services\MfaManager;

$mfaManager = new MfaManager();
$mfaManager->disableMethod($userId, 'totp');
$mfaManager->disableMethod($userId, 'sms');
```

### Vérifier le rate limiting

```php
use Modules\Auth\Services\AuditLogger;

$auditLogger = new AuditLogger();

$attempts = $auditLogger->getRecentFailedAttempts($userId, 15);
echo "Tentatives échouées : $attempts / 5\n";

if ($auditLogger->shouldRateLimit($userId)) {
    echo "⚠️ Utilisateur bloqué\n";
}
```

## Problèmes courants

### ❌ "Table mfa_methods not found"

**Solution** : Exécutez les migrations

```bash
php run_auth_migration.php
```

### ❌ "SMS OTP not available"

**Solution** : Activez le module SmsCore

```bash
# Vérifiez que SmsCore existe
ls Modules/SmsCore

# Synchronisez les modules
php sync_modules.php
```

### ❌ Le QR code TOTP ne s'affiche pas

**Solution temporaire** : Copiez manuellement le secret et ajoutez-le dans Google Authenticator

Le secret est affiché sous le QR code.

## Routes disponibles

| Route                 | Méthode  | Description            |
| --------------------- | -------- | ---------------------- |
| `/auth/login`         | GET/POST | Page de connexion      |
| `/auth/logout`        | GET      | Déconnexion            |
| `/auth/register`      | GET/POST | Inscription            |
| `/auth/mfa/challenge` | GET/POST | Vérification MFA       |
| `/auth/mfa/settings`  | GET      | Paramètres MFA         |
| `/auth/mfa/setup`     | POST     | Activer une méthode    |
| `/auth/mfa/disable`   | POST     | Désactiver une méthode |

## Support

📚 Documentation complète : `Modules/Auth/README.md`

🔍 Code source : `Modules/Auth/`

📝 Journal d'audit : Table `auth_logs`

---

✨ **Module prêt à l'emploi !**
