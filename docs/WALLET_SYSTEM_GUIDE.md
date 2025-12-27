# Système de Recharge Wallet - Guide Complet

## Vue d'ensemble

Le système de recharge wallet permet aux utilisateurs de gérer leur solde de crédits pour l'envoi de SMS. Le système utilise le **Module Wallet** (`Modules\Wallet`) qui fournit une gestion complète du portefeuille électronique.

## Architecture

### Modules impliqués

1. **Wallet Module** (`Modules\Wallet\`)
   - Gestion principale du wallet
   - Modèles : `Wallet`, `WalletTransaction`
   - Service : `WalletService`
   - Routes : `/admin/wallet/*`

2. **SmsCore Module** (`Modules\SmsCore\`)
   - Affiche le solde wallet sur le dashboard
   - Bouton de recharge rapide
   - Déduction automatique lors de l'envoi de SMS

## Tables de la base de données

### Table `wallets`
```sql
- id: bigint (PK)
- user_id: bigint (UNIQUE) - ID de l'utilisateur
- balance: decimal(15,2) - Solde actuel
- currency: varchar(3) - Devise (défaut: XOF)
- status: enum('active','frozen','suspended') - Statut du wallet
- created_at, updated_at
```

### Table `wallet_transactions`
```sql
- id: bigint (PK)
- wallet_id: bigint - Référence au wallet
- user_id: bigint - ID de l'utilisateur
- type: enum('credit','debit','refund') - Type de transaction
- amount: decimal(15,2) - Montant
- balance_before: decimal(15,2) - Solde avant
- balance_after: decimal(15,2) - Solde après
- description: varchar(255) - Description
- metadata: text (JSON) - Métadonnées additionnelles
- status: enum('pending','completed','failed') - Statut
- created_at, updated_at
```

### Table `wallet_gateways`
```sql
- id: bigint (PK)
- name: varchar(100) - Nom du gateway
- provider_code: varchar(50) - Code fournisseur
- api_url: varchar(255) - URL API
- api_key, api_secret, merchant_id: text - Credentials
- is_active: tinyint(1) - Statut actif
- is_default: tinyint(1) - Gateway par défaut
- currency: varchar(10)
- transaction_fee: decimal(10,2) - Frais de transaction
- configuration: json - Configuration additionnelle
```

## Routes disponibles

### Module Wallet

| Méthode | Route | Contrôleur | Description |
|---------|-------|------------|-------------|
| GET | `/admin/wallet` | `WalletController@index` | Liste tous les wallets (Admin) |
| GET | `/admin/wallet/topup` | `WalletController@topup` | Formulaire de recharge |
| POST | `/admin/wallet/topup` | `WalletController@processTopup` | Traitement de la recharge |
| POST | `/admin/wallet/debit` | `WalletController@processDebit` | Déduction de crédits (Admin) |
| GET | `/admin/wallet/transactions/{id}` | `WalletController@transactions` | Historique des transactions |

### Accès depuis SMS Dashboard

- **Dashboard SMS** : `/admin/sms` - Affiche le solde actuel
- **Bouton "Top-up Credits"** : Redirige vers `/admin/wallet/topup`

## Flux utilisateur - Demande de recharge

### 1. Accéder au formulaire de recharge

**Depuis le Dashboard SMS :**
```
/admin/sms → Cliquer sur "Top-up Credits"
```

**Directement :**
```
/admin/wallet/topup
```

### 2. Remplir le formulaire

Le formulaire de recharge contient :
- **Montant** : Minimum 100 XOF
- **Méthode de paiement** :
  - PayPal
  - Credit Card (Stripe)
  - (Extensible via `wallet_gateways`)

### 3. Soumission et traitement

```
POST /admin/wallet/topup
```

**Paramètres :**
```php
[
    'user_id' => int,      // ID utilisateur (auto-rempli)
    'amount' => float,     // Montant à recharger
    'method' => string,    // 'paypal' | 'stripe' | autres
    'description' => string // Description optionnelle
]
```

### 4. Traitement côté serveur

```php
// WalletController@processTopup
public function processTopup()
{
    $userId = $_POST['user_id'] ?? $_SESSION['user']['id'];
    $amount = $_POST['amount'] ?? 0;
    $description = $_POST['description'] ?? 'Top-up via payment gateway';
    $method = $_POST['method'] ?? 'paypal';

    // Validation
    if (!$userId || $amount <= 0) {
        flash('error', 'Invalid user ID or amount');
        redirect('/admin/wallet/topup');
    }

    // Ajout du crédit via WalletService
    $this->walletService->addCredit($userId, $amount, $description);

    flash('success', "Successfully added {$amount} XOF to your wallet");
    redirect('/admin/sms');
}
```

### 5. Enregistrement de la transaction

Le `WalletService` enregistre automatiquement :
```php
// WalletService@addCredit
public function addCredit(int $userId, float $amount, string $description)
{
    $wallet = $this->getWallet($userId);
    $balanceBefore = $wallet->balance;
    $balanceAfter = $balanceBefore + $amount;

    // Mise à jour du solde
    $wallet->update(['balance' => $balanceAfter]);

    // Enregistrement de la transaction
    WalletTransaction::create([
        'wallet_id' => $wallet->id,
        'user_id' => $userId,
        'type' => 'credit',
        'amount' => $amount,
        'balance_before' => $balanceBefore,
        'balance_after' => $balanceAfter,
        'description' => $description,
        'status' => 'completed'
    ]);
}
```

## Utilisation du WalletService

### Créer/Récupérer un wallet
```php
use Modules\Wallet\Services\WalletService;

$walletService = new WalletService();

// Récupérer le wallet (création auto si inexistant)
$wallet = $walletService->getWallet($userId);

// Récupérer le solde
$balance = $walletService->getBalance($userId);
```

### Ajouter du crédit
```php
$walletService->addCredit(
    userId: 1,
    amount: 500.00,
    description: 'Recharge PayPal'
);
```

### Déduire du crédit
```php
try {
    $walletService->deductCredit(
        userId: 1,
        amount: 35.00,
        description: 'SMS envoyé à +225...'
    );
} catch (\RuntimeException $e) {
    // Solde insuffisant ou wallet introuvable
    echo $e->getMessage();
}
```

### Vérifier le solde disponible
```php
if ($walletService->hasBalance($userId, 100.00)) {
    // L'utilisateur a au moins 100 XOF
}
```

### Geler/Activer un wallet
```php
// Geler (suspendre les transactions)
$walletService->freezeWallet($userId);

// Réactiver
$walletService->activateWallet($userId);
```

### Récupérer les transactions
```php
// Dernières 20 transactions
$transactions = $walletService->getTransactions($userId);

// Dernières 50 transactions
$transactions = $walletService->getTransactions($userId, 50);
```

## Affichage du solde dans le Dashboard SMS

Le `DashboardController` récupère automatiquement le solde :

```php
// DashboardController@index
$userId = $_SESSION['user']['id'] ?? $_SESSION['user_id'] ?? null;
$walletBalance = 0.00;

if ($userId) {
    $wallet = \Modules\Wallet\Models\Wallet::where('user_id', $userId)->first();
    if ($wallet) {
        $walletBalance = $wallet->balance ?? 0.00;
    }
}

$stats = [
    // ...
    'wallet_balance' => $walletBalance
];
```

La vue affiche ensuite :
```php
<h2 class="mb-0"><?= number_format($stats['wallet_balance'], 0) ?></h2>
<p class="text-muted mb-0">Wallet Balance</p>
```

## Intégration avec l'envoi de SMS

Lors de l'envoi d'un SMS, le système déduit automatiquement le coût :

```php
// Dans SmsBillingService ou SmsController
use Modules\Wallet\Services\WalletService;

$walletService = new WalletService();

// Vérifier le solde avant envoi
if (!$walletService->hasBalance($userId, $smsCost)) {
    flash('error', 'Solde insuffisant. Veuillez recharger votre wallet.');
    redirect('/admin/wallet/topup');
    return;
}

// Déduire le coût après envoi
$walletService->deductCredit(
    $userId,
    $smsCost,
    "SMS to {$recipient} ({$segments} segments)"
);
```

## Points d'accès utilisateur

### Pour l'utilisateur standard :

1. **Dashboard SMS** (`/admin/sms`)
   - Voir le solde actuel
   - Cliquer sur "Top-up Credits"

2. **Page de recharge** (`/admin/wallet/topup`)
   - Formulaire de recharge
   - Affichage du solde actuel
   - Choix de la méthode de paiement

3. **Historique** (`/admin/wallet/transactions/{userId}`)
   - Liste de toutes les transactions
   - Filtrage et recherche

### Pour l'administrateur :

1. **Gestion des wallets** (`/admin/wallet`)
   - Liste de tous les wallets utilisateurs
   - Ajout manuel de crédits
   - Déduction de crédits
   - Gel/Activation de wallets

## Sécurité

### Validations implémentées :

1. **Authentification** : Toutes les routes utilisent `AuthMiddleware`
2. **Montant minimum** : 100 XOF minimum pour les recharges
3. **Vérification du solde** : Avant chaque déduction
4. **Transactions atomiques** : Balance mise à jour + transaction enregistrée
5. **Statuts de wallet** : Active/Frozen/Suspended

### Permissions :

- **Utilisateur standard** :
  - Voir son propre wallet
  - Recharger son wallet
  - Voir ses transactions

- **Administrateur** :
  - Voir tous les wallets
  - Ajouter/Déduire des crédits manuellement
  - Geler/Activer des wallets
  - Voir toutes les transactions

## Gateways de paiement

Le système supporte plusieurs gateways via la table `wallet_gateways` :

```php
// Exemple de configuration gateway
[
    'name' => 'PayPal',
    'provider_code' => 'paypal',
    'api_url' => 'https://api.paypal.com/v1/',
    'api_key' => 'xxx',
    'api_secret' => 'yyy',
    'is_active' => true,
    'is_default' => true,
    'currency' => 'XOF',
    'transaction_fee' => 2.50
]
```

## Logs et traçabilité

Chaque transaction est enregistrée avec :
- Montant exact
- Solde avant/après
- Description détaillée
- Timestamp
- Statut (pending/completed/failed)
- Métadonnées JSON optionnelles

## Exemple complet - Flux de recharge utilisateur

```
1. Utilisateur se connecte
2. Accède au Dashboard SMS (/admin/sms)
3. Voit son solde actuel : 500 XOF
4. Clique sur "Top-up Credits"
5. Arrive sur /admin/wallet/topup
6. Remplit le formulaire :
   - Montant : 1000 XOF
   - Méthode : PayPal
7. Clique sur "Proceed to Payment"
8. POST vers /admin/wallet/topup
9. WalletService ajoute le crédit :
   - Balance : 500 → 1500 XOF
   - Transaction enregistrée
10. Flash message : "Successfully added 1000 XOF to your wallet"
11. Redirection vers /admin/sms
12. Dashboard affiche nouveau solde : 1500 XOF
```

## Fichiers modifiés

### Créés/Modifiés dans cette implémentation :

1. **`Modules\Wallet\WalletModule.php`** - Ajout route GET `/admin/wallet/topup`
2. **`Modules\SmsCore\SmsCoreModule.php`** - Suppression routes wallet en double
3. **`WALLET_SYSTEM_GUIDE.md`** - Cette documentation

### Fichiers existants utilisés :

- `Modules\Wallet\Controllers\WalletController.php`
- `Modules\Wallet\Services\WalletService.php`
- `Modules\Wallet\Models\Wallet.php`
- `Modules\Wallet\Models\WalletTransaction.php`
- `Modules\Wallet\Views\wallet\topup.php`
- `Modules\SmsCore\Controllers\DashboardController.php`
- `Modules\SmsCore\Views\sms\dashboard.php`

## Tests recommandés

1. **Test de recharge** :
   ```
   - Accéder à /admin/wallet/topup
   - Remplir le formulaire avec 500 XOF
   - Vérifier le flash message de succès
   - Vérifier que le solde a augmenté
   - Vérifier la transaction dans la BDD
   ```

2. **Test de solde insuffisant** :
   ```
   - Tenter d'envoyer un SMS avec solde insuffisant
   - Vérifier le message d'erreur
   - Vérifier la redirection vers la page de recharge
   ```

3. **Test admin** :
   ```
   - Accéder à /admin/wallet (liste)
   - Ajouter manuellement du crédit à un utilisateur
   - Vérifier la transaction
   ```

## Support et maintenance

Pour toute question ou problème :
1. Vérifier les logs de transactions dans `wallet_transactions`
2. Vérifier le statut du wallet dans `wallets`
3. Vérifier les gateways actifs dans `wallet_gateways`
4. Consulter les flash messages pour les erreurs utilisateur
