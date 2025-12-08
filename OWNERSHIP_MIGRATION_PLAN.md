# Plan de Migration : Contrôle d'Accès Basé sur la Propriété

Ce document détaille le plan de migration pour ajouter le contrôle d'accès basé sur la propriété aux modules existants.

## ✅ Composants Créés

### 1. Classes de Base
- ✅ `Core/Authorization/OwnershipPolicy.php` - Logique d'autorisation
- ✅ `Core/Authorization/Traits/AuthorizesOwnership.php` - Trait pour controllers
- ✅ `Core/Database/Traits/HasAuthor.php` - Trait pour modèles (déjà existant)

### 2. Documentation
- ✅ `OWNERSHIP_AUTHORIZATION_GUIDE.md` - Guide complet d'utilisation
- ✅ `OWNERSHIP_MIGRATION_PLAN.md` - Ce fichier

### 3. Exemples
- ✅ `Modules/SmsCore/Controllers/SmsCampaignControllerWithOwnership.php` - Exemple complet

### 4. Scripts
- ✅ `scripts/add_ownership_to_controllers.php` - Script d'aide à la migration

## 📋 Modules à Migrer

### Priority 1 : Modules SMS (Critique)

#### 1.1 SmsCore Module

**Controllers à migrer** :
- ✅ **SmsCampaignController** - Exemple créé
- 🔄 **SmsController** (send, bulk, history)
- 🔄 **SmsPricingController**
- 🔄 **SenderNameController**
- 🔄 **DashboardController**

**Modèles à vérifier** :
- SmsCampaign - Vérifie colonne `user_id`
- SmsMessage - Vérifie colonne `user_id`
- SmsQueue - Vérifie colonne `user_id`
- SenderName - Vérifie colonne `created_by`

**Actions** :
```bash
# 1. Vérifier les colonnes dans la base de données
mysql> SHOW COLUMNS FROM sms_campaigns LIKE '%user_id%';
mysql> SHOW COLUMNS FROM sms_campaigns LIKE '%created_by%';

# 2. Ajouter les colonnes si nécessaire (voir migrations ci-dessous)

# 3. Migrer les controllers
php scripts/add_ownership_to_controllers.php SmsCore SmsCampaignController
php scripts/add_ownership_to_controllers.php SmsCore SmsController
# ... etc
```

#### 1.2 Wallet Module

**Controllers** :
- 🔄 **WalletController** (index, topup, debit, history)

**Modèles** :
- Wallet - Colonne `user_id` (déjà présente)
- WalletTransaction - Colonne `user_id`

**Actions** :
```bash
php scripts/add_ownership_to_controllers.php Wallet WalletController
```

### Priority 2 : Modules de Gestion

#### 2.1 Contacts Module

**Controllers** :
- 🔄 **ContactController** (index, create, edit, delete)
- 🔄 **GroupController**

**Modèles** :
- Contact - Ajouter `created_by`
- ContactGroup - Ajouter `created_by`

#### 2.2 Settings Module

**Note** : Les settings sont globaux, mais on peut vouloir tracker qui a modifié.

**Controllers** :
- 🔄 **SettingsController** - Track `updated_by` uniquement

## 🗄️ Migrations de Base de Données

### Pour SmsCore

```php
<?php
// Modules/SmsCore/Database/Migrations/add_ownership_columns.php

use App\Core\Database\Migration;

class AddOwnershipColumnsToSmsTables extends Migration
{
    public function up()
    {
        // SmsCampaigns - si user_id n'existe pas
        if (!$this->schema->hasColumn('sms_campaigns', 'user_id')) {
            $this->schema->table('sms_campaigns', function($table) {
                $table->integer('user_id')->unsigned()->nullable()->after('id');
                $table->foreign('user_id')->references('id')->on('users');
            });
        }

        // Ajouter created_by, updated_by si pas présents
        if (!$this->schema->hasColumn('sms_campaigns', 'created_by')) {
            $this->schema->table('sms_campaigns', function($table) {
                $table->integer('created_by')->unsigned()->nullable();
                $table->integer('updated_by')->unsigned()->nullable();
                $table->foreign('created_by')->references('id')->on('users');
                $table->foreign('updated_by')->references('id')->on('users');
            });
        }

        // Même chose pour sms_messages
        if (!$this->schema->hasColumn('sms_messages', 'user_id')) {
            $this->schema->table('sms_messages', function($table) {
                $table->integer('user_id')->unsigned()->nullable()->after('id');
                $table->foreign('user_id')->references('id')->on('users');
            });
        }

        // Même chose pour sms_queue
        if (!$this->schema->hasColumn('sms_queue', 'user_id')) {
            $this->schema->table('sms_queue', function($table) {
                $table->integer('user_id')->unsigned()->nullable()->after('id');
                $table->foreign('user_id')->references('id')->on('users');
            });
        }
    }

    public function down()
    {
        // Rollback si nécessaire
        $this->schema->table('sms_campaigns', function($table) {
            $table->dropForeign(['user_id']);
            $table->dropColumn('user_id');
            $table->dropForeign(['created_by']);
            $table->dropColumn('created_by');
            $table->dropForeign(['updated_by']);
            $table->dropColumn('updated_by');
        });

        // Même chose pour les autres tables
    }
}
```

**Exécution** :
```bash
php artisan migrate:run --module=SmsCore
```

### Pour Contacts

```php
<?php
// Modules/Contacts/Database/Migrations/add_ownership_columns.php

use App\Core\Database\Migration;

class AddOwnershipColumnsToContacts extends Migration
{
    public function up()
    {
        if (!$this->schema->hasColumn('contacts', 'created_by')) {
            $this->schema->table('contacts', function($table) {
                $table->integer('created_by')->unsigned()->nullable();
                $table->integer('updated_by')->unsigned()->nullable();
                $table->foreign('created_by')->references('id')->on('users');
                $table->foreign('updated_by')->references('id')->on('users');
            });
        }

        if (!$this->schema->hasColumn('contact_groups', 'created_by')) {
            $this->schema->table('contact_groups', function($table) {
                $table->integer('created_by')->unsigned()->nullable();
                $table->integer('updated_by')->unsigned()->nullable();
                $table->foreign('created_by')->references('id')->on('users');
                $table->foreign('updated_by')->references('id')->on('users');
            });
        }
    }
}
```

## 📝 Checklist de Migration par Controller

Pour chaque controller à migrer :

### Étape 1 : Préparation
- [ ] Vérifier que le modèle a les colonnes nécessaires (user_id ou created_by)
- [ ] Ajouter le trait `HasAuthor` au modèle si besoin
- [ ] Créer et exécuter la migration si colonnes manquantes

### Étape 2 : Modification du Controller
- [ ] Ajouter `use App\Core\Authorization\Traits\AuthorizesOwnership;`
- [ ] Ajouter `use AuthorizesOwnership;` dans la classe
- [ ] Ajouter `$this->initializeOwnershipPolicy();` dans le constructeur

### Étape 3 : Méthode index()
- [ ] Remplacer `Model::all()` ou `Model::get()` par requête avec `scopeByOwnership()`
- [ ] Exemple :
  ```php
  // Avant
  $items = MyModel::all();

  // Après
  $query = MyModel::query();
  $query = $this->scopeByOwnership($query, 'user_id');
  $items = $query->get();
  ```

### Étape 4 : Méthodes show/edit
- [ ] Ajouter vérification après `find()`
- [ ] Exemple :
  ```php
  $item = MyModel::find($id);

  if (!$item) {
      flash('error', 'Ressource introuvable');
      redirect('/admin/items');
      return;
  }

  // Ajouter cette ligne
  $this->authorizeView($item, 'user_id', '/admin/items');
  ```

### Étape 5 : Méthode update
- [ ] Ajouter `$this->authorizeUpdate($item, 'user_id');`

### Étape 6 : Méthode delete
- [ ] Ajouter `$this->authorizeDelete($item, 'user_id');`

### Étape 7 : Vues
- [ ] Passer `isAdmin`, `canEdit`, `canDelete` aux vues
- [ ] Conditionner les boutons d'actions dans les vues

### Étape 8 : Tests
- [ ] Tester en tant qu'admin (doit tout voir)
- [ ] Tester en tant qu'utilisateur A (doit voir seulement ses ressources)
- [ ] Tester en tant qu'utilisateur B (ne doit pas voir les ressources de A)
- [ ] Tester les modifications/suppressions croisées (doivent être bloquées)

## 🎯 Exemple Complet : SmsController

### Avant Migration

```php
<?php

namespace Modules\SmsCore\Controllers;

use Modules\SmsCore\Models\SmsMessage;

class SmsController
{
    public function history()
    {
        $messages = SmsMessage::orderBy('created_at', 'DESC')->get();

        echo view('SmsCore/sms/history', [
            'messages' => $messages
        ]);
    }

    public function details($id)
    {
        $sms = SmsMessage::find($id);

        if (!$sms) {
            flash('error', 'SMS introuvable');
            redirect('/admin/sms/history');
            return;
        }

        echo view('SmsCore/sms/details', [
            'sms' => $sms
        ]);
    }
}
```

### Après Migration

```php
<?php

namespace Modules\SmsCore\Controllers;

use App\Core\Authorization\Traits\AuthorizesOwnership;
use Modules\SmsCore\Models\SmsMessage;

class SmsController
{
    use AuthorizesOwnership;

    public function __construct()
    {
        $this->initializeOwnershipPolicy();
    }

    public function history()
    {
        // Admin voit tous les SMS, utilisateurs voient leurs SMS
        $query = SmsMessage::query()->orderBy('created_at', 'DESC');
        $query = $this->scopeByOwnership($query, 'user_id');
        $messages = $query->get();

        echo view('SmsCore/sms/history', [
            'messages' => $messages,
            'isAdmin' => $this->isAdmin()
        ]);
    }

    public function details($id)
    {
        $sms = SmsMessage::find($id);

        if (!$sms) {
            flash('error', 'SMS introuvable');
            redirect('/admin/sms/history');
            return;
        }

        // Vérifier les droits d'accès
        $this->authorizeView($sms, 'user_id', '/admin/sms/history');

        echo view('SmsCore/sms/details', [
            'sms' => $sms
        ]);
    }
}
```

## 🔧 Scripts d'Aide

### Script SQL pour Vérifier les Colonnes

```sql
-- Vérifier quelles tables ont déjà user_id
SELECT
    TABLE_NAME,
    COLUMN_NAME,
    COLUMN_TYPE
FROM INFORMATION_SCHEMA.COLUMNS
WHERE TABLE_SCHEMA = 'sunuframework'
  AND COLUMN_NAME IN ('user_id', 'created_by', 'updated_by', 'deleted_by')
ORDER BY TABLE_NAME, COLUMN_NAME;
```

### Script PHP pour Lister les Controllers

```php
<?php
// scripts/list_controllers_without_ownership.php

$modulesPath = __DIR__ . '/../Modules';
$controllersWithoutOwnership = [];

foreach (glob($modulesPath . '/*/Controllers/*.php') as $file) {
    $content = file_get_contents($file);

    if (strpos($content, 'AuthorizesOwnership') === false) {
        $controllersWithoutOwnership[] = str_replace($modulesPath . '/', '', $file);
    }
}

echo "Controllers sans contrôle d'accès (" . count($controllersWithoutOwnership) . "):\n\n";
foreach ($controllersWithoutOwnership as $controller) {
    echo "- $controller\n";
}
```

## 📊 Suivi de Migration

### SmsCore Module
- [x] SmsCampaignController (exemple créé)
- [ ] SmsController
- [ ] SmsPricingController
- [ ] SenderNameController
- [ ] DashboardController

### Wallet Module
- [ ] WalletController

### Contacts Module
- [ ] ContactController
- [ ] GroupController

### Settings Module
- [ ] SettingsController (partiel - tracking uniquement)

## 🚀 Déploiement

### Étapes pour Déployer en Production

1. **Tests en Local**
   ```bash
   # Exécuter tous les tests
   php vendor/bin/phpunit tests/Authorization/
   ```

2. **Sauvegarde Base de Données**
   ```bash
   mysqldump -u user -p sunuframework > backup_$(date +%Y%m%d).sql
   ```

3. **Exécuter les Migrations**
   ```bash
   php artisan migrate:run --module=SmsCore
   php artisan migrate:run --module=Wallet
   php artisan migrate:run --module=Contacts
   ```

4. **Déployer le Code**
   ```bash
   git add .
   git commit -m "feat: Add ownership-based authorization system"
   git push origin main
   ```

5. **Sur le Serveur**
   ```bash
   cd /path/to/project
   git pull origin main
   php artisan migrate:run
   rm -rf storage/cache/*
   ```

6. **Tests Post-Déploiement**
   - Connectez-vous en tant qu'admin → Vérifier accès complet
   - Connectez-vous en tant qu'utilisateur → Vérifier accès limité
   - Essayer de modifier une ressource d'un autre utilisateur → Doit être bloqué

## 📚 Ressources

- **Guide Complet** : `OWNERSHIP_AUTHORIZATION_GUIDE.md`
- **Exemple** : `Modules/SmsCore/Controllers/SmsCampaignControllerWithOwnership.php`
- **Policy** : `Core/Authorization/OwnershipPolicy.php`
- **Trait** : `Core/Authorization/Traits/AuthorizesOwnership.php`

## ❓ FAQ

**Q: Que se passe-t-il si un utilisateur essaie d'accéder à une ressource d'un autre utilisateur ?**
R: Il sera automatiquement redirigé avec un message d'erreur "Vous n'avez pas l'autorisation..."

**Q: Les admins peuvent-ils toujours tout voir ?**
R: Oui, les utilisateurs avec le rôle `admin` ou `super_admin` ont accès à toutes les ressources.

**Q: Comment changer le champ de propriété ?**
R: Utilisez le deuxième paramètre : `$this->scopeByOwnership($query, 'owner_id')`

**Q: Puis-je avoir une logique personnalisée ?**
R: Oui, vous pouvez étendre `OwnershipPolicy` ou implémenter votre propre logique dans les controllers.

## ✅ Conclusion

Ce système fournit :
- ✅ Sécurité renforcée (isolation des données)
- ✅ Conformité (RGPD, data privacy)
- ✅ Facilité d'utilisation (trait simple)
- ✅ Flexibilité (personnalisable)
- ✅ Traçabilité (qui a créé/modifié/supprimé)

La migration est progressive et peut se faire module par module.
