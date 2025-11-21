# I18n - Guide d'Utilisation Rapide

## 🎯 Utilisation dans les Templates

### Traduction Simple

```php
<!-- Dans vos vues templates/admin/dashboard.php -->
<h1><?= __t('dashboard.title') ?></h1>
<p><?= __t('dashboard.welcome') ?></p>

<!-- Résultat en français: -->
<!-- <h1>Tableau de bord</h1> -->
<!-- <p>Bienvenue sur votre tableau de bord</p> -->
```

### Traduction avec Paramètres

```php
<!-- Dans templates/admin/users/show.php -->
<h2><?= __t('auth.welcome', ['name' => $user->username]) ?></h2>

<!-- Résultat: -->
<!-- <h2>Bienvenue, Jean</h2> -->
```

### Pluralisation

```php
<!-- Afficher le nombre d'utilisateurs -->
<p><?= trans_choice('users.count', $totalUsers, ['count' => $totalUsers]) ?></p>

<!-- Si $totalUsers = 1: "1 utilisateur" -->
<!-- Si $totalUsers = 5: "5 utilisateurs" -->
```

## 🎮 Dans les Contrôleurs

```php
<?php
namespace Modules\Users\Controllers;

class UserController
{
    public function store()
    {
        // ... logique de création

        // Flash message traduit
        flash('success', __t('messages.saved'));

        redirect('/users');
    }

    public function delete($id)
    {
        // ... logique de suppression

        flash('success', __t('messages.deleted'));
        redirect('/users');
    }
}
```

## 🌍 Changer la Langue

### Via URL

```php
<!-- Lien pour changer de langue -->
<a href="?lang=en">English</a>
<a href="?lang=fr">Français</a>
<a href="?lang=ar">العربية</a>
```

### Via Code

```php
// Dans un contrôleur
set_locale('en');

// Rediriger après changement
redirect($_SERVER['REQUEST_URI']);
```

### Sélecteur de Langue

```php
<!-- templates/admin/components/language-selector.php -->
<div class="dropdown">
    <button class="btn btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
        <i data-feather="globe"></i> <?= strtoupper(app_locale()) ?>
    </button>
    <ul class="dropdown-menu">
        <?php foreach (supported_locales() as $locale): ?>
            <li>
                <a class="dropdown-item" href="?lang=<?= $locale ?>">
                    <?= strtoupper($locale) ?>
                </a>
            </li>
        <?php endforeach; ?>
    </ul>
</div>
```

## 📝 Ajouter des Traductions

### Dans le Core

Éditer `languages/fr.json`:

```json
{
  "products": {
    "title": "Produits",
    "create": "Nouveau produit",
    "edit": "Modifier le produit",
    "delete_confirm": "Supprimer ce produit?"
  }
}
```

### Dans un Module

Créer `Modules/Products/languages/fr.json`:

```json
{
  "products": {
    "price": "Prix",
    "stock": "Stock",
    "category": "Catégorie"
  }
}
```

### Via le Back-office

1. Aller sur `/admin/i18n`
2. Cliquer "Nouvelle Traduction"
3. Remplir:
   - Clé: `products.new_key`
   - Traduction: `Ma nouvelle traduction`
4. Enregistrer

## 🔧 Commandes CLI Utiles

```bash
# Lister toutes les traductions françaises
php sunu i18n:list fr

# Trouver les traductions manquantes en arabe
php sunu i18n:missing ar

# Sauvegarder les traductions
php sunu i18n:export fr backup_fr.json

# Vider le cache
php sunu i18n:cache:clear
```

## ⚙️ Configuration

Éditer `config/app.php`:

```php
// Langue par défaut
'locale' => 'fr',

// Langue de secours si traduction manquante
'fallback_locale' => 'en',

// Langues supportées
'supported_locales' => ['fr', 'en', 'ar', 'es'],
```

## 💡 Astuces

### 1. Utiliser des Variables

```json
{
  "email": {
    "subject": "Bonjour :name, votre commande :order_id est confirmée"
  }
}
```

```php
__t('email.subject', [
    'name' => $user->name,
    'order_id' => $order->id
]);
```

### 2. Pluralisation Avancée

```json
{
  "items": {
    "cart": "Aucun article|Un article|:count articles"
  }
}
```

```php
trans_choice('items.cart', $count, ['count' => $count]);
// 0 items: "Aucun article"
// 1 item: "Un article"
// 5 items: "5 articles"
```

### 3. Fallback Automatique

Si une clé n'existe pas dans la langue actuelle, le système utilise automatiquement la langue de secours (fallback_locale).

```php
// Si 'products.new_feature' n'existe qu'en anglais
set_locale('fr');
echo __t('products.new_feature');
// Affichera la version anglaise automatiquement
```

## 📁 Structure des Fichiers

```
languages/
├── fr.json          ← Traductions françaises (core)
├── en.json          ← Traductions anglaises (core)
└── ar.json          ← Traductions arabes (core)

Modules/Blog/languages/
├── fr.json          ← Traductions françaises du module
└── en.json          ← Traductions anglaises du module

storage/i18n/overrides/
├── fr.json          ← Overrides français (backoffice)
└── en.json          ← Overrides anglais (backoffice)
```

## ✅ Checklist pour Nouveau Module

1. Créer `Modules/MonModule/languages/fr.json`
2. Ajouter vos traductions
3. Utiliser `__t('monmodule.key')` dans le code
4. Tester avec `php sunu i18n:list fr`
5. Synchroniser le cache: `php sunu i18n:sync`

---

**Documentation complète:** Voir [README.md](file:///C:/laragon/www/sunuframework2/Modules/I18n/README.md) et [walkthrough.md](file:///C:/Users/akpa.lath/.gemini/antigravity/brain/4e43105f-11e6-4b16-ac66-601993fd8250/walkthrough.md)
