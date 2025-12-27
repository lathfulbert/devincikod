# Audit de Sécurité des Routes - RBAC

**Date** : 2025-12-01  
**Statut** : Analyse Complète  
**Niveau de Risque Global** : 🟡 MOYEN

---

## Résumé Exécutif

### Statistiques

| Catégorie                   | Nombre     | Statut                |
| --------------------------- | ---------- | --------------------- |
| **Routes analysées**        | ~62 routes | ✅                    |
| **Routes publiques**        | 7 routes   | ✅ Correct            |
| **Routes protégées (Auth)** | 55 routes  | ⚠️ Protection basique |
| **Routes avec RBAC**        | 0 routes   | 🔴 **CRITIQUE**       |
| **Routes API**              | 6 routes   | ⚠️ À sécuriser        |

### Niveaux de Risque

- 🔴 **CRITIQUE** : Aucune route n'utilise les nouveaux middlewares RBAC
- 🟡 **MOYEN** : Authentification basique présente mais permissions manquantes
- 🟢 **BON** : Routes publiques correctement identifiées

---

## 1. Routes Publiques (Correctement exposées)

### Module Auth - Routes d'Authentification

```php
✅ GET  /login                    // Page de connexion
✅ POST /login                    // Traitement connexion
✅ GET  /register                 // Page d'inscription
✅ POST /register                 // Traitement inscription
✅ GET  /forgot-password          // Demande reset mot de passe
✅ POST /forgot-password          // Envoi lien reset
✅ GET  /reset-password           // Page reset mot de passe
✅ POST /reset-password           // Mise à jour mot de passe
```

**Status** : ✅ **CONFORME** - Ces routes doivent rester publiques.

---

## 2. Routes Admin - NÉCESSITE RBAC

### 2.1 Dashboard & Monitoring

| Route                          | Middleware Actuel | Permission Recommandée   | Priorité |
| ------------------------------ | ----------------- | ------------------------ | -------- |
| `GET /admin`                   | ✅ Auth           | 🔴 `admin.access`        | HAUTE    |
| `GET /admin/dashboard`         | ✅ Auth           | 🔴 `admin.access`        | HAUTE    |
| `GET /admin/monitoring`        | ✅ Auth           | 🔴 `admin.access`        | HAUTE    |
| `POST /admin/monitoring/clear` | ✅ Auth           | 🔴 `admin.settings.edit` | HAUTE    |

**Recommandation** :

```php
// AVANT
$router->get('/admin/dashboard', [AdminController::class, 'dashboard'], [$authMiddleware]);

// APRÈS
$router->get('/admin/dashboard', [AdminController::class, 'dashboard'])
    ->middleware('can:admin.access');
```

---

### 2.2 Gestion des Modules

| Route                               | Permission Recommandée | Niveau de Risque     |
| ----------------------------------- | ---------------------- | -------------------- |
| `GET /admin/modules`                | `admin.modules.view`   | 🟡 MOYEN             |
| `POST /admin/modules/enable`        | `admin.modules.manage` | 🔴 CRITIQUE          |
| `POST /admin/modules/disable`       | `admin.modules.manage` | 🔴 CRITIQUE          |
| `POST /admin/modules/install`       | `admin.modules.manage` | 🔴 **TRÈS CRITIQUE** |
| `POST /admin/modules/uninstall`     | `admin.modules.manage` | 🔴 **TRÈS CRITIQUE** |
| `POST /admin/modules/{name}/delete` | `admin.modules.manage` | 🔴 **TRÈS CRITIQUE** |

**⚠️ ALERTE SÉCURITÉ** : Les routes d'installation/désinstallation de modules peuvent **exécuter du code arbitraire**. Protection RBAC **OBLIGATOIRE**.

**Recommandation** :

```php
$router->post('/admin/modules/install', [ModuleController::class, 'install'])
    ->middleware('can:admin.modules.manage')
    ->middleware('role:admin'); // Double vérification
```

---

### 2.3 Gestion des Utilisateurs

| Route                           | Permission Recommandée | Niveau de Risque |
| ------------------------------- | ---------------------- | ---------------- |
| `GET /admin/users`              | `admin.users.view`     | 🟡 MOYEN         |
| `GET /admin/users/create`       | `admin.users.create`   | 🟡 MOYEN         |
| `POST /admin/users/store`       | `admin.users.create`   | 🔴 HAUTE         |
| `GET /admin/users/{id}/edit`    | `admin.users.edit`     | 🟡 MOYEN         |
| `POST /admin/users/{id}/update` | `admin.users.edit`     | 🔴 HAUTE         |
| `GET /admin/users/{id}/delete`  | `admin.users.delete`   | 🔴 **CRITIQUE**  |
| `POST /admin/users/{id}/delete` | `admin.users.delete`   | 🔴 **CRITIQUE**  |

**Recommandation** :

```php
// Vue
$router->get('/admin/users', [UserController::class, 'index'])
    ->middleware('can:admin.users.view');

// Création
$router->post('/admin/users/store', [UserController::class, 'store'])
    ->middleware('can:admin.users.create');

// Suppression (double protection)
$router->post('/admin/users/{id}/delete', [UserController::class, 'delete'])
    ->middleware('can:admin.users.delete')
    ->middleware('role:admin');
```

---

### 2.4 Gestion des Rôles

| Route                           | Permission Recommandée | Niveau de Risque |
| ------------------------------- | ---------------------- | ---------------- |
| `GET /admin/roles`              | `admin.roles.view`     | 🟡 MOYEN         |
| `GET /admin/roles/create`       | `admin.roles.create`   | 🟡 MOYEN         |
| `POST /admin/roles/store`       | `admin.roles.create`   | 🔴 **CRITIQUE**  |
| `GET /admin/roles/{id}/edit`    | `admin.roles.edit`     | 🟡 MOYEN         |
| `POST /admin/roles/{id}/update` | `admin.roles.edit`     | 🔴 **CRITIQUE**  |
| `POST /admin/roles/{id}/delete` | `admin.roles.delete`   | 🔴 **CRITIQUE**  |

**⚠️** Modifier les rôles affecte directement les permissions système !

---

### 2.5 Gestion des Permissions

| Route                                 | Permission Recommandée   | Niveau de Risque     |
| ------------------------------------- | ------------------------ | -------------------- |
| `GET /admin/permissions`              | `admin.permissions.view` | 🟡 MOYEN             |
| `POST /admin/permissions/store`       | `admin.permissions.view` | 🔴 **TRÈS CRITIQUE** |
| `POST /admin/permissions/{id}/update` | `admin.permissions.view` | 🔴 **TRÈS CRITIQUE** |

**🚨 ALERTE MAXIMALE** : La modification des permissions peut créer des **escalades de privilèges**. Réserver aux **super-admins uniquement**.

**Recommandation** :

```php
$router->post('/admin/permissions/{id}/update', [PermissionController::class, 'update'])
    ->middleware('role:super-admin'); // Rôle le plus élevé uniquement
```

---

### 2.6 Gestion de la Queue

| Route                         | Permission Recommandée | Niveau de Risque |
| ----------------------------- | ---------------------- | ---------------- |
| `GET /admin/queue`            | `queue.view`           | 🟡 MOYEN         |
| `GET /admin/queue/jobs`       | `queue.view`           | 🟡 MOYEN         |
| `GET /admin/queue/failed`     | `queue.view`           | 🟡 MOYEN         |
| `POST /admin/queue/retry`     | `queue.retry`          | 🟡 MOYEN         |
| `POST /admin/queue/retry-all` | `queue.manage`         | 🔴 HAUTE         |
| `POST /admin/queue/delete`    | `queue.delete`         | 🔴 HAUTE         |

---

### 2.7 Gestion des Tâches Cron

| Route                     | Permission Recommandée | Niveau de Risque |
| ------------------------- | ---------------------- | ---------------- |
| `GET /admin/cron`         | `cron.view`            | 🟡 MOYEN         |
| `POST /admin/cron/toggle` | `cron.manage`          | 🔴 HAUTE         |
| `POST /admin/cron/run`    | `cron.execute`         | 🔴 **CRITIQUE**  |
| `GET /admin/cron/logs`    | `cron.view`            | 🟡 MOYEN         |

---

## 3. Routes Auth (Profil & API Keys)

### 3.1 Profil Utilisateur

| Route                                 | Permission Recommandée | Niveau de Risque |
| ------------------------------------- | ---------------------- | ---------------- |
| `GET /admin/profile`                  | `auth.profile.view`    | 🟢 BAS           |
| `POST /admin/profile/update`          | `auth.profile.edit`    | 🟡 MOYEN         |
| `GET /admin/profile/change-password`  | `auth.profile.edit`    | 🟡 MOYEN         |
| `POST /admin/profile/update-password` | `auth.profile.edit`    | 🟡 MOYEN         |

**Note** : Ces routes permettent à un utilisateur de modifier **son propre** profil. Protection Auth suffisante, RBAC optionnel.

### 3.2 Clés API

| Route                             | Permission Recommandée | Niveau de Risque |
| --------------------------------- | ---------------------- | ---------------- |
| `GET /admin/api-keys`             | `apikeys.view`         | 🟡 MOYEN         |
| `POST /admin/api-keys/generate`   | `apikeys.create`       | 🔴 HAUTE         |
| `POST /admin/api-keys/regenerate` | `apikeys.create`       | 🔴 HAUTE         |
| `POST /admin/api-keys/revoke`     | `apikeys.revoke`       | 🟡 MOYEN         |

**⚠️** Les clés API donnent accès programmatique à l'application. Protection RBAC **recommandée**.

---

## 4. Routes API (Notifications)

| Route                                      | Protection Actuelle | Permission Recommandée | Risque          |
| ------------------------------------------ | ------------------- | ---------------------- | --------------- |
| `GET /api/v1/notifications/{id}`           | ❌ Aucune           | `notifications.view`   | 🔴 HAUTE        |
| `POST /api/v1/notifications`               | ❌ Aucune           | `notifications.send`   | 🔴 **CRITIQUE** |
| `GET /api/v1/users/{userId}/notifications` | ❌ Aucune           | `notifications.view`   | 🔴 HAUTE        |

**🚨 ALERTE CRITIQUE** : Routes API **totalement ouvertes** ! Ajout d'API Auth et RBAC **URGENT**.

**Recommandation** :

```php
// Dans Modules/Notifications/routes/api.php
$apiAuthMiddleware = [new \App\Core\Middleware\ApiAuthMiddleware(), 'handle'];

$router->get('/api/v1/notifications/{id}', [NotificationController::class, 'show'])
    ->middleware('api_auth')
    ->middleware('can:notifications.view');
```

---

## 5. Routes Fichiers (File Manager)

| Route                      | Protection Actuelle | Permission Recommandée | Risque   |
| -------------------------- | ------------------- | ---------------------- | -------- |
| `POST /api/files/upload`   | ✅ `secure_upload`  | ⚠️ Ajouter permission  | 🟡 MOYEN |
| `GET /api/files/list`      | ✅ `secure_upload`  | ⚠️ Ajouter permission  | 🟡 MOYEN |
| `DELETE /api/files/delete` | ✅ `secure_upload`  | ⚠️ Ajouter permission  | 🔴 HAUTE |

**Recommandation** : Ajouter des permissions spécifiques au file manager.

---

## 6. Plan d'Action Prioritaire

### 🔴 URGENT (Faire immédiatement)

1. **Sécuriser les routes de gestion des modules**

   ```php
   ->middleware('can:admin.modules.manage')
   ->middleware('role:admin')
   ```

2. **Sécuriser les routes de gestion des permissions**

   ```php
   ->middleware('role:super-admin')
   ```

3. **Sécuriser les API publiques**
   ```php
   ->middleware('api_auth')
   ->middleware('can:...')
   ```

### 🟡 IMPORTANT (Faire cette semaine)

4. **Sécuriser les routes utilisateurs/rôles**

   ```php
   ->middleware('can:admin.users.view')
   ```

5. **Sécuriser Queue et Cron**
   ```php
   ->middleware('can:queue.manage')
   ```

### 🟢 RECOMMANDÉ (Faire ce mois-ci)

6. **Ajouter RBAC aux routes de profil** (optionnel mais recommandé)
7. **Créer des logs d'audit** pour actions sensibles
8. **Implémenter une vérification 2FA** pour actions critiques

---

## 7. Template de Sécurisation

Voici un template à suivre pour chaque route :

```php
// LECTURE (GET) - Permission de vue
$router->get('/admin/resource', [Controller::class, 'index'])
    ->middleware('can:module.resource.view');

// CRÉATION (POST) - Permission de création
$router->post('/admin/resource/store', [Controller::class, 'store'])
    ->middleware('can:module.resource.create');

// MODIFICATION (POST) - Permission d'édition
$router->post('/admin/resource/{id}/update', [Controller::class, 'update'])
    ->middleware('can:module.resource.edit');

// SUPPRESSION (POST) - Double protection recommandée
$router->post('/admin/resource/{id}/delete', [Controller::class, 'delete'])
    ->middleware('can:module.resource.delete')
    ->middleware('role:admin'); // Optionnel mais recommandé

// ACTION CRITIQUE - Triple protection
$router->post('/admin/critical-action', [Controller::class, 'critical'])
    ->middleware('role:super-admin')
    ->middleware('can:critical.permission')
    ->middleware('require_2fa'); // Si disponible
```

---

## 8. Checklist de Vérification

Avant de considérer une route comme "sécurisée" :

- [ ] La route nécessite-t-elle une authentification ? → `AuthMiddleware`
- [ ] La route modifie-t-elle des données ? → Permission spécifique
- [ ] La route est-elle critique pour la sécurité ? → Double protection (permission + rôle)
- [ ] La route est-elle une API ? → `ApiAuthMiddleware`
- [ ] La route permet-elle l'upload de fichiers ? → `SecureUploadMiddleware`
- [ ] L'action est-elle irréversible (suppression, etc.) ? → Confirmation + Logs

---

## 9. Prochaines Étapes

1. ✅ **Permissions définies** (70 permissions chargées)
2. 🔄 **Audit des routes** (Ce document)
3. ⏳ **Application des middlewares RBAC** (À faire)
4. ⏳ **Tests de sécurité** (Vérifier que ça fonctionne)
5. ⏳ **Documentation développeur** (Guide pour futures routes)

---

**Conclusion** : Le système RBAC est prêt, mais **aucune route ne l'utilise encore**. L'application des middlewares est la priorité absolue pour sécuriser l'application.

**Estimation du travail** : 4-6 heures pour appliquer tous les middlewares recommandés.
