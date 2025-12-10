# Module ApiKeys - Gestion des Clés API

## 🔐 Sécurité des Clés API

### Format des clés

Les clés API générées ont le format suivant:
```
sk_[64 caractères hexadécimaux]
```

**Exemple:** `sk_ff13d4639f6491120e3c94dd584a6e282c9fa82994d4424edd7971ff3b97377c`

### Stockage Sécurisé

⚠️ **IMPORTANT:** Les clés API sont **hachées avec SHA256** avant d'être stockées en base de données.

#### Ce que vous voyez vs. ce qui est stocké:

1. **Lors de la génération:**
   ```
   Clé affichée: sk_ff13d4639f6491120e3c94dd584a6e282c9fa82994d4424edd7971ff3b97377c
   ```

2. **Dans la base de données:**
   ```
   Clé stockée: 6d73620ba741b2c614a7e53b78aa422125167a24f5cec52f5e64015d3c554621
   (Version hachée SHA256)
   ```

### 🎯 Quelle clé utiliser?

✅ **TOUJOURS utiliser la clé affichée lors de la génération:**
```
sk_ff13d4639f6491120e3c94dd584a6e282c9fa82994d4424edd7971ff3b97377c
```

❌ **NE JAMAIS utiliser la valeur de la base de données:**
```
6d73620ba741b2c614a7e53b78aa422125167a24f5cec52f5e64015d3c554621
```

### 💡 Pourquoi ce système?

**Sécurité:** Même si quelqu'un accède à votre base de données, il ne peut pas voler vos clés API car elles sont hachées (impossible à inverser).

**Fonctionnement:** Le middleware hache automatiquement la clé reçue et la compare avec la version hachée en base de données.

## 📝 Utilisation

### Générer une clé API

1. Accédez à `/admin/api-keys`
2. Cliquez sur "Générer une clé API"
3. **Copiez immédiatement la clé affichée** (elle ne sera plus jamais affichée)
4. Stockez-la en sécurité (gestionnaire de mots de passe, variables d'environnement)

### Utiliser la clé dans vos requêtes

#### Méthode 1: Authorization Header (Recommandée)
```bash
curl -H "Authorization: Bearer sk_votre_cle_api" \
     https://votre-domaine.com/api/v1/sms/balance
```

#### Méthode 2: Query Parameter
```bash
curl "https://votre-domaine.com/api/v1/sms/balance?api_key=sk_votre_cle_api"
```

#### Méthode 3: POST Data
```bash
curl -X POST https://votre-domaine.com/api/v1/sms/send \
     -d "api_key=sk_votre_cle_api" \
     -d "to=+225XXXXXXXX" \
     -d "message=Test"
```

#### Méthode 4: JSON Body
```bash
curl -X POST https://votre-domaine.com/api/v1/sms/send \
     -H "Content-Type: application/json" \
     -d '{
       "api_key": "sk_votre_cle_api",
       "to": "+225XXXXXXXX",
       "message": "Test"
     }'
```

## 🔄 Révoquer et Régénérer

### Révoquer une clé
1. Accédez à `/admin/api-keys`
2. Cliquez sur "Révoquer la clé"
3. La clé est **définitivement supprimée** (ne peut plus être utilisée)

### Régénérer une clé
1. Accédez à `/admin/api-keys`
2. Cliquez sur "Régénérer la clé"
3. **L'ancienne clé est révoquée** automatiquement
4. **Copiez la nouvelle clé immédiatement**
5. Mettez à jour vos applications avec la nouvelle clé

⚠️ **Attention:** Régénérer une clé révoque automatiquement l'ancienne. Assurez-vous de mettre à jour toutes vos applications.

## 🛡️ Bonnes Pratiques de Sécurité

### ✅ À FAIRE:

1. **Stockez les clés en sécurité:**
   - Variables d'environnement (`.env`)
   - Gestionnaire de secrets (Vault, AWS Secrets Manager)
   - Gestionnaire de mots de passe

2. **Utilisez HTTPS:**
   - Jamais de clés API sur HTTP non chiffré

3. **Rotation régulière:**
   - Régénérez vos clés tous les 3-6 mois

4. **Principe du moindre privilège:**
   - Créez des clés différentes pour différents environnements
   - Limitez les permissions si possible

5. **Monitoring:**
   - Surveillez `last_used_at` pour détecter les utilisations suspectes

### ❌ À NE PAS FAIRE:

1. ❌ Commiter les clés dans Git
2. ❌ Partager les clés par email/chat
3. ❌ Utiliser la même clé en dev et prod
4. ❌ Logger les clés API
5. ❌ Hardcoder les clés dans le code

## 📊 Exemple de Configuration Sécurisée

### Fichier .env
```env
# API Keys (NE JAMAIS COMMITER CE FICHIER)
API_KEY_PRODUCTION=sk_ff13d4639f6491120e3c94dd584a6e282c9fa82994d4424edd7971ff3b97377c
API_KEY_STAGING=sk_abc123...
```

### Code PHP
```php
// ✅ BON - Utilise variable d'environnement
$apiKey = $_ENV['API_KEY_PRODUCTION'];

// ❌ MAUVAIS - Hardcodé
$apiKey = 'sk_ff13d4639f6491120e3c94dd584a6e282c9fa82994d4424edd7971ff3b97377c';
```

## 🔍 Dépannage

### Erreur: "Invalid API key"

**Causes possibles:**

1. ✅ Vous utilisez la clé de la base de données (hachée)
   - **Solution:** Utilisez la clé affichée lors de la génération

2. ✅ La clé a été révoquée
   - **Solution:** Régénérez une nouvelle clé

3. ✅ La clé est inactive (`is_active = 0`)
   - **Solution:** Activez la clé ou générez-en une nouvelle

4. ✅ La clé a expiré
   - **Solution:** Générez une nouvelle clé

### Erreur: "IP address not allowed"

**Cause:** Votre IP n'est pas dans la whitelist de la clé

**Solution:** Contactez l'administrateur pour ajouter votre IP ou supprimer la restriction

## 📚 Documentation Complémentaire

- [API SMS Documentation](../SmsCore/Documentation/API_DOCUMENTATION.md)
- [Integration Guide](../SmsCore/Documentation/INTEGRATION_APIKEYS.md)

---

**Version:** 1.0
**Dernière mise à jour:** 10 Décembre 2025
