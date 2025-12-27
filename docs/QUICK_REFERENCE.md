# Quick Reference - Système SMS cPanel

## 🔧 Configuration Cron (à Copier-Coller dans cPanel)

### Obligatoire 1: Traitement SMS (chaque minute)
```bash
* * * * * /usr/local/bin/ea-php99 /home2/ticafzxr/sms.ticafrique.ci/sunu cron:run >> /home2/ticafzxr/sms.ticafrique.ci/storage/logs/cron.log 2>&1
```

### Obligatoire 2: Health Check (toutes les 5 minutes)
```bash
*/5 * * * * /usr/local/bin/ea-php99 /home2/ticafzxr/sms.ticafrique.ci/cron/health_check.php >> /home2/ticafzxr/sms.ticafrique.ci/storage/logs/health.log 2>&1
```

### Recommandé 3: Queue Worker (toutes les 5 minutes)
```bash
*/5 * * * * pgrep -f "queue:work" > /dev/null || nohup /usr/local/bin/ea-php99 /home2/ticafzxr/sms.ticafrique.ci/sunu queue:work --tries=3 --timeout=60 >> /home2/ticafzxr/sms.ticafrique.ci/storage/logs/queue.log 2>&1 &
```

### Optionnel 4: Rotation Logs (tous les jours 2h)
```bash
0 2 * * * find /home2/ticafzxr/sms.ticafrique.ci/storage/logs/*.log -type f -size +10M -exec sh -c 'mv "$1" "$1.old" && touch "$1"' _ {} \;
```

---

## 📊 Interfaces de Monitoring

### Web Interface
- **Logs Cron**: http://sms.ticafrique.ci/admin/logs/cron
- **Logs Health Check**: http://sms.ticafrique.ci/admin/logs/health
- **Logs SMS Queue**: http://sms.ticafrique.ci/admin/logs/sms-queue
- **System Health**: http://sms.ticafrique.ci/admin/health

### Menu
Configuration Générale → **Logs Système**

---

## 🧪 Tests Rapides

### Via SSH
```bash
# Test health check
php /home2/ticafzxr/sms.ticafrique.ci/cron/health_check.php

# Test cron
php /home2/ticafzxr/sms.ticafrique.ci/sunu cron:run

# Voir logs en temps réel
tail -f /home2/ticafzxr/sms.ticafrique.ci/storage/logs/cron.log
```

### Via SQL
```sql
-- Vérifier dernière exécution cron
SELECT name, last_run_at, TIMESTAMPDIFF(MINUTE, last_run_at, NOW()) as minutes_ago
FROM cron_tasks WHERE status = 'active';

-- État de la file d'attente
SELECT status, COUNT(*) FROM sms_queue GROUP BY status;

-- Messages bloqués
SELECT COUNT(*) FROM sms_queue
WHERE status = 'processing' AND updated_at < DATE_SUB(NOW(), INTERVAL 10 MINUTE);
```

---

## 🚨 Problèmes Fréquents

### SMS ne partent pas
1. Vérifier `/admin/logs/cron` (exécution?)
2. Vérifier `/admin/logs/health` (récupération?)
3. Attendre 5 min (health check automatique)
4. Ou forcer: `php cron/health_check.php`

### Statut campagne bloqué
- Health check met à jour automatiquement (5 min)
- Vérifier `/admin/logs/health`

### Logs trop gros
- Vider via `/admin/logs/cron` → "Vider les logs"
- Ou activer rotation automatique (cron #4)

---

## 📁 Chemins Clés

### Fichiers
- Cron principal: `sunu cron:run`
- Health check: `cron/health_check.php`
- Logs: `storage/logs/*.log`

### Logs
- `storage/logs/cron.log` - Exécutions cron
- `storage/logs/health.log` - Health check
- `storage/logs/sms_cron.log` - Dispatching SMS
- `storage/logs/queue.log` - Queue worker

---

## ✅ Checklist Déploiement

- [ ] Configurer cron #1 (traitement SMS)
- [ ] Configurer cron #2 (health check)
- [ ] Configurer cron #3 (queue worker) - recommandé
- [ ] Tester via SSH: `php sunu cron:run`
- [ ] Tester via SSH: `php cron/health_check.php`
- [ ] Vérifier web: `/admin/logs/cron`
- [ ] Créer campagne test
- [ ] Vérifier progression campagne
- [ ] Activer rotation logs (cron #4) - optionnel

---

## 📚 Documentation Complète

- **CPANEL_DEPLOYMENT_GUIDE.md** - Guide détaillé déploiement
- **MONITORING_DEPLOYMENT_COMPLETE.md** - Architecture complète
- **SMS_SCHEDULED_FIX.md** - Corrections techniques
- **SMS_CAMPAIGN_STATUS_FIX.md** - Gestion statuts

---

**Configuration Minimale**: Cron #1 + Cron #2
**Configuration Recommandée**: Cron #1 + #2 + #3
**Configuration Complète**: Cron #1 + #2 + #3 + #4

🚀 **Prêt pour Production!**
