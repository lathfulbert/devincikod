# 🔧 Correction Requise : helpers.php

**Problème**: Le fichier `Core/Support/helpers.php` est corrompu aux lignes 263-280.

**Erreur**: La fonction `can()` n'a pas de corps et `current_url()` est mal placée.

---

## 📋 Solution Manuelle

### Étape 1: Ouvrir le fichier

Ouvrir `Core/Support/helpers.php` dans votre éditeur.

### Étape 2: Localiser les lignes corrompues

Chercher la ligne **263** qui commence par `if (!function_exists('can')) {`

### Étape 3: Supprimer les lignes corrompues

Supprimer les lignes **263 à 280** (incluses).

### Étape 4: Copier le code correct

Copier le code depuis le fichier `CORRECTION_helpers_can_function.php` et le coller à la place.

---

## ✅ Code Correct

```php
if (!function_exists('can')) {
    /**
     * Check if the current user has a given permission.
     * Supports both array and object user types.
     *
     * @param string $permission Permission name
     * @param mixed $model Optional model instance
     * @return bool
     */
    function can(string $permission, $model = null): bool
    {
        // Check if auth system exists
        if (!function_exists('auth')) {
            return false;
        }

        $user = auth()->user();

        if (!$user) {
            return false;
        }

        // If user is an object with can() method, use it
        if (is_object($user) && method_exists($user, 'can')) {
            return $user->can($permission, $model);
        }

        // If user is an object with hasPermission() method, use it
        if (is_object($user) && method_exists($user, 'hasPermission')) {
            return $user->hasPermission($permission);
        }

        // If user is an object, check if admin
        if (is_object($user) && method_exists($user, 'isAdmin') && $user->isAdmin()) {
            return true;
        }

        // If user is an array, check role
        if (is_array($user)) {
            // Check if user is admin (array format)
            if (isset($user['role']) && $user['role'] === 'admin') {
                return true;
            }

            // Check if user has permission in permissions array
            if (isset($user['permissions']) && is_array($user['permissions'])) {
                return in_array($permission, $user['permissions']);
            }
        }

        return false;
    }
}

if (!function_exists('current_url')) {
    /**
     * Get the current URL.
     */
    function current_url(): string
    {
        $protocol = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ? "https" : "http";
        return $protocol . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
    }
}
```

---

## 🎯 Changements Apportés

### 1. Support Array ET Object

La fonction `can()` supporte maintenant **les deux formats** :

**Format Array** (actuel dans Auth):

```php
$user = [
    'id' => 1,
    'name' => 'John',
    'role' => 'admin',
    'permissions' => ['edit-posts', 'delete-posts']
];
```

**Format Object** (futur):

```php
$user = new User();
$user->can('edit-posts');
```

### 2. Vérifications Sécurisées

- `is_object($user)` avant d'appeler `method_exists()`
- `is_array($user)` avant d'accéder aux clés
- `isset()` pour éviter les erreurs

### 3. Logique de Permissions (Array)

Si l'utilisateur est un array :

- Admin a toutes les permissions (`$user['role'] === 'admin'`)
- Vérification dans `$user['permissions']` array

---

## ✅ Vérification

Après correction, exécuter :

```bash
php -l Core/Support/helpers.php
```

Résultat attendu :

```
No syntax errors detected
```

---

## 📝 Alternative : Utiliser Git

Si vous avez Git :

```bash
# Voir les changements
git diff Core/Support/helpers.php

# Restaurer depuis le dernier commit valide
git checkout HEAD -- Core/Support/helpers.php

# Puis réappliquer les modifications manuellement
```

---

**Une fois corrigé, le système d'autorisation fonctionnera parfaitement avec les arrays retournés par `auth()->user()` !**
