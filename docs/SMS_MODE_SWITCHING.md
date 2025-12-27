# Guide de Changement de Mode SMS (Mock ↔ Production)

## 📋 **Vue d'ensemble**

Le système SMS dispose de deux modes de fonctionnement :

| Mode | Description | Usage |
|------|-------------|-------|
| **🧪 MOCK** | Simulation | Les SMS ne sont **PAS** envoyés. Parfait pour les tests sans consommer de crédits. |
| **🚀 PRODUCTION** | Réel | Les SMS sont **réellement envoyés** et **facturés** par votre fournisseur. |

---

## 🎯 **Comment basculer entre les modes ?**

### **Via l'Interface Web** (Recommandé)

1. Allez sur **[/admin/settings/sms](http://localhost/admin/settings/sms)**
2. Dans le tableau des gateways, regardez la colonne **"Mode"** :
   - Badge orange **MOCK** = Mode simulation
   - Badge vert **PROD** = Mode production
3. Cliquez sur le bouton correspondant :
   - **Bouton "PROD"** (vert) : Passer en mode PRODUCTION
   - **Bouton "MOCK"** (orange) : Passer en mode SIMULATION

### **Passer de MOCK → PRODUCTION**

**Prérequis :**
- Vous devez avoir configuré vos credentials API (Client ID et Client Secret) depuis Orange Developer Portal
- Éditer le gateway → Entrer API Key et API Secret

**Étapes :**
1. Cliquez sur le bouton **"PROD"** (vert)
2. Une confirmation apparaît :
   ```
   ⚠️ ATTENTION!

   Passer en mode PRODUCTION signifie que:
   • Les SMS seront RÉELLEMENT envoyés
   • Votre compte sera FACTURÉ
   • Vous devez avoir des credentials valides

   Voulez-vous continuer?
   ```
3. Confirmez avec **OK**
4. Si les credentials sont présentes : ✅ **Mode PRODUCTION activé**
5. Si pas de credentials : ❌ **Erreur** - Il faut d'abord configurer vos API Key/Secret

### **Passer de PRODUCTION → MOCK**

**Étapes :**
1. Cliquez sur le bouton **"MOCK"** (orange)
2. Une confirmation apparaît :
   ```
   Passer en mode MOCK (simulation)?

   Les SMS ne seront PAS réellement envoyés.
   Utile pour les tests.
   ```
3. Confirmez avec **OK**
4. ✅ **Mode MOCK activé** - Les credentials sont supprimées (mises à NULL)
5. La page se recharge automatiquement

---

## 🔍 **Comment vérifier le mode actuel ?**

### **Méthode 1 : Interface Web**

Sur [/admin/settings/sms](http://localhost/admin/settings/sms), regardez la colonne **"Mode"** :
- 🧪 **Badge MOCK** (orange) = Simulation
- 🚀 **Badge PROD** (vert) = Production réelle

### **Méthode 2 : Script CLI**

```bash
php check_gateway_mode.php
```

**Output en mode MOCK :**
```
Mode : 🧪 SIMULATION (Mock)
Crédentials:
  API Key    : ❌ Non configurée
  API Secret : ❌ Non configurée
```

**Output en mode PRODUCTION :**
```
Mode : 🚀 PRODUCTION (Réel)
Crédentials:
  API Key    : ✓ Configurée (32 chars)
  API Secret : ✓ Configurée (44 chars)
```

### **Méthode 3 : Vérifier dans la BD**

```sql
SELECT
    name,
    provider_code,
    CASE
        WHEN api_key IS NULL THEN 'MOCK'
        ELSE 'PRODUCTION'
    END as mode
FROM sms_gateways;
```

---

## 💡 **Différences techniques**

### **Mode MOCK**

```php
// Dans la base de données
api_key = NULL
api_secret = NULL

// SmsGatewayFactory créera :
return new MockGateway($config);

// Résultat d'envoi
{
    "success": true,
    "mock": true,  // ← Indicateur de simulation
    "messageId": "MOCK-ABC123",
    "cost": 0.05   // Coût fictif
}
```

### **Mode PRODUCTION**

```php
// Dans la base de données
api_key = "VYqHNrIv99ZZfK64PvxGuPqTbBW2sBmp" (crypté)
api_secret = "sGE3dBtAvTuZUPNvvuF3fAMl8NJ4K8lLR7oYU09DECkt" (crypté)

// SmsGatewayFactory créera :
return new OrangeCIGateway($config);

// Résultat d'envoi
{
    "success": true,
    "resourceURL": "https://api.orange.com/smsmessaging/v1/...",
    "senderName": "TICAFRIQUE"
    // PAS de "mock": true
}
```

---

## ⚙️ **API Programmatique**

### **Vérifier le mode depuis le code**

```php
use Modules\Settings\Models\SmsGateway;

$gateway = SmsGateway::where('provider_code', 'orange_ci')->first();

// Méthode 1
if ($gateway->isMockMode()) {
    echo "En mode simulation";
} else {
    echo "En mode production réel";
}

// Méthode 2
$mode = $gateway->getMode(); // 'mock' ou 'production'
echo "Mode actuel: $mode";
```

### **Basculer le mode via API**

**Endpoint :**
```
POST /admin/settings/sms/gateways/{id}/switch-mode
Content-Type: application/json

{"mode": "production"}  // ou "mock"
```

**Réponse :**
```json
{
    "success": true,
    "message": "Mode PRODUCTION activé. Les SMS seront réellement envoyés et facturés."
}
```

---

## 🚨 **Avertissements**

### **⚠️ Mode PRODUCTION**
- Les SMS sont **RÉELLEMENT** envoyés
- Votre compte Orange/Infobip est **DÉBITÉ**
- Les numéros doivent être valides
- Vérifiez votre solde avant d'envoyer en masse

### **ℹ️ Mode MOCK**
- Aucun SMS réel n'est envoyé
- Aucun coût
- Parfait pour le développement et les tests
- 10% de chance d'échec simulé pour tester la gestion d'erreurs

---

## 📊 **Workflow Recommandé**

```
Développement (Local)
    ↓
🧪 Mode MOCK
    ↓
Tests fonctionnels avec simulation
    ↓
Configuration credentials réelles
    ↓
🚀 Basculer en PRODUCTION
    ↓
Test avec 1 SMS réel à votre numéro
    ↓
Vérification réception SMS
    ↓
Déploiement en production
```

---

## 🔧 **Troubleshooting**

### **Impossible de passer en PRODUCTION**

**Erreur :** "Aucune credential configurée"

**Solution :**
1. Allez sur [/admin/settings/sms](http://localhost/admin/settings/sms)
2. Cliquez sur **"Éditer"** (icône crayon)
3. Remplissez :
   - **API Key** : Votre Client ID Orange
   - **API Secret** : Votre Client Secret Orange
4. Cliquez **"Mettre à jour"**
5. Réessayez de passer en PRODUCTION

### **En PRODUCTION mais SMS non reçus**

**Vérifications :**
1. Les credentials sont-elles valides ?
   - Testez-les sur https://developer.orange.com
2. Votre compte Orange a-t-il du crédit ?
3. Le numéro destinataire est-il au bon format ?
   - Format international : `+225XXXXXXXX`
4. Vérifiez les logs dans `/admin/sms/history` → Détails
   - Regardez le champ `gateway_response`
   - Cherchez les erreurs

### **Retour en MOCK après redémarrage**

Si le système repasse en MOCK après un redémarrage serveur :
- Les credentials ont été supprimées de la BD
- Vérifiez que vous avez bien cliqué sur "Mettre à jour" lors de la configuration
- Reconfigurez vos credentials

---

**Auteur :** SunuFramework2 SMS Module
**Version :** 2.0
**Date :** 2025-11-29
