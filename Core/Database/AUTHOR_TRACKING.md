# Système de Tracking des Auteurs (Author Tracking)

## 📋 Vue d'ensemble

Le système de tracking des auteurs permet de suivre automatiquement qui a créé, modifié ou supprimé un enregistrement dans la base de données. Ce système est intégré à l'ORM et s'active simplement en ajoutant un trait à vos modèles.

## ✨ Fonctionnalités

- **Tracking automatique** : Les champs `created_by`, `updated_by`, et `deleted_by` sont remplis automatiquement
- **Intégration transparente** : Fonctionne avec les opérations CRUD existantes sans modification de code
- **Relations ORM** : Accédez facilement aux informations des utilisateurs auteurs
- **Support Soft Delete** : Compatible avec le trait `SoftDeletes`
- **Méthodes utilitaires** : Méthodes helper pour récupérer les noms des auteurs

---

## 🚀 Utilisation

### 1. Préparer la base de données

Exécutez la migration pour ajouter les colonnes aux tables existantes :

```bash
php public/index.php migrate
```

Cette migration ajoutera automatiquement les colonnes suivantes à vos tables :
- `created_by` INT UNSIGNED NULL
- `updated_by` INT UNSIGNED NULL
- `deleted_by` INT UNSIGNED NULL (si votre modèle utilise SoftDeletes)

### 2. Activer le tracking sur un modèle

Il suffit d'ajouter le trait `HasAuthor` à votre modèle :

```php
<?php

namespace Modules\YourModule\Models;

use App\Core\Database\Model;
use App\Core\Database\Traits\HasAuthor;

class YourModel extends Model
{
    use HasAuthor;

    protected static string $table = 'your_table';

    // ... reste du code
}
```

### 3. Avec Soft Deletes

Si votre modèle utilise le soft delete, ajoutez les deux traits :

```php
<?php

namespace Modules\YourModule\Models;

use App\Core\Database\Model;
use App\Core\Database\Traits\HasAuthor;
use App\Core\Database\Traits\SoftDeletes;

class YourModel extends Model
{
    use HasAuthor;
    use SoftDeletes;

    protected static string $table = 'your_table';

    // ... reste du code
}
```

---

## 📖 Exemples d'utilisation

### Création d'un enregistrement

Les champs `created_by` et `updated_by` sont automatiquement remplis avec l'ID de l'utilisateur connecté :

```php
// L'utilisateur connecté est automatiquement enregistré
$sms = SmsMessage::create([
    'to' => '+221771234567',
    'message' => 'Hello World',
    // created_by et updated_by seront remplis automatiquement
]);
```

### Modification d'un enregistrement

Le champ `updated_by` est automatiquement mis à jour :

```php
$sms = SmsMessage::find(1);
$sms->status = 'sent';
$sms->save(); // updated_by est automatiquement mis à jour
```

### Suppression (Soft Delete)

Le champ `deleted_by` est automatiquement rempli lors d'un soft delete :

```php
$contact = Contact::find(1);
$contact->delete(); // deleted_by est automatiquement rempli
```

### Accéder aux informations des auteurs

#### Via les relations ORM

```php
$sms = SmsMessage::find(1);

// Récupérer l'utilisateur créateur
$creator = $sms->creator()->get();
echo $creator->username;

// Récupérer l'utilisateur qui a modifié
$updater = $sms->updater()->get();
echo $updater->email;

// Pour un modèle avec soft delete
$contact = Contact::find(1);
$deleter = $contact->deleter()->get();
```

#### Via les méthodes helper

```php
$sms = SmsMessage::find(1);

// Obtenir le nom du créateur
echo $sms->getCreatorName(); // "John Doe" ou "john_doe"

// Obtenir le nom du modificateur
echo $sms->getUpdaterName(); // "Jane Smith"

// Obtenir le nom de celui qui a supprimé
echo $sms->getDeleterName(); // "Admin User"
```

#### Vérifications

```php
$sms = SmsMessage::find(1);
$userId = current_user_id();

// Vérifier si créé par l'utilisateur donné
if ($sms->isCreatedBy($userId)) {
    echo "Vous avez créé ce SMS";
}

// Vérifier si modifié par l'utilisateur donné
if ($sms->isUpdatedBy($userId)) {
    echo "Vous avez modifié ce SMS";
}

// Vérifier si supprimé par l'utilisateur donné
if ($sms->isDeletedBy($userId)) {
    echo "Vous avez supprimé ce contact";
}
```

---

## 🔧 Configuration

### Helpers disponibles

Le système fournit des helpers pour récupérer l'utilisateur connecté :

```php
// Récupérer l'utilisateur connecté (objet ou array)
$user = current_user();

// Récupérer l'ID de l'utilisateur connecté
$userId = current_user_id(); // int|null
```

### Personnalisation

Si vous avez besoin de personnaliser la récupération de l'utilisateur connecté, vous pouvez surcharger la méthode `getCurrentUserId()` dans votre modèle :

```php
class YourModel extends Model
{
    use HasAuthor;

    protected function getCurrentUserId(): ?int
    {
        // Votre logique personnalisée
        return $_SESSION['custom_user']['id'] ?? null;
    }
}
```

---

## 📊 Affichage dans les vues

### Exemple dans une table HTML

```php
<table class="table">
    <thead>
        <tr>
            <th>Nom</th>
            <th>Créé par</th>
            <th>Modifié par</th>
            <th>Date création</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($items as $item): ?>
        <tr>
            <td><?= e($item->name) ?></td>
            <td><?= e($item->getCreatorName() ?? 'N/A') ?></td>
            <td><?= e($item->getUpdaterName() ?? 'N/A') ?></td>
            <td><?= e($item->created_at) ?></td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>
```

### Exemple avec relation eager loading (à implémenter)

Pour optimiser les performances et éviter le problème N+1, vous pourriez charger les relations :

```php
// Dans votre contrôleur
$items = YourModel::all();

// Afficher dans la vue
foreach ($items as $item) {
    echo $item->getCreatorName(); // Utilisera la méthode helper
}
```

---

## 🎯 Modèles déjà configurés

Les modèles suivants ont déjà le trait `HasAuthor` activé :

### Module SmsCore
- ✅ `SmsMessage`
- ✅ `SmsCampaign`
- ✅ `SenderName` (avec SoftDeletes)

### Module Contacts
- ✅ `Contact` (avec SoftDeletes)

### Module ApiKeys
- ✅ `ApiKey` (avec SoftDeletes)

Pour activer le tracking sur d'autres modèles, suivez simplement les étapes de la section **Utilisation**.

---

## 🔍 Structure de la base de données

Les colonnes ajoutées par le système :

```sql
-- Colonne pour l'auteur de la création
created_by INT UNSIGNED NULL,

-- Colonne pour l'auteur de la dernière modification
updated_by INT UNSIGNED NULL,

-- Colonne pour l'auteur de la suppression (soft delete uniquement)
deleted_by INT UNSIGNED NULL,

-- Index pour améliorer les performances
KEY idx_table_created_by (created_by),
KEY idx_table_updated_by (updated_by),
KEY idx_table_deleted_by (deleted_by)
```

---

## ⚙️ Fonctionnement interne

Le système utilise des **hooks** dans l'ORM :

1. **beforeCreate()** : Appelé avant l'insertion d'un nouvel enregistrement
   - Remplit `created_by` avec l'ID de l'utilisateur connecté
   - Remplit `updated_by` avec l'ID de l'utilisateur connecté

2. **beforeUpdate()** : Appelé avant la mise à jour d'un enregistrement
   - Met à jour `updated_by` avec l'ID de l'utilisateur connecté

3. **beforeDelete()** : Appelé avant un soft delete
   - Remplit `deleted_by` avec l'ID de l'utilisateur connecté

Ces hooks sont appelés automatiquement par le `Model.php` base lors des opérations :
- `save()` : appelle `beforeCreate()` ou `beforeUpdate()`
- `delete()` : appelle `beforeDelete()` (trait SoftDeletes)

---

## 🛠️ Maintenance

### Ajouter une nouvelle table

1. Ajoutez la table et ses colonnes dans la migration :
   ```php
   // Dans Core/Database/Migrations/001_add_author_tracking_columns.php
   private array $tables = [
       'your_new_table' => ['created_by', 'updated_by', 'deleted_by'],
       // ...
   ];
   ```

2. Re-exécutez la migration :
   ```bash
   php public/index.php migrate
   ```

3. Ajoutez le trait dans le modèle :
   ```php
   use App\Core\Database\Traits\HasAuthor;
   ```

### Rollback de la migration

Si vous devez retirer les colonnes :

```php
// Dans le fichier de migration, utilisez la méthode down()
$migration = require 'Core/Database/Migrations/001_add_author_tracking_columns.php';
$migration->down();
```

---

## 🐛 Dépannage

### Les colonnes ne se remplissent pas automatiquement

**Vérifiez :**
1. Le trait `HasAuthor` est bien ajouté au modèle
2. Un utilisateur est connecté (`$_SESSION['user']['id']` existe)
3. Les colonnes existent dans la table (exécutez la migration)

### Les relations ne fonctionnent pas

**Vérifiez :**
1. Le modèle `User` existe et est correctement configuré
2. Les colonnes `created_by`, `updated_by`, `deleted_by` existent
3. Les valeurs ne sont pas NULL dans la base de données

### Performance dégradée

**Solutions :**
- Utilisez les index (créés automatiquement par la migration)
- Évitez d'appeler `getCreatorName()` dans des boucles larges
- Considérez le chargement eager loading si disponible

---

## 📚 Références

- **Trait HasAuthor** : `Core/Database/Traits/HasAuthor.php`
- **Migration** : `Core/Database/Migrations/001_add_author_tracking_columns.php`
- **Model Base** : `Core/Database/Model.php`
- **Helpers** : `Core/Support/helpers.php` (`current_user()`, `current_user_id()`)

---

## 💡 Bonnes pratiques

1. **Toujours utiliser le trait** sur les modèles qui nécessitent un tracking
2. **Ne pas modifier manuellement** les champs `*_by` dans votre code
3. **Utiliser les méthodes helper** pour afficher les noms des auteurs
4. **Combiner avec SoftDeletes** pour un audit complet
5. **Vérifier la présence d'un utilisateur connecté** avant les opérations critiques

---

## 🎉 Conclusion

Le système de tracking des auteurs est maintenant opérationnel dans votre application. Il fonctionne de manière transparente et automatique, vous permettant de garder un historique complet des actions effectuées sur vos données.

Pour toute question ou problème, consultez ce guide ou contactez l'équipe de développement.
