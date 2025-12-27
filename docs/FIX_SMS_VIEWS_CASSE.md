# Fix : Problème de casse des vues SMS (Linux vs Windows)

## Problème Rencontré

**Symptôme** : Les liens SMS fonctionnent en local (Windows) mais retournent "View SmsCore/sms/* not found" en ligne (Linux).

**Cause** : Incohérence de la casse dans les chemins de vues
- Sur **Windows** (local) : Le système de fichiers est insensible à la casse → `smscore` = `SmsCore`
- Sur **Linux** (serveur) : Le système de fichiers est sensible à la casse → `smscore` ≠ `SmsCore`

## Structure Réelle

```
Modules/SmsCore/         ← Avec majuscules
├── Controllers/
├── Views/
│   ├── sms/
│   ├── providers/
│   └── wallet/
└── ...
```

## Problème dans le Code

Avant la correction, les controllers utilisaient des casses différentes :

```php
// ❌ Incohérent - ne marche que sur Windows
echo view('smscore/sms/dashboard', [...]);  // Minuscules
echo view('SmsCore/providers/index', [...]);  // Majuscules
```

Le système de vues cherche dans `Modules/{ModuleName}/Views/` et sur Linux, `smscore` n'existe pas.

## Solution Appliquée

Tous les appels `view()` ont été standardisés pour utiliser **SmsCore** (avec majuscules) :

```php
// ✅ Correct - marche partout
echo view('SmsCore/sms/dashboard', [...]);
echo view('SmsCore/providers/index', [...]);
```

## Fichiers Corrigés

### Controllers Modifiés

1. **DashboardController.php**
   - `smscore/sms/dashboard` → `SmsCore/sms/dashboard`
   - `smscore/sms/statistics` → `SmsCore/sms/statistics`

2. **SenderNameController.php**
   - `smscore/sms/sender-names/index` → `SmsCore/sms/sender-names/index`
   - `smscore/sms/sender-names/create` → `SmsCore/sms/sender-names/create`
   - `smscore/sms/sender-names/edit` → `SmsCore/sms/sender-names/edit`
   - `smscore/sms/sender-names/assign` → `SmsCore/sms/sender-names/assign`

3. **SmsController.php**
   - `smscore/sms/send` → `SmsCore/sms/send`
   - `smscore/sms/history` → `SmsCore/sms/history`
   - `smscore/sms/details` → `SmsCore/sms/details`

4. **SmsCampaignController.php**
   - `smscore/sms/campaigns/index` → `SmsCore/sms/campaigns/index`
   - `smscore/sms/campaigns/create` → `SmsCore/sms/campaigns/create`
   - `smscore/sms/campaigns/edit` → `SmsCore/sms/campaigns/edit`
   - `smscore/sms/campaigns/show` → `SmsCore/sms/campaigns/show`

5. **SmsPricingController.php**
   - `smscore/sms/pricing/index` → `SmsCore/sms/pricing/index`
   - `smscore/sms/billing/index` → `SmsCore/sms/billing/index`

6. **WalletController.php**
   - `smscore/wallet/topup` → `SmsCore/wallet/topup`

7. **ProviderDashboardController.php**
   - Déjà correct avec `SmsCore/providers/...`

## Commande Utilisée

```bash
# Remplacer automatiquement dans tous les controllers
cd Modules/SmsCore/Controllers
sed -i "s/view('smscore\//view('SmsCore\//g" *.php
```

## Vérification

### Avant le déploiement
```bash
# Vérifier qu'il n'y a plus de minuscules
grep -r "view('smscore/" Modules/SmsCore/Controllers/
# Résultat attendu : Rien (sauf fichiers .example)

# Vérifier que tout est en SmsCore
grep -r "view('SmsCore/" Modules/SmsCore/Controllers/ | wc -l
# Résultat attendu : Plusieurs lignes
```

### Après le déploiement en ligne
1. Testez tous les liens SMS
2. Vérifiez les logs pour les erreurs "View not found"

## Bonnes Pratiques

### Pour Éviter ce Problème à l'Avenir

1. **Convention de Nommage Stricte**
   - Toujours utiliser la même casse que le nom du dossier
   - Module : `SmsCore` → vues : `view('SmsCore/...')`

2. **Tests en Environnement Similaire**
   - Tester sur Linux avant de déployer
   - Utiliser Docker avec Alpine/Ubuntu pour les tests

3. **Linter/Validation**
   - Créer un script de validation qui vérifie la cohérence des chemins

## Script de Validation (Optionnel)

Créer `scripts/validate_view_paths.php` :

```php
<?php
/**
 * Valide que tous les chemins de vues respectent la casse des dossiers
 */

$modulesPath = __DIR__ . '/../Modules';
$errors = [];

// Parcourir tous les modules
foreach (glob($modulesPath . '/*', GLOB_ONLYDIR) as $modulePath) {
    $moduleName = basename($modulePath);
    $controllersPath = $modulePath . '/Controllers';

    if (!is_dir($controllersPath)) continue;

    // Chercher les view() dans les controllers
    foreach (glob($controllersPath . '/*.php') as $controllerFile) {
        $content = file_get_contents($controllerFile);

        // Extraire tous les view('...')
        preg_match_all("/view\('([^']+)'/", $content, $matches);

        foreach ($matches[1] as $viewPath) {
            // Vérifier que le premier segment correspond au nom du module
            $segments = explode('/', $viewPath);
            $viewModule = $segments[0];

            if ($viewModule !== $moduleName) {
                $errors[] = [
                    'file' => $controllerFile,
                    'view' => $viewPath,
                    'expected' => $moduleName,
                    'found' => $viewModule
                ];
            }
        }
    }
}

if (empty($errors)) {
    echo "✅ Tous les chemins de vues sont corrects!\n";
    exit(0);
} else {
    echo "❌ Erreurs de casse détectées:\n\n";
    foreach ($errors as $error) {
        echo "Fichier: {$error['file']}\n";
        echo "  Vue: {$error['view']}\n";
        echo "  Attendu: {$error['expected']}\n";
        echo "  Trouvé: {$error['found']}\n\n";
    }
    exit(1);
}
```

Utilisation :
```bash
php scripts/validate_view_paths.php
```

## Déploiement

### Étapes pour Déployer le Fix

1. **Commit et Push**
   ```bash
   git add Modules/SmsCore/Controllers/
   git commit -m "Fix: Corriger la casse des chemins de vues SMS pour Linux"
   git push origin main
   ```

2. **Sur le Serveur**
   ```bash
   cd /path/to/project
   git pull origin main

   # Vider le cache si nécessaire
   rm -rf storage/cache/views/*
   ```

3. **Tester**
   - Accédez à toutes les pages SMS
   - Vérifiez les logs : `tail -f storage/logs/app.log`

## Problèmes Similaires Possibles

Si vous rencontrez le même problème avec d'autres modules :

```bash
# Trouver tous les modules avec incohérence de casse
find Modules -name "Controllers" -type d -exec sh -c '
  for dir; do
    module=$(basename $(dirname "$dir"))
    grep -r "view(\"" "$dir" | grep -v "$module" | grep -o "view('\''[^'\'']*" | cut -d"'\''" -f2
  done
' sh {} +
```

## Résumé

✅ **Problème résolu** : Tous les chemins de vues utilisent maintenant `SmsCore` (avec la bonne casse)
✅ **Compatible Linux** : Le code fonctionnera identique en local et en ligne
✅ **Maintenable** : Une convention claire pour les futurs développements

⚠️ **À retenir** : Toujours respecter la casse exacte des noms de modules dans les chemins de vues !
