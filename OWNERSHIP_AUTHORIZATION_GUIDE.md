# Guide : Contrôle d'Accès Basé sur la Propriété

Ce guide explique comment implémenter un système d'autorisation où :
- **Les admins** ont accès à toutes les ressources
- **Les autres utilisateurs** ne voient que ce qu'ils ont créé/modifié

## 📋 Table des Matières

1. [Architecture du Système](#architecture-du-système)
2. [Composants Créés](#composants-créés)
3. [Utilisation dans les Controllers](#utilisation-dans-les-controllers)
4. [Utilisation dans les Modèles](#utilisation-dans-les-modèles)
5. [Utilisation dans les Vues](#utilisation-dans-les-vues)
6. [Migration des Controllers Existants](#migration-des-controllers-existants)
7. [Exemples Concrets](#exemples-concrets)

## Architecture du Système

### Principe

```
┌─────────────────────────────────────────────────────────────┐
│                     Requête Utilisateur                      │
└───────────────────────────┬─────────────────────────────────┘
                            │
                            ▼
                ┌───────────────────────┐
                │   AuthorizesOwnership │ (Trait)
                │   dans Controller     │
                └───────────┬───────────┘
                            │
                            ▼
                ┌───────────────────────┐
                │   OwnershipPolicy     │
                │   Vérifie:            │
                │   - Est admin ?       │
                │   - Est propriétaire? │
                └───────────┬───────────┘
                            │
                ┌───────────┴───────────┐
                │                       │
                ▼                       ▼
        ┌─────────────┐         ┌─────────────┐
        │   Admin     │         │   User      │
        │ Accès: TOUT │         │ Accès: SIEN │
        └─────────────┘         └─────────────┘
```

## Composants Créés

### 1. **OwnershipPolicy** (`Core/Authorization/OwnershipPolicy.php`)

La classe de base qui gère toute la logique d'autorisation.

**Méthodes principales** :
- `isAdmin($user)` - Vérifie si l'utilisateur est admin
- `isOwner($user, $resource, $ownerField)` - Vérifie la propriété
- `view($user, $resource)` - Peut voir la ressource ?
- `update($user, $resource)` - Peut modifier ?
- `delete($user, $resource)` - Peut supprimer ?
- `viewAll($user)` - Peut voir tout ?

### 2. **AuthorizesOwnership** (`Core/Authorization/Traits/AuthorizesOwnership.php`)

Un trait à utiliser dans vos controllers pour simplifier les vérifications.

**Méthodes principales** :
- `isAdmin()` - L'utilisateur actuel est-il admin ?
- `canView($resource)` - Peut voir cette ressource ?
- `canUpdate($resource)` - Peut modifier ?
- `canDelete($resource)` - Peut supprimer ?
- `authorizeView($resource)` - Autorise ou redirige
- `authorizeUpdate($resource)` - Autorise ou redirige
- `authorizeDelete($resource)` - Autorise ou redirige
- `scopeByOwnership($query)` - Filtre une requête par propriétaire
- `filterByOwnership($array)` - Filtre un tableau

### 3. **HasAuthor** (`Core/Database/Traits/HasAuthor.php`)

Un trait pour les modèles qui track automatiquement qui a créé/modifié/supprimé.

**Colonnes nécessaires** :
- `created_by` (int) - ID de celui qui a créé
- `updated_by` (int) - ID de celui qui a modifié
- `deleted_by` (int) - ID de celui qui a supprimé (soft delete)

## Utilisation dans les Controllers

### Étape 1 : Ajouter le Trait

```php
<?php

namespace Modules\MonModule\Controllers;

use App\Core\Authorization\Traits\AuthorizesOwnership;
use Modules\MonModule\Models\MaRessource;

class MaRessourceController
{
    use AuthorizesOwnership;

    public function __construct()
    {
        // Initialiser la policy
        $this->initializeOwnershipPolicy();
    }
}
```

### Étape 2 : Filtrer les Listes

```php
/**
 * Lister les ressources
 * Admin: toutes
 * Autres: seulement les leurs
 */
public function index()
{
    // Méthode 1: Avec QueryBuilder
    $query = MaRessource::query()->orderBy('created_at', 'desc');
    $query = $this->scopeByOwnership($query, 'user_id');
    $ressources = $query->get();

    // Méthode 2: Avec un tableau
    $touteRessources = MaRessource::all();
    $ressources = $this->filterByOwnership($touteRessources, 'user_id');

    echo view('mon-module/index', [
        'ressources' => $ressources,
        'isAdmin' => $this->isAdmin()
    ]);
}
```

### Étape 3 : Autoriser les Actions

```php
/**
 * Voir les détails
 */
public function show()
{
    $id = (int)($_GET['id'] ?? 0);
    $ressource = MaRessource::find($id);

    if (!$ressource) {
        flash('error', 'Ressource introuvable');
        redirect('/admin/ressources');
        return;
    }

    // Vérifier l'autorisation (redirige automatiquement si refusé)
    $this->authorizeView($ressource, 'user_id', '/admin/ressources');

    echo view('mon-module/show', [
        'ressource' => $ressource,
        'canEdit' => $this->canUpdate($ressource, 'user_id'),
        'canDelete' => $this->canDelete($ressource, 'user_id')
    ]);
}

/**
 * Modifier
 */
public function update()
{
    $id = (int)($_POST['id'] ?? 0);
    $ressource = MaRessource::find($id);

    if (!$ressource) {
        flash('error', 'Ressource introuvable');
        redirect('/admin/ressources');
        return;
    }

    // Autoriser la modification
    $this->authorizeUpdate($ressource, 'user_id', '/admin/ressources');

    // Traiter la mise à jour
    $ressource->nom = $_POST['nom'] ?? '';
    $ressource->save();

    flash('success', 'Ressource modifiée avec succès');
    redirect('/admin/ressources');
}

/**
 * Supprimer
 */
public function delete()
{
    $id = (int)($_POST['id'] ?? 0);
    $ressource = MaRessource::find($id);

    if (!$ressource) {
        flash('error', 'Ressource introuvable');
        redirect('/admin/ressources');
        return;
    }

    // Autoriser la suppression
    $this->authorizeDelete($ressource, 'user_id', '/admin/ressources');

    $ressource->delete();

    flash('success', 'Ressource supprimée avec succès');
    redirect('/admin/ressources');
}
```

## Utilisation dans les Modèles

### Ajouter le Trait HasAuthor

```php
<?php

namespace Modules\MonModule\Models;

use App\Core\Database\Model;
use App\Core\Database\Traits\HasAuthor;

class MaRessource extends Model
{
    use HasAuthor;

    protected static string $table = 'ma_table';

    protected array $fillable = [
        'nom',
        'description',
        'user_id',
        // ... autres champs
    ];
}
```

### Migrations : Ajouter les Colonnes

```php
public function up()
{
    $this->schema->create('ma_table', function ($table) {
        $table->id();
        $table->string('nom');
        $table->text('description')->nullable();

        // Propriétaire principal
        $table->integer('user_id')->unsigned();

        // Tracking des auteurs (optionnel mais recommandé)
        $table->integer('created_by')->unsigned()->nullable();
        $table->integer('updated_by')->unsigned()->nullable();
        $table->integer('deleted_by')->unsigned()->nullable();

        $table->timestamps();
        $table->softDeletes();

        // Foreign keys
        $table->foreign('user_id')->references('id')->on('users');
        $table->foreign('created_by')->references('id')->on('users');
        $table->foreign('updated_by')->references('id')->on('users');
    });
}
```

## Utilisation dans les Vues

### Afficher le Créateur

```php
<?php if ($ressource->created_by): ?>
    <p>Créé par : <?= htmlspecialchars($ressource->getCreatorName()) ?></p>
<?php endif; ?>
```

### Boutons Conditionnels

```php
<!-- Bouton modifier (seulement si autorisé) -->
<?php if ($canEdit ?? false): ?>
    <a href="<?= url('/admin/ressources/edit?id=' . $ressource->id) ?>" class="btn btn-primary">
        <i data-feather="edit"></i> Modifier
    </a>
<?php endif; ?>

<!-- Bouton supprimer (seulement si autorisé) -->
<?php if ($canDelete ?? false): ?>
    <form method="POST" action="<?= url('/admin/ressources/delete') ?>" class="d-inline">
        <?= csrf_field() ?>
        <input type="hidden" name="id" value="<?= $ressource->id ?>">
        <button type="submit" class="btn btn-danger" onclick="return confirm('Êtes-vous sûr ?')">
            <i data-feather="trash"></i> Supprimer
        </button>
    </form>
<?php endif; ?>
```

### Badge Admin

```php
<?php if ($isAdmin ?? false): ?>
    <span class="badge badge-success">Mode Admin - Accès complet</span>
<?php else: ?>
    <span class="badge badge-info">Mes ressources uniquement</span>
<?php endif; ?>
```

## Migration des Controllers Existants

### Exemple : SmsCampaignController

**Avant** :
```php
public function index()
{
    $campaigns = SmsCampaign::orderBy('created_at', 'desc')->get();

    echo view('SmsCore/sms/campaigns/index', [
        'campaigns' => $campaigns
    ]);
}
```

**Après** :
```php
use App\Core\Authorization\Traits\AuthorizesOwnership;

class SmsCampaignController
{
    use AuthorizesOwnership;

    public function __construct()
    {
        $this->initializeOwnershipPolicy();
    }

    public function index()
    {
        $query = SmsCampaign::query()->orderBy('created_at', 'desc');

        // Filtre automatique : admin voit tout, autres voient leurs campagnes
        $query = $this->scopeByOwnership($query, 'user_id');

        $campaigns = $query->get();

        echo view('SmsCore/sms/campaigns/index', [
            'campaigns' => $campaigns,
            'isAdmin' => $this->isAdmin()
        ]);
    }

    public function show()
    {
        $id = (int)($_GET['id'] ?? 0);
        $campaign = SmsCampaign::find($id);

        if (!$campaign) {
            flash('error', 'Campagne introuvable');
            redirect('/admin/sms/campaigns');
            return;
        }

        // Vérification automatique des droits
        $this->authorizeView($campaign, 'user_id', '/admin/sms/campaigns');

        echo view('SmsCore/sms/campaigns/show', [
            'campaign' => $campaign,
            'canEdit' => $this->canUpdate($campaign, 'user_id'),
            'canDelete' => $this->canDelete($campaign, 'user_id')
        ]);
    }
}
```

## Exemples Concrets

### 1. Wallet Controller

```php
<?php

namespace Modules\Wallet\Controllers;

use App\Core\Authorization\Traits\AuthorizesOwnership;
use Modules\Wallet\Models\Wallet;

class WalletController
{
    use AuthorizesOwnership;

    public function __construct()
    {
        $this->initializeOwnershipPolicy();
    }

    public function index()
    {
        // Admin voit tous les wallets
        // Utilisateur voit seulement son wallet
        $query = Wallet::query();
        $query = $this->scopeByOwnership($query, 'user_id');
        $wallets = $query->get();

        echo view('wallet/wallet/index', [
            'wallets' => $wallets,
            'isAdmin' => $this->isAdmin()
        ]);
    }

    public function topup()
    {
        $userId = (int)($_POST['user_id'] ?? 0);
        $wallet = Wallet::where('user_id', $userId)->first();

        if (!$wallet) {
            flash('error', 'Wallet introuvable');
            redirect('/admin/wallet');
            return;
        }

        // Seul l'admin peut recharger d'autres wallets
        if (!$this->isAdmin() && !$this->canUpdate($wallet, 'user_id')) {
            flash('error', 'Vous ne pouvez pas recharger ce wallet');
            redirect('/admin/wallet');
            return;
        }

        // Traiter le rechargement
        // ...
    }
}
```

### 2. SMS Controller

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
        // Filtrer les messages par utilisateur
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

### 3. Contacts Controller

```php
<?php

namespace Modules\Contacts\Controllers;

use App\Core\Authorization\Traits\AuthorizesOwnership;
use Modules\Contacts\Models\Contact;

class ContactController
{
    use AuthorizesOwnership;

    public function __construct()
    {
        $this->initializeOwnershipPolicy();
    }

    public function index()
    {
        $query = Contact::query()->orderBy('first_name');
        $query = $this->scopeByOwnership($query, 'created_by');
        $contacts = $query->get();

        echo view('contacts/index', [
            'contacts' => $contacts,
            'isAdmin' => $this->isAdmin()
        ]);
    }
}
```

## Personnalisation

### Changer le Champ de Propriété

Par défaut, le système utilise `user_id`. Si votre table utilise un autre nom :

```php
// Au lieu de 'user_id', utiliser 'owner_id'
$query = $this->scopeByOwnership($query, 'owner_id');

// Ou 'created_by'
$query = $this->scopeByOwnership($query, 'created_by');
```

### Logique Personnalisée

Si vous avez besoin d'une logique plus complexe :

```php
public function index()
{
    if ($this->isAdmin()) {
        // Admin voit tout
        $campaigns = SmsCampaign::all();
    } else {
        $userId = $_SESSION['user']['id'] ?? null;

        // Utilisateur voit ses campagnes + celles de son équipe
        $campaigns = SmsCampaign::query()
            ->where(function($q) use ($userId) {
                $q->where('user_id', $userId)
                  ->orWhere('team_id', function($sub) use ($userId) {
                      // Sous-requête pour l'équipe
                  });
            })
            ->get();
    }

    echo view('campaigns/index', ['campaigns' => $campaigns]);
}
```

## Bonnes Pratiques

### ✅ À FAIRE

1. **Toujours initialiser la policy** dans le constructeur
   ```php
   public function __construct()
   {
       $this->initializeOwnershipPolicy();
   }
   ```

2. **Utiliser `authorizeX()`** pour les actions sensibles
   ```php
   $this->authorizeDelete($resource, 'user_id', $redirectUrl);
   ```

3. **Passer les permissions aux vues**
   ```php
   echo view('show', [
       'resource' => $resource,
       'canEdit' => $this->canUpdate($resource),
       'canDelete' => $this->canDelete($resource)
   ]);
   ```

4. **Ajouter HasAuthor** aux modèles pour le tracking
   ```php
   class MyModel extends Model
   {
       use HasAuthor;
   }
   ```

### ❌ À ÉVITER

1. **Ne pas vérifier les permissions**
   ```php
   // Mauvais
   $resource = Resource::find($id);
   $resource->delete();

   // Bon
   $resource = Resource::find($id);
   $this->authorizeDelete($resource);
   $resource->delete();
   ```

2. **Laisser les données filtrables dans les vues**
   ```php
   // Mauvais
   $allResources = Resource::all();
   // Puis filtrer en JavaScript côté client

   // Bon
   $query = Resource::query();
   $query = $this->scopeByOwnership($query);
   $resources = $query->get();
   ```

3. **Oublier de gérer les cas limites**
   ```php
   // Toujours vérifier que la ressource existe
   if (!$resource) {
       flash('error', 'Ressource introuvable');
       redirect($backUrl);
       return;
   }
   ```

## Tests

### Tester Manuellement

1. **En tant qu'admin** : vérifier que vous voyez tout
2. **En tant qu'utilisateur A** : créer des ressources
3. **En tant qu'utilisateur B** : vérifier que vous ne voyez pas les ressources de A
4. **Essayer de modifier/supprimer** une ressource d'un autre utilisateur (doit être refusé)

### Script de Test

```php
// test_ownership.php

require_once __DIR__ . '/bootstrap.php';

use Modules\SmsCore\Models\SmsCampaign;

// Simuler utilisateur 1
$_SESSION['user'] = ['id' => 1, 'roles' => [['slug' => 'user']]];

$campaign1 = SmsCampaign::create([
    'name' => 'Campagne User 1',
    'user_id' => 1
]);

// Simuler utilisateur 2
$_SESSION['user'] = ['id' => 2, 'roles' => [['slug' => 'user']]];

$campaign2 = SmsCampaign::create([
    'name' => 'Campagne User 2',
    'user_id' => 2
]);

// User 2 ne devrait voir que sa campagne
$controller = new SmsCampaignController();
$query = SmsCampaign::query();
$query = $controller->scopeByOwnership($query, 'user_id');
$campaigns = $query->get();

echo "User 2 voit " . count($campaigns) . " campagne(s)\n";
// Attendu: 1

// Admin devrait tout voir
$_SESSION['user'] = ['id' => 3, 'roles' => [['slug' => 'admin']]];
$query = SmsCampaign::query();
$query = $controller->scopeByOwnership($query, 'user_id');
$campaigns = $query->get();

echo "Admin voit " . count($campaigns) . " campagne(s)\n";
// Attendu: 2
```

## Conclusion

Ce système d'autorisation basé sur la propriété :
- ✅ Est simple à utiliser
- ✅ Est sécurisé par défaut
- ✅ S'intègre facilement dans le code existant
- ✅ Respecte le principe du moindre privilège
- ✅ Track automatiquement les auteurs avec HasAuthor

Pour toute question ou problème, consultez la documentation ou les exemples fournis.
