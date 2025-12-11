# Validation des Dates Programmées pour les Campagnes SMS

## Vue d'ensemble

Le système de campagnes SMS empêche maintenant la création ou la modification de campagnes avec des dates et heures passées (antidatées). Cette fonctionnalité garantit que toutes les campagnes programmées seront envoyées dans le futur.

## Fonctionnalités implémentées

### 1. Champ de planification

Un nouveau champ optionnel "Programmer l'envoi" a été ajouté aux formulaires de création et d'édition de campagnes :

- **Type de champ** : `datetime-local` (date + heure)
- **Optionnel** : Si vide, la campagne est envoyée immédiatement
- **Format** : YYYY-MM-DD HH:MM (ex: 2025-12-15 14:30)

### 2. Validations côté client (JavaScript)

#### Définition du minimum
```javascript
// Définit la date/heure minimale sélectionnable
const now = new Date();
now.setMinutes(now.getMinutes() + 5); // Buffer de 5 minutes
scheduledInput.min = now.toISOString().slice(0, 16);
```

#### Validation en temps réel
- **Événement** : Déclenché au changement de valeur (`change`)
- **Vérification** : Compare la date sélectionnée avec l'heure actuelle
- **Feedback visuel** :
  - Champ bordé en rouge (`is-invalid`)
  - Message d'erreur affiché sous le champ
  - Validation HTML5 (`setCustomValidity`)

#### Validation à la soumission
```javascript
document.getElementById('campaignForm').addEventListener('submit', function(e) {
    if (scheduledInput.value) {
        const selectedDate = new Date(scheduledInput.value);
        const now = new Date();

        if (selectedDate <= now) {
            e.preventDefault();
            alert('La date et l\'heure programmées doivent être dans le futur');
            return false;
        }
    }
});
```

### 3. Validations côté serveur (PHP)

#### Dans `SmsCampaignController@store()`

```php
// Validate scheduled date if provided
$scheduledAt = null;
if (!empty($_POST['scheduled_at'])) {
    $scheduledAt = sanitize($_POST['scheduled_at'], 'string');
    $scheduledTimestamp = strtotime($scheduledAt);

    // Verify the date is in the future
    if ($scheduledTimestamp <= time()) {
        flash('error', 'La date et l\'heure programmées doivent être dans le futur');
        redirect('/admin/sms/campaigns/create');
        return;
    }
}
```

#### Dans `SmsCampaignController@update()`

```php
// Validate scheduled date if provided
$scheduledAt = null;
if (!empty($_POST['scheduled_at'])) {
    $scheduledAt = sanitize($_POST['scheduled_at'], 'string');
    $scheduledTimestamp = strtotime($scheduledAt);

    // Verify the date is in the future
    if ($scheduledTimestamp <= time()) {
        flash('error', 'La date et l\'heure programmées doivent être dans le futur');
        redirect('/admin/sms/campaigns/' . $id . '/edit');
        return;
    }
}
```

### 4. Gestion du statut de campagne

Le statut de la campagne est automatiquement défini en fonction de la planification :

```php
// À la création
$campaign->status = $scheduledAt ? 'scheduled' : 'pending';

// À la mise à jour
if ($scheduledAt && $campaign->status === 'pending') {
    $campaign->status = 'scheduled';
} elseif (!$scheduledAt && $campaign->status === 'scheduled') {
    $campaign->status = 'pending';
}
```

**Statuts possibles :**
- `pending` : Campagne non programmée, prête à être envoyée
- `scheduled` : Campagne programmée pour une date/heure future
- `sending` : Envoi en cours
- `completed` : Envoi terminé
- `failed` : Échec de l'envoi
- `draft` : Brouillon

## Interface utilisateur

### Formulaire de création (`create.php`)

```html
<div class="mb-3">
    <label for="scheduled_at" class="form-label">
        Programmer l'envoi (optionnel)
    </label>
    <input type="datetime-local" name="scheduled_at" id="scheduled_at"
        class="form-control"
        value="<?= old('scheduled_at') ?>">
    <small class="form-text text-muted">
        Laissez vide pour envoyer immédiatement. La date et l'heure doivent être dans le futur.
    </small>
    <div id="scheduledDateError" class="text-danger small" style="display: none;">
        La date et l'heure doivent être dans le futur.
    </div>
</div>
```

### Formulaire d'édition (`edit.php`)

```html
<div class="mb-3">
    <label for="scheduled_at" class="form-label">
        Programmer l'envoi (optionnel)
    </label>
    <?php
    // Format scheduled_at for datetime-local input (YYYY-MM-DDTHH:MM)
    $scheduledValue = '';
    if (!empty($campaign->scheduled_at)) {
        $scheduledValue = date('Y-m-d\TH:i', strtotime($campaign->scheduled_at));
    }
    ?>
    <input type="datetime-local" name="scheduled_at" id="scheduled_at"
        class="form-control"
        value="<?= htmlspecialchars($scheduledValue) ?>">
    <small class="form-text text-muted">
        Laissez vide pour envoyer immédiatement. La date et l'heure doivent être dans le futur.
    </small>
    <div id="scheduledDateError" class="text-danger small" style="display: none;">
        La date et l'heure doivent être dans le futur.
    </div>
</div>
```

## Messages utilisateur

### Message de succès à la création

```php
$successMessage = 'Campagne créée avec succès ! ' . count($contactIds) . ' messages en attente.';
if ($scheduledAt) {
    $successMessage .= ' Envoi programmé le ' . date('d/m/Y à H:i', strtotime($scheduledAt)) . '.';
}
flash('success', $successMessage);
```

**Exemples :**
- Sans planification : *"Campagne créée avec succès ! 50 messages en attente."*
- Avec planification : *"Campagne créée avec succès ! 50 messages en attente. Envoi programmé le 15/12/2025 à 14:30."*

### Messages d'erreur

- **Validation client** : `"La date et l'heure programmées doivent être dans le futur"`
- **Validation serveur** : `"La date et l'heure programmées doivent être dans le futur"`

## Flux utilisateur

### Création d'une campagne programmée

```
1. Utilisateur accède à /admin/sms/campaigns/create
2. Remplit le formulaire (nom, message, contacts)
3. Clique sur le champ "Programmer l'envoi"
4. Navigateur affiche un sélecteur de date/heure
   - Dates passées sont désactivées (grâce à min="...")
5. Sélectionne une date/heure future (ex: 15/12/2025 14:30)
6. Validation JavaScript en temps réel :
   - ✓ Si date > maintenant → Pas d'erreur
   - ✗ Si date ≤ maintenant → Message d'erreur + bordure rouge
7. Soumet le formulaire
8. Validation JavaScript à la soumission
9. Validation PHP côté serveur
10. Campagne créée avec :
    - scheduled_at = "2025-12-15 14:30:00"
    - status = "scheduled"
11. Message de succès affiché avec date de programmation
```

### Modification d'une campagne

```
1. Utilisateur accède à /admin/sms/campaigns/{id}/edit
2. Le champ "Programmer l'envoi" est pré-rempli si une date existe
3. Peut :
   - Modifier la date programmée
   - Supprimer la date (vider le champ) → envoi immédiat
   - Laisser inchangé
4. Validation identique à la création
5. À la sauvegarde :
   - Si date ajoutée : status → "scheduled"
   - Si date supprimée : status → "pending"
```

## Protection multicouche

### Couche 1 : HTML5
```html
<input type="datetime-local" min="2025-12-10T15:30">
```
- Empêche la sélection de dates antérieures au minimum
- Support navigateur moderne

### Couche 2 : JavaScript temps réel
```javascript
scheduledInput.addEventListener('change', function() {
    if (selectedDate <= now) {
        this.setCustomValidity('La date doit être dans le futur');
    }
});
```
- Validation instantanée
- Feedback visuel immédiat

### Couche 3 : JavaScript soumission
```javascript
form.addEventListener('submit', function(e) {
    if (selectedDate <= now) {
        e.preventDefault();
        alert('...');
    }
});
```
- Dernière vérification avant envoi
- Empêche les contournements

### Couche 4 : PHP serveur
```php
if ($scheduledTimestamp <= time()) {
    flash('error', '...');
    redirect('...');
    return;
}
```
- Protection ultime
- Empêche les manipulations directes (API, curl, etc.)

## Base de données

### Champ `scheduled_at`

```sql
scheduled_at datetime NULL DEFAULT NULL
```

- **Type** : `datetime`
- **NULL** : Autorisé (optionnel)
- **Valeur** : Format MySQL datetime `YYYY-MM-DD HH:MM:SS`

### Exemple de données

```sql
-- Campagne immédiate
INSERT INTO sms_campaigns (name, status, scheduled_at, ...)
VALUES ('Campagne 1', 'pending', NULL, ...);

-- Campagne programmée
INSERT INTO sms_campaigns (name, status, scheduled_at, ...)
VALUES ('Campagne 2', 'scheduled', '2025-12-15 14:30:00', ...);
```

## Affichage dans les vues

### Vue show.php (détails de campagne)

```php
<?php if ($campaign->scheduled_at): ?>
    <dt class="col-sm-6">Programmé:</dt>
    <dd class="col-sm-6"><?= date('d/m/Y H:i', strtotime($campaign->scheduled_at)) ?></dd>
<?php endif; ?>
```

### Vue index.php (liste des campagnes)

Le badge de statut affichera `scheduled` pour les campagnes programmées :

```php
$statusClass = match($campaign->status) {
    'scheduled' => 'info',    // Bleu pour programmé
    'pending' => 'secondary',
    'sending' => 'warning',
    'completed' => 'success',
    'failed' => 'danger',
};
```

## Sécurité

### Protection contre les antidatages

- ✅ **Impossible** de créer une campagne avec une date passée
- ✅ **Impossible** de modifier une campagne pour avoir une date passée
- ✅ Validation à 4 niveaux (HTML5, JS temps réel, JS soumission, PHP)
- ✅ Messages d'erreur clairs pour l'utilisateur

### Cas limites gérés

1. **Date vide** : Acceptée → envoi immédiat
2. **Date dans 1 minute** : Rejetée (buffer de 5 minutes)
3. **Changement de fuseau horaire** : PHP utilise `time()` serveur
4. **Manipulation POST directe** : Bloquée par validation serveur

## Fichiers modifiés

### Vues

1. **`Modules/SmsCore/Views/sms/campaigns/create.php`**
   - Ajout du champ `scheduled_at`
   - Validation JavaScript complète
   - Messages d'erreur

2. **`Modules/SmsCore/Views/sms/campaigns/edit.php`**
   - Ajout du champ `scheduled_at` avec pré-remplissage
   - Validation JavaScript identique à create.php
   - Format de date pour input type="datetime-local"

### Contrôleurs

3. **`Modules/SmsCore/Controllers/SmsCampaignController.php`**
   - **store()** : Validation de `scheduled_at`, définition du status
   - **update()** : Validation de `scheduled_at`, mise à jour du status
   - Messages de succès incluant la date programmée

### Modèle

Le champ `scheduled_at` était déjà dans le fillable :

```php
protected array $fillable = [
    // ...
    'scheduled_at',
    // ...
];
```

## Tests recommandés

### Test 1 : Création avec date future
```
1. Créer une campagne
2. Définir scheduled_at = demain 10:00
3. ✓ Campagne créée avec status="scheduled"
4. ✓ Message "Envoi programmé le ..."
```

### Test 2 : Création avec date passée
```
1. Créer une campagne
2. Utiliser les devtools pour définir une date passée
3. Soumettre
4. ✓ Message d'erreur affiché
5. ✓ Formulaire non soumis
```

### Test 3 : Modification pour ajouter planification
```
1. Éditer une campagne existante (status="pending")
2. Ajouter une date future
3. Sauvegarder
4. ✓ status devient "scheduled"
```

### Test 4 : Modification pour retirer planification
```
1. Éditer une campagne programmée (status="scheduled")
2. Vider le champ scheduled_at
3. Sauvegarder
4. ✓ status devient "pending"
```

### Test 5 : Validation serveur
```
1. Utiliser curl/Postman
2. POST avec scheduled_at passé
3. ✓ Erreur retournée
4. ✓ Campagne non créée
```

## Évolutions futures possibles

1. **Répétition de campagnes** : Planifier des envois récurrents
2. **Fuseau horaire utilisateur** : Permettre de choisir le fuseau horaire
3. **Calendrier visuel** : Afficher les campagnes programmées dans un calendrier
4. **Notification avant envoi** : Alerter X minutes avant l'envoi programmé
5. **Annulation programmation** : Bouton pour annuler une campagne programmée

## Support

En cas de problème :
1. Vérifier que JavaScript est activé dans le navigateur
2. Vérifier le fuseau horaire du serveur : `date_default_timezone_get()`
3. Consulter les logs d'erreur PHP
4. Vérifier les flash messages pour les erreurs de validation
