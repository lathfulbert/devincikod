# Orange SMS CI - Guide d'implémentation

## 📋 Résumé

Implémentation complète de l'API Orange SMS pour Côte d'Ivoire dans SunuFramework2, avec support du mode Mock et Production.

## ✅ Fonctionnalités implémentées

### 1. **Dual Mode: Mock & Production**
- **Mode MOCK**: Simulation complète sans appels API réels
- **Mode PRODUCTION**: Intégration réelle avec Orange API
- **Switch dynamique**: Basculement facile via l'interface

### 2. **OAuth Token Caching**
- Cache automatique des tokens OAuth
- Renouvellement automatique avant expiration (30s avant)
- Un token par gateway (multi-tenant safe)
- Storage: `storage/cache/oauth/orange_ci_token_{id}.json`

### 3. **Format Orange API Compliant**
- URL encoding correct: `tel%3A%2B` dans le path
- Format numéros: `tel:+225XXXXXXXX`
- Support du header `X-API-Key` (optionnel)
- Support du paramètre `resource_type_parameter_management=SMS_OCB2` (on-net only)

### 4. **Sécurité**
- Encryption AES-256-CBC des credentials (API Key/Secret)
- Validation CSRF sur tous les endpoints AJAX
- Credentials jamais exposées dans les logs

### 5. **Traçabilité complète**
- Gateway response stockée en JSON
- gateway_message_id pour tracking
- Historique complet de tous les SMS
- Logs d'erreur détaillés

## 📝 Configuration Orange Developer

### Étape 1: Créer un compte
1. Allez sur https://developer.orange.com
2. Créez un compte développeur
3. Validez votre email

### Étape 2: Créer une application
1. **My Apps** → **Create new app**
2. Nom: Votre nom d'application
3. Description: Description de votre projet

### Étape 3: Souscrire à l'API SMS MEA
1. Dans votre application → **Add API**
2. Recherchez **"SMS API - MEA"** (Middle East & Africa)
3. Sélectionnez **"Côte d'Ivoire"**
4. Cliquez **Subscribe**

### Étape 4: Récupérer les credentials
1. Dans votre app → **Credentials**
2. Copiez:
   - **Client ID** (votre API Key)
   - **Client Secret** (votre API Secret)

### Étape 5: Whitelist Sender Name (optionnel)
Pour utiliser un nom d'expéditeur comme "TICAFRIQUE":
1. Contactez le support Orange
2. Demandez le whitelisting de votre sender name
3. Maximum 11 caractères alphanumériques

## 🔧 Configuration dans SunuFramework2

### Configuration de base

1. **Aller sur**: [http://localhost/admin/settings/sms](http://localhost/admin/settings/sms)

2. **Cliquer sur "Éditer"** (icône crayon)

3. **Remplir le formulaire**:
   ```
   Nom: Orange Côte d'Ivoire
   API URL: https://api.orange.com/smsmessaging/v1/outbound
   API Key: VOTRE_CLIENT_ID
   API Secret: VOTRE_CLIENT_SECRET
   Sender ID: +2250XXXXXXXXX (votre numéro enregistré)
   Priorité: 10
   ```

4. **Cliquer "Mettre à jour"**

5. **Cliquer le bouton "PROD"** pour activer le mode production

### Configuration avancée

Pour ajouter des options avancées, éditez directement en base de données:

```sql
UPDATE sms_gateways
SET configuration = '{
  "auth_type": "oauth2",
  "token_url": "https://api.orange.com/oauth/v3/token",
  "country_code": "+225",
  "x_api_key": "YOUR_X_API_KEY",
  "on_net_only": false
}'
WHERE provider_code = 'orange_ci';
```

**Options disponibles**:
- `token_url`: URL OAuth token (défaut: `https://api.orange.com/oauth/v3/token`)
- `country_code`: Code pays (défaut: `+225`)
- `x_api_key`: Header X-API-Key si requis par Orange
- `on_net_only`: `true` pour limiter aux numéros Orange uniquement

## 🚀 Utilisation

### Mode MOCK (Développement)

```php
use Modules\SmsCore\Services\SmsService;

$sms = new SmsService();

// Envoi simulation (mode MOCK)
$result = $sms->send(
    to: '+2250749270077',
    message: 'Test SMS',
    senderId: 'TICAFRIQUE'
);

// $result->success = true (toujours en MOCK)
// $result->mock = true
// $result->messageId = 'MOCK-ABC123'
```

### Mode PRODUCTION (Réel)

```php
use Modules\SmsCore\Services\SmsService;

$sms = new SmsService();

// Envoi réel via Orange API
$result = $sms->send(
    to: '+2250749270077',
    message: 'Votre code de vérification: 123456',
    senderId: 'TICAFRIQUE'
);

if ($result->success) {
    echo "SMS envoyé! ID: " . $result->messageId;
} else {
    echo "Erreur: " . $result->message;
}
```

## 📊 Vérification du mode

### Via l'interface

Allez sur [/admin/settings/sms](http://localhost/admin/settings/sms) et regardez la colonne **"Mode"**:
- 🧪 Badge orange **MOCK** = Mode simulation
- 🚀 Badge vert **PROD** = Mode production

### Via CLI

```bash
php check_gateway_mode.php
```

Output:
```
Mode : 🚀 PRODUCTION (Réel)
Crédentials:
  API Key    : ✓ Configurée (32 chars)
  API Secret : ✓ Configurée (44 chars)
```

## 🔍 Debugging

### Vérifier les logs d'erreur

Les erreurs Orange API sont loggées dans PHP error log:

```bash
tail -f c:/laragon/logs/php_error.log | grep "Orange CI"
```

### Tester les credentials

```bash
php test_orange_credentials.php YOUR_CLIENT_ID YOUR_CLIENT_SECRET
```

### Vérifier le cache OAuth

```bash
cat storage/cache/oauth/orange_ci_token_1.json
```

Output:
```json
{
  "access_token": "eyJhbGciOiJ...",
  "expires_at": 1735574400
}
```

### Vérifier l'historique SMS

Allez sur [/admin/sms/history](http://localhost/admin/sms/history) et cliquez sur "Détails" pour voir:
- La requête complète envoyée
- La réponse de l'API Orange
- Les headers utilisés
- Le status HTTP

## ⚠️ Problèmes courants

### 1. "Failed to obtain access token"

**Cause**: Credentials invalides ou application non activée

**Solution**:
1. Vérifiez vos credentials sur https://developer.orange.com
2. Vérifiez que vous avez souscrit à "SMS API - MEA"
3. Vérifiez que l'application est **Active** (pas Pending)

### 2. HTTP 404 sur token endpoint

**Cause**: URL OAuth incorrecte ou API non disponible pour votre région

**Solution**:
1. Utilisez `https://api.orange.com/oauth/v3/token`
2. Contactez le support Orange si le problème persiste

### 3. SMS non reçus en mode PRODUCTION

**Vérifications**:
1. ✅ Gateway en mode PROD (badge vert)
2. ✅ Credentials valides
3. ✅ Numéro au format international: `+225XXXXXXXX`
4. ✅ Sender ID whitelisté chez Orange
5. ✅ Crédit disponible sur votre compte Orange

### 4. "Invalid sender address"

**Cause**: Format du sender incorrect ou non whitelisté

**Solution**:
1. Utilisez un numéro valide comme sender: `+2250XXXXXXXXX`
2. Ou demandez le whitelisting de votre sender name auprès d'Orange

## 📚 Références

- **Documentation Orange SMS MEA**: https://developer.orange.com/apis/sms-mea
- **Orange Developer Portal**: https://developer.orange.com
- **Support Orange**: Via le portail Developer

## 🎯 Workflow recommandé

```
1. Développement Local
   ↓
   Mode MOCK (simulation)
   ↓
2. Tests fonctionnels
   ↓
   Tout fonctionne en MOCK
   ↓
3. Obtenir credentials Orange
   ↓
   Créer app sur developer.orange.com
   ↓
4. Configuration Production
   ↓
   Éditer gateway + entrer credentials
   ↓
5. Basculer en PROD
   ↓
   Cliquer bouton "PROD" (vert)
   ↓
6. Test 1 SMS réel
   ↓
   Envoyer à votre propre numéro
   ↓
7. Vérifier réception
   ↓
   SMS reçu = ✅ Système opérationnel
   ↓
8. Déploiement
```

## 📈 Statistiques et monitoring

### Consulter le solde SMS

```php
$gateway = SmsGateway::find(1);
$balance = $gateway->getBalance();
// Note: Orange API ne fournit pas cette fonction actuellement
```

### Historique des envois

Allez sur [/admin/sms/history](http://localhost/admin/sms/history) pour voir:
- Tous les SMS envoyés
- Status (success/failed)
- Gateway response
- Coût estimé

---

**Version**: 2.0
**Date**: 2025-11-29
**Auteur**: SunuFramework2 SMS Module
