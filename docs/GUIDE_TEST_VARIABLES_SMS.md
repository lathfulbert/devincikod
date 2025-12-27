# 🧪 Guide de Test - Variables SMS Excel

## ✅ Ce qui a été fait

1. **✅ Backend corrigé** - Variables maintenant fonctionnelles
2. **✅ Interface UI** - Boutons cliquables pour insérer variables
3. **✅ JavaScript automatique** - Upload → Détection → Aperçu
4. **✅ Commit sauvegardé** - f113df2

## 🚀 Comment Tester

### Étape 1: Préparer le fichier de test

Utilisez le fichier: **TEST_IMPORT_SMS.csv** (déjà créé)

Contenu:
```csv
Téléphone,Nom,Prenom,Montant,Solde
0708090102,Dupont,Jean,1000,5000
0709101112,Martin,Marie,2500,7500
...
```

### Étape 2: Aller sur la page SMS

1. Connexion: http://localhost/sunuframework2/admin/sms/send
2. Cliquer sur l'onglet **"Import Fichier"**

### Étape 3: Uploader le fichier

1. **Sélectionner** le fichier `TEST_IMPORT_SMS.csv`
2. **Attendre** 1-2 secondes

**Ce qui devrait apparaître automatiquement:**

#### A) Informations du fichier (Encadré bleu)
```
ℹ️ Informations du fichier
Nombre de lignes: 5
Colonne téléphone: Téléphone
Variables disponibles: 4 colonnes
```

#### B) Boutons de variables (Encadré vert)
```
✨ Variables disponibles (cliquez pour insérer)

[+ {{Nom}}]  [+ {{Prenom}}]  [+ {{Montant}}]  [+ {{Solde}}]

💡 Cliquez sur une variable pour l'insérer dans votre message
```

### Étape 4: Composer le message

Dans le champ **"Message"**, vous pouvez :

**Option A: Taper manuellement**
```
Bonjour {{Prenom}} {{Nom}}, votre solde est {{Solde}} FCFA.
```

**Option B: Cliquer sur les boutons** (Recommandé)
1. Taper: `Bonjour `
2. Cliquer sur `[+ {{Prenom}}]`
3. Taper: ` `
4. Cliquer sur `[+ {{Nom}}]`
5. Taper: `, votre solde est `
6. Cliquer sur `[+ {{Solde}}]`
7. Taper: ` FCFA.`

### Étape 5: Voir l'aperçu

**Automatiquement, l'encadré jaune "Aperçu" montre:**

```
👁️ Aperçu (3 premiers SMS)

→ 0708090102
"Bonjour Jean Dupont, votre solde est 5000 FCFA."

→ 0709101112
"Bonjour Marie Martin, votre solde est 7500 FCFA."

→ 0701234567
"Bonjour Amadou Diallo, votre solde est 3000 FCFA."
```

### Étape 6: Envoyer

1. Sélectionner **Sender Name**
2. Optionnel: Nom de campagne
3. Cliquer **"Importer et Envoyer"**

**Résultat attendu:**
- ✅ Les SMS sont envoyés avec les messages personnalisés
- ✅ Plus de `{{variables}}` en brut
- ✅ Chaque destinataire reçoit son message unique

## 🔍 Vérifications

### Dans la base de données

```sql
SELECT
    id,
    `to`,
    LEFT(message, 80) as message_preview,
    status,
    cost,
    sent_at
FROM sms_messages
ORDER BY id DESC
LIMIT 5;
```

**Vous devriez voir:**
```
0708090102 | "Bonjour Jean Dupont, votre solde est 5000 FCFA."
0709101112 | "Bonjour Marie Martin, votre solde est 7500 FCFA."
...
```

## 🐛 Dépannage

### Problème 1: Boutons de variables n'apparaissent pas

**Vérifier:**
```bash
# Le fichier JavaScript existe?
ls -la public/assets/js/sms-import-variables.js

# La vue inclut le script?
grep "sms-import-variables.js" Modules/SmsCore/Views/sms/send.php
```

**Solution:** Vider le cache navigateur (Ctrl+Shift+R)

### Problème 2: Variables toujours en brut {{nom}}

**Vérifier dans la console navigateur (F12):**
- Erreurs JavaScript?
- Requête `/admin/sms/parse-file` réussit?

**Vérifier les champs cachés du formulaire:**
```javascript
// Dans la console F12
console.log(document.querySelector('input[name="file_data"]').value);
console.log(document.querySelector('input[name="phone_column"]').value);
```

**Solution:** Les champs doivent contenir les données JSON

### Problème 3: Erreur "Données de fichier invalides"

**Cause:** Les champs cachés ne sont pas créés

**Solution:** Vider cache et recharger la page

## ✨ Fonctionnalités

### Ce qui fonctionne maintenant:

✅ **Upload automatique** - Dès sélection du fichier
✅ **Détection colonnes** - Headers détectés automatiquement
✅ **Colonne téléphone** - Auto-détectée
✅ **Boutons variables** - Cliquables et pratiques
✅ **Aperçu temps réel** - Se met à jour en tapant
✅ **Remplacement variables** - Backend complet
✅ **Mode direct** - < 100 SMS, envoi immédiat
✅ **Mode queue** - ≥ 100 SMS, file d'attente

### Exemple Message Marketing

**Template:**
```
🎉 Cher(e) {{Prenom}},
Profitez de {{Montant}} FCFA de bonus!
Votre nouveau solde: {{Solde}} FCFA
```

**Résultat pour Jean Dupont:**
```
🎉 Cher(e) Jean,
Profitez de 1000 FCFA de bonus!
Votre nouveau solde: 5000 FCFA
```

## 📊 Statistiques après envoi

Les SMS apparaîtront dans:
- `/admin/sms/history` - Historique complet
- `/admin/sms/statistics` - Statistiques avec coûts
- `/admin/sms/campaigns` - Détails de la campagne

## 🎯 Prochaines Étapes

Si tout fonctionne:

1. **Commit final:**
   ```bash
   git push origin devop
   ```

2. **Tester avec vrais numéros** (petit groupe d'abord)

3. **Créer documentation utilisateur**

## ❓ Questions Fréquentes

**Q: Puis-je utiliser plusieurs variables dans un message?**
R: Oui! Exemple: `Bonjour {{Prenom}} {{Nom}}, montant: {{Montant}}, solde: {{Solde}}`

**Q: Les variables sont sensibles à la casse?**
R: Non. `{{nom}}`, `{{Nom}}`, `{{NOM}}` fonctionnent tous.

**Q: Que se passe-t-il si une variable n'existe pas dans le fichier?**
R: La variable reste en `{{variable}}` dans le SMS.

**Q: Puis-je modifier la colonne téléphone?**
R: Pas pour l'instant, mais la détection est automatique et intelligente.

## ✅ Validation Réussie

Si vous voyez:
- ✅ Boutons de variables cliquables
- ✅ Aperçu avec données réelles
- ✅ SMS envoyés sans `{{}}` en brut
- ✅ Messages personnalisés dans la DB

**🎉 TOUT FONCTIONNE PARFAITEMENT !**
