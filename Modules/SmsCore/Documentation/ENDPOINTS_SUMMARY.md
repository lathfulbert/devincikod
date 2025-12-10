# Récapitulatif des Endpoints SMS

## 📍 Routes API disponibles

### Authentification
Toutes les routes API utilisent l'authentification par clé API via le middleware `api_auth`.

---

## 🔥 Endpoints API publics

### 1. Envoyer un SMS
**Route:** `POST /api/v1/sms/send`
**Middleware:** `api_auth`, `can:sms.send`
**Controller:** `SmsApiController@send`

**Paramètres:**
```json
{
  "to": "string (requis)",
  "message": "string (requis)",
  "sender_id": "string (optionnel)"
}
```

**Réponse succès (200):**
```json
{
  "success": true,
  "message": "SMS sent successfully",
  "data": {
    "message_id": "string",
    "recipient": "string",
    "segments": 1,
    "cost": 25.0,
    "currency": "XOF"
  }
}
```

---

### 2. Historique des SMS
**Route:** `GET /api/v1/sms/history`
**Middleware:** `api_auth`, `can:sms.history.view`
**Controller:** `SmsApiController@history`

**Paramètres (query):**
- `page` (int, défaut: 1)
- `limit` (int, défaut: 20, max: 100)
- `status` (string, optionnel)
- `from_date` (string YYYY-MM-DD, optionnel)
- `to_date` (string YYYY-MM-DD, optionnel)

**Réponse succès (200):**
```json
{
  "success": true,
  "data": [...],
  "pagination": {
    "total": 150,
    "page": 1,
    "limit": 20,
    "pages": 8
  }
}
```

---

### 3. Consulter le solde
**Route:** `GET /api/v1/sms/balance`
**Middleware:** `api_auth`, `can:sms.stats.view`
**Controller:** `SmsApiController@balance`

**Réponse succès (200):**
```json
{
  "success": true,
  "data": {
    "balance": 5000.0,
    "currency": "XOF",
    "statistics": {
      "total_sent": 250,
      "total_failed": 5,
      "total_cost": 6250.0
    }
  }
}
```

---

## 🌐 Routes Web (Dashboard)

### Dashboard & Statistiques

| Route | Méthode | Controller | Description |
|-------|---------|------------|-------------|
| `/admin/sms` | GET | DashboardController@index | Dashboard principal |
| `/admin/sms/statistics` | GET | DashboardController@statistics | Page statistiques |

### Gestion des SMS

| Route | Méthode | Controller | Description |
|-------|---------|------------|-------------|
| `/admin/sms/send` | GET/POST | SmsController@send | Envoi SMS unique |
| `/admin/sms/send-bulk` | POST | SmsController@sendBulk | Envoi SMS groupé |
| `/admin/sms/bulk` | GET/POST | SmsController@bulk | Interface envoi groupé |
| `/admin/sms/history` | GET | SmsController@history | Historique des SMS |
| `/admin/sms/details/{id}` | GET | SmsController@details | Détails d'un SMS |
| `/admin/sms/download-template` | GET | SmsController@downloadTemplate | Template CSV |
| `/admin/sms/contracts` | GET | SmsController@contracts | Contrats SMS |

### Campagnes

| Route | Méthode | Controller | Description |
|-------|---------|------------|-------------|
| `/admin/sms/campaigns` | GET | SmsCampaignController@index | Liste campagnes |
| `/admin/sms/campaigns/create` | GET | SmsCampaignController@create | Formulaire création |
| `/admin/sms/campaigns/store` | POST | SmsCampaignController@store | Enregistrer campagne |
| `/admin/sms/campaigns/{id}` | GET | SmsCampaignController@show | Détails campagne |
| `/admin/sms/campaigns/{id}/edit` | GET | SmsCampaignController@edit | Modifier campagne |
| `/admin/sms/campaigns/{id}/update` | POST | SmsCampaignController@update | Sauvegarder modif |
| `/admin/sms/campaigns/{id}/delete` | GET | SmsCampaignController@delete | Supprimer campagne |
| `/admin/sms/campaigns/preview` | POST | SmsCampaignController@preview | Prévisualiser |

### Tarification & Facturation

| Route | Méthode | Controller | Description |
|-------|---------|------------|-------------|
| `/admin/sms/pricing` | GET | SmsPricingController@index | Grille tarifaire |
| `/admin/sms/pricing/update` | POST | SmsPricingController@update | Maj tarification |
| `/admin/sms/pricing/update-default` | POST | SmsPricingController@updateDefault | Maj prix défaut |
| `/admin/sms/pricing/update-country` | POST | SmsPricingController@updateCountry | Maj prix pays |
| `/admin/sms/pricing/delete-country` | POST | SmsPricingController@deleteCountry | Suppr prix pays |
| `/admin/sms/billing` | GET | SmsPricingController@logs | Logs facturation |

### Portefeuille

| Route | Méthode | Controller | Description |
|-------|---------|------------|-------------|
| `/admin/wallet/topup` | GET | WalletController@topup | Page rechargement |
| `/admin/wallet/process-topup` | POST | WalletController@processTopup | Traiter recharge |

### Sender Names (Noms d'expéditeur)

| Route | Méthode | Controller | Description |
|-------|---------|------------|-------------|
| `/sms/sender-names` | GET | SenderNameController@index | Liste sender names |
| `/sms/sender-names/create` | GET | SenderNameController@create | Créer sender name |
| `/sms/sender-names/store` | POST | SenderNameController@store | Enregistrer |
| `/sms/sender-names/edit` | GET | SenderNameController@edit | Modifier |
| `/sms/sender-names/update` | POST | SenderNameController@update | Sauvegarder modif |
| `/sms/sender-names/delete` | POST | SenderNameController@delete | Supprimer |
| `/sms/sender-names/assign-users` | GET | SenderNameController@assignUsers | Assigner users |
| `/sms/sender-names/save-assignments` | POST | SenderNameController@saveAssignments | Sauvegarder assign |
| `/sms/sender-names/bulk-assign-to-user` | POST | SenderNameController@bulkAssignToUser | Assign groupée |
| `/api/sms/sender-names/user` | GET | SenderNameController@apiGetUserSenderNames | API user names |

### Fournisseurs (Providers)

| Route | Méthode | Controller | Description |
|-------|---------|------------|-------------|
| `/admin/sms/providers` | GET | ProviderDashboardController@index | Dashboard providers |
| `/admin/sms/providers/orange` | GET | ProviderDashboardController@orange | Stats Orange |

### Documentation API

| Route | Méthode | Controller | Description |
|-------|---------|------------|-------------|
| `/admin/sms/api/docs` | GET | ApiDocsController@index | Documentation API |
| `/admin/sms/api/keys` | GET | ApiDocsController@keys | Gestion clés API |
| `/admin/sms/api/generate-key` | POST | ApiDocsController@generateKey | Générer clé API |

---

## 🔐 Permissions RBAC

### Permissions nécessaires

| Permission | Description |
|------------|-------------|
| `sms.send` | Envoyer des SMS |
| `sms.bulk` | Envoi SMS groupé |
| `sms.history.view` | Voir l'historique |
| `sms.stats.view` | Voir les statistiques |
| `sms.campaigns.view` | Voir les campagnes |
| `sms.campaigns.create` | Créer des campagnes |
| `sms.sender_names.view` | Voir les sender names |
| `sms.sender_names.manage` | Gérer les sender names |
| `sms.sender_names.assign` | Assigner aux utilisateurs |

---

## 🎯 Services disponibles

### SmsSenderService
Service principal pour l'envoi de SMS avec gestion de la facturation.

**Méthodes:**
- `send(to, message, senderId, options)` - Envoyer un SMS
- `sendBulk(recipients, message, senderId, options)` - Envoi groupé

### SmsPricingService
Calcul des tarifs SMS.

**Méthodes:**
- `calculatePrice(recipient, segments)` - Calculer le prix
- `getCountryPricing(countryCode)` - Prix par pays
- `getOperatorPricing(operator)` - Prix par opérateur

### SmsBillingService
Gestion de la facturation et des transactions.

**Méthodes:**
- `createBillingLog(data)` - Créer un log facturation
- `getUserBalance(userId)` - Obtenir le solde
- `deductBalance(userId, amount)` - Débiter le compte

### SmsQueueService
Gestion de la file d'attente SMS.

**Méthodes:**
- `addToQueue(smsData)` - Ajouter à la queue
- `processQueue()` - Traiter la queue
- `getQueueStatus()` - Statut de la queue

### PhoneNumberService
Validation et formatage des numéros.

**Méthodes:**
- `validate(phoneNumber)` - Valider un numéro
- `format(phoneNumber)` - Formater un numéro
- `getCountryCode(phoneNumber)` - Extraire le code pays
- `getOperator(phoneNumber)` - Détecter l'opérateur

---

## 🏭 Gateways SMS

### OrangeCIGateway
Gateway pour Orange Côte d'Ivoire

### OrangeSmsGateway
Gateway Orange générique

### InfobipGateway
Gateway Infobip

### MockGateway
Gateway de test (ne pas utiliser en production)

---

## 📊 Modèles de données

### SmsMessage
Enregistrements SMS individuels

### SmsBillingLog
Logs de facturation

### SmsCampaign
Campagnes SMS

### SmsQueue
File d'attente SMS

### SenderName
Noms d'expéditeur approuvés

---

## 🔍 Comment tester

1. **Interface web:** Accédez à `/admin/sms/send`
2. **API:** Utilisez le script `test_api.php`
3. **Documentation:** Consultez `/admin/sms/api/docs`
4. **Clés API:** Gérez vos clés sur `/admin/sms/api/keys`

---

**Dernière mise à jour:** 10 Décembre 2025
