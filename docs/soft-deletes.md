# Trait SoftDeletes - Documentation

## Vue d'ensemble

Le trait `SoftDeletes` permet de "supprimer doucement" les enregistrements en les marquant comme supprimés plutôt que de les supprimer définitivement de la base de données.

## Migration requise

Pour utiliser le soft delete, votre table doit avoir une colonne `deleted_at` :

```php
use App\Core\Database\Schema\Schema;
use App\Core\Database\Schema\Blueprint;

Schema::create('users', function (Blueprint $table) {
    $table->id();
    $table->string('name');
    $table->string('email')->unique();
    $table->timestamp('deleted_at')->nullable();  // ← Requis pour soft delete
    $table->timestamps();
});
```

## Utilisation

### 1. Ajouter le trait au Model

```php
<?php

namespace App\Models;

use App\Core\Database\Model;
use App\Core\Database\Traits\SoftDeletes;

class User extends Model
{
    use SoftDeletes;

    protected static string $table = 'users';
}
```

### 2. Méthodes disponibles

#### Soft Delete (suppression douce)

```php
$user = User::find(1);
$user->delete();  // Marque comme supprimé (deleted_at = now())
```

#### Force Delete (suppression définitive)

```php
$user = User::find(1);
$user->forceDelete();  // Supprime réellement de la BDD
```

#### Restore (restauration)

```php
$user = User::withTrashed()->find(1);
$user->restore();  // Restaure l'enregistrement (deleted_at = null)
```

#### Vérifier si supprimé

```php
$user = User::find(1);
if ($user->trashed()) {
    echo "L'utilisateur est supprimé";
}
```

### 3. Récupération des données

#### Par défaut (exclut les supprimés)

```php
$users = User::all();  // Uniquement les utilisateurs actifs
$user = User::find(1); // null si supprimé
```

#### Inclure les supprimés

```php
$users = User::withTrashed();  // Tous les utilisateurs
```

#### Uniquement les supprimés

```php
$users = User::onlyTrashed();  // Seulement les supprimés
```

## Exemple complet

```php
use App\Models\User;

// Créer un utilisateur
$user = new User();
$user->name = "John Doe";
$user->email = "john@example.com";
$user->save();

// Soft delete
$user->delete();

// L'utilisateur n'apparaît plus dans les requêtes normales
User::find($user->id);  // null
User::all();            // N'inclut pas cet utilisateur

// Mais on peut le retrouver
$deletedUser = User::withTrashed()->find($user->id);

// Restaurer
$deletedUser->restore();

// Maintenant il réapparaît
User::find($user->id);  // Retourne l'utilisateur

// Suppression définitive
$user->forceDelete();  // Supprimé de la BDD pour toujours
```

## Avantages

✅ **Récupération facile** : Les données supprimées par erreur peuvent être restaurées
✅ **Audit** : Garder un historique des suppressions
✅ **Conformité** : RGPD et autres réglementations nécessitant une rétention de données
✅ **Relations** : Préserver l'intégrité référentielle temporairement

## Notes importantes

⚠️ **Migration** : N'oubliez pas d'ajouter `deleted_at` à vos tables
⚠️ **Performances** : Les requêtes ajoutent une condition `WHERE deleted_at IS NULL`
⚠️ **Stockage** : Les enregistrements supprimés restent en BDD (pensez au nettoyage périodique avec `forceDelete()`)

## Nettoyage périodique

Pour supprimer définitivement les enregistrements soft-deleted après un certain temps :

```php
// Supprimer les utilisateurs supprimés il y a plus de 30 jours
$thirtyDaysAgo = date('Y-m-d H:i:s', strtotime('-30 days'));

$users = User::onlyTrashed();
foreach ($users as $user) {
    if ($user->deleted_at < $thirtyDaysAgo) {
        $user->forceDelete();
    }
}
```
