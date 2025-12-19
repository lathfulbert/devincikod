# Intégration avec le module ApiKeys

## Vue d'ensemble

Le module SmsCore utilise le **module ApiKeys existant** pour la gestion des clés API au lieu de réimplémenter cette fonctionnalité.

## Architecture

```
Module ApiKeys (Modules/ApiKeys/)
    ↓
    Gère les clés API pour TOUS les modules
    ↓
Module SmsCore (Modules/SmsCore/)
    ↓
    Utilise les clés API pour l'authentification
```

## Flux d'authentification

### 1. Génération de clé API

**Utilisateur :**
1. Accède à `/admin/apikeys` (module ApiKeys)
2. Clique sur "Générer une clé API"
3. Copie la clé générée (format : `sk_xxxxxxxxxxxxxxxx`)

**Système :**
- Le module ApiKeys stocke la clé dans la table `api_keys`
- La clé est liée à l'utilisateur (`user_id`)

### 2. Utilisation de la clé API

**Requête API SMS :**
```bash
curl -X POST https://votre-domaine.com/api/v1/sms/send \
  -H "Authorization: Bearer sk_xxxxxxxxxxxxxxxx" \
  -d '{"to": "+225XXXXXXXX", "message": "Test"}'
```

**Traitement :**
1. Middleware `api_auth` intercepte la requête
2. Extrait la clé du header `Authorization`
3. Valide la clé via le module ApiKeys
4. Charge l'utilisateur associé
5. Vérifie les permissions RBAC
6. Exécute le contrôleur SMS

## Routes intégrées

### Module SmsCore

| Route | Action |
|-------|--------|
| `GET /admin/sms/api/docs` | Afficher la documentation API |
| `GET /admin/sms/api/keys` | Redirection vers `/admin/apikeys` |

### Module ApiKeys (utilisé)

| Route | Action |
|-------|--------|
| `GET /admin/apikeys` | Interface de gestion des clés |
| `POST /admin/apikeys/generate` | Générer une nouvelle clé |
| `POST /admin/apikeys/revoke` | Révoquer une clé |

## Middleware d'authentification

### ApiAuthMiddleware

**Localisation :** `App\Core\Middleware\ApiAuthMiddleware`

**Enregistrement :**
```php
// Core/Application.php
$this->router->registerMiddleware('api_auth', \App\Core\Middleware\ApiAuthMiddleware::class);
```

**Utilisation dans SmsCore :**
```php
// Modules/SmsCore/SmsCoreModule.php
['POST', '/api/v1/sms/send', [SmsApiController::class, 'send'], ['api_auth', 'can:sms.send']]
```

## Modèle de données

### Table api_keys (module ApiKeys)

```sql
CREATE TABLE api_keys (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    key VARCHAR(64) UNIQUE NOT NULL,
    name VARCHAR(255),
    last_used_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id)
);
```

### Modèle ApiKey

**Localisation :** `Modules\ApiKeys\Models\ApiKey`

**Méthodes principales :**
- `static validate($key)` - Valider une clé
- `user()` - Relation avec User
- `touch()` - Mettre à jour last_used_at

## Avantages de cette intégration

### ✅ Avantages

1. **Centralisation** : Une seule source de vérité pour les clés API
2. **Réutilisation** : Même clé API pour SMS, Email, etc.
3. **Maintenance** : Moins de code dupliqué
4. **Sécurité** : Gestion des clés centralisée et uniforme
5. **Monitoring** : Suivi centralisé de l'utilisation

### 📊 Statistiques d'utilisation

Le module ApiKeys peut tracker :
- Dernière utilisation (`last_used_at`)
- Nombre total d'appels API
- Appels par endpoint

Le module SmsCore peut tracker spécifiquement :
- SMS envoyés via API
- Coût total via API
- Taux de succès

## Exemple complet

### 1. Configuration utilisateur

```php
// L'utilisateur génère sa clé via /admin/apikeys
$apiKey = ApiKey::create([
    'user_id' => auth()->id(),
    'key' => 'sk_' . bin2hex(random_bytes(32)),
    'name' => 'Production API Key'
]);
```

### 2. Utilisation de la clé

```php
// Requête API
POST /api/v1/sms/send
Headers:
  Authorization: Bearer sk_abc123...
  Content-Type: application/json
Body:
  {
    "to": "+225XXXXXXXX",
    "message": "Test SMS"
  }
```

### 3. Traitement

```php
// ApiAuthMiddleware
$apiKey = ApiKey::validate($keyFromHeader);
$user = $apiKey->user;

// SmsApiController
$result = $smsSender->send($to, $message, [
    'user_id' => $user->id,
    'source' => 'api'
]);
```

### 4. Tracking

```php
// Module ApiKeys met à jour last_used_at
$apiKey->touch();

// Module SmsCore enregistre dans sms_billing_logs
SmsBillingLog::create([
    'user_id' => $user->id,
    'source' => 'api',
    'cost' => $result['cost']
]);
```

## Migration depuis l'ancienne implémentation

Si vous aviez une ancienne implémentation avec `users.api_key` :

### Étape 1 : Migrer les clés existantes

```php
// Migration
$users = User::whereNotNull('api_key')->get();
foreach ($users as $user) {
    ApiKey::create([
        'user_id' => $user->id,
        'key' => $user->api_key,
        'name' => 'Migrated Key'
    ]);
}
```

### Étape 2 : Mettre à jour le middleware

Le middleware `api_auth` cherche maintenant dans la table `api_keys` au lieu de `users.api_key`.

### Étape 3 : Nettoyer

```sql
-- Optionnel : supprimer l'ancienne colonne
ALTER TABLE users DROP COLUMN api_key;
```

## Support multi-clés

Le module ApiKeys permet à un utilisateur d'avoir **plusieurs clés API** :

```php
// Production
$prodKey = ApiKey::create([
    'user_id' => $user->id,
    'name' => 'Production',
    'key' => 'sk_prod_xxx'
]);

// Development
$devKey = ApiKey::create([
    'user_id' => $user->id,
    'name' => 'Development',
    'key' => 'sk_dev_xxx'
]);
```

Chaque clé peut avoir :
- Un nom distinct
- Un suivi d'utilisation séparé
- Une révocation indépendante

## Documentation utilisateur

**Liens importants :**
- Gestion clés : `/admin/apikeys`
- Documentation SMS : `/admin/sms/api/docs`
- Dashboard SMS : `/admin/sms`

**Documentation :**
- Guide complet : `Documentation/API_DOCUMENTATION.md`
- Guide admin : `Documentation/ADMIN_GUIDE.md`

---

**Dernière mise à jour :** 10 Décembre 2025
