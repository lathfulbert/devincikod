# Guide de Débogage CSRF

## Problème Rencontré
Erreur "CSRF token validation failed" lors de la soumission des formulaires de gestion des langues.

## Modifications Effectuées

### 1. Ajout de Tokens CSRF aux Formulaires ✅
Tous les formulaires dans `Modules/Settings/Views/languages/index.php` ont maintenant le champ CSRF:
```php
<?= csrf_field() ?>
```

### 2. Ajout de Logs de Débogage ✅
Le fichier `Core/Middleware/CSRFMiddleware.php` a été modifié pour logger:
- La méthode HTTP
- Le token reçu via POST
- Le token stocké en session
- Les données POST complètes

### 3. Cache des Vues Vidé ✅
Le cache des vues compilées a été vidé pour s'assurer que les nouvelles versions avec les tokens sont utilisées.

## Prochaines Étapes pour Diagnostiquer

### 1. Tester à Nouveau
1. Accédez à: http://localhost/sunuframework2/admin/settings/languages
2. Essayez d'activer, désactiver ou modifier une langue
3. Si l'erreur se produit toujours, passez à l'étape 2

### 2. Vérifier le Log de Débogage
Ouvrez le fichier: `storage/logs/csrf_debug.log`

Ce fichier contiendra des informations comme:
```
[2025-11-27 16:30:45] Method: POST | Token from POST: abc123... | Token from Session: abc123... | POST data: {"code":"en","_csrf_token":"abc123..."}
```

**Vérifiez:**
- Le token reçu via POST est-il NULL ou a-t-il une valeur?
- Le token en session est-il NULL ou a-t-il une valeur?
- Les deux tokens sont-ils identiques ou différents?

### 3. Cas Possibles

#### Cas A: Token POST est NULL
**Symptôme:** `Token from POST: NULL`
**Cause:** Le formulaire ne contient pas le champ CSRF ou JavaScript l'empêche d'être envoyé
**Solution:** Vérifier le HTML source de la page pour s'assurer que `<input name="_csrf_token">` est présent

#### Cas B: Token Session est NULL
**Symptôme:** `Token from Session: NULL`
**Cause:** La session n'est pas maintenue entre les requêtes
**Solution:** Vérifier la configuration de session PHP (cookies, domaine, etc.)

#### Cas C: Tokens Différents
**Symptôme:** `Token from POST: abc123...` et `Token from Session: xyz789...`
**Cause:** Le token a été régénéré entre le rendu de la page et la soumission
**Solution:** Vérifier si quelque chose régénère le token (login, refresh, etc.)

## Tests Supplémentaires

### Test 1: Page HTML de Test
Ouvrez: `test_form_submission.html` dans votre navigateur
- Cette page affiche le token actuel
- Contient un formulaire de test simple

### Test 2: Script PHP de Test
Exécutez:
```bash
php test_csrf.php
```
Ce script teste la génération et validation des tokens en isolation.

## Inspection du HTML Source

1. Allez sur la page de gestion des langues
2. Faites clic-droit > "Afficher le code source"
3. Cherchez (Ctrl+F) : `_csrf_token`
4. Vérifiez que les formulaires contiennent bien:
```html
<input type="hidden" name="_csrf_token" value="[un long token ici]">
```

## Inspection avec DevTools

1. Ouvrez DevTools (F12)
2. Allez dans l'onglet "Network" (Réseau)
3. Soumettez un formulaire
4. Cliquez sur la requête POST dans la liste
5. Regardez la section "Payload" ou "Form Data"
6. Vérifiez que `_csrf_token` est bien envoyé avec une valeur

## Solutions Potentielles

### Si les tokens ne sont pas générés:
```bash
# Vider tous les caches
rm -rf storage/cache/views/*
rm -rf storage/logs/*.log

# Vérifier les permissions
chmod -R 775 storage/
```

### Si la session n'est pas maintenue:
Vérifiez `config/app.php` ou `php.ini`:
```ini
session.cookie_httponly = 1
session.use_cookies = 1
session.cookie_lifetime = 0
```

### Si le problème persiste:
1. Partagez le contenu de `storage/logs/csrf_debug.log`
2. Partagez une capture d'écran de la section "Form Data" dans DevTools
3. Vérifiez le HTML source pour un des formulaires

## Commandes Utiles

```bash
# Voir le log CSRF en temps réel
tail -f storage/logs/csrf_debug.log

# Vider tous les logs
rm storage/logs/*.log

# Vider le cache des vues
rm -rf storage/cache/views/*

# Tester la génération de tokens
php test_csrf.php
```

## Contact
Si le problème persiste après ces vérifications, fournissez:
1. Le contenu de `storage/logs/csrf_debug.log`
2. Une capture d'écran du Network tab de DevTools
3. Le HTML source d'un des formulaires (Ctrl+U dans le navigateur)
