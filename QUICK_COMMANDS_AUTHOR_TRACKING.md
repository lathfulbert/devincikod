# ⚡ Commandes rapides - Author Tracking

## 🚀 Démarrage

### 1. Exécuter la migration (obligatoire)
```bash
cd c:\laragon\www\sunuframework2
php public/index.php migrate
```

### 2. Tester le système
```bash
php Core/Database/test_author_tracking.php
```

---

## 🛠️ Activer sur un modèle

### Option 1 : Automatique (recommandé)
```bash
php Core/Database/Scripts/enable_author_tracking.php Modules/YourModule/Models/YourModel.php
```

### Option 2 : Manuel

Éditez votre modèle et ajoutez :

```php
use App\Core\Database\Traits\HasAuthor;

class YourModel extends Model
{
    use HasAuthor;
}
```

---

## 🔍 Vérifications

### Vérifier qu'un utilisateur est connecté
```bash
# Dans n'importe quel fichier PHP
var_dump($_SESSION['user']['id'] ?? 'Non connecté');
```

### Vérifier les colonnes dans une table
```sql
SHOW COLUMNS FROM your_table LIKE '%_by';
```

### Tester sur un modèle spécifique
```php
$item = YourModel::find(1);
var_dump($item->created_by);
var_dump($item->getCreatorName());
```

---

## 📊 Exemples de requêtes SQL

### Voir tous les enregistrements avec leurs auteurs
```sql
SELECT
    sm.*,
    u1.username as creator_username,
    u2.username as updater_username
FROM sms_messages sm
LEFT JOIN users u1 ON sm.created_by = u1.id
LEFT JOIN users u2 ON sm.updated_by = u2.id
ORDER BY sm.created_at DESC
LIMIT 10;
```

### Compter par auteur
```sql
SELECT
    u.username,
    COUNT(*) as total_created
FROM sms_messages sm
JOIN users u ON sm.created_by = u.id
GROUP BY u.username
ORDER BY total_created DESC;
```

---

## 🎯 Activer sur plusieurs modèles

### Activer sur tous les modèles d'un module

**Module Wallet:**
```bash
php Core/Database/Scripts/enable_author_tracking.php Modules/Wallet/Models/Wallet.php
php Core/Database/Scripts/enable_author_tracking.php Modules/Wallet/Models/WalletTransaction.php
```

**Module Settings:**
```bash
php Core/Database/Scripts/enable_author_tracking.php Modules/Settings/Models/Setting.php
php Core/Database/Scripts/enable_author_tracking.php Modules/Settings/Models/SmsGateway.php
php Core/Database/Scripts/enable_author_tracking.php Modules/Settings/Models/WalletGateway.php
```

**Module RBAC:**
```bash
php Core/Database/Scripts/enable_author_tracking.php Modules/RBAC/Models/Role.php
php Core/Database/Scripts/enable_author_tracking.php Modules/RBAC/Models/Permission.php
php Core/Database/Scripts/enable_author_tracking.php Modules/RBAC/Models/Module.php
```

---

## 🧪 Tests unitaires (à créer)

### Créer un test simple
```php
// tests/AuthorTrackingTest.php
$_SESSION['user']['id'] = 1;

$sms = SmsMessage::create([
    'to' => '+221771234567',
    'message' => 'Test',
    'status' => 'pending'
]);

assert($sms->created_by === 1, 'created_by should be 1');
assert($sms->updated_by === 1, 'updated_by should be 1');

$_SESSION['user']['id'] = 2;
$sms->status = 'sent';
$sms->save();

assert($sms->updated_by === 2, 'updated_by should be 2');
assert($sms->created_by === 1, 'created_by should still be 1');

echo "✅ All tests passed!\n";
```

---

## 📝 Mise à jour des vues

### Vue simple avec auteur
```php
<!-- Dans n'importe quelle vue -->
<td><?= e($item->getCreatorName() ?? 'N/A') ?></td>
```

### Vue détaillée
```php
<dl>
    <dt>Créé par</dt>
    <dd>
        <i class="fas fa-user"></i>
        <?= e($item->getCreatorName()) ?>
        <small>(<?= e($item->created_at) ?>)</small>
    </dd>

    <?php if ($item->updated_by): ?>
    <dt>Modifié par</dt>
    <dd>
        <i class="fas fa-edit"></i>
        <?= e($item->getUpdaterName()) ?>
        <small>(<?= e($item->updated_at) ?>)</small>
    </dd>
    <?php endif; ?>
</dl>
```

---

## 🔒 Contrôle d'accès

### Vérifier l'auteur avant suppression
```php
public function delete($id)
{
    $item = YourModel::find($id);

    // Seul l'auteur ou un admin peut supprimer
    if (!$item->isCreatedBy(current_user_id()) && !can('delete_all')) {
        $_SESSION['flash_error'] = 'Accès refusé';
        redirect('/admin/items');
        return;
    }

    $item->delete();
    redirect('/admin/items');
}
```

---

## 📊 Rapports et statistiques

### Mes créations
```php
$myItems = YourModel::where('created_by', current_user_id())
    ->orderBy('created_at', 'DESC')
    ->get();
```

### Activité par utilisateur
```php
$activity = [
    'created' => YourModel::where('created_by', $userId)->count(),
    'updated' => YourModel::where('updated_by', $userId)->count(),
];
```

---

## 🐛 Dépannage rapide

### Colonnes non remplies ?
```bash
# 1. Vérifier le trait
grep -r "use HasAuthor" Modules/YourModule/Models/

# 2. Vérifier l'utilisateur
# Dans votre navigateur, exécutez :
var_dump($_SESSION['user']['id']);

# 3. Vérifier les colonnes
mysql -u root -p
USE your_database;
SHOW COLUMNS FROM your_table LIKE '%_by';
```

### Erreur "Column not found" ?
```bash
# Exécuter la migration
php public/index.php migrate
```

### Noms NULL ?
```bash
# Vérifier que les utilisateurs existent
mysql -u root -p
USE your_database;
SELECT * FROM users WHERE id IN (1, 2, 3);
```

---

## 🔄 Rollback

### Désactiver sur un modèle
Supprimez manuellement :
```php
// use App\Core\Database\Traits\HasAuthor; ← Supprimer
// use HasAuthor; ← Supprimer
```

### Supprimer les colonnes (attention !)
```php
// Modifier la migration et exécuter down()
$migration = require 'Core/Database/Migrations/001_add_author_tracking_columns.php';
$migration->down();
```

---

## 📚 Documentation

- **Guide complet** : [AUTHOR_TRACKING_IMPLEMENTATION.md](AUTHOR_TRACKING_IMPLEMENTATION.md)
- **Documentation technique** : [Core/Database/AUTHOR_TRACKING.md](Core/Database/AUTHOR_TRACKING.md)
- **Quick Start** : [Core/Database/QUICK_START_AUTHOR_TRACKING.md](Core/Database/QUICK_START_AUTHOR_TRACKING.md)
- **Exemples SmsCore** : [Modules/SmsCore/AUTHOR_TRACKING_EXAMPLE.md](Modules/SmsCore/AUTHOR_TRACKING_EXAMPLE.md)

---

## ✅ Checklist rapide

```
[ ] Migration exécutée
[ ] Test réussi
[ ] Trait ajouté aux modèles
[ ] Vues mises à jour
[ ] Contrôles d'accès ajoutés
[ ] Tests manuels effectués
[ ] Documentation lue
[ ] Équipe formée
```

---

## 🎯 Commandes les plus utiles

```bash
# Tout en un : Migration + Test
php public/index.php migrate && php Core/Database/test_author_tracking.php

# Activer sur un modèle
php Core/Database/Scripts/enable_author_tracking.php Modules/YourModule/Models/YourModel.php

# Voir la documentation
cat Core/Database/AUTHOR_TRACKING.md
```

---

**Vous êtes prêt ! 🚀**
