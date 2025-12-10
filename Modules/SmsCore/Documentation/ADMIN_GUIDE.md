# Guide Administrateur - Module SMS

## 📋 Table des matières

1. [Configuration initiale](#configuration-initiale)
2. [Gestion des utilisateurs](#gestion-des-utilisateurs)
3. [Configuration des tarifs](#configuration-des-tarifs)
4. [Gestion des Sender Names](#gestion-des-sender-names)
5. [Monitoring et statistiques](#monitoring-et-statistiques)
6. [API et intégrations](#api-et-intégrations)
7. [Dépannage](#dépannage)

---

## Configuration initiale

### 1. Configurer les gateways SMS

1. Accédez à **Paramètres > SMS Gateways**
2. Ajoutez vos identifiants pour chaque gateway :
   - **Orange CI** : Client ID, Client Secret, Sender ID
   - **Infobip** : API Key, Base URL
3. Activez le gateway par défaut

### 2. Configurer la tarification

1. Accédez à **SMS > Tarification** (`/admin/sms/pricing`)
2. Définissez le prix par défaut (ex: 25 XOF par segment)
3. Ajoutez des tarifs spécifiques par pays si nécessaire
4. Ajoutez des tarifs par opérateur pour plus de précision

### 3. Créer les permissions RBAC

Assurez-vous que ces permissions existent :

```
sms.send                  - Envoyer des SMS
sms.bulk                  - Envoi groupé
sms.history.view          - Voir l'historique
sms.stats.view            - Voir les statistiques
sms.campaigns.view        - Voir les campagnes
sms.campaigns.create      - Créer des campagnes
sms.sender_names.view     - Voir les sender names
sms.sender_names.manage   - Gérer les sender names
sms.sender_names.assign   - Assigner aux utilisateurs
```

---

## Gestion des utilisateurs

### Attribuer des permissions

1. Accédez à **RBAC > Rôles**
2. Éditez ou créez un rôle
3. Cochez les permissions SMS nécessaires
4. Assignez le rôle aux utilisateurs

### Gérer les clés API

#### Pour un utilisateur spécifique :

1. L'utilisateur accède à **SMS > Mes clés API**
2. Il clique sur "Générer ma clé API"
3. Il copie la clé et la conserve en sécurité

#### En tant qu'admin :

Vous pouvez consulter/régénérer les clés API via la base de données :

```sql
-- Voir tous les utilisateurs avec clé API
SELECT id, username, api_key, created_at
FROM users
WHERE api_key IS NOT NULL;

-- Régénérer la clé d'un utilisateur (via l'interface admin recommandé)
```

### Gérer les portefeuilles

1. Accédez à **Wallet > Top-up**
2. Rechargez le compte d'un utilisateur
3. Consultez l'historique des transactions

---

## Configuration des tarifs

### Tarif par défaut

Le tarif par défaut s'applique à tous les SMS qui n'ont pas de tarif spécifique.

**Recommandation :** 25-50 XOF par segment

### Tarifs par pays

Définissez des tarifs différents selon le pays de destination :

| Pays | Code | Tarif recommandé |
|------|------|-----------------|
| Côte d'Ivoire | CI | 25 XOF |
| Sénégal | SN | 30 XOF |
| Mali | ML | 35 XOF |
| France | FR | 100 XOF |

### Tarifs par opérateur

Pour une tarification encore plus précise :

| Opérateur | Pays | Tarif |
|-----------|------|-------|
| Orange | CI | 20 XOF |
| MTN | CI | 25 XOF |
| Moov | CI | 25 XOF |

---

## Gestion des Sender Names

Les Sender Names sont les noms d'expéditeur qui apparaissent sur les SMS reçus.

### Créer un Sender Name

1. Accédez à **SMS > Sender Names**
2. Cliquez sur "Créer un Sender Name"
3. Remplissez :
   - **Nom** : Max 11 caractères alphanumériques
   - **Description** : Usage prévu
   - **Statut** : Pending/Approved/Rejected
4. Validez avec l'opérateur (Orange, MTN, etc.)

### Assigner aux utilisateurs

#### Méthode 1 : Depuis le Sender Name
1. Cliquez sur "Assigner des utilisateurs" sur un sender name
2. Cochez les utilisateurs autorisés
3. Enregistrez

#### Méthode 2 : Depuis l'utilisateur
1. Accédez à la page utilisateur
2. Section "Sender Names"
3. Assignez en masse

### Validation opérateur

Les Sender Names doivent être approuvés par les opérateurs :

1. Créez le sender name avec statut "Pending"
2. Contactez votre opérateur (Orange, MTN)
3. Fournissez les documents requis
4. Une fois approuvé, changez le statut en "Approved"

**Documents généralement requis :**
- Registre de commerce
- Autorisation d'utilisation du nom
- Preuve d'identité

---

## Monitoring et statistiques

### Dashboard SMS

**URL :** `/admin/sms`

**Métriques affichées :**
- Total de messages
- Messages du jour
- Taux de succès
- Crédits utilisés
- Gateways actifs
- Solde portefeuille

### Page Statistiques

**URL :** `/admin/sms/statistics`

**Graphiques disponibles :**
- SMS envoyés par jour
- SMS délivrés vs échoués
- Répartition par opérateur
- Coûts par période
- Tendances hebdomadaires

### Historique et facturation

**URL :** `/admin/sms/billing`

Consultez tous les logs de facturation avec :
- Filtres par date
- Filtres par statut
- Filtres par utilisateur
- Export CSV

### Dashboard fournisseurs

**URL :** `/admin/sms/providers`

Statistiques par gateway :
- Orange CI
- Infobip
- Autres

---

## API et intégrations

### Documentation API

**URL :** `/admin/sms/api/docs`

Documentation complète avec :
- Tous les endpoints
- Exemples de code (PHP, Python, JavaScript, cURL)
- Codes de réponse
- Limites et quotas

### Tester l'API

Utilisez le script de test fourni :

```bash
cd Modules/SmsCore/Documentation
php test_api.php
```

### Limites recommandées

| Paramètre | Valeur recommandée |
|-----------|-------------------|
| Rate limit | 100 requêtes/min |
| Longueur message | 1600 caractères max |
| Segments | Illimité |
| Sender ID | 11 caractères max |

### Webhooks (futur)

Prévu pour notifier les événements :
- SMS délivré
- SMS échoué
- Solde faible

---

## Dépannage

### SMS non envoyés

**Symptôme :** Les SMS restent en statut "pending"

**Solutions :**
1. Vérifier que le cron job fonctionne
2. Vérifier les credentials du gateway
3. Consulter les logs d'erreur
4. Vérifier le solde du compte gateway

### Erreurs d'authentification API

**Symptôme :** "Invalid API key" ou "Unauthorized"

**Solutions :**
1. Vérifier que la clé API est active
2. Vérifier que l'utilisateur a les permissions
3. Vérifier le format du header Authorization
4. Régénérer la clé API si nécessaire

### Problèmes de tarification

**Symptôme :** Prix incorrects facturés

**Solutions :**
1. Vérifier la grille tarifaire (`/admin/sms/pricing`)
2. Vérifier que le pays est correctement détecté
3. Vérifier les logs de facturation
4. Recalculer manuellement si nécessaire

### Gateway timeout

**Symptôme :** "Gateway timeout" ou "Connection failed"

**Solutions :**
1. Vérifier la connectivité réseau
2. Vérifier les credentials du gateway
3. Tester avec MockGateway pour isoler le problème
4. Contacter le support du gateway

### Sender Name refusé

**Symptôme :** SMS envoyés avec un numéro au lieu du nom

**Solutions :**
1. Vérifier que le sender name est approuvé
2. Vérifier l'assignation utilisateur/sender name
3. Vérifier que le sender name respecte les règles (11 char max)
4. Contacter l'opérateur pour validation

---

## Commandes utiles

### Base de données

```sql
-- Statistiques globales
SELECT
    COUNT(*) as total_sms,
    SUM(cost) as total_cost,
    AVG(cost) as avg_cost,
    status
FROM sms_billing_logs
GROUP BY status;

-- SMS par utilisateur
SELECT
    u.username,
    COUNT(*) as sms_count,
    SUM(sbl.cost) as total_spent
FROM users u
LEFT JOIN sms_billing_logs sbl ON u.id = sbl.user_id
GROUP BY u.id, u.username
ORDER BY total_spent DESC;

-- SMS par jour (30 derniers jours)
SELECT
    DATE(created_at) as date,
    COUNT(*) as count,
    SUM(cost) as cost
FROM sms_billing_logs
WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
GROUP BY DATE(created_at)
ORDER BY date DESC;

-- Sender Names les plus utilisés
SELECT
    sender_id,
    COUNT(*) as usage_count
FROM sms_billing_logs
WHERE sender_id IS NOT NULL
GROUP BY sender_id
ORDER BY usage_count DESC
LIMIT 10;
```

### Cron jobs

```bash
# Traiter la queue SMS
php cli.php cron:run process-sms-queue

# Nettoyer les anciens logs (> 90 jours)
php cli.php sms:clean-logs --days=90
```

---

## Checklist de mise en production

- [ ] Configurer les gateways SMS avec les vrais credentials
- [ ] Définir la grille tarifaire
- [ ] Créer et valider les Sender Names
- [ ] Configurer les rôles et permissions RBAC
- [ ] Activer le cron job pour la queue SMS
- [ ] Tester l'envoi de SMS
- [ ] Tester l'API avec une clé de test
- [ ] Configurer le monitoring
- [ ] Former les utilisateurs
- [ ] Préparer la documentation utilisateur

---

## Support

- **Documentation API :** `/admin/sms/api/docs`
- **Guide utilisateur :** `/admin/sms/contracts`
- **Logs système :** `/storage/logs/`
- **Email support :** support@votre-domaine.com

---

**Dernière mise à jour :** 10 Décembre 2025
