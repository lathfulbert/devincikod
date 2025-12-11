# Structure des Champs Module dans la Table Permissions

## Vue d'ensemble

La table `permissions` a été harmonisée pour supporter trois champs liés aux modules, permettant une meilleure organisation et gestion des permissions par module.

## Champs Module

### 1. `module` (VARCHAR(50), NULL)
**Nom lisible du module**
- Utilisé pour l'affichage dans l'interface utilisateur
- Peut contenir des espaces et des caractères spéciaux
- Exemples: `"SMS"`, `"Wallet"`, `"Email Marketing"`

### 2. `module_slug` (VARCHAR(100), NULL, INDEXED)
**Slug du module**
- Identifiant unique en kebab-case pour le module
- Utilisé pour les requêtes et le filtrage
- Exemples: `"sms-core"`, `"wallet"`, `"email-marketing"`
- **Index ajouté** pour des performances optimales lors des requêtes

### 3. `module_id` (BIGINT UNSIGNED, NULL, INDEXED)
**ID du module**
- Référence à une future table `modules` (si créée)
- Actuellement NULL pour tous les modules
- Permettra une intégration plus poussée avec un système de gestion de modules
- **Index ajouté** pour les futures jointures

## Structure Complète de la Table

```sql
CREATE TABLE permissions (
    id              BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name            VARCHAR(100) NOT NULL UNIQUE,
    slug            VARCHAR(100) NOT NULL UNIQUE,
    description     TEXT NULL,
    module          VARCHAR(50) NULL,
    module_slug     VARCHAR(100) NULL,
    module_id       BIGINT UNSIGNED NULL,
    active          TINYINT(1) NOT NULL DEFAULT 1,
    created_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    INDEX idx_module_slug (module_slug),
    INDEX idx_module_id (module_id)
);
```

## Utilisation dans les Seeders

### Exemple avec SmsPermissionsSeeder

```php
public function run()
{
    // Définir les informations du module
    $moduleInfo = [
        'module' => 'SMS',
        'module_slug' => 'sms-core',
        'module_id' => null  // NULL jusqu'à création table modules
    ];

    // Créer une permission
    $this->db->query(
        "INSERT INTO permissions (name, slug, description, module, module_slug, module_id, created_at, updated_at)
         VALUES (?, ?, ?, ?, ?, ?, NOW(), NOW())",
        [
            'sms.dashboard.view',
            'sms-dashboard-view',
            'Voir le tableau de bord SMS',
            $moduleInfo['module'],
            $moduleInfo['module_slug'],
            $moduleInfo['module_id']
        ]
    );
}
```

## Requêtes Courantes

### Récupérer toutes les permissions d'un module
```sql
SELECT * FROM permissions
WHERE module_slug = 'sms-core'
ORDER BY name;
```

### Compter les permissions par module
```sql
SELECT module, module_slug, COUNT(*) as count
FROM permissions
GROUP BY module, module_slug
ORDER BY module;
```

### Mettre à jour les informations module pour des permissions existantes
```sql
UPDATE permissions
SET module = 'SMS',
    module_slug = 'sms-core',
    updated_at = NOW()
WHERE name LIKE 'sms.%';
```

## Migration des Données Existantes

La migration `002_add_module_fields_to_permissions.php` effectue automatiquement:

1. **Ajout des colonnes** `module_slug` et `module_id`
2. **Création des index** pour optimiser les performances
3. **Migration automatique** des valeurs `module` vers `module_slug`:
   - Conversion en minuscules
   - Remplacement des espaces par des tirets

```sql
UPDATE permissions
SET module_slug = LOWER(REPLACE(module, ' ', '-'))
WHERE module IS NOT NULL;
```

## Modules Actuellement Enregistrés

Au moment de cette documentation:

| Module | Module Slug | Nombre de Permissions |
|--------|-------------|----------------------|
| SMS | sms-core | 24 |
| users | users | 5 |
| roles | roles | 5 |
| permissions | permissions | 5 |
| system | system | 4 |
| content | content | 7 |

## Bonnes Pratiques

1. **Cohérence de Nommage**
   - Toujours utiliser le même `module_slug` pour un module donné
   - Format recommandé: kebab-case (ex: `email-marketing`, `sms-core`)

2. **Convention de Nommage des Permissions**
   - Format: `{module}.{resource}.{action}`
   - Exemples: `sms.campaigns.create`, `wallet.transactions.view`

3. **Slugs de Permissions**
   - Générer automatiquement à partir du nom
   - Remplacer les points par des tirets
   - Exemple: `sms.campaigns.create` → `sms-campaigns-create`

4. **Gestion des Mises à Jour**
   - Utiliser `COALESCE()` pour ne pas écraser les valeurs existantes
   - Toujours mettre à jour `updated_at` lors des modifications

## Évolution Future

La structure actuelle permet:

- **Table Modules**: Création future d'une table `modules` avec métadonnées complètes
- **Foreign Key**: Ajout d'une contrainte FK sur `module_id` vers `modules.id`
- **Activation/Désactivation**: Gestion de modules actifs/inactifs en cascade
- **Versioning**: Suivi des versions de modules et compatibilité des permissions

## Rollback

Pour annuler la migration:

```sql
ALTER TABLE permissions DROP COLUMN module_id;
ALTER TABLE permissions DROP COLUMN module_slug;
```

Ou via la migration:
```php
$migration = require 'Core/Database/Migrations/002_add_module_fields_to_permissions.php';
$migration->down();
```
