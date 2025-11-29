# Modules SMS & Wallet - SunuFramework2

## 📋 Vue d'ensemble

Ce document décrit l'implémentation complète des modules SMS et Wallet pour SunuFramework2, un framework PHP personnalisé inspiré de Laravel mais avec sa propre architecture modulaire.

## 🏗️ Architecture Respectée

### Structure Modulaire
```
Modules/Settings/
├── Controllers/
│   ├── SmsSettingsController.php       ✅ Complet (CRUD + Test)
│   └── WalletSettingsController.php    ✅ Complet (CRUD + Test)
├── Models/
│   ├── SmsGateway.php                  ✅ Avec encryption
│   └── WalletGateway.php               ✅ Avec encryption
├── Views/
│   ├── sms/
│   │   ├── index.php                   ✅
│   │   ├── create.php                  ✅
│   │   └── edit.php                    ✅
│   └── wallet/
│       ├── index.php                   ✅
│       ├── create.php                  ✅ NOUVEAU
│       └── edit.php                    ✅ NOUVEAU
├── Database/
│   ├── Migrations/
│   │   ├── 002_create_sms_gateways_table.php     ✅
│   │   └── 003_create_wallet_gateways_table.php  ✅
│   └── Seeders/
│       ├── SmsGatewaySeeder.php        ✅
│       └── WalletGatewaySeeder.php     ✅
└── SettingsModule.php                  ✅ Routes configurées
```

## 🚀 Installation

### Méthode Simple (Recommandée)

Exécutez le script d'installation fourni :

```bash
php install_sms_wallet.php
```

Ce script va :
1. ✅ Initialiser l'application SunuFramework2
2. ✅ Vérifier les tables existantes
3. ✅ Créer la table `sms_gateways`
4. ✅ Créer la table `wallet_gateways`
5. ✅ Insérer les gateways SMS par défaut (Orange CI, Infobip)
6. ✅ Insérer les gateways Wallet par défaut (PayDunya, CinetPay, etc.)

### Méthode Manuelle

Si vous préférez exécuter manuellement :

```php
<?php
require_once 'vendor/autoload.php';

use App\Core\Application;
use Modules\Settings\Database\Migrations\CreateSmsGatewaysTable;
use Modules\Settings\Database\Seeders\SmsGatewaySeeder;

$app = new Application(__DIR__);
$app->boot();

// Migration
$migration = new CreateSmsGatewaysTable();
$migration->up();

// Seeder
$seeder = new SmsGatewaySeeder();
$seeder->run();
```

## 📊 Schéma de Base de Données

### Table: sms_gateways

```sql
CREATE TABLE sms_gateways (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100),
    provider_code VARCHAR(50) UNIQUE,
    api_url VARCHAR(255),
    api_key TEXT,              -- Chiffré
    api_secret TEXT,           -- Chiffré
    sender_id VARCHAR(50),
    is_active BOOLEAN DEFAULT TRUE,
    is_default BOOLEAN DEFAULT FALSE,
    priority INT DEFAULT 0,
    configuration JSON,
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    INDEX idx_provider_code (provider_code),
    INDEX idx_is_active (is_active),
    INDEX idx_is_default (is_default),
    INDEX idx_priority (priority)
);
```

### Table: wallet_gateways

```sql
CREATE TABLE wallet_gateways (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100),
    provider_code VARCHAR(50) UNIQUE,
    api_url VARCHAR(255),
    api_key TEXT,              -- Chiffré
    api_secret TEXT,           -- Chiffré
    merchant_id TEXT,          -- Chiffré
    is_active BOOLEAN DEFAULT TRUE,
    is_default BOOLEAN DEFAULT FALSE,
    currency VARCHAR(10) DEFAULT 'XOF',
    transaction_fee DECIMAL(10,2) DEFAULT 0,
    configuration JSON,
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    INDEX idx_provider_code (provider_code),
    INDEX idx_is_active (is_active),
    INDEX idx_is_default (is_default)
);
```

## 🔐 Sécurité - Encryption des Credentials

Les modèles `SmsGateway` et `WalletGateway` chiffrent automatiquement les données sensibles :

```php
// Automatic encryption on save
$gateway->api_key = 'mon-api-key';  // Sera chiffré automatiquement
$gateway->save();

// Automatic decryption on read
echo $gateway->api_key;  // Déchiffré automatiquement
```

**Clé de chiffrement:** Utilise `APP_KEY` depuis `.env`

## 🎯 Utilisation

### 1. Configuration SMS

Accédez à `/admin/settings/sms` pour :
- Voir tous les gateways SMS
- Activer/Désactiver un gateway
- Définir le gateway par défaut
- Configurer les clés API
- Tester la connexion

### 2. Configuration Wallet

Accédez à `/admin/settings/wallet` pour :
- Voir tous les gateways de paiement
- Créer un nouveau gateway
- Éditer un gateway existant
- Tester la connexion API
- Définir le gateway par défaut

### 3. Providers Supportés

**SMS:**
- Orange Côte d'Ivoire (OAuth2)
- Infobip (API Key)

**Wallet:**
- PayDunya
- CinetPay
- Orange Money
- Wave
- Moov Money
- MTN Mobile Money

## 🛣️ Routes Disponibles

### Routes SMS
```php
GET    /admin/settings/sms                        // Liste
GET    /admin/settings/sms/gateways/create        // Créer
POST   /admin/settings/sms/gateways/store         // Enregistrer
GET    /admin/settings/sms/gateways/{id}/edit     // Éditer
POST   /admin/settings/sms/gateways/{id}/update   // Mettre à jour
POST   /admin/settings/sms/gateways/{id}/delete   // Supprimer
GET    /admin/settings/sms/gateways/{id}/test     // Tester
GET    /admin/settings/sms/gateways/{id}/set-default  // Définir par défaut
GET    /admin/settings/sms/gateways/{id}/toggle   // Activer/Désactiver
```

### Routes Wallet
```php
GET    /admin/settings/wallet                        // Liste
GET    /admin/settings/wallet/gateways/create       // Créer
POST   /admin/settings/wallet/gateways/store        // Enregistrer
GET    /admin/settings/wallet/gateways/{id}/edit    // Éditer
POST   /admin/settings/wallet/gateways/{id}/update  // Mettre à jour
POST   /admin/settings/wallet/gateways/{id}/delete  // Supprimer
GET    /admin/settings/wallet/gateways/{id}/test    // Tester (AJAX)
GET    /admin/settings/wallet/gateways/{id}/set-default  // Définir par défaut
GET    /admin/settings/wallet/gateways/{id}/toggle  // Activer/Désactiver
```

## 💻 Exemples de Code

### Récupérer le Gateway par Défaut

```php
use Modules\Settings\Models\SmsGateway;

$defaultSms = SmsGateway::getDefault();
if ($defaultSms) {
    echo "Gateway SMS: " . $defaultSms->name;
}
```

### Récupérer tous les Gateways Actifs

```php
use Modules\Settings\Models\WalletGateway;

$activeGateways = WalletGateway::getActive();
foreach ($activeGateways as $gateway) {
    echo $gateway->name . " - " . $gateway->currency;
}
```

### Tester une Connexion

```php
$gateway = SmsGateway::find(1);
$result = $gateway->testConnection();

if ($result['success']) {
    echo "✓ Connexion réussie";
} else {
    echo "✗ Erreur: " . $result['message'];
}
```

## 📝 TODO - Prochaines Étapes

### Phase 5: Services (Priorité HAUTE)
- [ ] Créer `Modules/Settings/Services/SmsService.php`
  - Méthode `send($to, $message, $gatewayId = null)`
  - Support failover (si gateway principal échoue, essayer le suivant)
  - Logging des envois

- [ ] Créer `Modules/Settings/Services/WalletService.php`
  - Méthode `initiatePayment($amount, $phoneNumber, $gatewayId = null)`
  - Méthode `checkStatus($transactionId)`
  - Webhooks handling
  - Logging des transactions

### Phase 6: Intégrations API (Priorité MOYENNE)
- [ ] Compléter intégration Orange CI SMS
- [ ] Compléter intégration Infobip SMS
- [ ] Implémenter PayDunya payment flow
- [ ] Implémenter CinetPay payment flow
- [ ] Implémenter Orange Money API
- [ ] Implémenter Wave API

### Phase 7: Tests & Documentation (Priorité BASSE)
- [ ] Tests fonctionnels
- [ ] Guide utilisateur
- [ ] Guide développeur API

## 🔧 Dépannage

### Les tables ne se créent pas
```bash
# Vérifier les permissions de la base de données
# Vérifier les credentials dans .env
php install_sms_wallet.php
```

### Les credentials ne sont pas chiffrés
```bash
# Vérifier que APP_KEY est défini dans .env
# Le modèle utilise APP_KEY pour le chiffrement
```

### Erreur lors du test de connexion
```bash
# Vérifier que curl est activé
# Vérifier que les clés API sont correctes
# Vérifier que l'URL API est accessible
```

## 📞 Support

Pour toute question sur l'architecture du framework :
- Documentation Core: `/Core/README.md`
- Structure Modulaire: `/Modules/README.md`
- Database Layer: `/Core/Database/README.md`

---

**Version:** 1.0.0
**Framework:** SunuFramework2
**Date:** Novembre 2025
