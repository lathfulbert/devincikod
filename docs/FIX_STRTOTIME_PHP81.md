# Fix : Erreur strtotime() avec NULL (PHP 8.1+)

## Problème Rencontré

**Erreur** :
```
ErrorException: strtotime(): Passing null to parameter #1 ($datetime) of type string is deprecated
File: storage/cache/views/491d4dc824006ac58359f95c40c045d2.php:76
```

**Cause** : À partir de PHP 8.1, `strtotime()` n'accepte plus `NULL` comme paramètre. Cela génère une erreur de dépréciation qui peut casser l'application si le mode strict est activé.

## Fichiers Corrigés

### 1. Modules/Wallet/Views/wallet/index.php (ligne 76)

**Avant** :
```php
<td><?= $wallet['created_at'] ? date('d/m/Y H:i', strtotime($wallet['created_at'])) : '-' ?></td>
```

**Problème** : `$wallet['created_at']` peut être `NULL`, et même si la condition `? :` vérifie la valeur, PHP 8.1 évalue quand même `strtotime(NULL)`.

**Après** :
```php
<td><?= !empty($wallet['created_at']) ? date('d/m/Y H:i', strtotime($wallet['created_at'])) : '-' ?></td>
```

**Solution** : Utiliser `!empty()` au lieu de la vérification booléenne simple. Cela garantit que la valeur n'est ni `NULL`, ni `false`, ni une chaîne vide.

### 2. Modules/Wallet/Views/wallet/history.php (ligne 78)

**Avant** :
```php
<td><?= date('M d, Y H:i', strtotime($trx['created_at'])) ?></td>
```

**Problème** : Aucune vérification, `$trx['created_at']` pourrait être `NULL`.

**Après** :
```php
<td><?= !empty($trx['created_at']) ? date('M d, Y H:i', strtotime($trx['created_at'])) : '-' ?></td>
```

**Solution** : Ajouter une vérification `!empty()` avec un fallback.

## Bonnes Pratiques pour PHP 8.1+

### ❌ À ÉVITER
```php
// Mauvais : pas de vérification
date('Y-m-d', strtotime($date));

// Mauvais : vérification booléenne simple
$date ? date('Y-m-d', strtotime($date)) : '-';

// Mauvais : isset() ne suffit pas (n'empêche pas les chaînes vides)
isset($date) ? date('Y-m-d', strtotime($date)) : '-';
```

### ✅ RECOMMANDÉ
```php
// Bon : utiliser !empty()
!empty($date) ? date('Y-m-d', strtotime($date)) : '-';

// Bon : vérification explicite
($date !== null && $date !== '') ? date('Y-m-d', strtotime($date)) : '-';

// Bon : coalescence nulle avec vérification
$formattedDate = !empty($date) ? date('Y-m-d', strtotime($date)) : 'N/A';

// Très bon : utiliser DateTime (PHP moderne)
try {
    $dateTime = new DateTime($date);
    echo $dateTime->format('d/m/Y H:i');
} catch (Exception $e) {
    echo '-';
}
```

## Script de Détection

Pour trouver d'autres occurrences potentiellement problématiques :

```bash
# Chercher tous les strtotime sans vérification
grep -rn "strtotime(\$" --include="*.php" . | grep -v "!empty\|isset\|??"

# Chercher dans les vues uniquement
grep -rn "strtotime(\$" --include="*.php" */Views/ */views/
```

## Actions Effectuées

1. ✅ Corrigé `Modules/Wallet/Views/wallet/index.php:76`
2. ✅ Corrigé `Modules/Wallet/Views/wallet/history.php:78`
3. ✅ Supprimé le cache de vue : `storage/cache/views/491d4dc824006ac58359f95c40c045d2.php`
4. ✅ Vérifié qu'il n'y a pas d'autres occurrences problématiques

## Test

Pour tester la correction :

1. **En local** :
   ```bash
   # Vider le cache
   rm -rf storage/cache/views/*

   # Accéder à la page wallet
   # http://localhost/admin/wallet
   ```

2. **En ligne** :
   ```bash
   # Sur le serveur
   cd /home2/ticafzxr/sms.ticafrique.ci
   git pull origin main
   rm -rf storage/cache/views/*
   ```

3. **Vérifier les logs** :
   ```bash
   tail -f storage/logs/app.log
   ```

## Compatibilité PHP

| Version PHP | Comportement |
|------------|--------------|
| PHP 7.4 et inférieur | ⚠️ Accepte `NULL` mais retourne `false` |
| PHP 8.0 | ⚠️ Accepte `NULL` avec warning |
| PHP 8.1+ | ❌ **Erreur de dépréciation** |
| PHP 9.0 (futur) | ❌ **Erreur fatale** (prévu) |

## Recommandations Générales

### 1. Vérification Systématique

Toujours vérifier les dates avant d'utiliser `strtotime()` :

```php
// Pattern recommandé
<?= !empty($date) ? date('d/m/Y', strtotime($date)) : '-' ?>
```

### 2. Utiliser DateTime

Pour du code plus robuste, utilisez `DateTime` :

```php
<?php
function formatDate($date, $format = 'd/m/Y H:i') {
    if (empty($date)) {
        return '-';
    }

    try {
        $dateTime = new DateTime($date);
        return $dateTime->format($format);
    } catch (Exception $e) {
        return '-';
    }
}
?>

<!-- Usage -->
<td><?= formatDate($wallet['created_at']) ?></td>
```

### 3. Helper Global (Optionnel)

Créer un helper dans `Core/Support/helpers.php` :

```php
if (!function_exists('format_date')) {
    /**
     * Format a date safely
     *
     * @param string|null $date
     * @param string $format
     * @param string $default
     * @return string
     */
    function format_date(?string $date, string $format = 'd/m/Y H:i', string $default = '-'): string
    {
        if (empty($date)) {
            return $default;
        }

        try {
            $dateTime = new DateTime($date);
            return $dateTime->format($format);
        } catch (Exception $e) {
            return $default;
        }
    }
}
```

Usage :
```php
<td><?= format_date($wallet['created_at']) ?></td>
<td><?= format_date($trx['created_at'], 'M d, Y H:i') ?></td>
```

## Prévention Future

### Linter PHP

Ajouter à votre CI/CD ou pre-commit hook :

```bash
# PHPStan
phpstan analyse --level 8 Modules/

# Psalm
psalm --show-info=true

# PHP_CodeSniffer
phpcs --standard=PSR12 Modules/
```

### Tests Automatisés

```php
// Test unitaire
public function testDateFormatting()
{
    $this->assertEquals('-', format_date(null));
    $this->assertEquals('-', format_date(''));
    $this->assertNotEquals('-', format_date('2024-01-01'));
}
```

## Résumé

✅ **Problème résolu** : Toutes les occurrences de `strtotime()` avec valeurs potentiellement `NULL` ont été corrigées
✅ **Compatible PHP 8.1+** : Le code ne génère plus d'erreurs de dépréciation
✅ **Robuste** : Gestion des cas limites avec fallback approprié

⚠️ **À faire** : Envisager de créer un helper `format_date()` global pour uniformiser le formatage des dates dans toute l'application.
