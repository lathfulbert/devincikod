# ✅ Système de Monitoring - PRÊT AU DÉPLOIEMENT

## 🎉 Installation Complète

Le système de monitoring des cron jobs et queue workers est maintenant **100% opérationnel** et prêt à être déployé !

---

## 📋 Ce qui a été installé

### ✅ 1. Base de Données

**Table** : `system_health_checks`

```sql
SELECT * FROM system_health_checks;
```

| Service | État | Statut |
|---------|------|--------|
| 🕐 Cron Jobs | Initialisé | ⚠️ Warning (jamais exécuté) |
| ⚙️ Queue Worker | Initialisé | ⚠️ Warning (jamais exécuté) |

### ✅ 2. Services PHP

| Fichier | Description |
|---------|-------------|
| `Core/Services/HealthCheckService.php` | Service de surveillance principal |
| `Core/Services/HeartbeatHelper.php` | Helper simple pour enregistrer les heartbeats |

### ✅ 3. Controller & Vues

| Fichier | Description |
|---------|-------------|
| `Modules/Settings/Controllers/HealthCheckController.php` | Controller avec 3 méthodes |
| `Modules/Settings/Views/health/index.php` | Dashboard de monitoring |

### ✅ 4. Routes Configurées

| Route | Méthode | Accès | Description |
|-------|---------|-------|-------------|
| `/admin/health` | GET | 🔒 Auth | Dashboard web de monitoring |
| `/api/health` | GET | 🌍 Public | API JSON pour monitoring externe |
| `/api/health/badge` | GET | 🌍 Public | Badge SVG (shields.io) |

Routes ajoutées dans `Modules/Settings/SettingsModule.php` ✅

### ✅ 5. Menu Navigation

Nouveau menu ajouté dans **Configuration Générale** :

```
📊 Monitoring Système → /admin/health
```

Visible dans le sidebar sous "Configuration Générale" avec l'icône `activity`.

### ✅ 6. Exemples de Code

| Fichier | Description |
|---------|-------------|
| `cron/process_sms_queue.php` | Exemple de cron job avec heartbeat |
| `workers/queue_worker.php` | Exemple de queue worker avec heartbeat |
| `scripts/test_heartbeat.php` | Script de test du système |

### ✅ 7. Documentation

| Document | Contenu |
|----------|---------|
| `HEALTH_CHECK_SUMMARY.md` | Guide rapide de déploiement |
| `HEALTH_CHECK_MONITORING.md` | Documentation complète (50+ pages) |
| `MONITORING_DEPLOYMENT_READY.md` | Ce fichier - checklist finale |

---

## 🚀 Test en Local

### 1. Accéder au Dashboard

```
URL: http://localhost/admin/health
```

**Attendu** :
- Page qui charge sans erreur
- 2 services affichés (cron_job, queue_worker)
- Status : ⚠️ WARNING (jamais exécuté)
- Auto-refresh toutes les 30 secondes

### 2. Tester l'API JSON

```bash
curl http://localhost/api/health
```

**Attendu** :

```json
{
  "status": "warning",
  "timestamp": 1733741384,
  "datetime": "2025-12-09 10:09:44",
  "services": {
    "cron_job": {
      "status": "warning",
      "last_run": null,
      "runs_today": 0
    },
    "queue_worker": {
      "status": "warning",
      "last_run": null,
      "runs_today": 0
    }
  }
}
```

### 3. Simuler une Exécution

```bash
# Simuler que le cron a tourné
mysql -u root sunuframework -e "
UPDATE system_health_checks
SET last_run_at = NOW(),
    runs_today = 1,
    last_status = 'ok'
WHERE service_name = 'cron_job';
"

# Recharger le dashboard → devrait afficher ✅ OK
```

### 4. Tester le Badge

```
URL: http://localhost/api/health/badge
```

**Attendu** : Redirection vers shields.io avec badge coloré

---

## 📦 Déploiement sur le Serveur

### Étape 1 : Déployer le Code

```bash
# Sur votre machine locale
git add .
git commit -m "feat: Add system health monitoring for cron & queue"
git push origin main

# Sur le serveur
cd /var/www/html
git pull origin main

# Vérifier que les fichiers sont présents
ls -la Core/Services/HealthCheckService.php
ls -la Modules/Settings/Controllers/HealthCheckController.php
ls -la Modules/Settings/Views/health/
```

### Étape 2 : Vérifier la Base de Données

```bash
# Vérifier que la table existe
mysql -u root -p sunuframework -e "SELECT * FROM system_health_checks;"

# Si la table n'existe pas, la créer
mysql -u root -p sunuframework < migration_health_check.sql
```

### Étape 3 : Tester les Routes

```bash
# Dashboard (doit nécessiter authentification)
curl -I https://votre-site.com/admin/health

# API (publique)
curl https://votre-site.com/api/health

# Badge
curl -I https://votre-site.com/api/health/badge
```

**Attendu** :
- `/admin/health` → 302 (redirect to login) ou 200 (si connecté)
- `/api/health` → 200 + JSON
- `/api/health/badge` → 302 (redirect to shields.io)

### Étape 4 : Vérifier le Menu

1. Se connecter au back-office
2. Aller dans le menu **Configuration Générale**
3. Vérifier la présence de **"Monitoring Système"**
4. Cliquer dessus → Dashboard doit s'afficher

---

## 🔧 Configuration des Cron Jobs

### Ajouter le Heartbeat

Dans **TOUS vos scripts cron existants**, ajoutez cette ligne au début :

```php
<?php
require_once __DIR__ . '/../vendor/autoload.php';

use App\Core\Application;
use App\Core\Services\HeartbeatHelper;

$app = new Application(dirname(__DIR__));
$app->boot();

// ✅ Enregistrer le heartbeat
HeartbeatHelper::ping('cron_job');

// ... votre code cron ...
```

### Exemple avec cron SMS

**Fichier** : `cron/process_sms_queue.php`

```php
#!/usr/bin/env php
<?php
require_once __DIR__ . '/../vendor/autoload.php';

use App\Core\Application;
use App\Core\Services\HeartbeatHelper;

$app = new Application(dirname(__DIR__));
$app->boot();

// Heartbeat AVANT le traitement
HeartbeatHelper::ping('cron_job');

// Votre code existant
$pending = SmsQueue::where('status', 'pending')->limit(50)->get();
foreach ($pending as $item) {
    processSms($item);
}
```

### Crontab Configuration

```bash
# Éditer la crontab
crontab -e

# Ajouter (si pas déjà présent)
* * * * * php /var/www/html/cron/process_sms_queue.php >> /var/log/cron_sms.log 2>&1
```

**Après la première exécution** :
- Dashboard → Status ✅ OK
- `runs_today` → 1 (puis incrémenté)

---

## ⚙️ Configuration Queue Worker

### Option 1 : Supervisord (Recommandé)

**Fichier** : `/etc/supervisor/conf.d/sms_queue.conf`

```ini
[program:sms_queue_worker]
command=php /var/www/html/workers/queue_worker.php
directory=/var/www/html
user=www-data
autostart=true
autorestart=true
startsecs=10
startretries=3
stdout_logfile=/var/log/supervisor/queue_worker.log
stderr_logfile=/var/log/supervisor/queue_worker_error.log
```

**Commandes** :

```bash
# Recharger
sudo supervisorctl reread
sudo supervisorctl update

# Démarrer
sudo supervisorctl start sms_queue_worker

# Vérifier
sudo supervisorctl status
```

### Option 2 : Démarrage Manuel

```bash
# En arrière-plan
nohup php workers/queue_worker.php > /var/log/queue_worker.log 2>&1 &

# Vérifier qu'il tourne
ps aux | grep queue_worker
```

### Vérification

Après démarrage :
- Dashboard → Queue Worker status ✅ OK
- `runs_today` → Incrémente toutes les 60 secondes

---

## 🔔 Monitoring Externe - UptimeRobot

### Étape 1 : Créer un Compte

1. Aller sur https://uptimerobot.com
2. S'inscrire (gratuit)

### Étape 2 : Ajouter un Monitor

1. Cliquer sur **"+ Add New Monitor"**
2. Paramètres :
   - **Monitor Type** : HTTP(s)
   - **Friendly Name** : Sunuframework Health Check
   - **URL** : `https://votre-site.com/api/health`
   - **Monitoring Interval** : 5 minutes
   - **Monitor Timeout** : 30 seconds

3. **Alert Contacts** :
   - Email : Votre email
   - (Optionnel) SMS, Slack, Discord

### Étape 3 : Configurer les Alertes

**Condition d'alerte** :

UptimeRobot envoie une alerte si :
- L'endpoint retourne **503** (Service Unavailable)
- L'endpoint ne répond pas (timeout)
- L'endpoint retourne une erreur 5xx

**Note** : Notre API retourne :
- `200` si status = "ok" ou "warning"
- `503` si status = "error" (pas tourné depuis > 24h)

### Étape 4 : Tester l'Alerte

```bash
# Simuler un service down
mysql -u root -p sunuframework -e "
UPDATE system_health_checks
SET last_run_at = DATE_SUB(NOW(), INTERVAL 25 HOUR)
WHERE service_name = 'cron_job';
"

# L'API retournera maintenant 503
# UptimeRobot enverra une alerte dans les 5 minutes
```

---

## 📊 Scénarios de Production

### Scénario 1 : Tout Fonctionne ✅

**Dashboard** :
```
🟢 Système opérationnel

🕐 Cron Jobs
   ✅ OK - Service actif
   Dernière exécution: 09/12/2025 14:30:15 (il y a 0.5 heures)
   Exécutions aujourd'hui: 870 fois

⚙️ Queue Worker
   ✅ OK - Service actif
   Dernière exécution: 09/12/2025 14:59:30 (il y a 0.01 heures)
   Exécutions aujourd'hui: 1440 fois
```

**API Response** : `200 OK`

### Scénario 2 : Cron Bloqué ⚠️

**Symptômes** :
- Dashboard affiche ⚠️ WARNING ou ❌ ERROR
- Message : "Pas d'activité depuis X heures"
- API retourne 503

**Actions** :

```bash
# 1. Vérifier que cron tourne
systemctl status cron

# 2. Vérifier crontab
crontab -l

# 3. Voir les logs
tail -50 /var/log/cron_sms.log

# 4. Tester manuellement
php /var/www/html/cron/process_sms_queue.php

# 5. Redémarrer si nécessaire
sudo systemctl restart cron
```

### Scénario 3 : Worker Crashé 💥

**Symptômes** :
- Dashboard → Queue Worker ERROR
- Messages bloqués dans la queue

**Actions** :

```bash
# Avec supervisord
sudo supervisorctl status sms_queue_worker
sudo supervisorctl restart sms_queue_worker

# Voir les logs
tail -50 /var/log/supervisor/queue_worker_error.log

# Vérifier qu'il a redémarré
sudo supervisorctl status
```

---

## 🧪 Checklist de Déploiement

### Avant le Déploiement

- [ ] Tables créées en local
- [ ] Dashboard testé en local (`/admin/health`)
- [ ] API testée en local (`/api/health`)
- [ ] Routes fonctionnelles
- [ ] Menu visible dans le sidebar

### Déploiement

- [ ] Code déployé sur le serveur
- [ ] Table `system_health_checks` existe en prod
- [ ] Dashboard accessible (`https://site.com/admin/health`)
- [ ] API accessible (`https://site.com/api/health`)
- [ ] Menu "Monitoring Système" visible

### Configuration Cron/Workers

- [ ] Heartbeat ajouté dans tous les cron jobs
- [ ] Cron jobs configurés dans crontab
- [ ] Queue worker configuré (Supervisord)
- [ ] Services démarrés et fonctionnels
- [ ] Dashboard affiche ✅ OK après première exécution

### Monitoring Externe

- [ ] Compte UptimeRobot créé
- [ ] Monitor ajouté sur `/api/health`
- [ ] Email d'alerte configuré
- [ ] Test d'alerte effectué (simuler down)
- [ ] Alerte reçue par email ✅

---

## 🎯 Résultat Final

Une fois tout configuré :

1. **Dashboard** : Vous voyez en temps réel l'état de vos services
2. **Auto-refresh** : Page se rafraîchit toutes les 30 secondes
3. **Alertes automatiques** : Email/SMS si un service ne tourne plus > 24h
4. **API publique** : Monitoring externe possible via `/api/health`
5. **Historique léger** : Seulement "aujourd'hui", pas de surcharge DB

---

## 📞 Support & Debugging

### Logs à Vérifier

```bash
# Logs système
tail -f /var/log/syslog

# Logs cron
tail -f /var/log/cron.log

# Logs PHP (si configuré)
tail -f /var/log/php_errors.log

# Logs supervisord
tail -f /var/log/supervisor/queue_worker.log
```

### Requêtes SQL de Debug

```sql
-- Voir l'état actuel
SELECT
    service_name,
    last_run_at,
    runs_today,
    last_status,
    TIMESTAMPDIFF(MINUTE, last_run_at, NOW()) as minutes_ago
FROM system_health_checks;

-- Réinitialiser un service
UPDATE system_health_checks
SET last_run_at = NOW(),
    runs_today = 1,
    last_status = 'ok',
    last_error = NULL
WHERE service_name = 'cron_job';
```

### Test API avec curl

```bash
# Verbose pour voir les headers
curl -v https://votre-site.com/api/health

# Joli format JSON
curl -s https://votre-site.com/api/health | jq

# Vérifier le code de statut
curl -o /dev/null -s -w "%{http_code}\n" https://votre-site.com/api/health
```

---

## 🎉 Félicitations !

Votre système de monitoring est maintenant **opérationnel** !

### Prochaines Étapes

1. ✅ Déployer sur le serveur
2. ✅ Configurer les heartbeats dans les cron
3. ✅ Configurer UptimeRobot
4. ✅ Recevoir les premières alertes

**Vous serez alerté automatiquement** dès qu'un cron ou worker ne tourne plus ! 🚨

---

📚 **Documentation Complète** : [HEALTH_CHECK_MONITORING.md](HEALTH_CHECK_MONITORING.md)

✅ **Guide Rapide** : [HEALTH_CHECK_SUMMARY.md](HEALTH_CHECK_SUMMARY.md)
