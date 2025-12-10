# Module SMS - SmsCore

Module complet de gestion et d'envoi de SMS avec API REST, facturation automatique, campagnes et statistiques avancées.

## 🎯 Fonctionnalités principales

### ✉️ Envoi de SMS
- **Envoi simple** : Interface web pour envoyer des SMS individuels
- **Envoi groupé** : Import CSV, sélection de contacts, personnalisation
- **Campagnes** : Créer et gérer des campagnes SMS avec suivi
- **API REST** : Intégration programmatique via API

### 💰 Facturation & Tarification
- **Tarification flexible** : Par défaut, par pays, par opérateur
- **Facturation automatique** : Déduction automatique du portefeuille
- **Logs détaillés** : Traçabilité complète de chaque SMS
- **Statistiques de coûts** : Rapports et analyses de dépenses

### 🏷️ Sender Names (Noms d'expéditeur)
- **Gestion centralisée** : Création et validation des sender names
- **Contrôle d'accès** : Attribution par utilisateur
- **Statuts** : Pending, Approved, Rejected
- **Validation opérateur** : Workflow de validation

### 📊 Dashboard & Statistiques
- **Dashboard temps réel** : Métriques clés en un coup d'œil
- **Graphiques avancés** : Évolution temporelle, répartition
- **Statistiques par fournisseur** : Performance de chaque gateway
- **Export de données** : CSV, Excel

### 🔌 Intégrations
- **Multi-gateway** : Orange CI, Infobip, extensible
- **API REST** : Documentation complète avec exemples
- **Webhooks** : (À venir) Notifications événements
- **MCP Integration** : Compatible avec le système de modules

### 🔐 Sécurité & Permissions
- **Authentification API** : Clés API sécurisées
- **RBAC** : Contrôle d'accès basé sur les rôles
- **Audit logs** : Traçabilité de toutes les actions
- **Rate limiting** : Protection contre les abus

---

## 📦 Installation

### Prérequis

- PHP 8.0+
- MySQL/MariaDB 5.7+
- Extensions PHP : curl, json, mbstring
- Composer (pour les dépendances)

### Installation

1. Le module est déjà inclus dans `Modules/SmsCore/`
2. Exécutez les migrations :

```bash
php cli.php migrate:run SmsCore
```

3. Configurez les permissions RBAC
4. Configurez vos gateways SMS
5. Définissez la grille tarifaire

---

## 🚀 Démarrage rapide

### Pour les utilisateurs

#### 1. Générer une clé API

```
1. Accédez à /admin/api-keys
2. Cliquez sur "Générer une clé API"
3. Copiez votre clé
```

#### 2. Envoyer un SMS via API

```bash
curl -X POST https://votre-domaine.com/api/v1/sms/send \
  -H "Authorization: Bearer VOTRE_CLE_API" \
  -H "Content-Type: application/json" \
  -d '{
    "to": "+225XXXXXXXX",
    "message": "Bonjour depuis l API",
    "sender_id": "MonApp"
  }'
```

#### 3. Consulter la documentation

Accédez à `/admin/sms/api/docs` pour la documentation complète interactive.

### Pour les administrateurs

#### 1. Configurer les tarifs

```
1. Accédez à /admin/sms/pricing
2. Définissez le prix par défaut (ex: 25 XOF)
3. Ajoutez des tarifs spécifiques si nécessaire
```

#### 2. Créer des Sender Names

```
1. Accédez à /sms/sender-names
2. Cliquez sur "Créer un Sender Name"
3. Attendez la validation de l'opérateur
4. Assignez aux utilisateurs autorisés
```

#### 3. Consulter le guide admin

Lisez `Documentation/ADMIN_GUIDE.md` pour le guide complet.

---

## 📚 Documentation

### Documentation principale

| Document | Description | Public |
|----------|-------------|--------|
| [INDEX.md](Documentation/INDEX.md) | Index de toute la documentation | Tous |
| [API_DOCUMENTATION.md](Documentation/API_DOCUMENTATION.md) | Documentation API complète | Utilisateurs |
| [ADMIN_GUIDE.md](Documentation/ADMIN_GUIDE.md) | Guide administrateur | Admins |
| [ENDPOINTS_SUMMARY.md](Documentation/ENDPOINTS_SUMMARY.md) | Référence technique | Développeurs |

### Accès rapide

- **Documentation interactive** : `/admin/sms/api/docs`
- **Gestion clés API** : `/admin/api-keys`
- **Dashboard** : `/admin/sms`

---

## 🏗️ Architecture

```
SmsCore/
├── Controllers/              # Contrôleurs
│   ├── SmsApiController      # API REST
│   ├── SmsController         # Interface web
│   ├── DashboardController   # Statistiques
│   ├── SmsCampaignController # Campagnes
│   ├── SenderNameController  # Sender names
│   ├── SmsPricingController  # Tarification
│   └── ApiDocsController     # Documentation
│
├── Models/                   # Modèles
│   ├── SmsMessage           # Messages SMS
│   ├── SmsBillingLog        # Logs facturation
│   ├── SmsCampaign          # Campagnes
│   ├── SmsQueue             # File d'attente
│   └── SenderName           # Noms expéditeur
│
├── Services/                 # Services métier
│   ├── SmsSenderService     # Envoi SMS + facturation
│   ├── SmsPricingService    # Calcul tarifs
│   ├── SmsBillingService    # Gestion facturation
│   ├── SmsQueueService      # Gestion queue
│   └── PhoneNumberService   # Validation numéros
│
├── Gateways/                 # Providers SMS
│   ├── OrangeCIGateway      # Orange Côte d'Ivoire
│   ├── InfobipGateway       # Infobip
│   └── MockGateway          # Tests
│
├── Views/                    # Templates
│   ├── sms/                 # Vues SMS
│   ├── api/                 # Vues API/docs
│   └── campaigns/           # Vues campagnes
│
├── Routes/                   # Routes
│   └── web.php              # Définition routes
│
├── Documentation/            # 📚 Documentation
│   ├── INDEX.md
│   ├── API_DOCUMENTATION.md
│   ├── ADMIN_GUIDE.md
│   ├── ENDPOINTS_SUMMARY.md
│   └── test_api.php
│
└── SmsCoreModule.php        # Point d'entrée
```

---

## 🔌 API REST

### Endpoints principaux

| Méthode | Endpoint | Description |
|---------|----------|-------------|
| POST | `/api/v1/sms/send` | Envoyer un SMS |
| GET | `/api/v1/sms/history` | Historique SMS |
| GET | `/api/v1/sms/balance` | Consulter solde |

### Authentification

```http
Authorization: Bearer VOTRE_CLE_API
```

### Exemple complet

```php
<?php
$apiKey = 'votre_cle_api';
$url = 'https://votre-domaine.com/api/v1/sms/send';

$data = [
    'to' => '+225XXXXXXXX',
    'message' => 'Bonjour!',
    'sender_id' => 'MonApp'
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
$result = json_decode($response, true);

if ($result['success']) {
    echo "SMS envoyé ! ID: " . $result['data']['message_id'];
}
?>
```

---

## 🔐 Permissions RBAC

### Permissions disponibles

```php
'sms.send'                  => 'Envoyer des SMS',
'sms.bulk'                  => 'Envoi SMS groupé',
'sms.history.view'          => 'Voir l\'historique',
'sms.stats.view'            => 'Voir les statistiques',
'sms.campaigns.view'        => 'Voir les campagnes',
'sms.campaigns.create'      => 'Créer des campagnes',
'sms.sender_names.view'     => 'Voir les sender names',
'sms.sender_names.manage'   => 'Gérer les sender names',
'sms.sender_names.assign'   => 'Assigner aux utilisateurs',
```

### Configuration recommandée

**Rôle "User" :**
- `sms.send`
- `sms.history.view`
- `sms.stats.view`

**Rôle "Manager" :**
- Toutes les permissions "User" +
- `sms.bulk`
- `sms.campaigns.view`
- `sms.campaigns.create`

**Rôle "Admin" :**
- Toutes les permissions

---

## 📊 Base de données

### Tables principales

#### sms_messages
Enregistrements de tous les SMS envoyés

```sql
CREATE TABLE sms_messages (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT,
    recipient VARCHAR(20),
    message TEXT,
    sender_id VARCHAR(11),
    status ENUM('pending', 'sent', 'delivered', 'failed'),
    gateway VARCHAR(50),
    segments INT,
    cost DECIMAL(10,2),
    created_at TIMESTAMP
);
```

#### sms_billing_logs
Logs de facturation détaillés

```sql
CREATE TABLE sms_billing_logs (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT,
    recipient VARCHAR(20),
    cost DECIMAL(10,2),
    segments INT,
    status VARCHAR(20),
    gateway VARCHAR(50),
    country_code VARCHAR(5),
    operator VARCHAR(50),
    source VARCHAR(20),
    created_at TIMESTAMP
);
```

#### sender_names
Noms d'expéditeur approuvés

```sql
CREATE TABLE sender_names (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(11) UNIQUE,
    description TEXT,
    status ENUM('pending', 'approved', 'rejected'),
    created_at TIMESTAMP
);
```

---

## 🧪 Tests

### Test API

```bash
cd Modules/SmsCore/Documentation
php test_api.php
```

### Test interface web

1. Accédez à `/admin/sms/send`
2. Envoyez un SMS de test
3. Vérifiez dans l'historique

---

## 🔧 Configuration

### Gateways SMS

Configurez vos credentials dans `config/sms.php` ou via l'interface admin :

```php
'gateways' => [
    'orange_ci' => [
        'enabled' => true,
        'client_id' => env('ORANGE_CLIENT_ID'),
        'client_secret' => env('ORANGE_CLIENT_SECRET'),
    ],
    'infobip' => [
        'enabled' => true,
        'api_key' => env('INFOBIP_API_KEY'),
        'base_url' => env('INFOBIP_BASE_URL'),
    ]
]
```

### Tarification

Définissez dans `/admin/sms/pricing` :
- Prix par défaut : 25 XOF/segment
- Prix par pays
- Prix par opérateur

---

## 📈 Monitoring

### Métriques disponibles

- Total SMS envoyés
- Taux de succès
- Coût total
- SMS par jour/semaine/mois
- Répartition par gateway
- Répartition par opérateur

### Logs

```bash
# Logs applicatifs
tail -f storage/logs/app.log

# Logs SMS
tail -f storage/logs/sms.log
```

---

## 🛠️ Maintenance

### Commandes utiles

```bash
# Traiter la queue SMS
php cli.php cron:run process-sms-queue

# Nettoyer les anciens logs
php cli.php sms:clean-logs --days=90

# Statistiques
php cli.php sms:stats --period=month
```

---

## 🤝 Support

- **Documentation web** : `/admin/sms/api/docs`
- **Gestion clés API** : `/admin/api-keys`
- **Email** : support@votre-domaine.com
- **Guide admin** : `Documentation/ADMIN_GUIDE.md`

---

## 📝 Licence

Propriétaire - Tous droits réservés

---

## 👥 Contributeurs

- **Équipe de développement** - Développement initial et maintenance
- **Équipe produit** - Spécifications et tests

---

**Version actuelle :** 2.0
**Dernière mise à jour :** 10 Décembre 2025
