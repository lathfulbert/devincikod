# 🚀 Quick Start - Author Tracking

## Pour activer le tracking sur un modèle existant

### 1️⃣ Ajoutez le trait au modèle

```php
<?php

namespace Modules\YourModule\Models;

use App\Core\Database\Model;
use App\Core\Database\Traits\HasAuthor;

class YourModel extends Model
{
    use HasAuthor;

    // ... reste du code
}
```

### 2️⃣ Si le modèle utilise Soft Delete

```php
use App\Core\Database\Traits\HasAuthor;
use App\Core\Database\Traits\SoftDeletes;

class YourModel extends Model
{
    use HasAuthor;
    use SoftDeletes;

    // ... reste du code
}
```

### 3️⃣ Exécutez la migration (une seule fois)

```bash
php public/index.php migrate
```

## ✅ C'est tout !

Le système est maintenant actif. Les champs `created_by`, `updated_by`, et `deleted_by` seront automatiquement remplis.

## 📖 Utilisation dans le code

### Afficher le nom du créateur

```php
$item = YourModel::find(1);
echo $item->getCreatorName(); // "John Doe"
```

### Vérifier si créé par l'utilisateur courant

```php
if ($item->isCreatedBy(current_user_id())) {
    echo "Vous avez créé cet élément";
}
```

### Afficher dans une vue

```php
<td><?= e($item->getCreatorName() ?? 'N/A') ?></td>
<td><?= e($item->getUpdaterName() ?? 'N/A') ?></td>
```

## 📚 Documentation complète

Voir [AUTHOR_TRACKING.md](./AUTHOR_TRACKING.md) pour la documentation complète.

## 🎯 Modèles déjà configurés

- ✅ SmsMessage
- ✅ SmsCampaign
- ✅ SenderName
- ✅ Contact
- ✅ ApiKey

## ⚠️ Important

- Les colonnes sont ajoutées automatiquement par la migration
- L'utilisateur doit être connecté pour que le tracking fonctionne
- Ne modifiez jamais manuellement les champs `*_by`
