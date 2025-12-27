# Test de la Génération de Clés API

## Modifications effectuées

### 1. Contrôleur ApiKeyController amélioré
- ✅ Utilisation directe de requêtes SQL au lieu de `$user->update()`
- ✅ Gestion d'erreurs avec try/catch
- ✅ Messages flash en français
- ✅ Messages d'erreur explicites

### 2. Routes vérifiées
- ✅ GET /admin/api-keys
- ✅ POST /admin/api-keys/generate
- ✅ POST /admin/api-keys/revoke
- ✅ GET /admin/api-keys/docs

### 3. Base de données
- ✅ Colonne `api_key` (VARCHAR(64))
- ✅ Colonne `api_key_created_at` (DATETIME)

## Instructions de test

### Étape 1 : Se connecter
1. Assurez-vous d'être connecté à l'interface admin
2. Vérifiez que `$_SESSION['user_id']` est défini

### Étape 2 : Accéder à la page API Keys
1. Cliquez sur le menu **SMS** → **API Keys**
2. URL : `http://localhost:81/sunuframework2/admin/api-keys`

### Étape 3 : Générer une clé
1. Cliquez sur le bouton **"Générer une Clé API"**
2. **Résultat attendu :**
   - Message de succès en vert : "Clé API générée avec succès !"
   - La clé API s'affiche (64 caractères hexadécimaux)
   - Bouton de copie disponible
   - Date de création affichée

### Étape 4 : Vérifier les messages flash

Si aucun message ne s'affiche, vérifiez :

1. **Le composant alerts existe :**
   ```php
   <?php component('alerts'); ?>
   ```

2. **La session est démarrée :**
   ```php
   var_dump($_SESSION); // Devrait contenir 'user_id'
   ```

3. **Les flash messages sont définis :**
   ```php
   var_dump($_SESSION['flash_success'] ?? 'aucun');
   var_dump($_SESSION['flash_error'] ?? 'aucun');
   ```

## Débogage

### Vérifier l'utilisateur connecté
```bash
php check_users_table.php
```

### Vérifier les routes
```bash
php check_apikeys_routes.php
```

### Tester manuellement la génération (après connexion)
```php
// Dans le navigateur, après être connecté
var_dump($_SESSION);
```

## Solutions aux problèmes courants

### Problème : Aucune notification
**Cause :** Le composant alerts ne lit pas les flash messages
**Solution :** Vérifier que `component('alerts')` utilise `$_SESSION['flash_*']`

### Problème : Clé non générée
**Cause :** La session n'a pas de `user_id`
**Solution :** Se reconnecter à l'application

### Problème : Erreur CSRF
**Cause :** Token CSRF manquant ou invalide
**Solution :** Vérifier que `<?= csrf_field() ?>` est dans le formulaire

## Vérification finale

Après avoir cliqué sur "Générer une Clé API", la page devrait :
1. ✅ Se recharger automatiquement
2. ✅ Afficher un message de succès vert
3. ✅ Montrer la clé API générée
4. ✅ Afficher le bouton "Révoquer la Clé API"
5. ✅ Cacher le bouton "Générer une Clé API"
