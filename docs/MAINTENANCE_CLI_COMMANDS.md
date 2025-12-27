# Commandes CLI - Mode Maintenance

Ce document décrit toutes les commandes CLI disponibles pour gérer le mode maintenance de SunuFramework2.

## Commandes disponibles

### 1. `maintenance:up` - Activer la maintenance

Active le mode maintenance immédiatement.

**Usage basique** :
```bash
php sunu maintenance:up
```

**Avec options** :
```bash
php sunu maintenance:up --message="Mise à jour système en cours" --end="2025-12-10 06:00" --retry=7200
```

**Options disponibles** :
- `--message` : Message personnalisé affiché aux visiteurs
- `--end` : Date et heure de fin (format: `YYYY-MM-DD HH:MM`)
- `--retry` : Délai Retry-After en secondes (défaut: 3600)

**Exemples** :
```bash
# Activation simple
php sunu maintenance:up

# Avec message personnalisé
php sunu maintenance:up --message="Maintenance de sécurité"

# Avec date de fin
php sunu maintenance:up --end="2025-12-10 06:00"

# Configuration complète
php sunu maintenance:up --message="Migration base de données" --end="2025-12-10 04:00" --retry=1800
```

---

### 2. `maintenance:down` - Désactiver la maintenance

Désactive le mode maintenance et rend le site accessible.

**Usage** :
```bash
php sunu maintenance:down
```

**Exemples** :
```bash
# Désactiver la maintenance
php sunu maintenance:down
```

**Comportement** :
- Si la maintenance est déjà désactivée, affiche un message informatif
- Si la maintenance est active, la désactive immédiatement
- Tous les visiteurs peuvent à nouveau accéder au site

---

### 3. `maintenance:status` - Afficher le statut

Affiche l'état actuel de la maintenance et toute la configuration.

**Usage** :
```bash
php sunu maintenance:status
```

**Informations affichées** :
- Statut actuel (activé/désactivé)
- Configuration (titre, message, dates)
- Contrôle d'accès (IPs, rôles, utilisateurs autorisés)
- Apparence (couleur, image de fond)
- Temps restant (si date de fin définie)

**Exemple de sortie** :
```
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
   STATUT DU MODE MAINTENANCE
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━

🔧 Statut: ACTIVÉ (Site en maintenance)

Configuration:
  Titre: Site en Maintenance
  Message: Migration base de données en cours
  Fin prévue: 2025-12-10 06:00:00
  Temps restant: 4h 30m
  Retry-After: 3600 secondes
  Compte à rebours: Activé

Contrôle d'accès:
  IPs autorisées: 2
    - 192.168.1.100
    - 203.0.113.45
  Rôles autorisés: 1 rôle(s)
  Utilisateurs autorisés: 3 utilisateur(s)

Apparence:
  Couleur de fond: #4466f2
  Image de fond: Configurée

━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
```

---

### 4. `maintenance:schedule` - Planifier une maintenance

Planifie une période de maintenance avec dates de début et fin automatiques.

**Usage** :
```bash
php sunu maintenance:schedule --start="YYYY-MM-DD HH:MM" --end="YYYY-MM-DD HH:MM"
```

**Options disponibles** :
- `--start` : Date et heure de début (format: `YYYY-MM-DD HH:MM`)
- `--end` : Date et heure de fin (format: `YYYY-MM-DD HH:MM`)
- `--title` : Titre personnalisé
- `--message` : Message personnalisé

**Exemples** :
```bash
# Planification basique (samedi nuit)
php sunu maintenance:schedule --start="2025-12-14 23:00" --end="2025-12-15 05:00"

# Avec titre et message
php sunu maintenance:schedule \
  --start="2025-12-10 02:00" \
  --end="2025-12-10 06:00" \
  --title="Maintenance Planifiée" \
  --message="Mise à jour majeure du système"

# Seulement la date de fin (commence immédiatement)
php sunu maintenance:schedule --end="2025-12-10 06:00"

# Seulement la date de début (durée indéterminée)
php sunu maintenance:schedule --start="2025-12-10 02:00"
```

**Comportement** :
- Active automatiquement la maintenance
- Le système active/désactive automatiquement selon les dates
- Le compte à rebours s'affiche si date de fin définie
- Valide que `start_time < end_time`

---

## Cas d'usage courants

### Maintenance d'urgence immédiate

```bash
# Activer immédiatement
php sunu maintenance:up --message="Problème technique détecté - correction en cours"

# Vérifier le statut
php sunu maintenance:status

# Désactiver quand résolu
php sunu maintenance:down
```

### Maintenance planifiée (nuit du samedi)

```bash
# Planifier pour samedi 23h à dimanche 5h
php sunu maintenance:schedule \
  --start="2025-12-14 23:00" \
  --end="2025-12-15 05:00" \
  --message="Mise à jour hebdomadaire - retour prévu à 5h du matin"
```

### Maintenance avec durée précise

```bash
# Activer pour 2 heures
php sunu maintenance:up \
  --message="Maintenance rapide - environ 2 heures" \
  --end="$(date -d '+2 hours' +'%Y-%m-%d %H:%M')" \
  --retry=7200
```

### Vérification rapide

```bash
# Afficher le statut actuel
php sunu maintenance:status

# Dans un script bash
if php sunu maintenance:status | grep -q "ACTIVÉ"; then
    echo "Site en maintenance"
else
    echo "Site accessible"
fi
```

## Intégration dans des scripts

### Script de déploiement

```bash
#!/bin/bash
# deploy.sh

echo "=== Démarrage du déploiement ==="

# Activer la maintenance
php sunu maintenance:up --message="Déploiement en cours..." --end="$(date -d '+30 minutes' +'%Y-%m-%d %H:%M')"

# Exécuter le déploiement
git pull origin main
composer install --no-dev --optimize-autoloader
php sunu migrate
php sunu cache:clear

# Désactiver la maintenance
php sunu maintenance:down

echo "=== Déploiement terminé ==="
```

### Script de sauvegarde automatisée

```bash
#!/bin/bash
# backup.sh

# Activer la maintenance (mode lecture seule)
php sunu maintenance:up --message="Sauvegarde en cours" --retry=300

# Effectuer la sauvegarde
mysqldump -u root sunuframework2 > backup_$(date +%Y%m%d_%H%M%S).sql
tar -czf files_backup_$(date +%Y%m%d_%H%M%S).tar.gz public/uploads

# Désactiver la maintenance
php sunu maintenance:down

echo "Sauvegarde terminée"
```

### Cron pour maintenance planifiée

Ajouter dans votre crontab :

```cron
# Activer maintenance samedi 23h
0 23 * * 6 cd /path/to/sunuframework2 && php sunu maintenance:up --end="2025-12-15 05:00"

# Désactiver maintenance dimanche 5h (sécurité)
0 5 * * 0 cd /path/to/sunuframework2 && php sunu maintenance:down
```

## Sortie et codes de retour

### Codes de retour

- `0` : Succès
- `1` : Erreur (configuration introuvable, erreur DB, etc.)

### Utilisation dans des scripts

```bash
# Vérifier le succès d'une commande
if php sunu maintenance:up; then
    echo "Maintenance activée avec succès"
else
    echo "Erreur lors de l'activation" >&2
    exit 1
fi

# Capturer la sortie
STATUS=$(php sunu maintenance:status)
echo "$STATUS"
```

## Permissions et sécurité

### Permissions requises

- Accès en lecture/écriture à la base de données
- Droits d'exécution sur `sunu`
- Pas besoin d'être super admin (commandes CLI)

### Bonnes pratiques

1. **Utiliser sudo si nécessaire** :
   ```bash
   sudo -u www-data php sunu maintenance:up
   ```

2. **Logger les actions** :
   ```bash
   php sunu maintenance:up 2>&1 | tee -a /var/log/maintenance.log
   ```

3. **Notifications par email** :
   ```bash
   php sunu maintenance:up && \
   echo "Maintenance activée" | mail -s "Alert: Site en maintenance" admin@example.com
   ```

4. **Scripts idempotents** :
   ```bash
   # Désactiver seulement si activé
   if php sunu maintenance:status | grep -q "ACTIVÉ"; then
       php sunu maintenance:down
   fi
   ```

## Dépannage

### Problème : "Configuration introuvable"

**Solution** : Vérifier que la table `maintenance_mode` existe et contient au moins un enregistrement.

```bash
mysql -u root -D sunuframework2 -e "SELECT * FROM maintenance_mode;"
```

### Problème : Erreur de permission

**Solution** : S'assurer que l'utilisateur a les droits sur la DB.

```bash
# Exécuter en tant que www-data
sudo -u www-data php sunu maintenance:up
```

### Problème : Format de date invalide

**Solution** : Utiliser le format exact `YYYY-MM-DD HH:MM`.

```bash
# Incorrect
php sunu maintenance:up --end="10/12/2025 6am"

# Correct
php sunu maintenance:up --end="2025-12-10 06:00"
```

## Aide rapide

Pour afficher toutes les commandes disponibles :

```bash
php sunu list
# ou
php sunu --help
```

Pour l'aide sur une commande spécifique :

```bash
php sunu maintenance:schedule
# (sans arguments affiche l'usage)
```

---

**Documentation créée pour SunuFramework2**
**Version** : 1.0
**Date** : Décembre 2025
