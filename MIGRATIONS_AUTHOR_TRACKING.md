# 📋 Migrations Author Tracking - Guide Complet

## 🎯 Objectif

Intégrer les colonnes de tracking des auteurs (`created_by`, `updated_by`, `deleted_by`) directement dans les migrations de chaque module pour une installation cohérente.

---

## ✅ État actuel

### Migrations mises à jour

#### Module SmsCore ✅
- `001_create_sms_messages_table.php` - ✅ Ajouté created_by, updated_by
- `003_create_sms_campaigns_table.php` - ✅ Ajouté updated_by (created_by existait déjà)
- `001_create_sender_names_table.php` - ✅ Ajouté updated_by, deleted_at, deleted_by

#### Module Wallet ✅
- `001_create_wallets_table.php` - ✅ Ajouté created_by, updated_by
- `002_create_wallet_transactions_table.php` - ✅ Ajouté created_by

---

## 📦 Pour les nouvelles installations

### Les colonnes sont créées automatiquement

Lorsque vous exécutez `php public/index.php migrate` sur une nouvelle installation, les tables seront créées avec les colonnes author tracking incluses.

**Aucune action supplémentaire nécessaire !** ✅

---

## 🔄 Pour les installations existantes

### Option 1 : Migration globale (Recommandée)

Si votre base de données existe déjà, utilisez la migration globale :

```bash
php public/index.php migrate
```

Cette commande exécutera automatiquement la migration :
`Core/Database/Migrations/001_add_author_tracking_columns.php`

Cette migration ajoutera les colonnes aux tables existantes qui n'en ont pas.

### Option 2 : Migration manuelle

Si vous préférez contrôler manuellement, ajoutez les colonnes table par table :

```sql
-- Pour une table standard
ALTER TABLE nom_table
ADD COLUMN created_by INT UNSIGNED NULL AFTER created_at,
ADD COLUMN updated_by INT UNSIGNED NULL AFTER updated_at,
ADD INDEX idx_nom_table_created_by (created_by),
ADD INDEX idx_nom_table_updated_by (updated_by);

-- Pour une table avec soft delete
ALTER TABLE nom_table
ADD COLUMN deleted_by INT UNSIGNED NULL AFTER deleted_at,
ADD INDEX idx_nom_table_deleted_by (deleted_by);
```

---

## 📊 Liste complète des migrations

### Modules avec migrations mises à jour

| Module | Migration | Colonnes ajoutées | Statut |
|--------|-----------|-------------------|---------|
| **SmsCore** | 001_create_sms_messages_table.php | created_by, updated_by | ✅ |
| **SmsCore** | 003_create_sms_campaigns_table.php | updated_by | ✅ |
| **SmsCore** | 001_create_sender_names_table.php | updated_by, deleted_at, deleted_by | ✅ |
| **Wallet** | 001_create_wallets_table.php | created_by, updated_by | ✅ |
| **Wallet** | 002_create_wallet_transactions_table.php | created_by | ✅ |

### Modules à mettre à jour manuellement (si besoin)

Pour ces modules, la migration globale `001_add_author_tracking_columns.php` ajoutera les colonnes automatiquement lors du premier `php public/index.php migrate`.

| Module | Tables concernées |
|--------|-------------------|
| **Settings** | settings, sms_gateways, wallet_gateways, translations, webhooks |
| **RBAC** | roles, permissions, modules |
| **EmailMarketing** | email_campaigns, email_templates, workflows |
| **Contacts** | contacts, contact_field_definitions |
| **ApiKeys** | api_keys, api_request_logs |
| **Notifications** | notifications, notification_templates, delivery_logs |

---

## 🚀 Processus de migration complet

### Pour une installation existante :

```bash
# 1. Faire un backup de la base de données
mysqldump -u root -p your_database > backup_$(date +%Y%m%d_%H%M%S).sql

# 2. Exécuter les migrations
cd c:\laragon\www\sunuframework2
php public/index.php migrate

# 3. Tester le système
php Core/Database/test_author_tracking.php

# 4. Vérifier les colonnes
mysql -u root -p your_database -e "SHOW COLUMNS FROM sms_messages LIKE '%_by';"
```

### Pour une nouvelle installation :

```bash
# 1. Installer la base de données
php public/index.php migrate

# 2. Tout est déjà configuré ! ✅
```

---

## 🔍 Vérification

### Vérifier qu'une table a les colonnes :

```bash
# Via MySQL
SHOW COLUMNS FROM sms_messages LIKE '%_by';

# Via script PHP
php -r "
\$db = new PDO('mysql:host=localhost;dbname=your_db', 'root', '');
\$stmt = \$db->query(\"SHOW COLUMNS FROM sms_messages LIKE '%_by'\");
\$columns = \$stmt->fetchAll(PDO::FETCH_COLUMN);
print_r(\$columns);
"
```

### Résultat attendu :

```
Array
(
    [0] => created_by
    [1] => updated_by
)
```

---

## 📝 Structure des colonnes

### Colonnes standard :

```sql
created_by INT UNSIGNED NULL COMMENT 'User who created the record'
updated_by INT UNSIGNED NULL COMMENT 'User who last updated the record'
```

### Colonnes soft delete (en plus) :

```sql
deleted_at TIMESTAMP NULL COMMENT 'Soft delete timestamp'
deleted_by INT UNSIGNED NULL COMMENT 'User who deleted the record'
```

### Index pour performance :

```sql
INDEX idx_table_created_by (created_by)
INDEX idx_table_updated_by (updated_by)
INDEX idx_table_deleted_by (deleted_by)  -- Si soft delete
```

---

## ⚠️ Important

### Pour les développeurs

1. **Nouvelles migrations** : Incluez toujours les colonnes author tracking dans vos nouvelles migrations

```php
// Dans votre migration
$table->timestamps();

// Author tracking
$table->unsignedInteger('created_by')->nullable();
$table->unsignedInteger('updated_by')->nullable();

// Si soft delete
$table->softDeletes(); // deleted_at
$table->unsignedInteger('deleted_by')->nullable();

// Index
$table->index('created_by');
$table->index('updated_by');
$table->index('deleted_by'); // Si soft delete
```

2. **Modèles** : N'oubliez pas d'ajouter le trait

```php
use App\Core\Database\Traits\HasAuthor;

class YourModel extends Model
{
    use HasAuthor;
}
```

---

## 🎯 Cas d'usage

### Scénario 1 : Installation fraîche

```bash
# Cloner le repo
git clone ...
cd sunuframework2

# Installer les dépendances
composer install

# Configurer .env
cp .env.example .env

# Exécuter les migrations
php public/index.php migrate

# ✅ Les colonnes author tracking sont créées automatiquement
```

### Scénario 2 : Mise à jour d'une installation existante

```bash
# Pull la dernière version
git pull origin main

# Exécuter les migrations
php public/index.php migrate

# ✅ La migration 001_add_author_tracking_columns.php ajoutera les colonnes manquantes
```

### Scénario 3 : Migration module par module

```bash
# Migrer un module spécifique
php public/index.php migrate --module=SmsCore

# Vérifier
php Core/Database/test_author_tracking.php
```

---

## 📚 Documentation associée

- [AUTHOR_TRACKING_IMPLEMENTATION.md](AUTHOR_TRACKING_IMPLEMENTATION.md) - Guide de déploiement complet
- [README_AUTHOR_TRACKING.md](README_AUTHOR_TRACKING.md) - Vue d'ensemble du système
- [Core/Database/AUTHOR_TRACKING.md](Core/Database/AUTHOR_TRACKING.md) - Documentation technique
- [QUICK_COMMANDS_AUTHOR_TRACKING.md](QUICK_COMMANDS_AUTHOR_TRACKING.md) - Commandes rapides

---

## 🐛 Dépannage

### Erreur "Column 'created_by' not found"

**Solution** : Exécutez la migration globale

```bash
php public/index.php migrate
```

### Erreur "Duplicate column name 'created_by'"

**Cause** : La colonne existe déjà

**Solution** : Aucune action nécessaire, c'est normal.

### Les colonnes ne se remplissent pas automatiquement

**Vérifications** :
1. Le trait `HasAuthor` est ajouté au modèle
2. Un utilisateur est connecté (`$_SESSION['user']['id']`)
3. Les colonnes existent dans la table

---

## ✅ Checklist de migration

Pour une installation existante :

- [ ] Backup de la base de données effectué
- [ ] Migrations exécutées (`php public/index.php migrate`)
- [ ] Tests réussis (`php Core/Database/test_author_tracking.php`)
- [ ] Vérification des colonnes en base de données
- [ ] Traits ajoutés aux modèles
- [ ] Tests manuels sur quelques enregistrements
- [ ] Documentation lue

Pour une nouvelle installation :

- [ ] Migrations exécutées (`php public/index.php migrate`)
- [ ] Tests réussis (`php Core/Database/test_author_tracking.php`)
- [ ] Documentation lue

---

## 🎉 Conclusion

Le système de tracking des auteurs est maintenant intégré à deux niveaux :

1. **Migrations de modules** - Les nouvelles installations auront les colonnes dès le départ
2. **Migration globale** - Les installations existantes peuvent ajouter les colonnes facilement

**Le système s'adapte à toutes les situations ! 🚀**

Pour toute question, consultez la documentation complète ou exécutez les scripts de test.
