# Guide de Déploiement cPanel - SMS System

## Configuration Cron Jobs (Obligatoire)

### 1. Tâche Principale - Traitement SMS
```bash
* * * * * /usr/local/bin/ea-php99 /home2/ticafzxr/sms.ticafrique.ci/sunu cron:run >> /home2/ticafzxr/sms.ticafrique.ci/storage/logs/cron.log 2>&1
```
- **Fréquence**: Chaque minute
- **Fonction**: Dispatche les SMS programmés vers la file d'attente
- **Log**: storage/logs/cron.log

### 2. Health Check - Récupération et Statistiques
```bash
*/5 * * * * /usr/local/bin/ea-php99 /home2/ticafzxr/sms.ticafrique.ci/cron/health_check.php >> /home2/ticafzxr/sms.ticafrique.ci/storage/logs/health.log 2>&1
```
- **Fréquence**: Toutes les 5 minutes
- **Fonction**:
  - Récupère les messages bloqués
  - Met à jour les statistiques des campagnes
  - Affiche l'état de la file d'attente
- **Log**: storage/logs/health.log

### 3. Queue Worker - Traitement Asynchrone (Recommandé)
```bash
*/5 * * * * pgrep -f "queue:work" > /dev/null || nohup /usr/local/bin/ea-php99 /home2/ticafzxr/sms.ticafrique.ci/sunu queue:work --tries=3 --timeout=60 >> /home2/ticafzxr/sms.ticafrique.ci/storage/logs/queue.log 2>&1 &
```
- **Fréquence**: Toutes les 5 minutes (vérifie et redémarre si nécessaire)
- **Fonction**: Traite les jobs de la file d'attente
- **Log**: storage/logs/queue.log

### 4. Rotation des Logs (Optionnel mais Recommandé)
```bash
0 2 * * * find /home2/ticafzxr/sms.ticafrique.ci/storage/logs/*.log -type f -size +10M -exec sh -c 'mv "$1" "$1.old" && touch "$1"' _ {} \;
```
- **Fréquence**: Tous les jours à 2h du matin
- **Fonction**: Archive les logs > 10MB

## Comment Configurer dans cPanel

### Accès aux Cron Jobs
1. Connexion à cPanel
2. Chercher "Cron Jobs" ou "Tâches Cron"
3. Section "Add New Cron Job"

### Configuration de Chaque Tâche

#### Tâche 1: Traitement SMS (Critique)
- **Common Settings**: Every Minute (*/1)
- **Command**:
  ```
  /usr/local/bin/ea-php99 /home2/ticafzxr/sms.ticafrique.ci/sunu cron:run >> /home2/ticafzxr/sms.ticafrique.ci/storage/logs/cron.log 2>&1
  ```

#### Tâche 2: Health Check (Critique)
- **Minute**: */5 (Toutes les 5 minutes)
- **Hour**: * (Toutes les heures)
- **Day**: * (Tous les jours)
- **Month**: * (Tous les mois)
- **Weekday**: * (Tous les jours de la semaine)
- **Command**:
  ```
  /usr/local/bin/ea-php99 /home2/ticafzxr/sms.ticafrique.ci/cron/health_check.php >> /home2/ticafzxr/sms.ticafrique.ci/storage/logs/health.log 2>&1
  ```

#### Tâche 3: Queue Worker (Recommandé)
- **Minute**: */5
- **Hour**: *
- **Day**: *
- **Month**: *
- **Weekday**: *
- **Command**:
  ```
  pgrep -f "queue:work" > /dev/null || nohup /usr/local/bin/ea-php99 /home2/ticafzxr/sms.ticafrique.ci/sunu queue:work --tries=3 --timeout=60 >> /home2/ticafzxr/sms.ticafrique.ci/storage/logs/queue.log 2>&1 &
  ```

## Vérification du Déploiement

### 1. Via Interface Web

**Accès aux Logs**:
- URL: http://sms.ticafrique.ci/admin/logs/cron
- Menu: Configuration Générale → Logs Système

**Vérifications**:
- ✅ Voir les exécutions du cron
- ✅ Vérifier qu'il n'y a pas d'erreurs (lignes rouges)
- ✅ Voir les statistiques des SMS
- ✅ Auto-refresh toutes les 30 secondes

### 2. Via SSH

**Test Health Check**:
```bash
php /home2/ticafzxr/sms.ticafrique.ci/cron/health_check.php
```
Sortie attendue:
```
[2025-12-09 XX:XX:XX] Queue stats (last 24h): pending=X sent=Y failed=Z
[2025-12-09 XX:XX:XX] Health check completed in Xs
```

**Test Cron Task**:
```bash
php /home2/ticafzxr/sms.ticafrique.ci/sunu cron:run
```
Sortie attendue:
```
Found 1 due task(s).
Running Modules\SmsCore\Cron\ProcessPendingSmsTask...
✅ Modules\SmsCore\Cron\ProcessPendingSmsTask completed in 0s
```

**Voir les Logs**:
```bash
# Logs cron
tail -f /home2/ticafzxr/sms.ticafrique.ci/storage/logs/cron.log

# Logs health check
tail -f /home2/ticafzxr/sms.ticafrique.ci/storage/logs/health.log

# Logs SMS
tail -f /home2/ticafzxr/sms.ticafrique.ci/storage/logs/sms_cron.log
```

### 3. Via Base de Données

**Vérifier les Tâches Cron**:
```sql
SELECT name, schedule, last_run_at,
       TIMESTAMPDIFF(MINUTE, last_run_at, NOW()) as minutes_ago
FROM cron_tasks
WHERE status = 'active';
```
`last_run_at` doit être récent (< 1-2 minutes)

**Vérifier la File d'Attente**:
```sql
SELECT status, COUNT(*) as count
FROM sms_queue
WHERE created_at > DATE_SUB(NOW(), INTERVAL 1 HOUR)
GROUP BY status;
```

**Vérifier les Campagnes**:
```sql
SELECT id, name, status, sent_count, failed_count, total_recipients,
       CONCAT(ROUND((sent_count + failed_count) / total_recipients * 100, 1), '%') as progress
FROM sms_campaigns
WHERE status IN ('scheduled', 'sending')
ORDER BY created_at DESC;
```

## Flux de Traitement

### Campagne Programmée
1. **Création**: Utilisateur crée une campagne avec `scheduled_at`
2. **Insertion**: SMS insérés dans `sms_queue` avec statut `pending`
3. **Dispatching**: Cron détecte les SMS programmés (scheduled_at <= NOW)
4. **Traitement**:
   - Avec queue worker: SMS envoyés immédiatement
   - Sans queue worker: SMS restent dans `jobs`, traités au prochain cycle
5. **Mise à jour**: Health check met à jour les statistiques de campagne
6. **Statut**: scheduled → sending → completed

### Messages Bloqués
1. **Détection**: Health check trouve les messages en `processing` > 10 min
2. **Récupération**:
   - Si tentatives < 3: Remis à `pending`
   - Si tentatives >= 3: Marqués `failed`
3. **Statistiques**: Mise à jour des compteurs de campagne

## Résolution de Problèmes

### ❌ Problème: SMS Ne Partent Pas

**Diagnostic**:
```sql
-- Messages en attente
SELECT COUNT(*) FROM sms_queue WHERE status = 'pending';

-- Messages bloqués
SELECT * FROM sms_queue
WHERE status = 'processing'
AND updated_at < DATE_SUB(NOW(), INTERVAL 10 MINUTE);
```

**Solution**:
1. Vérifier que le cron tourne (voir logs)
2. Attendre le prochain health check (max 5 min)
3. Ou forcer manuellement:
   ```bash
   php /home2/ticafzxr/sms.ticafrique.ci/cron/health_check.php
   ```

### ❌ Problème: Statut de Campagne Bloqué

**Diagnostic**:
```sql
SELECT c.id, c.name, c.status, c.sent_count, c.total_recipients,
       COUNT(q.id) as queue_pending
FROM sms_campaigns c
LEFT JOIN sms_queue q ON c.id = q.campaign_id AND q.status = 'pending'
WHERE c.status IN ('scheduled', 'sending')
GROUP BY c.id;
```

**Solution**:
- Health check met à jour automatiquement (toutes les 5 min)
- Vérifier les logs: `/admin/logs/health`

### ❌ Problème: Logs Trop Volumineux

**Solution 1**: Vider via interface web
- URL: http://sms.ticafrique.ci/admin/logs/cron
- Cliquer sur "Vider les logs"

**Solution 2**: Via SSH
```bash
> /home2/ticafzxr/sms.ticafrique.ci/storage/logs/cron.log
> /home2/ticafzxr/sms.ticafrique.ci/storage/logs/health.log
> /home2/ticafzxr/sms.ticafrique.ci/storage/logs/sms_cron.log
```

**Solution 3**: Activer rotation automatique (voir config cron #4)

### ❌ Problème: Cron Ne Tourne Pas

**Vérification**:
```sql
SELECT * FROM cron_tasks WHERE name LIKE '%SMS%';
```
Si `last_run_at` est NULL ou ancien > 5 minutes:

**Solution**:
1. Vérifier config cPanel Cron Jobs
2. Vérifier le chemin PHP: `/usr/local/bin/ea-php99`
3. Tester manuellement via SSH
4. Vérifier les permissions des dossiers

## Points Importants

### ⚠️ À NE PAS FAIRE
- ❌ Ne pas rediriger vers `/dev/null` (perte des logs!)
- ❌ Ne pas utiliser `>> /dev/null 2>&1`
- ❌ Ne pas oublier le health check (critique!)

### ✅ BONNES PRATIQUES
- ✅ Toujours rediriger vers des fichiers logs
- ✅ Utiliser `2>&1` pour capturer les erreurs
- ✅ Surveiller les logs via interface web
- ✅ Activer la rotation des logs
- ✅ Tester après chaque modification

## Chemins Importants

### Exécutables
- PHP: `/usr/local/bin/ea-php99`
- Sunu CLI: `/home2/ticafzxr/sms.ticafrique.ci/sunu`
- Health Check: `/home2/ticafzxr/sms.ticafrique.ci/cron/health_check.php`

### Logs
- Cron: `/home2/ticafzxr/sms.ticafrique.ci/storage/logs/cron.log`
- Health: `/home2/ticafzxr/sms.ticafrique.ci/storage/logs/health.log`
- SMS Queue: `/home2/ticafzxr/sms.ticafrique.ci/storage/logs/sms_cron.log`
- Queue Worker: `/home2/ticafzxr/sms.ticafrique.ci/storage/logs/queue.log`

### Interface Web
- Logs Cron: http://sms.ticafrique.ci/admin/logs/cron
- Logs Health: http://sms.ticafrique.ci/admin/logs/health
- Logs SMS: http://sms.ticafrique.ci/admin/logs/sms-queue
- Monitoring: http://sms.ticafrique.ci/admin/health

## Support

Pour vérifier que tout fonctionne:
1. ✅ Créer une campagne programmée
2. ✅ Attendre 1-2 minutes
3. ✅ Vérifier `/admin/logs/cron` - doit voir "Dispatched X SMS"
4. ✅ Vérifier `/admin/logs/health` - doit voir "Updated X campaign(s)"
5. ✅ Vérifier statut campagne - doit passer à "sending" puis "completed"

Si problème persiste, consulter `MONITORING_DEPLOYMENT_COMPLETE.md` pour diagnostic avancé.

---
**Système prêt pour production!** 🚀
