# Guide de Vérification SMS - Process & Traçabilité

Ce document explique comment vérifier si les SMS passent réellement par les gateways et comment tracer toutes les étapes.

## 📋 Architecture du Système SMS

### 1. **Flow d'envoi de SMS**

```
User (Interface)
    ↓
SmsController::send()
    ↓
SmsGatewayFactory::create() → Crée l'instance du gateway (OrangeCIGateway ou InfobipGateway)
    ↓
Gateway::send() → Appelle l'API du fournisseur (Orange, Infobip, etc.)
    ↓
Réponse API → Stockée dans gateway_response
    ↓
SmsMessage::markAsSent() ou markAsFailed()
    ↓
Redirection vers /admin/sms/history avec message flash
```

### 2. **Comment vérifier que ça passe par le gateway ?**

#### A. **Vérification dans la Base de Données**

Chaque SMS envoyé est enregistré dans `sms_messages` avec:

| Champ | Description | Usage |
|-------|-------------|-------|
| `status` | pending, sent, delivered, failed | Indique si l'envoi a réussi |
| `gateway` | orange_ci, infobip, etc. | Quel gateway a été utilisé |
| `gateway_message_id` | ID retourné par le fournisseur | Preuve que l'API a été appelée |
| `gateway_response` | JSON complet de la réponse | Toute la réponse du fournisseur |
| `sent_at` | Timestamp | Quand le SMS a été envoyé |
| `error` | Message d'erreur | Si échec, pourquoi |

**Requête SQL pour vérifier:**
```sql
SELECT
    id,
    `to`,
    gateway,
    status,
    gateway_message_id,
    sent_at,
    JSON_PRETTY(gateway_response) as response
FROM sms_messages
ORDER BY created_at DESC
LIMIT 10;
```

#### B. **Via l'Interface Web**

1. **Page SMS History** (`/admin/sms/history`)
   - Liste tous les SMS avec leur statut
   - Bouton "Détails" pour chaque SMS

2. **Page Détails** (`/admin/sms/details/{id}`)
   - Affiche toutes les informations
   - **Gateway Response**: montre la réponse complète de l'API
   - Présence du `gateway_message_id` = preuve que l'API a répondu

#### C. **Logs Gateway** (À implémenter si besoin)

Pour un suivi encore plus détaillé, vous pouvez ajouter des logs:

```php
// Dans OrangeCIGateway::send()
error_log("[SMS] Envoi vers {$to} via Orange CI");
error_log("[SMS] Payload: " . json_encode($payload));
error_log("[SMS] Response HTTP {$httpCode}: " . $response);
```

## 🔍 **Tests de Vérification**

### Test 1: Gateway Orange CI (avec vraies credentials)

1. Configurez Orange CI dans `/admin/settings/sms`
2. Entrez vos vraies API credentials
3. Envoyez un SMS test
4. Allez dans Détails du SMS
5. Vérifiez:
   - ✅ `gateway_message_id` présent = API appelée
   - ✅ `gateway_response` contient la réponse d'Orange
   - ✅ `status` = sent (si succès) ou failed (si erreur)
   - ✅ `error` explique le problème si échec

### Test 2: Gateway Infobip

Même process que Orange CI.

### Test 3: Vérifier avec CURL (Simulation)

Vous pouvez tester l'API directement:

```bash
# Test Orange CI Token
curl -X POST https://api.orange.com/oauth/v2/token \
  -u "YOUR_API_KEY:YOUR_API_SECRET" \
  -d "grant_type=client_credentials"

# Test Infobip
curl -X POST https://api.infobip.com/sms/2/text/advanced \
  -H "Authorization: App YOUR_API_KEY" \
  -H "Content-Type: application/json" \
  -d '{
    "messages": [{
      "from": "YourSender",
      "destinations": [{"to": "+225XXXXXXXX"}],
      "text": "Test message"
    }]
  }'
```

## 🎯 **Indicateurs de Succès**

### ✅ **SMS envoyé avec succès:**
- `status` = `sent`
- `gateway_message_id` présent (ex: `msg-abc123`)
- `gateway_response` contient l'objet JSON complet
- `sent_at` rempli
- `error` = NULL
- Message flash: "SMS envoyé avec succès via Orange Côte d'Ivoire - ID: msg-abc123"

### ❌ **SMS échoué:**
- `status` = `failed`
- `error` contient la raison (ex: "Invalid API key", "Insufficient balance")
- `gateway_response` peut contenir les détails de l'erreur
- Message flash: "Échec d'envoi: Invalid API key"

### ⚠️ **Gateway non configuré:**
- Message flash: "Aucun gateway SMS disponible. Veuillez configurer un gateway."
- Aucun enregistrement créé dans `sms_messages`

## 🔄 **Process Complet de Vérification**

### Étape 1: Configuration
```bash
# Vérifier les gateways disponibles
php check_sms_gateways.php
```

Output attendu:
```
Gateways SMS disponibles:
=========================

- Orange Côte d'Ivoire [orange_ci]
  Active: Oui
  Default: Oui

- Infobip [infobip]
  Active: Non
  Default: Non
```

### Étape 2: Envoi de SMS Test

1. Aller sur `/admin/sms/send`
2. Remplir:
   - To: +225XXXXXXXX
   - Sender: TestApp
   - Message: Test d'envoi SMS
   - Gateway: Orange Côte d'Ivoire (ou Auto)
3. Cliquer "Send SMS"

### Étape 3: Vérification Immédiate

**A. Via Interface:**
- Redirection automatique vers `/admin/sms/history`
- Message flash en haut: succès ou échec
- SMS apparaît dans la liste

**B. Cliquer sur "Détails":**
- Voir tous les champs
- **Gateway Response** montre la réponse JSON complète de l'API

**C. Via Base de Données:**
```sql
SELECT * FROM sms_messages WHERE id = LAST_INSERT_ID();
```

### Étape 4: Vérification Chez le Fournisseur

**Orange Developer Portal:**
- Connectez-vous sur https://developer.orange.com
- Allez dans "My Apps" → "SMS API"
- Consultez les logs d'usage
- Vérifiez que votre `gateway_message_id` apparaît

**Infobip Dashboard:**
- Connectez-vous sur https://portal.infobip.com
- SMS → Reports
- Cherchez le message par ID

## 📊 **Tableaux de Diagnostic**

### Scénario 1: Credentials Invalides

| Champ | Valeur |
|-------|--------|
| status | failed |
| error | "Failed to obtain access token" (Orange) ou "Invalid API key" (Infobip) |
| gateway_response | {"error": "invalid_grant"} |
| gateway_message_id | NULL |

### Scénario 2: Envoi Réussi

| Champ | Valeur |
|-------|--------|
| status | sent |
| error | NULL |
| gateway_response | {"messages": [{"messageId": "abc123", "status": {...}}]} |
| gateway_message_id | abc123 ou resourceURL |
| sent_at | 2025-01-15 10:30:45 |

### Scénario 3: Numéro Invalide

| Champ | Valeur |
|-------|--------|
| status | failed |
| error | "Invalid phone number format" |
| gateway_response | {"requestError": {"serviceException": {"text": "Invalid destination"}}} |
| gateway_message_id | NULL |

## 🚀 **Prochaines Étapes (Optionnel)**

### 1. **Queue System** (Pour envois massifs)

Créer une table `sms_queue`:
```sql
CREATE TABLE sms_queue (
    id INT PRIMARY KEY AUTO_INCREMENT,
    sms_message_id INT,
    status ENUM('pending', 'processing', 'completed', 'failed'),
    attempts INT DEFAULT 0,
    next_retry_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
```

### 2. **Webhook/Callback** (Pour delivery reports)

Ajouter un endpoint pour recevoir les DLR (Delivery Report):
```php
// Route: POST /api/sms/webhook/{provider}
public function webhook($provider) {
    $payload = json_decode(file_get_contents('php://input'), true);

    // Trouver le SMS par gateway_message_id
    $sms = SmsMessage::where('gateway_message_id', $payload['messageId'])->first();

    if ($sms && $payload['status'] === 'delivered') {
        $sms->markAsDelivered();
    }
}
```

### 3. **Cron Job** (Pour les retry)

```bash
# Crontab: toutes les 5 minutes
*/5 * * * * php /path/to/sunu/artisan sms:retry-failed
```

## 📝 **Checklist Finale**

Avant de mettre en production:

- [ ] Credentials réelles configurées dans `/admin/settings/sms`
- [ ] Test d'envoi réussi avec chaque gateway
- [ ] `gateway_message_id` présent dans les détails
- [ ] Vérification dans le dashboard du fournisseur
- [ ] Messages flash fonctionnels (succès/erreur)
- [ ] Page détails affiche `gateway_response`
- [ ] Logs activés si nécessaire
- [ ] Webhook configuré (optionnel)
- [ ] Queue system en place pour bulk (optionnel)

## 🔧 **Troubleshooting**

| Problème | Solution |
|----------|----------|
| `gateway_message_id` est NULL | L'API n'a pas été appelée ou a échoué. Vérifier `error` et `gateway_response` |
| `gateway_response` est NULL | Exception PHP avant l'appel API. Vérifier les logs PHP |
| Status toujours "pending" | `markAsSent()` ou `markAsFailed()` non appelé. Bug dans le code |
| Pas de réponse du fournisseur | Timeout réseau ou credentials invalides |

---

**Auteur:** SunuFramework2 SMS Module
**Version:** 1.0
**Date:** 2025
