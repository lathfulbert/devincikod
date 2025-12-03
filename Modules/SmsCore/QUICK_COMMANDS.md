# 🚀 Commandes Rapides - Sender Names

## 📊 Consultation

### Voir tous les Sender Names
```sql
SELECT * FROM sender_names ORDER BY created_at DESC;
```

### Voir les Sender Names actifs et approuvés
```sql
SELECT * FROM sender_names
WHERE status = 'approved' AND is_active = 1
ORDER BY name;
```

### Voir les attributions d'un utilisateur
```sql
SELECT
    sn.id,
    sn.name,
    sn.operator,
    sn.status,
    usn.assigned_at
FROM sender_names sn
JOIN user_sender_names usn ON sn.id = usn.sender_name_id
WHERE usn.user_id = 1;  -- Remplacer 1 par l'ID utilisateur
```

### Voir qui a accès à un Sender Name
```sql
SELECT
    u.id,
    u.username,
    u.email,
    usn.assigned_at,
    ua.username as assigned_by_username
FROM users u
JOIN user_sender_names usn ON u.id = usn.user_id
LEFT JOIN users ua ON usn.assigned_by = ua.id
WHERE usn.sender_name_id = 1;  -- Remplacer 1 par l'ID du Sender Name
```

---

## ➕ Création

### Créer un nouveau Sender Name
```sql
INSERT INTO sender_names (name, operator, status, is_active, validation_date, notes)
VALUES ('NEWNAME', 'Orange CI', 'approved', 1, CURDATE(), 'Validated by Orange');
```

### Créer plusieurs Sender Names
```sql
INSERT INTO sender_names (name, operator, status, is_active, validation_date) VALUES
('COMPANY1', 'Orange CI', 'approved', 1, CURDATE()),
('COMPANY2', 'MTN CI', 'approved', 1, CURDATE()),
('COMPANY3', 'Moov CI', 'pending', 1, NULL);
```

---

## 🔗 Attribution

### Attribuer un Sender Name à un utilisateur
```sql
INSERT INTO user_sender_names (user_id, sender_name_id, assigned_by)
VALUES (2, 1, 1);  -- user_id=2, sender_name_id=1, assigned_by=1
```

### Attribuer plusieurs Sender Names à un utilisateur
```sql
INSERT INTO user_sender_names (user_id, sender_name_id, assigned_by) VALUES
(2, 1, 1),  -- TICAFRIQUE
(2, 2, 1),  -- SUNUBANK
(2, 3, 1);  -- MYCOMPANY
```

### Attribuer un Sender Name à plusieurs utilisateurs
```sql
INSERT INTO user_sender_names (user_id, sender_name_id, assigned_by)
SELECT id, 1, 1
FROM users
WHERE is_active = 1 AND id != 1;  -- Tous sauf admin
```

### Retirer un Sender Name d'un utilisateur
```sql
DELETE FROM user_sender_names
WHERE user_id = 2 AND sender_name_id = 1;
```

### Retirer tous les Sender Names d'un utilisateur
```sql
DELETE FROM user_sender_names WHERE user_id = 2;
```

---

## ✏️ Modification

### Changer le statut
```sql
UPDATE sender_names
SET status = 'approved', validation_date = CURDATE()
WHERE id = 4;
```

### Désactiver un Sender Name
```sql
UPDATE sender_names
SET is_active = 0
WHERE id = 4;
```

### Mettre à jour les notes
```sql
UPDATE sender_names
SET notes = 'Validated by Orange CI on 2025-12-03'
WHERE id = 1;
```

---

## 🗑️ Suppression

### Supprimer un Sender Name (+ toutes ses attributions)
```sql
DELETE FROM sender_names WHERE id = 4;
-- Les attributions sont automatiquement supprimées (CASCADE)
```

### Supprimer toutes les attributions d'un Sender Name
```sql
DELETE FROM user_sender_names WHERE sender_name_id = 4;
```

---

## 🔍 Statistiques

### Nombre total de Sender Names
```sql
SELECT COUNT(*) as total FROM sender_names;
```

### Nombre par statut
```sql
SELECT status, COUNT(*) as count
FROM sender_names
GROUP BY status;
```

### Nombre d'utilisateurs par Sender Name
```sql
SELECT
    sn.name,
    COUNT(usn.user_id) as user_count
FROM sender_names sn
LEFT JOIN user_sender_names usn ON sn.id = usn.sender_name_id
GROUP BY sn.id, sn.name
ORDER BY user_count DESC;
```

### Top 5 Sender Names les plus attribués
```sql
SELECT
    sn.id,
    sn.name,
    sn.operator,
    COUNT(usn.user_id) as assigned_count
FROM sender_names sn
LEFT JOIN user_sender_names usn ON sn.id = usn.sender_name_id
GROUP BY sn.id
ORDER BY assigned_count DESC
LIMIT 5;
```

### Utilisateurs sans Sender Name
```sql
SELECT
    u.id,
    u.username,
    u.email
FROM users u
LEFT JOIN user_sender_names usn ON u.id = usn.user_id
WHERE usn.id IS NULL AND u.is_active = 1;
```

### Sender Names non attribués
```sql
SELECT
    sn.id,
    sn.name,
    sn.operator,
    sn.status
FROM sender_names sn
LEFT JOIN user_sender_names usn ON sn.id = usn.sender_name_id
WHERE usn.id IS NULL AND sn.status = 'approved' AND sn.is_active = 1;
```

---

## 🧹 Maintenance

### Nettoyer les attributions d'utilisateurs inactifs
```sql
DELETE usn FROM user_sender_names usn
JOIN users u ON usn.user_id = u.id
WHERE u.is_active = 0;
```

### Désactiver les Sender Names non utilisés depuis 6 mois
```sql
-- À implémenter avec une table de logs d'utilisation
```

### Réinitialiser toutes les attributions
```sql
TRUNCATE TABLE user_sender_names;
```

---

## 🔄 Migration de Données

### Importer depuis un CSV (conceptuel)
```sql
LOAD DATA LOCAL INFILE '/path/to/sender_names.csv'
INTO TABLE sender_names
FIELDS TERMINATED BY ','
ENCLOSED BY '"'
LINES TERMINATED BY '\n'
IGNORE 1 ROWS
(name, operator, status, validation_date, notes);
```

### Copier les attributions d'un utilisateur à un autre
```sql
INSERT INTO user_sender_names (user_id, sender_name_id, assigned_by)
SELECT 3, sender_name_id, 1
FROM user_sender_names
WHERE user_id = 1;  -- Copier de user_id=1 vers user_id=3
```

---

## 🎯 Scénarios Courants

### Nouvel utilisateur : attribuer les Sender Names par défaut
```sql
INSERT INTO user_sender_names (user_id, sender_name_id, assigned_by)
SELECT 5, id, 1  -- user_id=5, assigned_by=1 (admin)
FROM sender_names
WHERE status = 'approved'
  AND is_active = 1
  AND name IN ('TICAFRIQUE', 'SUNUBANK');
```

### Révoquer l'accès à un Sender Name pour tous
```sql
DELETE FROM user_sender_names WHERE sender_name_id = 3;
```

### Migrer vers un nouveau Sender Name
```sql
-- Remplacer les attributions de l'ancien par le nouveau
UPDATE user_sender_names
SET sender_name_id = 5  -- Nouveau Sender Name
WHERE sender_name_id = 2;  -- Ancien Sender Name
```

---

## 🧪 Tests

### Vérifier l'intégrité des données
```sql
-- Vérifier les orphelins (ne devrait rien retourner)
SELECT * FROM user_sender_names usn
LEFT JOIN users u ON usn.user_id = u.id
LEFT JOIN sender_names sn ON usn.sender_name_id = sn.id
WHERE u.id IS NULL OR sn.id IS NULL;
```

### Tester l'accès d'un utilisateur
```sql
SELECT
    CASE
        WHEN EXISTS (
            SELECT 1 FROM user_sender_names
            WHERE user_id = 1 AND sender_name_id = 1
        )
        THEN 'HAS ACCESS'
        ELSE 'NO ACCESS'
    END as access_status;
```

---

## 📱 API cURL

### Récupérer les Sender Names de l'utilisateur connecté
```bash
curl -X GET http://localhost/api/sms/sender-names/user \
  -H "Cookie: PHPSESSID=YOUR_SESSION_ID" \
  -H "Content-Type: application/json"
```

### Response attendue
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "name": "TICAFRIQUE",
      "operator": "Orange CI"
    },
    {
      "id": 2,
      "name": "SUNUBANK",
      "operator": "MTN CI"
    }
  ]
}
```

---

## 🐛 Debugging

### Voir les dernières attributions
```sql
SELECT
    u.username,
    sn.name as sender_name,
    usn.assigned_at,
    ua.username as assigned_by
FROM user_sender_names usn
JOIN users u ON usn.user_id = u.id
JOIN sender_names sn ON usn.sender_name_id = sn.id
LEFT JOIN users ua ON usn.assigned_by = ua.id
ORDER BY usn.assigned_at DESC
LIMIT 10;
```

### Vérifier les doublons
```sql
SELECT user_id, sender_name_id, COUNT(*) as count
FROM user_sender_names
GROUP BY user_id, sender_name_id
HAVING count > 1;
```

---

## 💡 Tips

1. **Toujours vérifier l'accès avant l'envoi** :
   ```php
   if (!SenderName::userHasAccess($userId, $senderNameId)) {
       throw new Exception('Access denied');
   }
   ```

2. **Logger l'utilisation** pour les statistiques

3. **Valider les Sender Names** avant de les activer

4. **Backup régulier** de la table user_sender_names

5. **Index les colonnes** fréquemment utilisées

---

**Dernière mise à jour** : 3 Décembre 2025
