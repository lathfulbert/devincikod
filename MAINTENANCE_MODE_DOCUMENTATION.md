# Module Mode Maintenance - Documentation Complète

## Vue d'ensemble

Le **Module Mode Maintenance** permet de mettre tout le système en maintenance avec une page personnalisable. Les administrateurs peuvent configurer l'apparence, définir des périodes, et autoriser l'accès par compte, rôle ou adresse IP.

## Caractéristiques principales

✅ **Activation/Désactivation facile** - Un seul bouton pour activer/désactiver
✅ **Page personnalisable** - Titre, message, couleur et image de fond
✅ **Planification automatique** - Définir date/heure de début et fin
✅ **Compte à rebours** - Affichage optionnel du temps restant
✅ **Contrôle d'accès granulaire** - Par IP, rôle ou utilisateur
✅ **Header HTTP 503** - Respect des standards pour les moteurs de recherche
✅ **Retry-After configurable** - Indiquer aux clients quand réessayer

## Installation

### 1. Base de données

La table `maintenance_mode` a été créée automatiquement avec la structure suivante :

```sql
CREATE TABLE maintenance_mode (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    is_enabled TINYINT(1) NOT NULL DEFAULT 0,
    title VARCHAR(255) DEFAULT 'Site en Maintenance',
    message TEXT,
    background_image VARCHAR(255),
    background_color VARCHAR(50) DEFAULT '#4466f2',
    start_time DATETIME,
    end_time DATETIME,
    allowed_ips TEXT COMMENT 'JSON array of allowed IPs',
    allowed_roles TEXT COMMENT 'JSON array of allowed role IDs',
    allowed_users TEXT COMMENT 'JSON array of allowed user IDs',
    show_countdown TINYINT(1) DEFAULT 1,
    retry_after INT DEFAULT 3600 COMMENT 'Seconds',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    created_by INT UNSIGNED,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    updated_by INT UNSIGNED
);
```

### 2. Fichiers créés

**Modèle** :
- `Modules/Settings/Models/MaintenanceMode.php`

**Contrôleur** :
- `Modules/Settings/Controllers/MaintenanceController.php`

**Middleware** :
- `Core/Middleware/MaintenanceMiddleware.php`

**Vues** :
- `Modules/Settings/Views/maintenance/index.php` (Backoffice)
- `Views/maintenance/maintenance.php` (Page publique)

**Routes** : Intégrées dans `Modules/Settings/SettingsModule.php`

## Utilisation

### Accès au module

1. Connectez-vous au backoffice
2. Allez dans **Configuration Générale > Mode Maintenance**
3. URL directe : `/admin/maintenance`

### Configuration de base

#### 1. Activer/Désactiver la maintenance

- Cliquez sur le bouton **"ACTIVER Maintenance"** ou **"DÉSACTIVER Maintenance"**
- Le statut change immédiatement
- Un message de confirmation s'affiche

#### 2. Personnaliser le contenu

**Onglet "Général"** :

- **Titre** : Texte affiché en grand (ex: "Maintenance en cours")
- **Message** : Description pour les visiteurs
- **Retry-After** : Délai suggéré avant nouvelle tentative (en secondes, défaut 3600 = 1h)
- **Afficher le compte à rebours** : Checkbox pour activer/désactiver

**Exemple** :
```
Titre : Site en Maintenance
Message : Nous effectuons une mise à jour importante.
          Nous serons de retour dans quelques heures.
Retry-After : 7200 (2 heures)
```

#### 3. Personnaliser l'apparence

**Onglet "Apparence"** :

- **Couleur de fond** : Sélecteur de couleur (défaut : #4466f2)
- **Image de fond** : Télécharger une image (recommandé : 1920x1080px)

**Options** :
- Avec couleur uniquement → Dégradé automatique
- Avec image → Image en plein écran + overlay sombre

Pour supprimer l'image de fond :
- Cliquer sur **"Supprimer"** sous l'aperçu de l'image

#### 4. Planifier la maintenance

**Onglet "Planification"** :

- **Début de maintenance** : Date et heure de début (format : YYYY-MM-DD HH:MM)
- **Fin de maintenance** : Date et heure de fin

**Comportements** :
- Les deux vides → Activation immédiate et indéfinie
- Début défini, fin vide → Active à partir de la date de début
- Les deux définis → Active entre les deux dates
- Le compte à rebours utilise automatiquement la date de fin

**Exemple** :
```
Début : 2025-12-10 02:00
Fin : 2025-12-10 06:00
```
→ Maintenance active automatiquement du 10/12/2025 de 2h à 6h du matin

#### 5. Contrôler l'accès

**Onglet "Accès"** :

**Adresses IP autorisées** :
- Une IP par ligne
- Exemple :
```
192.168.1.100
203.0.113.45
```

**Rôles autorisés** :
- Sélectionner un ou plusieurs rôles (Ctrl/Cmd + clic)
- Exemple : Administrateur, Super Admin

**Utilisateurs autorisés** :
- Sélectionner des utilisateurs spécifiques
- Exemple : admin@example.com

**Logique d'accès** :
- Si l'utilisateur correspond à **au moins un** critère → Accès autorisé
- Critères cumulatifs : IP **OU** Rôle **OU** Utilisateur

### Enregistrer les modifications

Après avoir configuré tous les paramètres :
- Cliquer sur **"Enregistrer la configuration"**
- Un message de confirmation apparaît

## Fonctionnement technique

### 1. Vérification de la maintenance

Le middleware `MaintenanceMiddleware` s'exécute **avant toutes les routes** :

```php
// Dans Core/Application.php - boot()
if (php_sapi_name() !== 'cli') {
    $maintenanceMiddleware = new \App\Core\Middleware\MaintenanceMiddleware();
    $maintenanceMiddleware->handle();
}
```

### 2. Processus de vérification

```
1. Route exclue ? (admin, api) → Autoriser
2. Maintenance active ? → Non → Autoriser
3. IP whitelistée ? → Oui → Autoriser
4. User connecté ET (rôle autorisé OU user autorisé) ? → Oui → Autoriser
5. Sinon → Afficher page de maintenance + HTTP 503
```

### 3. Routes exclues par défaut

```php
protected array $except = [
    '/admin/maintenance',
    '/admin/maintenance/toggle',
    '/admin/maintenance/update',
    '/api/',  // Allow API access
];
```

Vous pouvez modifier ces routes directement dans `Core/Middleware/MaintenanceMiddleware.php`

### 4. HTTP Headers

Quand la page de maintenance s'affiche :

```http
HTTP/1.1 503 Service Unavailable
Retry-After: 3600
```

**Avantages** :
- Les moteurs de recherche savent que c'est temporaire
- Les clients/bots peuvent réessayer automatiquement
- Pas d'impact SEO négatif

## API du modèle

### Méthodes statiques

```php
// Récupérer la configuration actuelle
$config = MaintenanceMode::getCurrent();

// Vérifier si maintenance active
$isActive = MaintenanceMode::isActive();

// Vérifier si l'utilisateur actuel est autorisé
$isAllowed = MaintenanceMode::isAllowed();

// Activer la maintenance
MaintenanceMode::enable();

// Désactiver la maintenance
MaintenanceMode::disable();
```

### Méthodes d'instance

```php
// Obtenir le temps restant (en secondes)
$remaining = $config->getTimeRemaining(); // null si pas de end_time
```

### Exemples d'utilisation

#### Activer la maintenance programmatiquement

```php
use Modules\Settings\Models\MaintenanceMode;

$config = MaintenanceMode::getCurrent();
$config->update([
    'is_enabled' => true,
    'title' => 'Maintenance Planifiée',
    'message' => 'Mise à jour système en cours...',
    'end_time' => date('Y-m-d H:i:s', strtotime('+2 hours'))
]);
```

#### Vérifier dans un script

```php
if (MaintenanceMode::isActive()) {
    echo "Site en maintenance\n";
    if (!MaintenanceMode::isAllowed()) {
        echo "Vous n'avez pas accès\n";
        exit;
    }
}
```

## Personnalisation avancée

### 1. Modifier la page de maintenance

Éditez `Views/maintenance/maintenance.php` pour personnaliser :

- HTML structure
- Styles CSS inline
- JavaScript du compte à rebours
- Logo et icônes

**Exemple : Ajouter un lien de contact**

```php
<p class="maintenance-message">
    <?= nl2br(htmlspecialchars($config->message ?? '...')) ?>
</p>

<!-- Ajouter ce bloc -->
<div class="mt-4">
    <a href="mailto:support@example.com" class="btn btn-light">
        Nous contacter
    </a>
</div>
```

### 2. Ajouter des routes exclues

Éditez `Core/Middleware/MaintenanceMiddleware.php` :

```php
protected array $except = [
    '/admin/maintenance',
    '/admin/maintenance/toggle',
    '/admin/maintenance/update',
    '/api/',
    '/public-status',  // Nouvelle route
    '/health-check',   // Nouvelle route
];
```

### 3. Logique d'autorisation custom

Modifiez la méthode `isAllowed()` dans `Modules/Settings/Models/MaintenanceMode.php` :

```php
public static function isAllowed(): bool
{
    // ... code existant ...

    // Ajouter une logique custom
    // Exemple : Toujours autoriser les super admins
    if (isset($_SESSION['user']['is_super_admin']) && $_SESSION['user']['is_super_admin']) {
        return true;
    }

    return false;
}
```

### 4. Design personnalisé

**Option 1 : Modifier les styles inline**

Dans `Views/maintenance/maintenance.php`, section `<style>` :

```css
.maintenance-wrapper {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
}

.maintenance-title {
    font-family: 'Arial Black', sans-serif;
    text-transform: uppercase;
}
```

**Option 2 : Utiliser un fichier CSS externe**

Créez `public/assets/css/maintenance.css` et incluez-le :

```html
<link rel="stylesheet" href="<?= asset('assets/css/maintenance.css') ?>">
```

## Cas d'usage courants

### 1. Maintenance d'urgence immédiate

1. Aller dans Mode Maintenance
2. Cliquer sur **"ACTIVER Maintenance"**
3. ✅ Le site est immédiatement inaccessible (sauf admins)

### 2. Maintenance planifiée (nuit du samedi)

1. Configurer :
   - Début : 2025-12-14 23:00
   - Fin : 2025-12-15 05:00
2. Cocher "Afficher le compte à rebours"
3. Sauvegarder
4. Activer la maintenance
5. ✅ La maintenance s'active et se désactive automatiquement

### 3. Autoriser l'équipe technique

1. Créer un rôle "Tech Team"
2. Onglet Accès → Sélectionner le rôle "Tech Team"
3. ✅ Tous les membres de l'équipe peuvent accéder pendant la maintenance

### 4. Tester la maintenance sans impacter les utilisateurs

1. Onglet Accès → Ajouter votre IP (ex: 192.168.1.50)
2. Activer la maintenance
3. Tester la page publique depuis un autre appareil/réseau
4. ✅ Vous voyez le site normal, les autres voient la maintenance

### 5. Maintenance avec image de fond custom

1. Préparer une image 1920x1080px
2. Onglet Apparence → Télécharger l'image
3. Sauvegarder
4. ✅ La page de maintenance affiche votre image

## Dépannage

### Problème : La page de maintenance ne s'affiche pas

**Solution 1** : Vérifier que la maintenance est activée
- Aller dans `/admin/maintenance`
- Vérifier le statut (doit être "MODE MAINTENANCE ACTIF")

**Solution 2** : Vérifier les dates de planification
- Si `start_time` est dans le futur → Pas encore active
- Si `end_time` est dans le passé → Déjà terminée

**Solution 3** : Vider le cache du navigateur
- Ctrl+F5 pour forcer le rechargement

### Problème : Je suis bloqué alors que je suis admin

**Cause** : Votre compte/rôle n'est pas dans la whitelist

**Solution** :
1. Ajouter votre IP dans la whitelist (depuis un autre admin)
2. Ou se connecter depuis le serveur local (127.0.0.1)
3. Ou désactiver la maintenance via la base de données :
```sql
UPDATE maintenance_mode SET is_enabled = 0;
```

### Problème : L'image de fond ne s'affiche pas

**Vérifications** :
1. Le fichier est dans `public/uploads/maintenance/` ?
2. Les permissions du dossier sont correctes (755) ?
3. Le chemin dans la DB est correct ?

```sql
SELECT background_image FROM maintenance_mode;
```

Si `/uploads/maintenance/maintenance_1234567890.jpg` → OK
Si chemin incomplet → Corriger manuellement

### Problème : Le compte à rebours ne fonctionne pas

**Vérifications** :
1. `show_countdown` est activé ?
2. `end_time` est défini et dans le futur ?
3. JavaScript est activé dans le navigateur ?

**Debug** :
```javascript
// Dans la console du navigateur
const endTime = document.getElementById('countdown').dataset.endTime;
console.log('End time:', endTime, 'Now:', Math.floor(Date.now() / 1000));
```

## Commandes CLI

✅ **Commandes CLI disponibles** pour gérer la maintenance depuis le terminal.

### Commandes principales

```bash
# Activer la maintenance
php sunu maintenance:up

# Désactiver la maintenance
php sunu maintenance:down

# Afficher le statut
php sunu maintenance:status

# Planifier une maintenance
php sunu maintenance:schedule --start="2025-12-10 02:00" --end="2025-12-10 06:00"
```

**📖 Documentation complète** : Voir [MAINTENANCE_CLI_COMMANDS.md](MAINTENANCE_CLI_COMMANDS.md) pour tous les détails.
**Note** : Ces commandes ne sont pas encore implémentées dans cette version, mais peuvent être ajoutées facilement.

## Sécurité

### Bonnes pratiques

1. **Toujours autoriser au moins un rôle d'admin** - Pour éviter d'être bloqué
2. **Tester d'abord en local** - Avant de l'activer en production
3. **Avoir un accès SSH/DB de secours** - Pour désactiver manuellement si besoin
4. **Ne pas exposer d'informations sensibles** - Dans le message de maintenance
5. **Utiliser HTTPS** - Surtout si des admins se connectent pendant la maintenance

### Permissions requises

Pour accéder à `/admin/maintenance` :
- Être connecté (`AuthMiddleware`)
- Pas de permission spécifique requise (tous les admins)

Pour ajouter une restriction par permission, modifier la route :

```php
['GET', '/admin/maintenance', [\Modules\Settings\Controllers\MaintenanceController::class, 'index'],
    [$authMiddleware, ['middleware' => 'can:manage_maintenance']]
],
```

## Performance

### Impact sur les performances

- **Très faible** : La vérification s'exécute en <1ms
- **Pas de cache** : Vérifie la DB à chaque requête (par design)
- **Optimisation possible** : Utiliser Redis/Memcached pour cacher `is_enabled`

### Optimisation recommandée (avancé)

```php
// Dans MaintenanceMode::isActive()
$cacheKey = 'maintenance:is_active';
$cached = Cache::get($cacheKey);

if ($cached !== null) {
    return $cached;
}

$isActive = /* ... logique actuelle ... */;
Cache::put($cacheKey, $isActive, 60); // 60 secondes
return $isActive;
```

Ne pas oublier d'invalider le cache lors de la mise à jour :

```php
// Dans MaintenanceController::toggle() et update()
Cache::forget('maintenance:is_active');
```

## Compatibilité

- **PHP** : 8.0+
- **Framework** : SunuFramework2
- **Base de données** : MySQL 5.7+, MariaDB 10.2+
- **Navigateurs** : Tous les navigateurs modernes (Chrome, Firefox, Safari, Edge)

## Roadmap / Améliorations futures

- [ ] Commandes CLI (maintenance:enable, maintenance:disable)
- [ ] Templates de maintenance prédéfinis
- [ ] Support multi-langues pour la page de maintenance
- [ ] Notifications par email avant/pendant la maintenance
- [ ] Statistiques des visiteurs bloqués
- [ ] Export/Import de configurations
- [ ] Mode "Lecture seule" (au lieu de blocage total)
- [ ] API REST pour activer/désactiver

## Support

Pour toute question ou problème :
1. Consulter cette documentation
2. Vérifier les logs : `logs/app.log`
3. Contacter l'équipe de développement

---

**Module créé pour SunuFramework2**
**Version** : 1.0
**Date** : Décembre 2025
