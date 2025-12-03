# Système de Gestion des Sender Names

## Vue d'ensemble

Le système de Sender Names permet aux administrateurs de gérer les noms d'expéditeurs (Sender IDs) validés par les opérateurs téléphoniques et d'attribuer ces noms aux utilisateurs pour l'envoi de SMS.

## Fonctionnalités

### Pour les Administrateurs

1. **Gestion des Sender Names**
   - Créer de nouveaux Sender Names
   - Modifier les Sender Names existants
   - Approuver/Rejeter les Sender Names
   - Activer/Désactiver les Sender Names

2. **Attribution aux Utilisateurs**
   - Attribuer des Sender Names spécifiques à des utilisateurs
   - Gérer les accès par utilisateur
   - Voir qui a accès à quel Sender Name

### Pour les Utilisateurs

1. **Sélection de Sender Name**
   - Voir uniquement les Sender Names qui leur sont attribués
   - Sélectionner le Sender Name pour l'envoi de SMS
   - Utiliser dans les campagnes SMS

## Structure de la Base de Données

### Table `sender_names`

| Colonne | Type | Description |
|---------|------|-------------|
| id | INT | Identifiant unique |
| name | VARCHAR(11) | Nom du Sender (max 11 caractères) |
| operator | VARCHAR(50) | Opérateur ayant validé le Sender Name |
| status | ENUM | Statut: pending, approved, rejected |
| is_active | TINYINT | 1 si actif, 0 sinon |
| validation_date | DATE | Date de validation par l'opérateur |
| notes | TEXT | Notes de l'administrateur |
| created_by | BIGINT UNSIGNED | ID de l'utilisateur créateur |

### Table `user_sender_names`

| Colonne | Type | Description |
|---------|------|-------------|
| id | INT | Identifiant unique |
| user_id | BIGINT UNSIGNED | ID de l'utilisateur |
| sender_name_id | INT | ID du Sender Name |
| assigned_by | BIGINT UNSIGNED | ID de l'admin ayant attribué |
| assigned_at | TIMESTAMP | Date d'attribution |

## Routes Disponibles

### Routes Admin

- `GET /sms/sender-names` - Liste des Sender Names
- `GET /sms/sender-names/create` - Formulaire de création
- `POST /sms/sender-names/store` - Enregistrer un nouveau Sender Name
- `GET /sms/sender-names/edit?id={id}` - Formulaire d'édition
- `POST /sms/sender-names/update` - Mettre à jour un Sender Name
- `POST /sms/sender-names/delete` - Supprimer un Sender Name
- `GET /sms/sender-names/assign-users?id={id}` - Gérer les attributions
- `POST /sms/sender-names/save-assignments` - Sauvegarder les attributions
- `POST /sms/sender-names/bulk-assign-to-user` - Attribution en masse

### API

- `GET /api/sms/sender-names/user` - Obtenir les Sender Names de l'utilisateur connecté

## Utilisation du Modèle

### Récupérer les Sender Names actifs et approuvés

```php
$senderNames = SenderName::getActiveApproved();
```

### Récupérer les Sender Names d'un utilisateur

```php
$userSenderNames = SenderName::getForUser($userId);
```

### Vérifier l'accès d'un utilisateur

```php
$hasAccess = SenderName::userHasAccess($userId, $senderNameId);
```

### Attribuer un Sender Name à un utilisateur

```php
SenderName::assignToUser($senderNameId, $userId, $assignedBy);
```

### Synchroniser les Sender Names d'un utilisateur

```php
$senderNameIds = [1, 2, 3];
SenderName::syncForUser($userId, $senderNameIds, $assignedBy);
```

## Permissions Requises

- `sms.sender_names.view` - Voir les Sender Names
- `sms.sender_names.manage` - Créer/Modifier/Supprimer
- `sms.sender_names.assign` - Attribuer aux utilisateurs

## Exemple d'intégration dans un formulaire

```php
// Dans le contrôleur SMS
public function sendForm()
{
    $userId = $_SESSION['user']['id'];
    $senderNames = SenderName::getForUser($userId);

    echo $this->app->view->render('sms/send', [
        'senderNames' => $senderNames
    ]);
}
```

```html
<!-- Dans la vue -->
<select name="sender_name_id" required>
    <option value="">Sélectionner un Sender Name</option>
    <?php foreach($senderNames as $senderName): ?>
        <option value="<?= $senderName->id ?>">
            <?= htmlspecialchars($senderName->name) ?>
            (<?= htmlspecialchars($senderName->operator) ?>)
        </option>
    <?php endforeach; ?>
</select>
```

## API JavaScript

```javascript
// Récupérer les Sender Names de l'utilisateur connecté
fetch('/api/sms/sender-names/user')
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            const select = document.getElementById('sender-name-select');
            data.data.forEach(sn => {
                const option = document.createElement('option');
                option.value = sn.id;
                option.textContent = `${sn.name} (${sn.operator})`;
                select.appendChild(option);
            });
        }
    });
```

## Notes Importantes

1. **Validation Opérateur** : Tous les Sender Names doivent être validés par l'opérateur avant d'être utilisés
2. **Limite de caractères** : Les Sender Names sont limités à 11 caractères
3. **Attribution obligatoire** : Un utilisateur ne peut utiliser que les Sender Names qui lui sont explicitement attribués
4. **Statut** : Seuls les Sender Names avec le statut "approved" et is_active=1 sont disponibles

## Migration

Pour créer les tables, exécutez :

```bash
php sunu migrate
```

Les migrations se trouvent dans :
- `Modules/SmsCore/Database/Migrations/001_create_sender_names_table.php`
- `Modules/SmsCore/Database/Migrations/002_create_user_sender_names_table.php`

## Données de test

Des Sender Names de test ont été créés :
- **TICAFRIQUE** (Orange CI) - Approved
- **SUNUBANK** (MTN CI) - Approved
- **MYCOMPANY** (Moov CI) - Approved
- **TESTNAME** (Orange CI) - Pending

Pour attribuer un Sender Name à un utilisateur en SQL direct :

```sql
INSERT INTO user_sender_names (user_id, sender_name_id, assigned_by)
VALUES (1, 1, 1);
```

Où :
- user_id = ID de l'utilisateur destinataire
- sender_name_id = ID du Sender Name
- assigned_by = ID de l'admin qui fait l'attribution
