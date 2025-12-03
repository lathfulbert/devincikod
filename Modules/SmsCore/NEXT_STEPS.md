# 🎯 Prochaines Étapes - Système Sender Names

## ✅ Ce qui est déjà fait

1. ✅ Tables de base de données créées (`sender_names`, `user_sender_names`)
2. ✅ Modèle `SenderName` avec toutes les méthodes nécessaires
3. ✅ Contrôleur `SenderNameController` complet
4. ✅ Routes définies dans `Modules/SmsCore/Routes/web.php`
5. ✅ Migrations exécutées
6. ✅ Données de test créées (4 Sender Names)
7. ✅ Vue liste des Sender Names créée

## 📋 Étapes Restantes

### 1. **Créer les Vues Manquantes** 🎨

#### a) Vue de Création
Fichier: `Modules/SmsCore/Views/sender-names/create.php`
- Formulaire pour créer un nouveau Sender Name
- Champs: Name, Operator, Status, Notes

#### b) Vue d'Édition
Fichier: `Modules/SmsCore/Views/sender-names/edit.php`
- Formulaire pour modifier un Sender Name existant
- Checkbox pour activer/désactiver

#### c) Vue d'Attribution
Fichier: `Modules/SmsCore/Views/sender-names/assign.php`
- Liste des utilisateurs avec checkboxes
- Permet de sélectionner les utilisateurs qui auront accès au Sender Name

### 2. **Ajouter les Permissions** 🔐

Ajouter dans la table `permissions`:

```sql
INSERT INTO permissions (name, description) VALUES
('sms.sender_names.view', 'View sender names'),
('sms.sender_names.manage', 'Create, edit, delete sender names'),
('sms.sender_names.assign', 'Assign sender names to users'),
('sms.send', 'Send single SMS'),
('sms.bulk', 'Send bulk SMS'),
('sms.campaigns.view', 'View SMS campaigns'),
('sms.campaigns.create', 'Create SMS campaigns');
```

Puis attribuer ces permissions au rôle Admin:

```sql
-- Récupérer l'ID du rôle admin
SELECT id FROM roles WHERE name = 'admin';

-- Attribuer les permissions (remplacer {admin_role_id} par l'ID)
INSERT INTO role_permissions (role_id, permission_id)
SELECT {admin_role_id}, id FROM permissions WHERE name LIKE 'sms.%';
```

### 3. **Intégrer dans les Formulaires SMS** 📱

#### a) Modifier le Contrôleur SMS

Dans `Modules/SmsCore/Controllers/SmsController.php`:

```php
public function sendForm()
{
    $userId = $_SESSION['user']['id'];
    $senderNames = SenderName::getForUser($userId);

    echo $this->app->view->render('sms/send', [
        'title' => 'Send SMS',
        'senderNames' => $senderNames
    ]);
}

public function send()
{
    $senderNameId = (int)($_POST['sender_name_id'] ?? 0);
    $recipient = $_POST['recipient'] ?? '';
    $message = $_POST['message'] ?? '';
    $userId = $_SESSION['user']['id'];

    // Vérifier l'accès au Sender Name
    if (!SenderName::userHasAccess($userId, $senderNameId)) {
        $_SESSION['error'] = 'You do not have access to this sender name';
        header('Location: /sms/send');
        exit;
    }

    // Récupérer le Sender Name
    $senderName = SenderName::find($senderNameId);

    // Envoyer le SMS avec le Sender Name
    // ... logique d'envoi
}
```

#### b) Ajouter le Select dans la Vue

Dans `Modules/SmsCore/Views/sms/send.php`:

```html
<div class="form-group">
    <label for="sender_name_id">Sender Name *</label>
    <select name="sender_name_id" id="sender_name_id" class="form-control" required>
        <option value="">-- Select Sender Name --</option>
        <?php foreach($senderNames as $sn): ?>
            <option value="<?= $sn->id ?>">
                <?= htmlspecialchars($sn->name) ?> (<?= htmlspecialchars($sn->operator) ?>)
            </option>
        <?php endforeach; ?>
    </select>
    <small class="form-text text-muted">
        Select the sender name that will appear on the recipient's phone
    </small>
</div>
```

### 4. **Mettre à Jour le Service d'Envoi SMS** 📤

Dans `Modules/SmsCore/Services/SmsSenderService.php`:

```php
public function send(string $to, string $message, $senderId, array $options = []): array
{
    // Si $senderId est un ID numérique, récupérer le Sender Name
    if (is_numeric($senderId)) {
        $senderName = SenderName::find($senderId);
        if ($senderName) {
            $senderId = $senderName->name;
        }
    }

    // ... reste de la logique d'envoi
}
```

### 5. **Ajouter un Menu dans le Sidebar** 📊

Dans votre fichier de navigation (sidebar):

```html
<li class="nav-item">
    <a href="#" class="nav-link has-submenu">
        <i class="fas fa-sms"></i>
        <span>SMS Management</span>
    </a>
    <ul class="submenu">
        <li><a href="/sms/send">Send SMS</a></li>
        <li><a href="/sms/bulk">Bulk SMS</a></li>
        <li><a href="/sms/campaigns">Campaigns</a></li>
        <li class="divider"></li>
        <li><a href="/sms/sender-names">Sender Names</a></li>
    </ul>
</li>
```

### 6. **Créer les Campagnes SMS avec Sender Names** 📢

Modifier la table `sms_campaigns` pour ajouter:

```sql
ALTER TABLE sms_campaigns
ADD COLUMN sender_name_id INT NULL AFTER sender_id,
ADD FOREIGN KEY (sender_name_id) REFERENCES sender_names(id) ON DELETE SET NULL;
```

### 7. **Tester le Système** 🧪

#### Test 1: Attribution d'un Sender Name
```sql
-- Attribuer "TICAFRIQUE" à l'utilisateur ID 1
INSERT INTO user_sender_names (user_id, sender_name_id, assigned_by)
VALUES (1, 1, 1);
```

#### Test 2: Envoi d'un SMS
1. Se connecter en tant qu'utilisateur
2. Aller sur `/sms/send`
3. Sélectionner un Sender Name
4. Envoyer un SMS
5. Vérifier que le SMS est envoyé avec le bon Sender Name

#### Test 3: API
```bash
curl -X GET http://localhost/api/sms/sender-names/user \
  -H "Cookie: PHPSESSID=your_session_id"
```

### 8. **Améliorer l'UX** ✨

#### a) Validation côté client
```javascript
// Vérifier qu'un Sender Name est sélectionné
document.getElementById('smsForm').addEventListener('submit', function(e) {
    const senderNameId = document.getElementById('sender_name_id').value;
    if (!senderNameId) {
        e.preventDefault();
        alert('Please select a sender name');
    }
});
```

#### b) Indicateur de disponibilité
Afficher le nombre de Sender Names disponibles pour l'utilisateur:

```php
$availableCount = count(SenderName::getForUser($userId));
echo "You have access to {$availableCount} sender name(s)";
```

### 9. **Logs et Audits** 📝

Créer une table pour tracker l'utilisation:

```sql
CREATE TABLE sms_sender_usage_logs (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL,
    sender_name_id INT NOT NULL,
    sms_id BIGINT NULL,
    used_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (sender_name_id) REFERENCES sender_names(id) ON DELETE CASCADE,
    INDEX idx_user_id (user_id),
    INDEX idx_sender_name_id (sender_name_id),
    INDEX idx_used_at (used_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
```

### 10. **Documentation Utilisateur** 📖

Créer un guide utilisateur expliquant:
- Comment demander un nouveau Sender Name
- Procédure de validation par l'opérateur
- Temps de validation estimé
- Restrictions et limitations

## 🚀 Commandes Rapides

### Attribution en masse
```php
// Attribuer tous les Sender Names approuvés à un utilisateur
$approvedSenderNames = SenderName::getActiveApproved();
$senderNameIds = array_column($approvedSenderNames, 'id');
SenderName::syncForUser($userId, $senderNameIds, $adminId);
```

### Vérification des accès
```php
// Vérifier si un utilisateur a au moins un Sender Name
$hasSenderNames = !empty(SenderName::getForUser($userId));
if (!$hasSenderNames) {
    echo "You don't have any sender names assigned. Please contact your administrator.";
}
```

## 📊 Tableau de Bord Suggéré

Créer une page stats affichant:
- Nombre total de Sender Names
- Nombre de Sender Names par statut
- Top 5 Sender Names les plus utilisés
- Utilisateurs avec le plus de Sender Names

## 🔔 Notifications

Implémenter des notifications pour:
- Nouvel Sender Name approuvé
- Sender Name attribué à un utilisateur
- Sender Name révoqué
- Sender Name expiré (si applicable)

## 💡 Fonctionnalités Futures

1. **Import CSV** - Importer plusieurs Sender Names en batch
2. **Expiration** - Gérer la date d'expiration des Sender Names
3. **Historique** - Voir l'historique des modifications
4. **Templates** - Associer des templates SMS à des Sender Names
5. **Quotas** - Limiter l'utilisation par Sender Name

## 📞 Support

Pour toute question ou assistance, contacter l'équipe de développement.
