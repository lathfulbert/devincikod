# 🔄 Guide Complet de Récupération des Commits Perdus

## 📊 État Actuel

✅ **DÉJÀ FAIT** (modifications détectées):
1. ✅ `Modules/Wallet/WalletModule.php` - Menu dropdown
2. ⚠️ `Modules/SmsCore/SmsCoreModule.php` - Route parse-file (partiellement)
3. ⚠️ `Modules/SmsCore/Services/FileImportService.php` - Template modifié (manque les nouvelles méthodes)

## 🚀 MODIFICATIONS À APPLIQUER

### 1. Menu Wallet ✅ (DÉJÀ FAIT)
Le menu dropdown est déjà en place avec les sous-menus.

### 2. SMS Statistics - DashboardController.php

**Fichier**: `Modules/SmsCore/Controllers/DashboardController.php`
**Méthode**: `statistics()` (remplacer complètement lignes 61-84)

**Action**: Copier le contenu de `PATCH_FILES/DashboardController_statistics.php` pour remplacer la méthode statistics() actuelle.

**Points clés**:
- ✅ Utilise `user_id` au lieu de `owner_id`
- ✅ Utilise `$msg->to` au lieu de `$msg->phone`
- ✅ Fonctions array (count, array_filter) au lieu de collections
- ✅ Filtrage role-based (admin vs owner)
- ✅ Statistiques réelles de la DB

### 3. Chart.js v3.9.1

**Fichier**: `public/assets/js/chart/chartjs/chart-v3.min.js`

**Actions**:
1. Télécharger Chart.js v3.9.1 depuis: https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js
2. Sauvegarder comme `chart-v3.min.js` dans le dossier indiqué

**OU utiliser cette commande**:
```bash
cd public/assets/js/chart/chartjs/
curl -o chart-v3.min.js https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js
```

### 4. Fix startTime - master.php

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

### 5. Enregistrement Coûts SMS - SmsController.php

**Fichier**: `Modules/SmsCore/Controllers/SmsController.php`

**Modification 1** - Méthode `send()` ligne ~108:
```php
// Set cost from pricing calculation
if (isset($result['cost'])) {
    $smsMessage->cost = $result['cost'];
}
```

**Modification 2** - Méthode `sendBulk()` lignes ~260-302:
Ajouter le support des variables avec `file_data` et `phone_column`.
Voir fichier: `PATCH_FILES/SmsController_cost_recording.php` - PARTIE 2

**Modification 3** - Méthode `sendDirectBulk()` lignes ~440-547:
Voir fichier complet: `PATCH_FILES/SmsController_cost_recording.php`

**Modification 4** - Méthode `sendViaQueue()` ligne ~561:
Ajouter le mapping de données pour variables.

### 6. SmsQueueService.php - Création sms_messages avec coûts

**Fichier**: `Modules/SmsCore/Services/SmsQueueService.php`
**Méthode**: `processQueue()`
**Lignes**: ~173-219

Voir fichier: `PATCH_FILES/SmsQueueService_cost_recording.php`

**Action**: Dans la boucle foreach, ajouter la création de SmsMessage::create() pour les SMS sent ET failed.

### 7. SmsOtpProvider.php - Coûts OTP

**Fichier**: `Modules/Auth/Providers/SmsOtpProvider.php`
**Lignes**: ~272-275

Voir fichier: `PATCH_FILES/SmsOtpProvider_cost_recording.php`

### 8. FileImportService.php - Variables Excel (IMPORTANT!)

**Fichier**: `Modules/SmsCore/Services/FileImportService.php`

**⚠️ État actuel**: Le template a été modifié mais les méthodes principales manquent!

**Méthodes à ajouter** (avant la méthode `generateTemplate()`):

1. `importWithColumns(array $file): array`
2. `importCSVWithColumns(string $filePath): array`
3. `importExcelWithColumns(string $filePath, string $extension): array`
4. `replaceVariables(string $template, array $data): string`

**Code complet disponible dans la session précédente** ou je peux le régénérer.

### 9. Route + parseFile()

**Fichier 1**: `Modules/SmsCore/SmsCoreModule.php`
**Ligne**: Ajouter après ligne 29

```php
['POST', '/admin/sms/parse-file', [\Modules\SmsCore\Controllers\SmsController::class, 'parseFile'], [$authMiddleware]],
```

**Fichier 2**: `Modules/SmsCore/Controllers/SmsController.php`
**Méthode**: Ajouter la méthode `parseFile()` (nouvelle méthode complète)

Cette méthode:
- Reçoit un fichier Excel/CSV
- Détecte les colonnes automatiquement
- Identifie la colonne téléphone
- Retourne JSON avec headers, phone_column, variable_columns, preview_data

### 10. Interface Variables Excel + JavaScript

**Fichier**: `Modules/SmsCore/Views/sms/send.php`
**Section**: Tab "Import Fichier" (lignes ~227-319)

**Modifications nécessaires**:
1. Modifier le formulaire pour upload AJAX
2. Ajouter zone d'affichage des colonnes détectées
3. Ajouter sélecteur de colonne téléphone
4. Afficher boutons pour insérer variables: `{{nom}}`, `{{prenom}}`, etc.
5. Zone d'aperçu des 3 premiers SMS personnalisés
6. Champs cachés pour stocker file_data et phone_column

## 📝 Ordre d'Application Recommandé

1. ✅ WalletModule.php (DÉJÀ FAIT)
2. ⏭️ **COMMENCER PAR**: Chart.js v3.9.1 (téléchargement)
3. ⏭️ master.php fix startTime (1 ligne)
4. ⏭️ DashboardController.php statistics() (grosse méthode)
5. ⏭️ SmsController.php coûts SMS (partie 1 - send())
6. ⏭️ SmsQueueService.php sms_messages
7. ⏭️ SmsOtpProvider.php coûts OTP
8. ⏭️ FileImportService.php (4 nouvelles méthodes)
9. ⏭️ SmsController.php parseFile() + sendBulk modifications
10. ⏭️ SmsCoreModule.php route
11. ⏭️ Interface UI + JavaScript (le plus complexe)

## 🔍 Vérification

Après chaque modification:
```bash
php -l nom_fichier.php
```

Base de données:
```bash
mysql -u root sunuframework2 < scripts/verify_database.sql
```

## 💾 Commit Git

Une fois TOUTES les modifications appliquées:
```bash
git add -A
git commit -m "Récupération commits: Menu Wallet dropdown, Stats SMS réelles, Coûts SMS, Variables Excel"
git push origin devop
```

## 🆘 Besoin d'Aide?

Si vous voulez que je génère:
1. Le code complet de FileImportService.php
2. Le code complet de parseFile()
3. L'interface JavaScript complète
4. Les modifications complètes de sendBulk()

**Dites-moi quelle partie vous voulez et je la génère immédiatement!**
