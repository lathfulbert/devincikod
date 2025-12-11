# 🔄 Récupération des Commits Perdus - Guide Simple

## ✅ CE QUI EST DÉJÀ FAIT

J'ai déjà appliqué automatiquement:

1. ✅ **Menu Wallet** → Dropdown avec sous-menus
2. ✅ **FileImportService.php** → Nouvelles méthodes pour variables Excel complètes
3. ✅ **Commit Git** → Premier batch sauvegardé

## 📦 CE QU'IL VOUS RESTE À FAIRE

### Option A: Utilisation du script automatique (Recommandé)

```bash
bash APPLIQUER_TOUS_LES_PATCHES.sh
```

Ce script va automatiquement:
- Télécharger Chart.js v3.9.1
- Fixer master.php pour startTime
- Créer des backups avant modifications

### Option B: Application manuelle

#### 1. Chart.js v3.9.1

**Télécharger**:
```bash
cd public/assets/js/chart/chartjs/
curl -o chart-v3.min.js https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js
```

OU télécharger depuis: https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js
et sauvegarder comme `public/assets/js/chart/chartjs/chart-v3.min.js`

#### 2. Fix master.php startTime

**Fichier**: `resources/views/backend/layouts/master.php`
**Ligne**: ~22

**Changer**:
```php
<body onload="startTime()">
```

**En**:
```php
<body onload="if(typeof startTime === 'function') startTime()">
```

#### 3. DashboardController.php - statistics()

**Fichier**: `Modules/SmsCore/Controllers/DashboardController.php`

**Action**: Remplacer toute la méthode `statistics()` (lignes 61-84 actuelles) par le contenu du fichier:
```
PATCH_FILES/DashboardController_statistics.php
```

**Points clés de cette méthode**:
- ✅ Utilise `user_id` (pas `owner_id`)
- ✅ Utilise `$msg->to` (pas `$msg->phone`)
- ✅ Fonctions array (pas collections Laravel)
- ✅ Filtrage admin/owner
- ✅ Statistiques réelles

#### 4. SmsController.php - Enregistrement coûts

**Fichier**: `Modules/SmsCore/Controllers/SmsController.php`

**3 modifications à faire**:

**A) Dans send()** (ligne ~108):
```php
// Set cost from pricing calculation
if (isset($result['cost'])) {
    $smsMessage->cost = $result['cost'];
}
```

**B) Modifier sendDirectBulk()** - Voir fichier complet:
```
PATCH_FILES/SmsController_cost_recording.php (PARTIE 2)
```

Cette modification ajoute:
- Support des variables avec `file_data` et `phoneColumn`
- Création sms_messages pour chaque envoi (sent/failed)
- Message personnalisé par destinataire

**C) Ajouter la méthode parseFile()** - Code complet ci-dessous

#### 5. SmsQueueService.php - sms_messages

**Fichier**: `Modules/SmsCore/Services/SmsQueueService.php`

**Action**: Dans la méthode `processQueue()`, dans la boucle foreach, remplacer par le code de:
```
PATCH_FILES/SmsQueueService_cost_recording.php
```

Cela crée les enregistrements `sms_messages` pour les SMS en queue.

#### 6. SmsOtpProvider.php - Coûts OTP

**Fichier**: `Modules/Auth/Providers/SmsOtpProvider.php`

**Action**: Après ligne 270, ajouter:
```php
// Set cost from pricing calculation
if (isset($result['cost'])) {
    $smsMessage->cost = $result['cost'];
}
```

#### 7. SmsCoreModule.php - Route

**Fichier**: `Modules/SmsCore/SmsCoreModule.php`

**Action**: Dans `getRoutes()`, après la ligne 29, ajouter:
```php
['POST', '/admin/sms/parse-file', [\Modules\SmsCore\Controllers\SmsController::class, 'parseFile'], [$authMiddleware]],
```

#### 8. SmsController.php - Méthode parseFile()

**Fichier**: `Modules/SmsCore/Controllers/SmsController.php`

**Action**: Ajouter cette nouvelle méthode complète (avant la méthode `contracts()`):

```php
/**
 * Parse uploaded file and return column information
 * Route: POST /admin/sms/parse-file
 */
public function parseFile()
{
    header('Content-Type: application/json');

    try {
        if (!isset($_FILES['file'])) {
            echo json_encode([
                'success' => false,
                'message' => 'Aucun fichier fourni'
            ]);
            exit;
        }

        // Import file with full column detection
        $result = FileImportService::importWithColumns($_FILES['file']);

        // Detect phone column
        $phoneColumn = null;
        $headers = $result['headers'];

        foreach ($headers as $header) {
            $headerLower = strtolower($header);
            if (strpos($headerLower, 'phone') !== false ||
                strpos($headerLower, 'téléphone') !== false ||
                strpos($headerLower, 'telephone') !== false ||
                strpos($headerLower, 'tel') !== false ||
                strpos($headerLower, 'mobile') !== false) {
                $phoneColumn = $header;
                break;
            }
        }

        // If no phone column detected, check values
        if ($phoneColumn === null && !empty($result['data'])) {
            foreach ($headers as $header) {
                $firstValue = $result['data'][0][$header] ?? '';
                if (preg_match('/^[\+]?[\d\s\-\(\)]{6,}$/', $firstValue)) {
                    $phoneColumn = $header;
                    break;
                }
            }
        }

        // Default to first column
        if ($phoneColumn === null && !empty($headers)) {
            $phoneColumn = $headers[0];
        }

        // Get variable columns
        $variableColumns = array_filter($headers, function ($h) use ($phoneColumn) {
            return $h !== $phoneColumn;
        });

        // Preview data
        $previewData = array_slice($result['data'], 0, 3);

        echo json_encode([
            'success' => true,
            'data' => [
                'headers' => $headers,
                'phone_column' => $phoneColumn,
                'variable_columns' => array_values($variableColumns),
                'total_rows' => count($result['data']),
                'preview_data' => $previewData,
                'full_data' => $result['data']
            ]
        ]);
    } catch (\Exception $e) {
        echo json_encode([
            'success' => false,
            'message' => $e->getMessage()
        ]);
    }

    exit;
}
```

## 🧪 VÉRIFICATION

Après chaque modification, vérifier la syntaxe:
```bash
php -l nom_du_fichier.php
```

## 💾 COMMIT FINAL

Une fois TOUTES les modifications faites:
```bash
git add -A
git commit -m "Récupération complète: Stats SMS, Coûts, Variables Excel"
git push origin devop
```

## 📊 TABLEAU RÉCAPITULATIF

| Fichier | Statut | Action |
|---------|---------|--------|
| WalletModule.php | ✅ FAIT | - |
| FileImportService.php | ✅ FAIT | - |
| Chart.js v3.9.1 | ⏳ À FAIRE | Télécharger |
| master.php | ⏳ À FAIRE | 1 ligne à changer |
| DashboardController.php | ⏳ À FAIRE | Remplacer statistics() |
| SmsController.php | ⏳ À FAIRE | 3 modifications |
| SmsQueueService.php | ⏳ À FAIRE | Ajouter sms_messages |
| SmsOtpProvider.php | ⏳ À FAIRE | Ajouter cost |
| SmsCoreModule.php | ⏳ À FAIRE | Ajouter route |

## ❓ BESOIN D'AIDE?

Si un fichier patch n'est pas clair, consultez:
- `GUIDE_RECUPERATION_COMPLETE.md` → Guide détaillé
- `PATCH_FILES/` → Tous les patches

## 🎯 EXEMPLE COMPLET - Variables Excel

Une fois terminé, vous pourrez:

1. Uploader un fichier Excel:
```
Téléphone    | Nom    | Prenom | Montant
0708090102   | Dupont | Jean   | 1000
0709101112   | Martin | Marie  | 2500
```

2. Composer un SMS:
```
Bonjour {{Prenom}} {{Nom}},
votre solde est de {{Montant}} FCFA.
```

3. Résultat automatique:
```
→ 0708090102: "Bonjour Jean Dupont, votre solde est de 1000 FCFA."
→ 0709101112: "Bonjour Marie Martin, votre solde est de 2500 FCFA."
```

## ✨ BON COURAGE!

Toutes les modifications sont prêtes. Suivez simplement ce guide étape par étape.
