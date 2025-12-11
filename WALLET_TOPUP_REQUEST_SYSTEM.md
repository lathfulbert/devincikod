# Système de Demande de Rechargement Wallet

## Vue d'ensemble

Le système de rechargement wallet a été complètement refactoré pour implémenter un workflow d'approbation administrateur pour les paiements offline et une intégration avec des passerelles de paiement pour les paiements en ligne.

## Problématiques résolues

### Avant
- ✗ Les utilisateurs pouvaient se créditer directement sans vérification
- ✗ Aucun historique des demandes de rechargement
- ✗ Pas de validation par un administrateur pour les paiements offline
- ✗ Aucune intégration avec les passerelles de paiement

### Après
- ✓ Toutes les demandes de rechargement passent par un système de requêtes
- ✓ Historique complet de toutes les demandes (pending, approved, rejected, completed, etc.)
- ✓ Workflow d'approbation administrateur pour paiements offline
- ✓ Architecture prête pour l'intégration avec passerelles de paiement
- ✓ Traçabilité complète (IP, user agent, timestamp, reviewer, notes)

## Architecture du système

### 1. Base de données

**Table : `wallet_topup_requests`**

```sql
CREATE TABLE `wallet_topup_requests` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `user_id` BIGINT UNSIGNED NOT NULL,
    `wallet_id` BIGINT UNSIGNED NOT NULL,
    `amount` DECIMAL(15,2) NOT NULL,

    -- Méthode de paiement
    `payment_method` ENUM('gateway', 'cash', 'mobile_money', 'bank_transfer', 'other') NOT NULL DEFAULT 'cash',

    -- Informations gateway (si payment_method = 'gateway')
    `gateway_id` BIGINT UNSIGNED NULL,
    `gateway_transaction_id` VARCHAR(255) NULL,
    `gateway_response` JSON NULL,
    `gateway_status` VARCHAR(50) NULL COMMENT 'SUCCESS, FAILED, PENDING, CANCELLED',

    -- Statut de la demande
    `status` ENUM('pending', 'processing', 'approved', 'rejected', 'completed', 'failed', 'cancelled') NOT NULL DEFAULT 'pending',

    -- Notes et preuves
    `notes` TEXT NULL COMMENT 'Notes de l\'utilisateur',
    `proof_of_payment` VARCHAR(255) NULL COMMENT 'Chemin vers la preuve de paiement',

    -- Révision admin
    `reviewed_by` BIGINT UNSIGNED NULL,
    `reviewed_at` TIMESTAMP NULL,
    `admin_notes` TEXT NULL,

    -- Audit
    `ip_address` VARCHAR(45) NULL,
    `user_agent` VARCHAR(255) NULL,

    -- Timestamps
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    -- Foreign keys
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`wallet_id`) REFERENCES `wallets`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`gateway_id`) REFERENCES `wallet_gateways`(`id`) ON DELETE SET NULL,
    FOREIGN KEY (`reviewed_by`) REFERENCES `users`(`id`) ON DELETE SET NULL,

    -- Indexes
    INDEX `idx_user_id` (`user_id`),
    INDEX `idx_wallet_id` (`wallet_id`),
    INDEX `idx_status` (`status`),
    INDEX `idx_payment_method` (`payment_method`),
    INDEX `idx_created_at` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

**Colonnes clés :**

| Colonne | Type | Description |
|---------|------|-------------|
| `payment_method` | ENUM | `gateway`, `cash`, `mobile_money`, `bank_transfer`, `other` |
| `status` | ENUM | `pending`, `processing`, `approved`, `rejected`, `completed`, `failed`, `cancelled` |
| `gateway_status` | VARCHAR | Statut retourné par la gateway (`SUCCESS`, `FAILED`, etc.) |
| `gateway_response` | JSON | Réponse complète de la gateway (pour débogage) |
| `reviewed_by` | BIGINT UNSIGNED | ID de l'admin qui a approuvé/rejeté |
| `proof_of_payment` | VARCHAR | Chemin vers fichier de preuve (reçu, screenshot, etc.) |

### 2. Modèle : `WalletTopupRequest`

**Fichier :** `Modules/Wallet/Models/WalletTopupRequest.php`

#### Relations

```php
// Utilisateur qui fait la demande
public function user(): BelongsTo
{
    return $this->belongsTo(User::class, 'user_id');
}

// Wallet à créditer
public function wallet(): BelongsTo
{
    return $this->belongsTo(Wallet::class, 'wallet_id');
}

// Gateway utilisée (si payment_method = 'gateway')
public function gateway(): BelongsTo
{
    return $this->belongsTo(WalletGateway::class, 'gateway_id');
}

// Administrateur qui a révisé la demande
public function reviewer(): BelongsTo
{
    return $this->belongsTo(User::class, 'reviewed_by');
}
```

#### Méthodes de vérification de statut

```php
// Vérifier si la demande est en attente
public function isPending(): bool
{
    return $this->status === 'pending';
}

// Vérifier si la demande est approuvée
public function isApproved(): bool
{
    return $this->status === 'approved';
}

// Vérifier si la demande nécessite une approbation admin
public function requiresApproval(): bool
{
    return in_array($this->payment_method, ['cash', 'mobile_money', 'bank_transfer', 'other']);
}

// Vérifier si c'est un paiement via gateway
public function isGatewayPayment(): bool
{
    return $this->payment_method === 'gateway';
}
```

#### Méthodes d'aide UI

```php
// Classe CSS pour le badge de statut
public function getStatusBadgeClass(): string
{
    return match($this->status) {
        'pending' => 'warning',
        'processing' => 'info',
        'approved' => 'primary',
        'completed' => 'success',
        'rejected' => 'danger',
        'failed' => 'danger',
        'cancelled' => 'secondary',
        default => 'secondary'
    };
}

// Libellé en français du statut
public function getStatusLabel(): string
{
    return match($this->status) {
        'pending' => 'En attente',
        'processing' => 'En traitement',
        'approved' => 'Approuvée',
        'completed' => 'Complétée',
        'rejected' => 'Rejetée',
        'failed' => 'Échouée',
        'cancelled' => 'Annulée',
        default => $this->status
    };
}

// Libellé de la méthode de paiement
public function getPaymentMethodLabel(): string
{
    return match($this->payment_method) {
        'gateway' => 'Passerelle de paiement',
        'cash' => 'Espèces',
        'mobile_money' => 'Mobile Money',
        'bank_transfer' => 'Virement bancaire',
        'other' => 'Autre',
        default => $this->payment_method
    };
}
```

#### Méthodes de transition d'état

```php
// Marquer comme approuvée par un admin
public function markAsApproved(int $reviewerId, ?string $adminNotes = null): bool
{
    return $this->update([
        'status' => 'approved',
        'reviewed_by' => $reviewerId,
        'reviewed_at' => date('Y-m-d H:i:s'),
        'admin_notes' => $adminNotes
    ]);
}

// Marquer comme rejetée par un admin
public function markAsRejected(int $reviewerId, ?string $adminNotes = null): bool
{
    return $this->update([
        'status' => 'rejected',
        'reviewed_by' => $reviewerId,
        'reviewed_at' => date('Y-m-d H:i:s'),
        'admin_notes' => $adminNotes
    ]);
}

// Marquer comme complétée (après crédit du wallet)
public function markAsCompleted(): bool
{
    return $this->update(['status' => 'completed']);
}

// Marquer comme échouée
public function markAsFailed(?string $reason = null): bool
{
    return $this->update([
        'status' => 'failed',
        'admin_notes' => $reason
    ]);
}
```

### 3. Contrôleur : `WalletController`

**Fichier :** `Modules/Wallet/Controllers/WalletController.php`

#### Méthode `processTopup()` - Créer une demande de rechargement

**Workflow complet :**

```php
public function processTopup()
{
    // 1. Récupération et validation des données
    $userId = $_SESSION['user']['id'];
    $amount = (float)($_POST['amount'] ?? 0);
    $paymentMethod = sanitize($_POST['payment_method'] ?? 'cash', 'string');
    $gatewayId = !empty($_POST['gateway_id']) ? (int)$_POST['gateway_id'] : null;
    $notes = sanitize($_POST['notes'] ?? '', 'string');

    // 2. Validations de sécurité
    if ($amount <= 0) {
        flash('error', 'Le montant doit être supérieur à 0');
        redirect('/admin/wallet/topup');
        exit;
    }

    if ($amount < 100) {
        flash('error', 'Le montant minimum est de 100 XOF');
        redirect('/admin/wallet/topup');
        exit;
    }

    if ($amount > 10000000) {
        flash('error', 'Le montant maximum par recharge est de 10,000,000 XOF');
        redirect('/admin/wallet/topup');
        exit;
    }

    // 3. Vérification du solde résultant
    $currentBalance = $this->walletService->getBalance($userId);
    $newBalance = $currentBalance + $amount;

    if ($newBalance > 9999999999999.99) {
        flash('error', 'Cette recharge dépasserait la limite maximale du solde');
        redirect('/admin/wallet/topup');
        exit;
    }

    // 4. Récupération du wallet
    $wallet = Wallet::where('user_id', $userId)->first();
    if (!$wallet) {
        flash('error', 'Wallet introuvable');
        redirect('/admin/wallet/topup');
        exit;
    }

    // 5. Création de la demande de rechargement
    $request = WalletTopupRequest::create([
        'user_id' => $userId,
        'wallet_id' => $wallet->id,
        'amount' => $amount,
        'payment_method' => $paymentMethod,
        'gateway_id' => $gatewayId,
        'status' => 'pending',
        'notes' => $notes,
        'ip_address' => $_SERVER['REMOTE_ADDR'] ?? null,
        'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? null
    ]);

    // 6. Traitement selon la méthode de paiement
    if ($paymentMethod === 'gateway' && $gatewayId) {
        // === PAIEMENT PAR GATEWAY ===

        // TODO: Intégration réelle avec la passerelle de paiement
        // Pour l'instant, simulation d'un paiement réussi

        $request->update([
            'status' => 'processing',
            'gateway_transaction_id' => 'TXN_' . time() . '_' . rand(1000, 9999),
            'gateway_status' => 'SUCCESS',
            'gateway_response' => json_encode([
                'status' => 'SUCCESS',
                'transaction_id' => 'TXN_' . time(),
                'amount' => $amount,
                'timestamp' => date('Y-m-d H:i:s')
            ])
        ]);

        // Crédit automatique du wallet
        $this->walletService->addCredit(
            $userId,
            $amount,
            'Recharge via gateway #' . $request->id
        );

        // Marquer comme complétée
        $request->markAsCompleted();

        flash('success', 'Recharge de ' . number_format($amount, 0, ',', ' ') . ' XOF effectuée avec succès via la passerelle de paiement');
        redirect('/admin/wallet/requests');

    } else {
        // === PAIEMENT OFFLINE ===
        // La demande reste en statut 'pending'
        // Elle nécessite l'approbation d'un administrateur

        flash('info', 'Votre demande de recharge de ' . number_format($amount, 0, ',', ' ') . ' XOF a été soumise et est en attente de validation par un administrateur.');
        redirect('/admin/wallet/requests');
    }
}
```

#### Méthode `requests()` - Historique utilisateur

Affiche l'historique des demandes de l'utilisateur connecté :

```php
public function requests()
{
    $userId = $_SESSION['user']['id'];

    $requests = WalletTopupRequest::where('user_id', $userId)
        ->orderBy('created_at', 'desc')
        ->get();

    echo view('Wallet/wallet/requests', [
        'title' => 'Mes demandes de rechargement',
        'requests' => $requests
    ]);
}
```

#### Méthode `adminRequests()` - Gestion admin

Affiche toutes les demandes avec filtrage par statut :

```php
public function adminRequests()
{
    // Filtrage par statut (optionnel)
    $status = $_GET['status'] ?? null;

    $query = WalletTopupRequest::query()
        ->orderBy('created_at', 'desc');

    if ($status) {
        $query->where('status', $status);
    }

    $requests = $query->get();

    // Compteur des demandes en attente
    $pendingCount = WalletTopupRequest::where('status', 'pending')->count();

    echo view('Wallet/wallet/admin-requests', [
        'title' => 'Gestion des demandes de rechargement',
        'requests' => $requests,
        'pendingCount' => $pendingCount,
        'currentStatus' => $status
    ]);
}
```

#### Méthode `approveRequest($id)` - Approuver une demande

```php
public function approveRequest($id)
{
    $adminId = $_SESSION['user']['id'];
    $adminNotes = sanitize($_POST['admin_notes'] ?? '', 'string');

    $request = WalletTopupRequest::find($id);

    if (!$request) {
        flash('error', 'Demande introuvable');
        redirect('/admin/wallet/admin-requests');
        return;
    }

    // Vérifier que la demande est bien en attente
    if (!$request->isPending()) {
        flash('error', 'Cette demande a déjà été traitée');
        redirect('/admin/wallet/admin-requests');
        return;
    }

    // Vérifier le solde résultant
    $wallet = Wallet::find($request->wallet_id);
    $newBalance = $wallet->balance + $request->amount;

    if ($newBalance > 9999999999999.99) {
        flash('error', 'Cette recharge dépasserait la limite maximale du solde');
        redirect('/admin/wallet/admin-requests');
        return;
    }

    // Marquer comme approuvée
    $request->markAsApproved($adminId, $adminNotes);

    // Créditer le wallet
    $this->walletService->addCredit(
        $request->user_id,
        $request->amount,
        'Recharge approuvée par admin (Demande #' . $request->id . ')'
    );

    // Marquer comme complétée
    $request->markAsCompleted();

    flash('success', 'Demande approuvée et ' . number_format($request->amount, 0, ',', ' ') . ' XOF crédité au wallet');
    redirect('/admin/wallet/admin-requests');
}
```

#### Méthode `rejectRequest($id)` - Rejeter une demande

```php
public function rejectRequest($id)
{
    $adminId = $_SESSION['user']['id'];
    $adminNotes = sanitize($_POST['admin_notes'] ?? '', 'string');

    $request = WalletTopupRequest::find($id);

    if (!$request) {
        flash('error', 'Demande introuvable');
        redirect('/admin/wallet/admin-requests');
        return;
    }

    if (!$request->isPending()) {
        flash('error', 'Cette demande a déjà été traitée');
        redirect('/admin/wallet/admin-requests');
        return;
    }

    // Marquer comme rejetée
    $request->markAsRejected($adminId, $adminNotes);

    flash('success', 'Demande rejetée');
    redirect('/admin/wallet/admin-requests');
}
```

### 4. Routes

**Fichier :** `Modules/Wallet/WalletModule.php`

```php
public function getRoutes(): array
{
    $authMiddleware = [new \App\Core\Middleware\AuthMiddleware(), 'handle'];

    return [
        // Wallet Management
        ['GET', '/admin/wallet', [\Modules\Wallet\Controllers\WalletController::class, 'index'], [$authMiddleware]],
        ['GET', '/admin/wallet/topup', [\Modules\Wallet\Controllers\WalletController::class, 'topup'], [$authMiddleware]],
        ['POST', '/admin/wallet/topup', [\Modules\Wallet\Controllers\WalletController::class, 'processTopup'], [$authMiddleware]],
        ['POST', '/admin/wallet/debit', [\Modules\Wallet\Controllers\WalletController::class, 'processDebit'], [$authMiddleware]],
        ['GET', '/admin/wallet/transactions/{id}', [\Modules\Wallet\Controllers\WalletController::class, 'transactions'], [$authMiddleware]],

        // Topup Requests
        ['GET', '/admin/wallet/requests', [\Modules\Wallet\Controllers\WalletController::class, 'requests'], [$authMiddleware]],
        ['GET', '/admin/wallet/admin-requests', [\Modules\Wallet\Controllers\WalletController::class, 'adminRequests'], [$authMiddleware]],
        ['POST', '/admin/wallet/approve/{id}', [\Modules\Wallet\Controllers\WalletController::class, 'approveRequest'], [$authMiddleware]],
        ['POST', '/admin/wallet/reject/{id}', [\Modules\Wallet\Controllers\WalletController::class, 'rejectRequest'], [$authMiddleware]],
    ];
}
```

## Workflows

### Workflow 1 : Paiement via Gateway

```
1. Utilisateur accède à /admin/wallet/topup
2. Remplit le formulaire :
   - Montant : 5000 XOF
   - Méthode de paiement : Gateway
   - Sélectionne une gateway
3. Soumet le formulaire
4. WalletController::processTopup() :
   ✓ Valide le montant
   ✓ Crée une demande avec status='pending'
   ✓ TODO: Redirige vers la gateway de paiement
   ✓ (Actuellement : simule SUCCESS)
   ✓ Met à jour gateway_status='SUCCESS'
   ✓ Crédite automatiquement le wallet
   ✓ Change status='completed'
5. Utilisateur redirigé vers /admin/wallet/requests
6. Message : "Recharge de 5 000 XOF effectuée avec succès"
```

**Statuts dans ce workflow :**
- `pending` → `processing` → `completed`

### Workflow 2 : Paiement Offline (Cash, Mobile Money, etc.)

```
1. Utilisateur accède à /admin/wallet/topup
2. Remplit le formulaire :
   - Montant : 10000 XOF
   - Méthode de paiement : Mobile Money
   - Notes : "Paiement Orange Money - Ref: 123456789"
   - (Optionnel) Upload preuve de paiement
3. Soumet le formulaire
4. WalletController::processTopup() :
   ✓ Valide le montant
   ✓ Crée une demande avec status='pending'
   ✓ NE crédite PAS le wallet
5. Utilisateur redirigé vers /admin/wallet/requests
6. Message : "Demande en attente de validation par un administrateur"

--- ATTENTE ---

7. Administrateur accède à /admin/wallet/admin-requests
8. Voit la demande avec badge "En attente"
9. Clique sur "Approuver" ou "Rejeter"

--- SI APPROUVÉ ---

10. Admin entre des notes (optionnel) : "Vérifié avec Orange Money"
11. Soumet l'approbation
12. WalletController::approveRequest() :
    ✓ Marque status='approved'
    ✓ Enregistre reviewed_by et reviewed_at
    ✓ Crédite le wallet de 10000 XOF
    ✓ Marque status='completed'
13. Admin redirigé vers /admin/wallet/admin-requests
14. Message : "Demande approuvée et 10 000 XOF crédité"

--- SI REJETÉ ---

10. Admin entre des notes : "Preuve de paiement invalide"
11. Soumet le rejet
12. WalletController::rejectRequest() :
    ✓ Marque status='rejected'
    ✓ Enregistre reviewed_by et reviewed_at
    ✓ N'effectue AUCUN crédit
13. Admin redirigé vers /admin/wallet/admin-requests
14. Message : "Demande rejetée"
```

**Statuts dans ce workflow (approuvé) :**
- `pending` → `approved` → `completed`

**Statuts dans ce workflow (rejeté) :**
- `pending` → `rejected`

### Workflow 3 : Consultation de l'historique

**Par l'utilisateur :**

```
1. Utilisateur accède à /admin/wallet/requests
2. Voit la liste de toutes ses demandes :
   - ID, Montant, Méthode de paiement, Statut, Date
3. Pour chaque demande :
   - Badge de statut coloré
   - Notes de l'utilisateur (si saisies)
   - Notes de l'admin (si demande révisée)
   - Date de révision (si révisée)
```

**Par l'administrateur :**

```
1. Admin accède à /admin/wallet/admin-requests
2. Voit :
   - Badge "X demandes en attente"
   - Filtres par statut
3. Liste toutes les demandes de tous les utilisateurs :
   - Utilisateur, Montant, Méthode, Statut, Date
4. Peut filtrer :
   - /admin/wallet/admin-requests?status=pending
   - /admin/wallet/admin-requests?status=completed
   - etc.
5. Boutons d'action pour demandes pending :
   - "Approuver"
   - "Rejeter"
```

## Machine à états (State Machine)

```
                  ┌──────────────────┐
                  │                  │
                  │     PENDING      │
                  │   (En attente)   │
                  │                  │
                  └────────┬─────────┘
                           │
              ┌────────────┴────────────┐
              │                         │
      [Gateway Payment]          [Offline Payment]
              │                         │
              v                         v
     ┌────────────────┐        ┌────────────────┐
     │   PROCESSING   │        │    (wait for   │
     │  (Traitement)  │        │  admin review) │
     └────────┬───────┘        └────────┬───────┘
              │                         │
              │                    ┌────┴────┐
              │                    │         │
              │              [Approve]   [Reject]
              │                    │         │
              │                    v         v
              │           ┌────────────┐  ┌──────────┐
              │           │  APPROVED  │  │ REJECTED │
              │           │ (Approuvée)│  │(Rejetée) │
              │           └──────┬─────┘  └──────────┘
              │                  │
              │                  v
              │           [Credit Wallet]
              │                  │
              └──────────────────┘
                                 │
                                 v
                        ┌────────────────┐
                        │   COMPLETED    │
                        │  (Complétée)   │
                        └────────────────┘

            ┌──────────────────────────────┐
            │  FAILED / CANCELLED          │
            │  (Échec / Annulée)           │
            └──────────────────────────────┘
```

**Transitions possibles :**

| De | Vers | Condition | Action |
|----|------|-----------|--------|
| `pending` | `processing` | Payment method = gateway | Redirection vers gateway |
| `processing` | `completed` | Gateway status = SUCCESS | Crédit automatique |
| `processing` | `failed` | Gateway status = FAILED | Aucun crédit |
| `pending` | `approved` | Admin approuve (offline) | Aucune action immédiate |
| `approved` | `completed` | Après approbation | Crédit du wallet |
| `pending` | `rejected` | Admin rejette | Aucun crédit |
| `*` | `cancelled` | User/Admin annule | Aucun crédit |

## Sécurité et validations

### 1. Validations de montant

| Règle | Valeur | Emplacement |
|-------|--------|-------------|
| Montant minimum | 100 XOF | `WalletController::processTopup()` |
| Montant maximum par transaction | 10,000,000 XOF | `WalletController::processTopup()` |
| Solde maximum du wallet | 9,999,999,999,999.99 XOF | `WalletController::processTopup()` et `approveRequest()` |
| Type de données | `decimal(15,2)` | Base de données |

### 2. Protection contre les doublons

**Problème potentiel :** Utilisateur soumet plusieurs fois le même formulaire

**Solutions possibles (à implémenter) :**
1. Token CSRF unique par formulaire
2. Vérification de demande en double (même montant, même méthode, dans les 5 dernières minutes)
3. Désactivation du bouton submit après clic (JavaScript)

### 3. Audit trail complet

Chaque demande enregistre :
- `ip_address` : IP de l'utilisateur
- `user_agent` : Navigateur de l'utilisateur
- `created_at` : Date de création
- `reviewed_by` : ID de l'admin qui a révisé
- `reviewed_at` : Date de révision
- `admin_notes` : Notes de l'admin

**Utilité :**
- Détecter les activités suspectes
- Tracer les actions administrateur
- Analyser les patterns de fraude

### 4. Permissions et rôles

**À implémenter :**

```php
// Dans AuthMiddleware ou RoleMiddleware
if (in_array($route, ['/admin/wallet/admin-requests', '/admin/wallet/approve/*'])) {
    // Vérifier que l'utilisateur a le rôle 'admin' ou 'finance'
    if (!hasRole(['admin', 'finance'])) {
        flash('error', 'Accès refusé');
        redirect('/admin');
        exit;
    }
}
```

## Vues à créer

### 1. `Modules/Wallet/Views/wallet/requests.php`

**Affiche l'historique de l'utilisateur :**

```php
<!-- Liste des demandes de l'utilisateur -->
<div class="card">
    <div class="card-header">
        <h5>Mes demandes de rechargement</h5>
    </div>
    <div class="card-body">
        <?php if (empty($requests)): ?>
            <p class="text-muted">Aucune demande de rechargement.</p>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Montant</th>
                            <th>Méthode</th>
                            <th>Statut</th>
                            <th>Date</th>
                            <th>Notes</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($requests as $request): ?>
                            <tr>
                                <td><?= $request->id ?></td>
                                <td><?= number_format($request->amount, 0, ',', ' ') ?> XOF</td>
                                <td><?= $request->getPaymentMethodLabel() ?></td>
                                <td>
                                    <span class="badge bg-<?= $request->getStatusBadgeClass() ?>">
                                        <?= $request->getStatusLabel() ?>
                                    </span>
                                </td>
                                <td><?= date('d/m/Y H:i', strtotime($request->created_at)) ?></td>
                                <td>
                                    <?php if ($request->notes): ?>
                                        <small><?= htmlspecialchars($request->notes) ?></small>
                                    <?php endif; ?>
                                    <?php if ($request->admin_notes): ?>
                                        <br><small class="text-muted">
                                            <strong>Admin:</strong> <?= htmlspecialchars($request->admin_notes) ?>
                                        </small>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>
```

### 2. `Modules/Wallet/Views/wallet/admin-requests.php`

**Gestion admin de toutes les demandes :**

```php
<!-- Filtres de statut -->
<div class="card mb-3">
    <div class="card-body">
        <div class="btn-group" role="group">
            <a href="/admin/wallet/admin-requests" class="btn btn-outline-primary <?= !$currentStatus ? 'active' : '' ?>">
                Toutes
            </a>
            <a href="/admin/wallet/admin-requests?status=pending" class="btn btn-outline-warning <?= $currentStatus === 'pending' ? 'active' : '' ?>">
                En attente (<?= $pendingCount ?>)
            </a>
            <a href="/admin/wallet/admin-requests?status=approved" class="btn btn-outline-primary">
                Approuvées
            </a>
            <a href="/admin/wallet/admin-requests?status=completed" class="btn btn-outline-success">
                Complétées
            </a>
            <a href="/admin/wallet/admin-requests?status=rejected" class="btn btn-outline-danger">
                Rejetées
            </a>
        </div>
    </div>
</div>

<!-- Liste des demandes -->
<div class="card">
    <div class="card-header">
        <h5>Demandes de rechargement</h5>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Utilisateur</th>
                        <th>Montant</th>
                        <th>Méthode</th>
                        <th>Statut</th>
                        <th>Date</th>
                        <th>Notes</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($requests as $request): ?>
                        <tr>
                            <td><?= $request->id ?></td>
                            <td><?= htmlspecialchars($request->user->name ?? 'N/A') ?></td>
                            <td><strong><?= number_format($request->amount, 0, ',', ' ') ?> XOF</strong></td>
                            <td><?= $request->getPaymentMethodLabel() ?></td>
                            <td>
                                <span class="badge bg-<?= $request->getStatusBadgeClass() ?>">
                                    <?= $request->getStatusLabel() ?>
                                </span>
                            </td>
                            <td><?= date('d/m/Y H:i', strtotime($request->created_at)) ?></td>
                            <td>
                                <?php if ($request->notes): ?>
                                    <small><?= htmlspecialchars($request->notes) ?></small>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($request->isPending()): ?>
                                    <!-- Bouton Approuver -->
                                    <button class="btn btn-sm btn-success" data-bs-toggle="modal" data-bs-target="#approveModal<?= $request->id ?>">
                                        Approuver
                                    </button>

                                    <!-- Bouton Rejeter -->
                                    <button class="btn btn-sm btn-danger" data-bs-toggle="modal" data-bs-target="#rejectModal<?= $request->id ?>">
                                        Rejeter
                                    </button>
                                <?php else: ?>
                                    <span class="text-muted">-</span>
                                <?php endif; ?>
                            </td>
                        </tr>

                        <!-- Modal Approuver -->
                        <div class="modal fade" id="approveModal<?= $request->id ?>" tabindex="-1">
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    <form method="POST" action="/admin/wallet/approve/<?= $request->id ?>">
                                        <div class="modal-header">
                                            <h5 class="modal-title">Approuver la demande #<?= $request->id ?></h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                        </div>
                                        <div class="modal-body">
                                            <p>Montant à créditer : <strong><?= number_format($request->amount, 0, ',', ' ') ?> XOF</strong></p>
                                            <p>Utilisateur : <strong><?= htmlspecialchars($request->user->name ?? 'N/A') ?></strong></p>

                                            <div class="mb-3">
                                                <label for="admin_notes" class="form-label">Notes (optionnel)</label>
                                                <textarea name="admin_notes" id="admin_notes" class="form-control" rows="3"></textarea>
                                            </div>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                                            <button type="submit" class="btn btn-success">Approuver et créditer</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>

                        <!-- Modal Rejeter -->
                        <div class="modal fade" id="rejectModal<?= $request->id ?>" tabindex="-1">
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    <form method="POST" action="/admin/wallet/reject/<?= $request->id ?>">
                                        <div class="modal-header">
                                            <h5 class="modal-title">Rejeter la demande #<?= $request->id ?></h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                        </div>
                                        <div class="modal-body">
                                            <p>Montant : <strong><?= number_format($request->amount, 0, ',', ' ') ?> XOF</strong></p>

                                            <div class="mb-3">
                                                <label for="admin_notes_reject" class="form-label">Raison du rejet (obligatoire)</label>
                                                <textarea name="admin_notes" id="admin_notes_reject" class="form-control" rows="3" required></textarea>
                                            </div>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                                            <button type="submit" class="btn btn-danger">Rejeter</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
```

### 3. Mise à jour de `topup.php`

Ajouter un sélecteur de méthode de paiement :

```php
<!-- Méthode de paiement -->
<div class="mb-3">
    <label for="payment_method" class="form-label">Méthode de paiement</label>
    <select name="payment_method" id="payment_method" class="form-select" required>
        <option value="">-- Sélectionner --</option>
        <option value="cash">Espèces</option>
        <option value="mobile_money">Mobile Money</option>
        <option value="bank_transfer">Virement bancaire</option>
        <?php if (!empty($gateways)): ?>
            <optgroup label="Passerelles en ligne">
                <?php foreach ($gateways as $gateway): ?>
                    <option value="gateway" data-gateway-id="<?= $gateway->id ?>">
                        <?= htmlspecialchars($gateway->name) ?>
                    </option>
                <?php endforeach; ?>
            </optgroup>
        <?php endif; ?>
    </select>
</div>

<input type="hidden" name="gateway_id" id="gateway_id">

<!-- Notes de paiement -->
<div class="mb-3">
    <label for="notes" class="form-label">Notes / Référence de paiement (optionnel)</label>
    <textarea name="notes" id="notes" class="form-control" rows="3" placeholder="Ex: Référence Orange Money, numéro de transaction, etc."></textarea>
    <small class="form-text text-muted">
        Indiquez toute information utile pour la validation de votre paiement.
    </small>
</div>

<script>
// Gérer la sélection de gateway
document.getElementById('payment_method').addEventListener('change', function() {
    const selected = this.options[this.selectedIndex];
    const gatewayId = selected.getAttribute('data-gateway-id');
    document.getElementById('gateway_id').value = gatewayId || '';
});
</script>
```

## Intégration avec passerelle de paiement (TODO)

### Structure générique d'intégration

```php
// Dans WalletController::processTopup()

if ($paymentMethod === 'gateway' && $gatewayId) {
    $gateway = WalletGateway::find($gatewayId);

    if (!$gateway || !$gateway->is_active) {
        flash('error', 'Passerelle de paiement indisponible');
        redirect('/admin/wallet/topup');
        exit;
    }

    // Créer la demande
    $request = WalletTopupRequest::create([...]);

    // Préparer les données de paiement
    $paymentData = [
        'amount' => $amount,
        'currency' => 'XOF',
        'reference' => 'TOPUP_' . $request->id,
        'customer' => [
            'name' => $_SESSION['user']['name'],
            'email' => $_SESSION['user']['email'],
            'phone' => $_SESSION['user']['phone']
        ],
        'return_url' => url('/admin/wallet/callback?request_id=' . $request->id),
        'cancel_url' => url('/admin/wallet/topup?cancelled=1'),
        'webhook_url' => url('/api/webhook/payment')
    ];

    // Initialiser le paiement selon la gateway
    switch ($gateway->code) {
        case 'stripe':
            // Intégration Stripe
            break;
        case 'paypal':
            // Intégration PayPal
            break;
        case 'cinetpay':
            // Intégration CinetPay (Afrique de l'Ouest)
            break;
        case 'fedapay':
            // Intégration FedaPay (Bénin, Togo, etc.)
            break;
        case 'orange_money':
            // Intégration Orange Money API
            break;
        case 'mtn_momo':
            // Intégration MTN Mobile Money
            break;
        default:
            flash('error', 'Passerelle non supportée');
            redirect('/admin/wallet/topup');
            exit;
    }

    // Rediriger vers la page de paiement de la gateway
    redirect($paymentUrl);
}
```

### Callback après paiement

```php
// Nouvelle méthode dans WalletController

public function paymentCallback()
{
    $requestId = $_GET['request_id'] ?? null;
    $request = WalletTopupRequest::find($requestId);

    if (!$request) {
        flash('error', 'Demande introuvable');
        redirect('/admin/wallet');
        return;
    }

    // Récupérer le statut de la gateway
    // (Selon la gateway : query params, session, API call, etc.)

    $gatewayStatus = $_GET['status'] ?? 'UNKNOWN';
    $transactionId = $_GET['transaction_id'] ?? null;

    // Mettre à jour la demande
    $request->update([
        'gateway_status' => $gatewayStatus,
        'gateway_transaction_id' => $transactionId,
        'gateway_response' => json_encode($_GET)
    ]);

    if ($gatewayStatus === 'SUCCESS') {
        // Crédit automatique
        $this->walletService->addCredit(
            $request->user_id,
            $request->amount,
            'Recharge via ' . $request->gateway->name . ' #' . $request->id
        );

        $request->markAsCompleted();

        flash('success', 'Paiement réussi ! Votre wallet a été crédité de ' . number_format($request->amount, 0, ',', ' ') . ' XOF');
    } else {
        $request->markAsFailed('Paiement échoué ou annulé');
        flash('error', 'Le paiement a échoué ou a été annulé');
    }

    redirect('/admin/wallet/requests');
}
```

### Webhook pour notifications asynchrones

```php
// Route: POST /api/webhook/payment

public function paymentWebhook()
{
    // Récupérer le payload (selon la gateway)
    $payload = file_get_contents('php://input');
    $data = json_decode($payload, true);

    // Vérifier la signature (sécurité)
    // Selon la gateway : HMAC, JWT, etc.

    // Identifier la demande
    $reference = $data['reference'] ?? null; // Ex: "TOPUP_123"
    $requestId = str_replace('TOPUP_', '', $reference);

    $request = WalletTopupRequest::find($requestId);

    if (!$request) {
        http_response_code(404);
        echo json_encode(['error' => 'Request not found']);
        exit;
    }

    // Mettre à jour selon le statut
    $status = $data['status'] ?? 'UNKNOWN';

    $request->update([
        'gateway_status' => $status,
        'gateway_transaction_id' => $data['transaction_id'] ?? null,
        'gateway_response' => $payload
    ]);

    if ($status === 'SUCCESS' && $request->status !== 'completed') {
        // Crédit du wallet
        $this->walletService->addCredit(
            $request->user_id,
            $request->amount,
            'Recharge via webhook #' . $request->id
        );

        $request->markAsCompleted();
    } elseif ($status === 'FAILED') {
        $request->markAsFailed('Gateway reported failure');
    }

    // Répondre à la gateway
    http_response_code(200);
    echo json_encode(['success' => true]);
}
```

## Notifications (à implémenter)

### 1. Notification utilisateur

**Après soumission d'une demande offline :**
```
Titre : Demande de rechargement soumise
Message : Votre demande de rechargement de 10 000 XOF a été soumise et est en attente de validation.
Vous recevrez une notification une fois la demande traitée.
```

**Après approbation :**
```
Titre : Demande approuvée
Message : Votre demande de rechargement de 10 000 XOF a été approuvée.
Votre wallet a été crédité. Nouveau solde : 25 000 XOF.
```

**Après rejet :**
```
Titre : Demande rejetée
Message : Votre demande de rechargement de 10 000 XOF a été rejetée.
Raison : Preuve de paiement invalide.
```

### 2. Notification admin

**Nouvelle demande en attente :**
```
Titre : Nouvelle demande de rechargement
Message : L'utilisateur John Doe a soumis une demande de rechargement de 50 000 XOF via Mobile Money.
[Voir les détails]
```

## Tests recommandés

### Test 1 : Paiement Gateway (simulation)

```
1. Accéder à /admin/wallet/topup
2. Montant : 5000 XOF
3. Méthode : Gateway (simulée)
4. Soumettre
5. ✓ Vérifier demande créée avec status='pending'
6. ✓ Vérifier passage à 'completed'
7. ✓ Vérifier crédit du wallet (+5000)
8. ✓ Vérifier message de succès
9. ✓ Vérifier apparition dans /admin/wallet/requests
```

### Test 2 : Paiement Offline - Workflow complet

```
1. USER: Accéder à /admin/wallet/topup
2. USER: Montant = 10000, Méthode = Mobile Money, Notes = "Ref: 123"
3. USER: Soumettre
4. ✓ Vérifier demande créée avec status='pending'
5. ✓ Vérifier message "En attente de validation"
6. ✓ Vérifier apparition dans /admin/wallet/requests (badge "En attente")
7. ✓ Vérifier wallet NOT credited yet

8. ADMIN: Accéder à /admin/wallet/admin-requests
9. ✓ Vérifier badge "1 demande en attente"
10. ✓ Vérifier demande visible avec boutons "Approuver/Rejeter"
11. ADMIN: Cliquer "Approuver"
12. ADMIN: Entrer notes admin = "Vérifié OK"
13. ADMIN: Soumettre
14. ✓ Vérifier status='approved' puis 'completed'
15. ✓ Vérifier wallet crédité (+10000)
16. ✓ Vérifier reviewed_by et reviewed_at renseignés
17. ✓ Vérifier message de succès admin

18. USER: Recharger /admin/wallet/requests
19. ✓ Vérifier demande affichée avec badge "Complétée"
20. ✓ Vérifier notes admin visibles
```

### Test 3 : Rejet de demande

```
1. USER: Créer demande 15000 XOF Cash
2. ADMIN: Accéder à admin-requests
3. ADMIN: Cliquer "Rejeter"
4. ADMIN: Entrer raison = "Preuve non fournie"
5. ADMIN: Soumettre
6. ✓ Vérifier status='rejected'
7. ✓ Vérifier wallet NOT credited
8. ✓ Vérifier admin_notes="Preuve non fournie"
9. USER: Voir demande rejetée avec raison
```

### Test 4 : Validation de montants

```
1. Tenter montant = 50 XOF
2. ✓ Erreur "Montant minimum 100 XOF"

3. Tenter montant = 20000000 XOF
4. ✓ Erreur "Montant maximum 10,000,000 XOF"

5. Wallet balance = 9999995000000
6. Tenter montant = 10000000
7. ✓ Erreur "Dépasserait la limite maximale"
```

### Test 5 : Filtres admin

```
1. Créer 3 demandes : pending, completed, rejected
2. ADMIN: Accéder à /admin/wallet/admin-requests
3. ✓ Vérifier 3 demandes affichées
4. Cliquer filtre "En attente"
5. ✓ Vérifier 1 seule demande (pending)
6. Cliquer filtre "Complétées"
7. ✓ Vérifier 1 seule demande (completed)
```

## Fichiers modifiés/créés

### Base de données
- ✅ `Modules/Wallet/Database/Migrations/create_wallet_topup_requests_table.sql` - Nouvelle table

### Modèles
- ✅ `Modules/Wallet/Models/WalletTopupRequest.php` - Nouveau modèle

### Contrôleurs
- ✅ `Modules/Wallet/Controllers/WalletController.php` - Méthodes ajoutées/modifiées :
  - `processTopup()` - Complètement refactoré
  - `requests()` - NOUVEAU
  - `adminRequests()` - NOUVEAU
  - `approveRequest($id)` - NOUVEAU
  - `rejectRequest($id)` - NOUVEAU

### Routes
- ✅ `Modules/Wallet/WalletModule.php` - Routes ajoutées :
  - GET `/admin/wallet/requests`
  - GET `/admin/wallet/admin-requests`
  - POST `/admin/wallet/approve/{id}`
  - POST `/admin/wallet/reject/{id}`

### Vues (À CRÉER)
- ⏳ `Modules/Wallet/Views/wallet/requests.php` - Historique utilisateur
- ⏳ `Modules/Wallet/Views/wallet/admin-requests.php` - Gestion admin
- ⏳ `Modules/Wallet/Views/wallet/topup.php` - À mettre à jour (sélecteur méthode paiement)

### Documentation
- ✅ `WALLET_TOPUP_REQUEST_SYSTEM.md` - Ce document

## Améliorations futures

### 1. Upload de preuve de paiement

```php
// Dans processTopup()
if (isset($_FILES['proof_of_payment']) && $_FILES['proof_of_payment']['error'] === UPLOAD_ERR_OK) {
    // Utiliser FileManager pour uploader
    $uploadedPath = $fileManager->upload($_FILES['proof_of_payment'], 'wallet/proofs');

    $request->update(['proof_of_payment' => $uploadedPath]);
}
```

### 2. Notifications email

```php
// Après création de demande offline
$emailService->send([
    'to' => $user->email,
    'subject' => 'Demande de rechargement soumise',
    'template' => 'wallet/topup_pending',
    'data' => ['request' => $request]
]);

// Après approbation
$emailService->send([
    'to' => $user->email,
    'subject' => 'Demande de rechargement approuvée',
    'template' => 'wallet/topup_approved',
    'data' => ['request' => $request]
]);
```

### 3. Notifications push (in-app)

```php
// Utiliser le système de notifications existant
Notification::create([
    'user_id' => $request->user_id,
    'type' => 'wallet_topup_approved',
    'title' => 'Demande approuvée',
    'message' => 'Votre wallet a été crédité de ' . $request->amount . ' XOF',
    'data' => json_encode(['request_id' => $request->id])
]);
```

### 4. Rapports et statistiques

```php
// Dashboard admin - Statistiques de rechargement
public function dashboardStats()
{
    $stats = [
        'pending_count' => WalletTopupRequest::where('status', 'pending')->count(),
        'pending_amount' => WalletTopupRequest::where('status', 'pending')->sum('amount'),
        'completed_today' => WalletTopupRequest::where('status', 'completed')
            ->whereDate('updated_at', today())->count(),
        'completed_amount_today' => WalletTopupRequest::where('status', 'completed')
            ->whereDate('updated_at', today())->sum('amount'),
        'rejected_count' => WalletTopupRequest::where('status', 'rejected')->count(),
        'average_approval_time' => // Calcul du temps moyen entre created_at et reviewed_at
    ];

    return $stats;
}
```

### 5. Limites par utilisateur

```php
// Vérifier les limites avant création de demande
$todayRequests = WalletTopupRequest::where('user_id', $userId)
    ->whereDate('created_at', today())
    ->count();

if ($todayRequests >= 5) {
    flash('error', 'Vous avez atteint la limite de 5 demandes par jour');
    redirect('/admin/wallet/topup');
    exit;
}
```

### 6. Auto-approbation pour petits montants

```php
// Si montant < seuil, approuver automatiquement
if ($paymentMethod === 'mobile_money' && $amount <= 1000) {
    $request->update([
        'status' => 'approved',
        'admin_notes' => 'Auto-approuvé (montant < 1000 XOF)'
    ]);

    $this->walletService->addCredit($userId, $amount, '...');
    $request->markAsCompleted();

    flash('success', 'Recharge approuvée automatiquement');
}
```

### 7. Annulation de demande par l'utilisateur

```php
// Méthode dans WalletController
public function cancelRequest($id)
{
    $userId = $_SESSION['user']['id'];

    $request = WalletTopupRequest::where('id', $id)
        ->where('user_id', $userId)
        ->first();

    if (!$request || !$request->isPending()) {
        flash('error', 'Impossible d\'annuler cette demande');
        redirect('/admin/wallet/requests');
        return;
    }

    $request->update(['status' => 'cancelled']);

    flash('success', 'Demande annulée');
    redirect('/admin/wallet/requests');
}
```

## Support et débogage

### Logs recommandés

```php
// Dans processTopup()
Log::info('Topup request created', [
    'request_id' => $request->id,
    'user_id' => $userId,
    'amount' => $amount,
    'payment_method' => $paymentMethod,
    'ip' => $_SERVER['REMOTE_ADDR']
]);

// Dans approveRequest()
Log::info('Topup request approved', [
    'request_id' => $request->id,
    'approved_by' => $adminId,
    'amount' => $request->amount,
    'wallet_credited' => true
]);
```

### Vérifications en cas de problème

1. **Demande créée mais wallet non crédité :**
   - Vérifier `status` de la demande (doit être 'completed')
   - Vérifier `wallet_transactions` pour le crédit correspondant
   - Consulter les logs d'erreur PHP

2. **Boutons Approuver/Rejeter ne s'affichent pas :**
   - Vérifier que la demande est bien `status='pending'`
   - Vérifier que l'utilisateur a les permissions admin

3. **Gateway payment ne redirige pas :**
   - Vérifier que `$gateway->is_active = 1`
   - Vérifier les credentials de la gateway
   - Consulter `gateway_response` pour les erreurs

## Conclusion

Le système de demande de rechargement wallet est maintenant :

✅ **Sécurisé** - Approbation admin pour paiements offline
✅ **Traçable** - Audit complet de toutes les demandes
✅ **Flexible** - Support gateway et offline
✅ **Évolutif** - Architecture prête pour intégrations réelles
✅ **Validé** - Multi-couches de validation des montants

**Prochaines étapes recommandées :**
1. Créer les vues manquantes (requests.php, admin-requests.php)
2. Intégrer une vraie passerelle de paiement
3. Ajouter upload de preuve de paiement
4. Implémenter notifications email/push
5. Créer dashboard statistiques

**Ressources :**
- [Stripe Documentation](https://stripe.com/docs)
- [PayPal REST API](https://developer.paypal.com/api/rest/)
- [CinetPay API](https://cinetpay.com/developers)
- [FedaPay Docs](https://docs.fedapay.com/)
