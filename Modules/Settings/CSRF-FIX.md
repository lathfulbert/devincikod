# Correction CSRF - Module Settings

## Problème
Erreur lors de la soumission des formulaires : **"CSRF token validation failed. Votre session a peut-être expiré."**

## Cause
Les formulaires n'incluaient pas le token CSRF requis par le middleware de sécurité.

## Solution Appliquée

### 1. Ajout des Tokens CSRF dans les Formulaires

Ajouté `<?= csrf_field() ?>` dans tous les formulaires de [Views/index.php](Views/index.php) :

```php
<!-- Site Settings -->
<form action="<?= url('admin/settings/site/update') ?>" method="POST">
    <?= csrf_field() ?>
    <!-- champs du formulaire -->
</form>

<!-- Theme Settings -->
<form action="<?= url('admin/settings/theme/update') ?>" method="POST">
    <?= csrf_field() ?>
    <!-- champs du formulaire -->
</form>

<!-- API Settings -->
<form action="<?= url('admin/settings/api/update') ?>" method="POST">
    <?= csrf_field() ?>
    <!-- champs du formulaire -->
</form>

<!-- Mail Settings -->
<form action="<?= url('admin/settings/mail/update') ?>" method="POST">
    <?= csrf_field() ?>
    <!-- champs du formulaire -->
</form>
```

### 2. Correction des URLs de Formulaire

Les formulaires pointaient vers des URLs incorrectes. Corrigé pour utiliser les routes `/update` :

**Avant:**
```php
<form action="<?= url('admin/settings') ?>" method="POST">
```

**Après:**
```php
<form action="<?= url('admin/settings/site/update') ?>" method="POST">
```

### 3. Redirection après Soumission

Modifié les contrôleurs pour rediriger vers `/admin/settings` au lieu des pages individuelles :

**Fichiers modifiés:**
- [SiteSettingsController.php:71](Controllers/SiteSettingsController.php#L71)
- [ThemeSettingsController.php:50](Controllers/ThemeSettingsController.php#L50)
- [ApiSettingsController.php:47](Controllers/ApiSettingsController.php#L47)
- [MailSettingsController.php:49](Controllers/MailSettingsController.php#L49)

```php
// Avant
return $app->redirect('/admin/settings/site');

// Après
return $app->redirect('/admin/settings');
```

### 4. Affichage des Messages Flash

Ajouté l'affichage des messages de succès/erreur dans [Views/index.php:28-40](Views/index.php#L28-L40) :

```php
<?php if (isset($_SESSION['flash_success'])): ?>
<div class="alert alert-success alert-dismissible fade show" role="alert">
    <i data-feather="check-circle"></i> <?= htmlspecialchars($_SESSION['flash_success']) ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php unset($_SESSION['flash_success']); endif; ?>

<?php if (isset($_SESSION['flash_error'])): ?>
<div class="alert alert-danger alert-dismissible fade show" role="alert">
    <i data-feather="alert-circle"></i> <?= htmlspecialchars($_SESSION['flash_error']) ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php unset($_SESSION['flash_error']); endif; ?>
```

## Comment fonctionne CSRF dans SunuFramework

### 1. Génération du Token
La classe `App\Core\Security\CSRF` génère un token unique par session :

```php
$token = bin2hex(random_bytes(32));
$_SESSION['_csrf_token'] = $token;
```

### 2. Insertion dans les Formulaires
La fonction helper `csrf_field()` génère un champ caché :

```php
function csrf_field(): string {
    return '<input type="hidden" name="_csrf_token" value="' . csrf_token() . '">';
}
```

### 3. Validation
Le middleware `CSRFMiddleware` valide le token lors des requêtes POST/PUT/PATCH/DELETE :

```php
public function handle($request): bool {
    if (in_array($request->method(), ['POST', 'PUT', 'PATCH', 'DELETE'])) {
        $token = $request->post('_csrf_token');

        if (!CSRF::getInstance()->validateToken($token)) {
            throw new Exception('CSRF token validation failed');
        }
    }
    return true;
}
```

## Routes Concernées

| Méthode | Route | Contrôleur | Action |
|---------|-------|------------|--------|
| POST | `/admin/settings/site/update` | SiteSettingsController | update() |
| POST | `/admin/settings/theme/update` | ThemeSettingsController | update() |
| POST | `/admin/settings/api/update` | ApiSettingsController | update() |
| POST | `/admin/settings/mail/update` | MailSettingsController | update() |

## Test

Pour tester la protection CSRF :

1. **Test Normal (avec token):**
   ```
   Accéder à http://localhost/admin/settings
   Modifier un paramètre
   Cliquer sur "Enregistrer"
   ✅ Devrait afficher: "Paramètres mis à jour avec succès"
   ```

2. **Test Sans Token (devrait échouer):**
   ```bash
   curl -X POST http://localhost/admin/settings/site/update \
        -d "site_name=Test" \
        -H "Cookie: PHPSESSID=..."

   ❌ Devrait retourner: "CSRF token validation failed"
   ```

## Sécurité

Le système CSRF protège contre:
- ✅ Les attaques CSRF (Cross-Site Request Forgery)
- ✅ Les requêtes non autorisées depuis des sites tiers
- ✅ Les requêtes forgées

Le token est:
- 🔒 Unique par session
- 🔒 Stocké en session serveur
- 🔒 Validé avec `hash_equals()` (protection timing attacks)
- 🔒 Régénéré après login/logout

## Fichiers Modifiés

1. **Modules/Settings/Views/index.php**
   - Ajout de `csrf_field()` dans 4 formulaires
   - Correction des URLs d'action
   - Ajout de l'affichage des messages flash

2. **Modules/Settings/Controllers/SiteSettingsController.php**
   - Redirection vers `/admin/settings`

3. **Modules/Settings/Controllers/ThemeSettingsController.php**
   - Redirection vers `/admin/settings`

4. **Modules/Settings/Controllers/ApiSettingsController.php**
   - Redirection vers `/admin/settings`

5. **Modules/Settings/Controllers/MailSettingsController.php**
   - Redirection vers `/admin/settings`

## Prochaines Étapes

- ✅ Protection CSRF activée
- ⏳ Ajouter la validation des données côté serveur
- ⏳ Implémenter la sanitization des entrées
- ⏳ Ajouter des tests automatisés pour CSRF

---

**Date**: 2025-11-23
**Statut**: ✅ Résolu
