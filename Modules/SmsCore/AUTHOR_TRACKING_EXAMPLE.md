# 📱 Exemple d'utilisation du Author Tracking dans SmsCore

## Modèles configurés

Les modèles suivants du module SmsCore ont le tracking activé :

- ✅ `SmsMessage` - Track created_by et updated_by
- ✅ `SmsCampaign` - Track created_by et updated_by
- ✅ `SenderName` - Track created_by, updated_by, et deleted_by (soft delete)

## Exemples concrets

### 1. Dans le contrôleur SmsController

#### Envoi d'un SMS

```php
public function send()
{
    // ... code existant ...

    // Le tracking est automatique lors de la création
    $smsMessage = SmsMessage::create([
        'user_id' => $_SESSION['user']['id'] ?? null,
        'to' => $to,
        'from' => $sender,
        'message' => $message,
        'gateway' => $gatewayConfig->provider_code,
        'status' => 'pending',
        'message_id' => 'SMS-' . uniqid(),
        // created_by et updated_by sont automatiquement remplis ✅
    ]);

    // ... reste du code ...
}
```

### 2. Dans les vues - Historique SMS

#### Fichier : `Views/sms/history.php`

Ajoutez une colonne pour afficher qui a envoyé le SMS :

```php
<table class="table table-striped">
    <thead>
        <tr>
            <th>ID</th>
            <th>Destinataire</th>
            <th>Message</th>
            <th>Status</th>
            <th>Envoyé par</th>
            <th>Date</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($messages as $sms): ?>
        <tr>
            <td><?= $sms->id ?></td>
            <td><?= e($sms->to) ?></td>
            <td><?= e(str_limit($sms->message, 50)) ?></td>
            <td>
                <span class="badge badge-<?= $sms->status === 'sent' ? 'success' : 'warning' ?>">
                    <?= e($sms->status) ?>
                </span>
            </td>
            <td>
                <!-- Afficher le nom de l'auteur -->
                <span class="text-muted">
                    <i class="fas fa-user"></i>
                    <?= e($sms->getCreatorName() ?? 'Système') ?>
                </span>
            </td>
            <td><?= e($sms->created_at) ?></td>
            <td>
                <a href="/admin/sms/details/<?= $sms->id ?>" class="btn btn-sm btn-info">
                    Détails
                </a>
            </td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>
```

### 3. Page de détails SMS

#### Fichier : `Views/sms/details.php`

Affichez les informations complètes d'audit :

```php
<div class="card">
    <div class="card-header">
        <h4>Détails du SMS #<?= $sms->id ?></h4>
    </div>
    <div class="card-body">
        <dl class="row">
            <dt class="col-sm-3">Destinataire</dt>
            <dd class="col-sm-9"><?= e($sms->to) ?></dd>

            <dt class="col-sm-3">Message</dt>
            <dd class="col-sm-9"><?= e($sms->message) ?></dd>

            <dt class="col-sm-3">Status</dt>
            <dd class="col-sm-9">
                <span class="badge badge-<?= $sms->status === 'sent' ? 'success' : 'warning' ?>">
                    <?= e($sms->status) ?>
                </span>
            </dd>

            <dt class="col-sm-3">Gateway</dt>
            <dd class="col-sm-9"><?= e($sms->gateway) ?></dd>

            <!-- Informations d'audit -->
            <dt class="col-sm-3">Créé par</dt>
            <dd class="col-sm-9">
                <i class="fas fa-user-plus text-success"></i>
                <?= e($sms->getCreatorName() ?? 'N/A') ?>
                <small class="text-muted">(le <?= e($sms->created_at) ?>)</small>
            </dd>

            <?php if ($sms->updated_by && $sms->updated_at !== $sms->created_at): ?>
            <dt class="col-sm-3">Modifié par</dt>
            <dd class="col-sm-9">
                <i class="fas fa-user-edit text-info"></i>
                <?= e($sms->getUpdaterName() ?? 'N/A') ?>
                <small class="text-muted">(le <?= e($sms->updated_at) ?>)</small>
            </dd>
            <?php endif; ?>
        </dl>
    </div>
</div>
```

### 4. Campagnes SMS

#### Fichier : `Views/sms/campaigns/list.php`

```php
<table class="table">
    <thead>
        <tr>
            <th>Campagne</th>
            <th>Status</th>
            <th>Destinataires</th>
            <th>Créée par</th>
            <th>Modifiée par</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($campaigns as $campaign): ?>
        <tr>
            <td><?= e($campaign->name) ?></td>
            <td>
                <span class="badge badge-<?= $campaign->status === 'completed' ? 'success' : 'warning' ?>">
                    <?= e($campaign->status) ?>
                </span>
            </td>
            <td><?= $campaign->total_recipients ?></td>
            <td>
                <div class="d-flex align-items-center">
                    <i class="fas fa-user-circle mr-2"></i>
                    <div>
                        <div><?= e($campaign->getCreatorName() ?? 'N/A') ?></div>
                        <small class="text-muted"><?= e($campaign->created_at) ?></small>
                    </div>
                </div>
            </td>
            <td>
                <?php if ($campaign->updated_by): ?>
                    <small><?= e($campaign->getUpdaterName()) ?></small>
                <?php else: ?>
                    <small class="text-muted">-</small>
                <?php endif; ?>
            </td>
            <td>
                <!-- Actions -->
            </td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>
```

### 5. Gestion des Sender Names

#### Fichier : `Controllers/SenderNameController.php`

```php
public function delete($id)
{
    $senderName = SenderName::find($id);

    if (!$senderName) {
        $_SESSION['flash_error'] = 'Sender Name introuvable';
        redirect('/admin/sender-names');
        return;
    }

    // Vérifier les permissions (optionnel)
    $currentUserId = current_user_id();
    if (!can('delete_sender_names') && !$senderName->isCreatedBy($currentUserId)) {
        $_SESSION['flash_error'] = 'Vous ne pouvez supprimer que vos propres Sender Names';
        redirect('/admin/sender-names');
        return;
    }

    // Soft delete - deleted_by sera automatiquement rempli ✅
    $senderName->delete();

    $_SESSION['flash_success'] = 'Sender Name supprimé avec succès';
    redirect('/admin/sender-names');
}
```

### 6. Filtrer par créateur

#### Dans un contrôleur

```php
public function myCampaigns()
{
    $userId = current_user_id();

    // Récupérer uniquement les campagnes créées par l'utilisateur connecté
    $campaigns = SmsCampaign::where('created_by', $userId)
        ->orderBy('created_at', 'DESC')
        ->get();

    echo view('smscore/campaigns/my_campaigns', [
        'campaigns' => $campaigns,
        'title' => 'Mes Campagnes'
    ]);
}
```

### 7. Logs d'audit

#### Créer une page d'audit

```php
public function auditLog($id)
{
    $sms = SmsMessage::find($id);

    if (!$sms) {
        $_SESSION['flash_error'] = 'SMS introuvable';
        redirect('/admin/sms/history');
        return;
    }

    // Informations d'audit
    $auditInfo = [
        'created' => [
            'user' => $sms->getCreatorName(),
            'date' => $sms->created_at,
            'user_id' => $sms->created_by
        ],
        'updated' => [
            'user' => $sms->getUpdaterName(),
            'date' => $sms->updated_at,
            'user_id' => $sms->updated_by
        ]
    ];

    echo view('smscore/sms/audit', [
        'sms' => $sms,
        'audit' => $auditInfo,
        'title' => 'Audit SMS'
    ]);
}
```

## 🎨 Composants réutilisables

### Component: Affichage auteur

Créez un composant réutilisable dans `resources/views/backend/components/author_info.php` :

```php
<?php
/**
 * Component: Author Info
 * Usage: <?php component('author_info', ['model' => $item]) ?>
 */
?>
<div class="author-info">
    <div class="d-flex align-items-center">
        <i class="fas fa-user-circle text-primary mr-2"></i>
        <div>
            <strong><?= e($model->getCreatorName() ?? 'N/A') ?></strong>
            <br>
            <small class="text-muted">
                Créé le <?= e($model->created_at) ?>
            </small>
            <?php if ($model->updated_by && $model->updated_at !== $model->created_at): ?>
            <br>
            <small class="text-info">
                <i class="fas fa-edit"></i>
                Modifié par <?= e($model->getUpdaterName()) ?> le <?= e($model->updated_at) ?>
            </small>
            <?php endif; ?>
        </div>
    </div>
</div>
```

### Utilisation du composant

```php
<!-- Dans n'importe quelle vue -->
<?php component('author_info', ['model' => $sms]) ?>
```

## 🔒 Contrôle d'accès basé sur l'auteur

```php
// Vérifier si l'utilisateur peut modifier un SMS
if ($sms->isCreatedBy(current_user_id()) || can('edit_all_sms')) {
    // Autoriser la modification
} else {
    $_SESSION['flash_error'] = 'Vous ne pouvez modifier que vos propres SMS';
    redirect('/admin/sms/history');
}
```

## 📊 Statistiques par utilisateur

```php
// Dans un dashboard
public function userStats()
{
    $userId = current_user_id();

    // Nombre de SMS envoyés par l'utilisateur
    $sentCount = SmsMessage::where('created_by', $userId)
        ->where('status', 'sent')
        ->count();

    // Nombre de campagnes créées
    $campaignCount = SmsCampaign::where('created_by', $userId)->count();

    echo view('smscore/dashboard/user_stats', [
        'sentCount' => $sentCount,
        'campaignCount' => $campaignCount
    ]);
}
```

## ✅ Avantages pour SmsCore

1. **Traçabilité** : Savoir qui a envoyé quel SMS
2. **Audit** : Historique complet des modifications
3. **Sécurité** : Contrôle d'accès basé sur l'auteur
4. **Statistiques** : Rapports par utilisateur
5. **Conformité** : Respect des exigences d'audit

## 🎯 Prochaines étapes

1. ✅ Les modèles sont déjà configurés
2. ✅ Les migrations sont prêtes
3. ⏳ Mettez à jour vos vues pour afficher les informations d'auteur
4. ⏳ Ajoutez des contrôles d'accès basés sur l'auteur si nécessaire
5. ⏳ Créez des rapports et statistiques par utilisateur

## 📞 Support

Pour toute question, consultez la documentation complète dans `Core/Database/AUTHOR_TRACKING.md`.
