# Système de Contrôle d'Accès aux Modules (RBAC)

## Vue d'ensemble

Ce système implémente un contrôle d'accès au niveau des modules dans le framework. Chaque module nécessite une permission `access.<module_key>` pour être accessible. Sans cette permission, le module et tous ses sous-services sont automatiquement masqués et leurs routes/API renvoient une erreur 403 Forbidden.

## Fonctionnalités

### 1. Permission automatique par module

Chaque module déclare automatiquement une permission `access.<module_key>` lors de son activation. Cette permission est :
- Générée automatiquement à partir du nom du module
- Enregistrée dans la base de données lors de l'activation du module
- Vérifiée avant tout accès au module

**Format de la permission :** `access.<module_key>`

**Exemples :**
- Module `RBAC` → Permission `access.rbac`
- Module `SmsCore` → Permission `access.sms_core`
- Module `EmailMarketing` → Permission `access.email_marketing`

### 2. Filtrage automatique du menu

Le `SidebarService` filtre automatiquement les modules selon les permissions de l'utilisateur :
- Les modules sans permission `access.<module_key>` ne sont pas affichés dans le menu
- Les sous-menus sont également filtrés selon leurs permissions spécifiques

### 3. Protection des routes

Toutes les routes des modules sont automatiquement protégées par le middleware `ModuleAccessMiddleware` :
- Les routes web sont enveloppées dans un groupe avec le middleware `module_access:<module_key>`
- Les routes API sont également protégées
- Accès direct via URL → 403 Forbidden si permission manquante

### 4. API Frontend

Endpoint disponible pour le frontend React/Dashboard :

```
GET /api/auth/me/permissions
```

**Réponse :**
```json
{
  "success": true,
  "data": {
    "permissions": [
      "access.rbac",
      "access.sms_core",
      "admin.users.view",
      ...
    ],
    "accessible_modules": [
      "rbac",
      "sms_core",
      "wallet"
    ],
    "user": {
      "id": 1,
      "username": "admin",
      "email": "admin@example.com"
    }
  }
}
```

## Utilisation

### Pour les développeurs de modules

Aucune action supplémentaire n'est requise ! Le système fonctionne automatiquement :

1. **Activation du module** : La permission `access.<module_key>` est automatiquement créée
2. **Routes** : Les routes sont automatiquement protégées
3. **Menu** : Le menu est automatiquement filtré

### Pour les administrateurs

1. **Attribuer les permissions** :
   - Aller dans `/admin/roles`
   - Sélectionner un rôle
   - Attribuer la permission `access.<module_key>` pour chaque module accessible

2. **Vérifier les permissions** :
   - Utiliser l'endpoint `/api/auth/me/permissions` pour voir les permissions d'un utilisateur
   - Vérifier dans la base de données la table `permissions` et `role_permissions`

### Pour le frontend React

```javascript
// Récupérer les permissions utilisateur
const response = await fetch('/api/auth/me/permissions', {
  headers: {
    'Authorization': `Bearer ${token}`
  }
});

const { data } = await response.json();

// Filtrer les modules accessibles
const accessibleModules = data.accessible_modules;

// Vérifier si un module est accessible
if (accessibleModules.includes('rbac')) {
  // Afficher le module RBAC
}

// Vérifier une permission spécifique
if (data.permissions.includes('admin.users.view')) {
  // Afficher le bouton "Voir les utilisateurs"
}
```

## Architecture technique

### Fichiers modifiés/créés

1. **Core/Module/AbstractModule.php**
   - Ajout de `getModuleKey()` pour normaliser le nom du module
   - Modification de `getPermissions()` pour inclure automatiquement `access.<module_key>`

2. **Core/Module/ModuleActivator.php**
   - Modification de `registerPermissions()` pour enregistrer les permissions avec slug
   - Ajout de `getModuleKey()` helper

3. **Core/View/SidebarService.php**
   - Ajout du filtrage par permissions dans `getItems()`
   - Ajout de `filterMenuItemsByPermissions()` pour filtrer les sous-menus

4. **Core/Application.php**
   - Enregistrement du middleware `module_access`
   - Application automatique du middleware aux routes des modules
   - Ajout de helpers `getModuleKey()` et `applyModuleAccessMiddleware()`

5. **Core/Module/Middleware/ModuleAccessMiddleware.php**
   - Amélioration pour fonctionner comme middleware de route standard
   - Compatible avec le système de middleware du Router

6. **Modules/Auth/Controllers/AuthApiController.php** (nouveau)
   - Endpoint `/api/auth/me/permissions`
   - Endpoint `/api/auth/me`

7. **Modules/Auth/Routes/api.php** (nouveau)
   - Routes API pour les permissions utilisateur

## Règles d'accès

### Règle principale

> **Un module n'est visible ni accessible que si l'utilisateur possède la permission `access.<module>`.**

### Hiérarchie des vérifications

1. **Niveau Module** : Vérification de `access.<module_key>`
   - Si non autorisé → Module complètement masqué
   - Si autorisé → Vérification des permissions spécifiques

2. **Niveau Service/Sous-menu** : Vérification des permissions spécifiques
   - Exemple : `admin.users.view`, `admin.roles.edit`, etc.

### Comportement

- **Sans permission `access.<module>`** :
  - ❌ Module non affiché dans le menu
  - ❌ Routes du module → 403 Forbidden
  - ❌ API du module → 403 Forbidden
  - ❌ Widgets du module → Non chargés

- **Avec permission `access.<module>`** :
  - ✅ Module affiché dans le menu
  - ✅ Routes du module accessibles (sous réserve des permissions spécifiques)
  - ✅ API du module accessibles
  - ✅ Widgets du module chargés

## Exemples concrets

### Exemple 1 : Module RBAC

**Permission requise :** `access.rbac`

**Sans permission :**
- Menu "RBAC" non affiché
- `/admin/roles` → 403 Forbidden
- `/admin/permissions` → 403 Forbidden

**Avec permission :**
- Menu "RBAC" affiché
- Accès aux routes selon les permissions spécifiques (`admin.roles.view`, etc.)

### Exemple 2 : Module SMS

**Permission requise :** `access.sms_core`

**Sans permission :**
- Module SMS non affiché
- `/api/v1/sms/send` → 403 Forbidden
- Widgets SMS non chargés

**Avec permission :**
- Module SMS affiché
- Accès aux fonctionnalités SMS selon les permissions spécifiques

## Migration depuis l'ancien système

Si vous avez des modules existants :

### Option 1 : Script automatique (Recommandé)

Exécutez le script d'application des permissions :

```bash
# Créer les permissions pour tous les modules
php apply_module_access_permissions.php

# Créer les permissions ET les attribuer au rôle admin
php apply_module_access_permissions.php --assign-to-admin
```

Ce script :
- ✅ Découvre automatiquement tous les modules
- ✅ Crée les permissions `access.<module_key>` manquantes
- ✅ Peut attribuer automatiquement les permissions au rôle admin
- ✅ Gère correctement les acronymes (AI → `access.ai`, RBAC → `access.rbac`)

### Option 2 : Activation manuelle

1. **Réactiver les modules** pour créer automatiquement les permissions `access.<module_key>`
2. **Attribuer les permissions** aux rôles appropriés via `/admin/roles`
3. **Tester** que les modules s'affichent correctement dans le menu

## Dépannage

### Module non affiché dans le menu

1. Vérifier que la permission `access.<module_key>` existe dans la base de données
2. Vérifier que le rôle de l'utilisateur a cette permission
3. Vérifier avec `/api/auth/me/permissions` que l'utilisateur a bien la permission

### Route retourne 403

1. Vérifier que l'utilisateur a la permission `access.<module_key>`
2. Vérifier les logs pour voir quelle permission est requise
3. Vérifier que le middleware est bien appliqué (voir `Application.php`)

### Permission non créée automatiquement

1. Vérifier que le module hérite de `AbstractModule`
2. Réactiver le module pour forcer la création de la permission
3. Vérifier les logs pour voir les erreurs éventuelles

## Notes importantes

- Les permissions sont créées avec le format `slug` dans la base de données
- Le nom du module est normalisé (PascalCase → snake_case) pour créer la clé
- Le système est rétrocompatible : les modules existants continueront de fonctionner
- Les permissions spécifiques des modules (ex: `admin.users.view`) continuent de fonctionner comme avant

