# SMS API Documentation

## Table des matières
1. [Introduction](#introduction)
2. [Authentification](#authentification)
3. [Endpoints](#endpoints)
   - [Envoyer un SMS](#1-envoyer-un-sms)
   - [Historique des SMS](#2-historique-des-sms)
   - [Consulter le solde](#3-consulter-le-solde)
4. [Codes de réponse](#codes-de-réponse)
5. [Exemples de code](#exemples-de-code)
6. [Limites et quotas](#limites-et-quotas)

---

## Introduction

L'API SMS vous permet d'envoyer des SMS, de consulter votre historique et de gérer votre solde de manière programmatique. Tous les endpoints nécessitent une authentification via clé API.

**Base URL:** `https://votre-domaine.com/api/v1`

---

## Authentification

Toutes les requêtes API doivent inclure votre clé API pour l'authentification. Vous pouvez obtenir votre clé API depuis votre tableau de bord utilisateur.

### Méthodes d'authentification

#### 1. En-tête Authorization (Recommandé)
```http
Authorization: Bearer VOTRE_CLE_API
```

#### 2. Paramètre de requête
```http
GET /api/v1/sms/history?api_key=VOTRE_CLE_API
```

#### 3. Corps de la requête (POST)
```json
{
  "api_key": "VOTRE_CLE_API",
  "to": "+225XXXXXXXX",
  "message": "Votre message"
}
```

---

## Endpoints

### 1. Envoyer un SMS

Envoie un SMS à un destinataire unique.

**Endpoint:** `POST /api/v1/sms/send`

**Permissions requises:** `sms.send`

#### Paramètres de la requête

| Paramètre | Type | Requis | Description |
|-----------|------|--------|-------------|
| `to` | string | Oui | Numéro de téléphone du destinataire (format international) |
| `message` | string | Oui | Contenu du SMS (max 1600 caractères) |
| `sender_id` | string | Non | Identifiant de l'expéditeur (11 caractères max) |

#### Exemple de requête

```bash
curl -X POST https://votre-domaine.com/api/v1/sms/send \
  -H "Authorization: Bearer VOTRE_CLE_API" \
  -H "Content-Type: application/json" \
  -d '{
    "to": "+225XXXXXXXX",
    "message": "Bonjour, ceci est un test",
    "sender_id": "MonAppli"
  }'
```

#### Réponse en cas de succès (200)

```json
{
  "success": true,
  "message": "SMS sent successfully",
  "data": {
    "message_id": "msg_123456789",
    "recipient": "+225XXXXXXXX",
    "segments": 1,
    "cost": 25.0,
    "currency": "XOF"
  }
}
```

#### Réponses d'erreur

**400 Bad Request - Champs manquants**
```json
{
  "success": false,
  "error": "Bad Request",
  "message": "Missing required fields: to, message"
}
```

**400 Bad Request - Message trop long**
```json
{
  "success": false,
  "error": "Bad Request",
  "message": "Message too long (max 1600 characters)"
}
```

**401 Unauthorized**
```json
{
  "success": false,
  "error": "Unauthorized",
  "message": "Invalid API key"
}
```

**500 Server Error**
```json
{
  "success": false,
  "error": "Server Error",
  "message": "Failed to send SMS"
}
```

---

### 2. Historique des SMS

Récupère l'historique des SMS envoyés avec filtres et pagination.

**Endpoint:** `GET /api/v1/sms/history`

**Permissions requises:** `sms.history.view`

#### Paramètres de requête (Query Parameters)

| Paramètre | Type | Requis | Description | Défaut |
|-----------|------|--------|-------------|--------|
| `page` | integer | Non | Numéro de la page | 1 |
| `limit` | integer | Non | Nombre d'éléments par page (max 100) | 20 |
| `status` | string | Non | Filtrer par statut (paid, pending, failed) | - |
| `from_date` | string | Non | Date de début (YYYY-MM-DD) | - |
| `to_date` | string | Non | Date de fin (YYYY-MM-DD) | - |

#### Exemple de requête

```bash
curl -X GET "https://votre-domaine.com/api/v1/sms/history?page=1&limit=20&status=paid" \
  -H "Authorization: Bearer VOTRE_CLE_API"
```

#### Réponse en cas de succès (200)

```json
{
  "success": true,
  "data": [
    {
      "id": 123,
      "recipient": "+225XXXXXXXX",
      "sender_id": "MonAppli",
      "message": "Bonjour, ceci est un test",
      "status": "paid",
      "segments": 1,
      "cost": 25.0,
      "currency": "XOF",
      "gateway": "orange_ci",
      "country_code": "CI",
      "operator": "Orange",
      "created_at": "2025-12-10 14:30:00"
    }
  ],
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

Récupère le solde du portefeuille et les statistiques d'utilisation.

**Endpoint:** `GET /api/v1/sms/balance`

**Permissions requises:** `sms.stats.view`

#### Exemple de requête

```bash
curl -X GET https://votre-domaine.com/api/v1/sms/balance \
  -H "Authorization: Bearer VOTRE_CLE_API"
```

#### Réponse en cas de succès (200)

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

## Codes de réponse

| Code | Description |
|------|-------------|
| 200 | Succès |
| 400 | Requête invalide (paramètres manquants ou incorrects) |
| 401 | Non autorisé (clé API invalide ou manquante) |
| 403 | Accès refusé (permissions insuffisantes) |
| 404 | Ressource non trouvée |
| 429 | Trop de requêtes (rate limit dépassé) |
| 500 | Erreur serveur |

---

## Exemples de code

### PHP (cURL)

```php
<?php
$apiKey = 'VOTRE_CLE_API';
$url = 'https://votre-domaine.com/api/v1/sms/send';

$data = [
    'to' => '+225XXXXXXXX',
    'message' => 'Bonjour depuis PHP',
    'sender_id' => 'MonAppli'
];

$ch = curl_init($url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Authorization: Bearer ' . $apiKey,
    'Content-Type: application/json'
]);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

$result = json_decode($response, true);

if ($httpCode === 200 && $result['success']) {
    echo "SMS envoyé avec succès! ID: " . $result['data']['message_id'];
} else {
    echo "Erreur: " . $result['message'];
}
?>
```

### Python (requests)

```python
import requests

api_key = 'VOTRE_CLE_API'
url = 'https://votre-domaine.com/api/v1/sms/send'

headers = {
    'Authorization': f'Bearer {api_key}',
    'Content-Type': 'application/json'
}

data = {
    'to': '+225XXXXXXXX',
    'message': 'Bonjour depuis Python',
    'sender_id': 'MonAppli'
}

response = requests.post(url, json=data, headers=headers)

if response.status_code == 200:
    result = response.json()
    if result['success']:
        print(f"SMS envoyé avec succès! ID: {result['data']['message_id']}")
    else:
        print(f"Erreur: {result['message']}")
else:
    print(f"Erreur HTTP {response.status_code}: {response.text}")
```

### JavaScript (Fetch API)

```javascript
const apiKey = 'VOTRE_CLE_API';
const url = 'https://votre-domaine.com/api/v1/sms/send';

const data = {
    to: '+225XXXXXXXX',
    message: 'Bonjour depuis JavaScript',
    sender_id: 'MonAppli'
};

fetch(url, {
    method: 'POST',
    headers: {
        'Authorization': `Bearer ${apiKey}`,
        'Content-Type': 'application/json'
    },
    body: JSON.stringify(data)
})
.then(response => response.json())
.then(result => {
    if (result.success) {
        console.log(`SMS envoyé avec succès! ID: ${result.data.message_id}`);
    } else {
        console.error(`Erreur: ${result.message}`);
    }
})
.catch(error => {
    console.error('Erreur:', error);
});
```

### Node.js (Axios)

```javascript
const axios = require('axios');

const apiKey = 'VOTRE_CLE_API';
const url = 'https://votre-domaine.com/api/v1/sms/send';

const data = {
    to: '+225XXXXXXXX',
    message: 'Bonjour depuis Node.js',
    sender_id: 'MonAppli'
};

axios.post(url, data, {
    headers: {
        'Authorization': `Bearer ${apiKey}`,
        'Content-Type': 'application/json'
    }
})
.then(response => {
    if (response.data.success) {
        console.log(`SMS envoyé avec succès! ID: ${response.data.data.message_id}`);
    } else {
        console.error(`Erreur: ${response.data.message}`);
    }
})
.catch(error => {
    console.error('Erreur:', error.message);
});
```

---

## Limites et quotas

### Limites générales

- **Longueur du message:** Maximum 1600 caractères
- **Segments SMS:**
  - 1 segment = 160 caractères (GSM 7-bit)
  - 1 segment = 70 caractères (Unicode pour caractères spéciaux)
- **Sender ID:** Maximum 11 caractères alphanumériques
- **Rate limit:** 100 requêtes par minute par clé API

### Segmentation des SMS

Les SMS sont facturés par segment. Un message est automatiquement divisé en plusieurs segments selon sa longueur :

| Type de caractères | Caractères par segment | Segments multiples |
|-------------------|----------------------|-------------------|
| Standard (GSM 7-bit) | 160 | 153 par segment |
| Unicode (émojis, accents) | 70 | 67 par segment |

**Exemples:**
- Message de 150 caractères = 1 segment
- Message de 200 caractères = 2 segments (153 + 47)
- Message avec émojis de 100 caractères = 2 segments

### Tarification

La tarification dépend de:
- L'opérateur destination
- Le pays destination
- Le nombre de segments

Consultez `/admin/sms/pricing` pour voir la grille tarifaire complète.

---

## Support

Pour toute question ou problème:
- **Email:** support@votre-domaine.com
- **Téléphone:** +225 XX XX XX XX XX
- **Documentation complète:** `/admin/sms/contracts`

---

**Dernière mise à jour:** 10 Décembre 2025
