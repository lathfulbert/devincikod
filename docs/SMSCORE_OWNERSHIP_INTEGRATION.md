# Intégration du Contrôle d'Accès dans SmsCore

Ce document récapitule l'intégration du système de contrôle d'accès basé sur la propriété dans le module SmsCore.

## ✅ Modifications Effectuées

### 1. Controllers Migrés

#### SmsCampaignController
**Fichier** : [Modules/SmsCore/Controllers/SmsCampaignController.php](Modules/SmsCore/Controllers/SmsCampaignController.php)

**Changements** :
- ✅ Ajout du trait `AuthorizesOwnership`
- ✅ Initialisation de la policy dans le constructeur
- ✅ Filtrage dans `index()` - Admin voit tout, utilisateurs voient leurs campagnes
- ✅ Autorisation dans `show()` - Vérifie le droit de voir une campagne
- ✅ Autorisation dans `edit()` - Vérifie le droit de modifier
- ✅ Autorisation dans `update()` - Vérifie le droit de mettre à jour
- ✅ Autorisation dans `delete()` - Vérifie le droit de supprimer
- ✅ Passage de `canEdit` et `canDelete` à la vue `show`

**Champ de propriété** : `created_by`

#### SmsController
**Fichier** : [Modules/SmsCore/Controllers/SmsController.php](Modules/SmsCore/Controllers/SmsController.php)

**Changements** :
- ✅ Ajout du trait `AuthorizesOwnership`
- ✅ Initialisation de la policy dans le constructeur
- ✅ Filtrage dans `history()` - Admin voit tous les SMS, utilisateurs voient leurs SMS
- ✅ Autorisation dans `details()` - Vérifie le droit de voir un SMS

**Champ de propriété** : `user_id`

### 2. Modèles Déjà Configurés

Les modèles utilisent déjà le trait `HasAuthor` pour le tracking automatique :

#### SmsCampaign
```php
use App\Core\Database\Traits\HasAuthor;

class SmsCampaign extends Model
{
    use HasAuthor;

    protected array $fillable = [
        // ...
        'created_by',
        'updated_by',
    ];
}
```

#### SmsMessage
```php
use App\Core\Database\Traits\HasAuthor;

class SmsMessage extends Model
{
    use HasAuthor;

    protected array $fillable = [
        'user_id',
        // ...
        'created_by',
        'updated_by'
    ];
}
```

## 🎯 Comportement Actuel

### Campagnes SMS

| Action | Admin | Utilisateur Normal |
|--------|-------|-------------------|
| Voir toutes les campagnes | ✅ | ❌ (seulement les siennes) |
| Créer une campagne | ✅ | ✅ |
| Voir détails d'une campagne | ✅ (toutes) | ✅ (siennes uniquement) |
| Modifier une campagne | ✅ (toutes) | ✅ (siennes uniquement) |
| Supprimer une campagne | ✅ (toutes) | ✅ (siennes uniquement) |

### Messages SMS

| Action | Admin | Utilisateur Normal |
|--------|-------|-------------------|
| Voir l'historique complet | ✅ | ❌ (seulement ses SMS) |
| Envoyer un SMS | ✅ | ✅ |
| Voir détails d'un SMS | ✅ (tous) | ✅ (siens uniquement) |

## 📝 Code Avant/Après

### SmsCampaignController::index()

**Avant** :
```php
public function index()
{
    $campaigns = SmsCampaign::orderBy('created_at', 'desc')->get();

    echo view('SmsCore/sms/campaigns/index', [
        'campaigns' => $campaigns,
        'title' => 'SMS Campaigns'
    ]);
}
```

**Après** :
```php
public function index()
{
    // Filtrer par propriétaire (admin voit tout)
    $query = SmsCampaign::query()->orderBy('created_at', 'desc');
    $query = $this->scopeByOwnership($query, 'created_by');
    $campaigns = $query->get();

    echo view('SmsCore/sms/campaigns/index', [
        'campaigns' => $campaigns,
        'title' => 'SMS Campaigns',
        'isAdmin' => $this->isAdmin() // Nouveau
    ]);
}
```

### SmsCampaignController::show()

**Avant** :
```php
public function show($id)
{
    $campaign = SmsCampaign::find($id);

    if (!$campaign) {
        $_SESSION['flash_error'] = 'Campagne introuvable.';
        redirect('/admin/sms/campaigns');
        exit;
    }

    echo view('SmsCore/sms/campaigns/show', [
        'campaign' => $campaign,
        'queueItems' => $queueItems,
        'title' => 'Campaign: ' . $campaign->name
    ]);
}
```

**Après** :
```php
public function show($id)
{
    $campaign = SmsCampaign::find($id);

    if (!$campaign) {
        $_SESSION['flash_error'] = 'Campagne introuvable.';
        redirect('/admin/sms/campaigns');
        exit;
    }

    // Vérifier l'autorisation (redirige automatiquement si refusé)
    $this->authorizeView($campaign, 'created_by', '/admin/sms/campaigns');

    echo view('SmsCore/sms/campaigns/show', [
        'campaign' => $campaign,
        'queueItems' => $queueItems,
        'title' => 'Campaign: ' . $campaign->name,
        'canEdit' => $this->canUpdate($campaign, 'created_by'), // Nouveau
        'canDelete' => $this->canDelete($campaign, 'created_by') // Nouveau
    ]);
}
```

### SmsController::history()

**Avant** :
```php
public function history()
{
    $messages = SmsMessage::orderBy('created_at', 'DESC')->get();

    echo view('SmsCore/sms/history', [
        'messages' => $messages,
        'title' => 'SMS History'
    ]);
}
```

**Après** :
```php
public function history()
{
    // Filtrer par propriétaire (admin voit tout)
    $query = SmsMessage::query()->orderBy('created_at', 'DESC');
    $query = $this->scopeByOwnership($query, 'user_id');
    $messages = $query->get();

    echo view('SmsCore/sms/history', [
        'messages' => $messages,
        'title' => 'SMS History',
        'isAdmin' => $this->isAdmin() // Nouveau
    ]);
}
```

## ✅ Modifications des Vues (Complétées)

### campaigns/index.php

Badge pour indiquer le mode (admin vs utilisateur) :

```php
<div class="card-header d-flex justify-content-between align-items-center">
    <div>
        <h5>Liste des Campagnes SMS</h5>
        <?php if ($isAdmin ?? false): ?>
            <span class="badge badge-success">
                <i data-feather="shield"></i> Mode Admin - Toutes les campagnes
            </span>
        <?php else: ?>
            <span class="badge badge-info">
                <i data-feather="user"></i> Mes campagnes uniquement
            </span>
        <?php endif; ?>
    </div>
    <a href="<?= url('/admin/sms/bulk') ?>" class="btn btn-primary">
        <i data-feather="plus"></i> Nouvelle Campagne
    </a>
</div>
```

### campaigns/show.php

Boutons conditionnels basés sur les permissions :

```php
<div class="mt-3">
    <?php if ($canEdit ?? false): ?>
        <a href="<?= url('/admin/sms/campaigns/' . $campaign->id . '/edit') ?>" class="btn btn-warning btn-block mb-2">
            <i data-feather="edit"></i> Modifier la Campagne
        </a>
    <?php endif; ?>

    <?php if ($canDelete ?? false): ?>
        <form method="POST" action="<?= url('/admin/sms/campaigns/' . $campaign->id . '/delete') ?>">
            <?= csrf_field() ?>
            <button type="submit" class="btn btn-danger btn-block" onclick="return confirm('Êtes-vous sûr de vouloir supprimer cette campagne ?')">
                <i data-feather="trash-2"></i> Supprimer la Campagne
            </button>
        </form>
    <?php endif; ?>

    <a href="<?= url('/admin/sms/campaigns') ?>" class="btn btn-secondary btn-block mt-2">
        <i data-feather="arrow-left"></i> Retour à la liste
    </a>
</div>
```

### sms/history.php

Badge de filtre visuel et colonne utilisateur pour admins :

```php
<div class="card-header">
    <div class="row">
        <div class="col-md-6">
            <h5>SMS History</h5>
            <?php if ($isAdmin ?? false): ?>
                <span class="badge badge-success">
                    <i data-feather="eye"></i> Vue Admin - Tous les SMS
                </span>
            <?php else: ?>
                <span class="badge badge-info">
                    <i data-feather="user"></i> Mes SMS uniquement
                </span>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Table avec colonne utilisateur conditionnelle -->
<thead>
    <tr>
        <th>#</th>
        <th>Recipient</th>
        <th>Message</th>
        <th>Gateway</th>
        <th>Status</th>
        <th>Cost</th>
        <?php if ($isAdmin ?? false): ?>
            <th>Utilisateur</th>
        <?php endif; ?>
        <th>Date</th>
        <th>Actions</th>
    </tr>
</thead>
<tbody>
    <?php foreach ($messages as $msg): ?>
        <tr>
            <!-- ... colonnes existantes ... -->
            <td>$<?= number_format($msg->cost ?? 0, 2) ?></td>
            <?php if ($isAdmin ?? false): ?>
                <td><?= htmlspecialchars($msg->getCreatorName() ?? 'Système') ?></td>
            <?php endif; ?>
            <td><?= date('M d, H:i', strtotime($msg->created_at)) ?></td>
            <!-- ... -->
        </tr>
    <?php endforeach; ?>
</tbody>
```

## 🔄 Controllers Restants à Migrer

### Priorité Haute
- [ ] **DashboardController** - Statistiques filtrées
- [ ] **SmsPricingController** - Facturation par utilisateur
- [ ] **SenderNameController** - Déjà filtré mais à vérifier

### Priorité Moyenne
- [ ] **WalletController** (module séparé) - Soldes par utilisateur

## 🧪 Tests à Effectuer

### Test 1 : Admin
1. Connectez-vous avec un compte admin
2. Allez dans **SMS > Campagnes**
3. ✅ Vérifiez que vous voyez toutes les campagnes (de tous les utilisateurs)
4. Ouvrez une campagne créée par un autre utilisateur
5. ✅ Vérifiez que vous pouvez la modifier/supprimer

### Test 2 : Utilisateur Normal (User A)
1. Connectez-vous avec User A
2. Créez une campagne "Test User A"
3. Allez dans **SMS > Campagnes**
4. ✅ Vérifiez que vous ne voyez que vos campagnes
5. ✅ Vérifiez que vous pouvez modifier/supprimer vos campagnes

### Test 3 : Utilisateur Normal (User B)
1. Connectez-vous avec User B
2. Allez dans **SMS > Campagnes**
3. ✅ Vérifiez que vous ne voyez PAS la campagne "Test User A"
4. Essayez d'accéder directement à `/admin/sms/campaigns/{id-campagne-user-a}`
5. ✅ Vérifiez que vous êtes redirigé avec un message d'erreur

### Test 4 : Historique SMS
1. User A envoie un SMS
2. User B envoie un SMS
3. Connectez-vous avec User A
4. Allez dans **SMS > Historique**
5. ✅ Vérifiez que vous ne voyez que vos SMS
6. Connectez-vous en admin
7. ✅ Vérifiez que vous voyez tous les SMS

## 📊 Base de Données

### Colonnes Requises

#### Table `sms_campaigns`
```sql
ALTER TABLE sms_campaigns
ADD COLUMN IF NOT EXISTS created_by INT UNSIGNED NULL,
ADD COLUMN IF NOT EXISTS updated_by INT UNSIGNED NULL,
ADD FOREIGN KEY (created_by) REFERENCES users(id),
ADD FOREIGN KEY (updated_by) REFERENCES users(id);
```

#### Table `sms_messages`
```sql
-- user_id devrait déjà exister
-- Vérifier :
SHOW COLUMNS FROM sms_messages LIKE 'user_id';

-- Si pas de created_by/updated_by :
ALTER TABLE sms_messages
ADD COLUMN IF NOT EXISTS created_by INT UNSIGNED NULL,
ADD COLUMN IF NOT EXISTS updated_by INT UNSIGNED NULL,
ADD FOREIGN KEY (created_by) REFERENCES users(id),
ADD FOREIGN KEY (updated_by) REFERENCES users(id);
```

### Vérification

```sql
-- Vérifier les campagnes avec propriétaire
SELECT
    c.id,
    c.name,
    c.created_by,
    u.username as creator
FROM sms_campaigns c
LEFT JOIN users u ON u.id = c.created_by
ORDER BY c.created_at DESC
LIMIT 10;

-- Vérifier les messages avec propriétaire
SELECT
    m.id,
    m.to,
    m.message,
    m.user_id,
    u.username as owner
FROM sms_messages m
LEFT JOIN users u ON u.id = m.user_id
ORDER BY m.created_at DESC
LIMIT 10;
```

## 🚀 Déploiement

### Étapes

1. **Backup de la base de données**
   ```bash
   mysqldump -u user -p sunuframework > backup_before_ownership_$(date +%Y%m%d).sql
   ```

2. **Commit des changements**
   ```bash
   git add Modules/SmsCore/Controllers/
   git commit -m "feat(SmsCore): Add ownership-based authorization"
   git push origin main
   ```

3. **Sur le serveur**
   ```bash
   cd /path/to/project
   git pull origin main
   rm -rf storage/cache/*
   ```

4. **Vérifier les migrations**
   ```bash
   php artisan migrate:status
   ```

5. **Tests post-déploiement**
   - Tester avec compte admin
   - Tester avec 2 comptes utilisateurs différents
   - Vérifier les logs : `tail -f storage/logs/app.log`

## ✅ Résultat Final

Le module SmsCore est maintenant sécurisé avec un contrôle d'accès basé sur la propriété :

- ✅ **Isolation des données** : Chaque utilisateur ne voit que ses propres ressources
- ✅ **Accès admin préservé** : Les admins ont toujours accès complet
- ✅ **Sécurité renforcée** : Impossible d'accéder aux ressources d'autrui via URL directe
- ✅ **Traçabilité** : Tracking automatique avec `created_by`/`updated_by`
- ✅ **Interface adaptée** : Boutons d'actions conditionnels selon les droits

## 📚 Ressources

- **Guide Complet** : [OWNERSHIP_AUTHORIZATION_GUIDE.md](OWNERSHIP_AUTHORIZATION_GUIDE.md)
- **Plan de Migration** : [OWNERSHIP_MIGRATION_PLAN.md](OWNERSHIP_MIGRATION_PLAN.md)
- **Policy** : [Core/Authorization/OwnershipPolicy.php](Core/Authorization/OwnershipPolicy.php)
- **Trait** : [Core/Authorization/Traits/AuthorizesOwnership.php](Core/Authorization/Traits/AuthorizesOwnership.php)
