# 🏥 Système de Surveillance Cron & Queue - Health Check

Date : 2025-12-09

## 🎯 Objectif

Système **léger et simple** pour vérifier que les cron jobs et queue workers tournent normalement sur le serveur en production.

**Principe** : Enregistrement d'un "heartbeat" (battement de cœur) à chaque exécution, permettant de détecter si un service ne tourne plus.

---

## 📊 Architecture

### Table de Surveillance

**Table** : `system_health_checks`

```sql
CREATE TABLE system_health_checks (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    service_name VARCHAR(50) NOT NULL UNIQUE,
    last_run_at TIMESTAMP NULL,           -- Dernière exécution
    runs_today INT DEFAULT 0,              -- Nombre d'exécutions aujourd'hui
    last_status ENUM('ok', 'warning', 'error') DEFAULT 'ok',
    last_error TEXT NULL,                  -- Dernier message d'erreur
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);
```

### Services Surveillés

| Service | Description | Fréquence Attendue |
|---------|-------------|-------------------|
| `cron_job` | Cron jobs (traitement SMS, nettoyage, etc.) | Toutes les minutes |
| `queue_worker` | Worker de file d'attente | En continu |

---

## 🔧 Installation & Configuration

### 1. Tables Créées ✅

Les tables sont déjà créées avec les services initialisés :

```bash
mysql> SELECT * FROM system_health_checks;
```

```
+----+--------------+-------------+------------+-------------+------------+---------------------+
| id | service_name | last_run_at | runs_today | last_status | last_error | updated_at          |
+----+--------------+-------------+------------+-------------+------------+---------------------+
|  1 | cron_job     | NULL        |          0 | warning     | NULL       | 2025-12-09 09:19:55 |
|  2 | queue_worker | NULL        |          0 | warning     | NULL       | 2025-12-09 09:19:55 |
+----+--------------+-------------+------------+-------------+------------+---------------------+
```

### 2. Services PHP Créés ✅

**HealthCheckService.php** : Service principal de surveillance
- `recordHeartbeat()` - Enregistrer qu'un service a tourné
- `recordError()` - Enregistrer une erreur
- `checkServiceHealth()` - Vérifier l'état d'un service
- `checkAllServices()` - Vérifier tous les services
- `getHealthSummary()` - Résumé pour l'API

**HeartbeatHelper.php** : Helper pour simplifier l'usage
- `ping()` - Enregistrer rapidement un heartbeat
- `runWithHeartbeat()` - Wrapper pour exécuter du code avec heartbeat automatique

---

## 💻 Utilisation dans les Cron Jobs

### Méthode 1 : Ping Simple (Recommandé)

Au début de votre script cron, ajoutez simplement :

```php
<?php
require_once __DIR__ . '/../vendor/autoload.php';

use App\Core\Application;
use App\Core\Services\HeartbeatHelper;

$app = new Application(dirname(__DIR__));
$app->boot();

// ✅ Enregistrer le heartbeat
HeartbeatHelper::ping('cron_job');

// Votre code cron ici
processSmsQueue();
cleanupOldLogs();
// ...
```

### Méthode 2 : Wrapper avec Gestion d'Erreurs

```php
<?php
use App\Core\Services\HeartbeatHelper;

HeartbeatHelper::runWithHeartbeat('cron_job', function() {
    // Votre code cron ici
    processSmsQueue();

    // Si une exception est levée, elle sera enregistrée automatiquement
});
```

### Méthode 3 : Enregistrement Manuel d'Erreurs

```php
<?php
use App\Core\Services\HeartbeatHelper;
use App\Core\Services\HealthCheckService;

try {
    HeartbeatHelper::ping('cron_job');

    // Votre code
    processSmsQueue();

} catch (Exception $e) {
    // Enregistrer l'erreur
    HealthCheckService::recordError('cron_job', $e->getMessage());

    // Re-lancer l'exception si nécessaire
    throw $e;
}
```

---

## ⚙️ Configuration Crontab

### Exemple de Crontab

```bash
# Traiter la queue SMS toutes les minutes
* * * * * php /var/www/html/cron/process_sms_queue.php >> /var/log/cron.log 2>&1

# Nettoyer les logs tous les jours à 2h
0 2 * * * php /var/www/html/cron/cleanup_logs.php >> /var/log/cron.log 2>&1
```

### Vérifier que le Cron Tourne

```bash
# Vérifier que crontab est configuré
crontab -l

# Voir les logs cron système
tail -f /var/log/cron.log

# Vérifier le service cron
systemctl status cron
```

---

## 🔄 Queue Worker (Long-Running Process)

### Démarrage Manuel

```bash
# Démarrer le worker
php workers/queue_worker.php

# En arrière-plan
nohup php workers/queue_worker.php > /var/log/queue_worker.log 2>&1 &

# Voir les logs
tail -f /var/log/queue_worker.log
```

### Avec Supervisord (Recommandé)

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
# Recharger la configuration
sudo supervisorctl reread
sudo supervisorctl update

# Démarrer/Arrêter/Redémarrer
sudo supervisorctl start sms_queue_worker
sudo supervisorctl stop sms_queue_worker
sudo supervisorctl restart sms_queue_worker

# Voir le statut
sudo supervisorctl status
```

---

## 📱 Dashboard de Monitoring

### Accès Web

**URL** : `https://votre-site.com/admin/health`

Le dashboard affiche :
- ✅ **Statut global** (ok, warning, error)
- 🕐 **Dernière exécution** de chaque service
- 📊 **Nombre d'exécutions** aujourd'hui
- ⏱️ **Temps écoulé** depuis la dernière exécution
- 🔄 **Auto-refresh** toutes les 30 secondes

### Codes Couleur

| Status | Badge | Signification |
|--------|-------|---------------|
| `ok` | <span style="color: green;">●</span> **SUCCESS** | Service actif (< 2h) |
| `warning` | <span style="color: orange;">●</span> **WARNING** | Pas tourné depuis 2-24h |
| `error` | <span style="color: red;">●</span> **ERROR** | Pas tourné depuis > 24h |

---

## 🌐 API de Monitoring

### Endpoint JSON

**URL** : `https://votre-site.com/api/health`

**Réponse** :

```json
{
    "status": "ok",
    "timestamp": 1733741384,
    "datetime": "2025-12-09 10:09:44",
    "services": {
        "cron_job": {
            "status": "ok",
            "last_run": "2025-12-09 10:09:44",
            "runs_today": 15
        },
        "queue_worker": {
            "status": "ok",
            "last_run": "2025-12-09 10:08:12",
            "runs_today": 1440
        }
    }
}
```

**Codes HTTP** :
- `200` - Système OK ou Warning
- `503` - Service Unavailable (erreur détectée)

### Badge SVG

**URL** : `https://votre-site.com/api/health/badge`

Redirige vers shields.io pour générer un badge :

![System Status](https://img.shields.io/badge/system-healthy-brightgreen)

**Usage dans README.md** :

```markdown
![System Health](https://votre-site.com/api/health/badge)
```

---

## 🔔 Monitoring Externe

### Option 1 : UptimeRobot (Gratuit)

1. Créer un compte sur [uptimerobot.com](https://uptimerobot.com)
2. Ajouter un monitor de type **HTTP(s)**
3. URL : `https://votre-site.com/api/health`
4. Intervalle : 5 minutes
5. Configurer les alertes :
   - Email
   - SMS
   - Slack/Discord webhook

**Avantage** : Alertes automatiques 24/7

### Option 2 : Cron de Vérification Local

Créer un script qui vérifie l'API et envoie une alerte :

```bash
#!/bin/bash
# /usr/local/bin/check_health.sh

HEALTH_URL="https://votre-site.com/api/health"
ALERT_EMAIL="admin@votre-site.com"

STATUS=$(curl -s "$HEALTH_URL" | jq -r '.status')

if [ "$STATUS" != "ok" ]; then
    echo "⚠️ ALERTE: Système en état $STATUS" | mail -s "Health Check Failed" "$ALERT_EMAIL"
fi
```

**Crontab** :

```bash
# Vérifier toutes les 10 minutes
*/10 * * * * /usr/local/bin/check_health.sh
```

### Option 3 : Pingdom, Better Uptime, etc.

Services similaires à UptimeRobot, tous supportent l'endpoint `/api/health`.

---

## 🧪 Tests

### Test Manuel SQL

```sql
-- Simuler l'exécution d'un cron
UPDATE system_health_checks
SET last_run_at = NOW(),
    runs_today = runs_today + 1,
    last_status = 'ok'
WHERE service_name = 'cron_job';

-- Vérifier l'état
SELECT
    service_name,
    last_run_at,
    runs_today,
    last_status,
    TIMESTAMPDIFF(HOUR, last_run_at, NOW()) as hours_since
FROM system_health_checks;
```

### Test avec Script PHP

```bash
# Tester le système heartbeat
php scripts/test_heartbeat.php
```

### Test API avec curl

```bash
# Vérifier l'API
curl https://votre-site.com/api/health | jq

# Télécharger le badge
curl -o health_badge.svg https://votre-site.com/api/health/badge
```

---

## 📋 Scénarios d'Alerte

### Scénario 1 : Cron Arrêté

**Symptômes** :
- Dashboard affiche `error` pour `cron_job`
- Message : "Pas d'activité depuis X heures"
- `runs_today` = 0

**Diagnostic** :
```bash
# Vérifier crontab
crontab -l

# Vérifier le service cron
systemctl status cron

# Voir les logs
tail -50 /var/log/cron.log
```

**Solutions** :
1. Redémarrer cron : `sudo systemctl restart cron`
2. Vérifier les permissions du script
3. Tester le script manuellement

### Scénario 2 : Queue Worker Crashé

**Symptômes** :
- Dashboard affiche `error` pour `queue_worker`
- Pas d'activité récente
- Messages bloqués dans la queue

**Diagnostic** :
```bash
# Vérifier avec supervisord
sudo supervisorctl status sms_queue_worker

# Voir les logs
tail -50 /var/log/supervisor/queue_worker_error.log
```

**Solutions** :
1. Redémarrer : `sudo supervisorctl restart sms_queue_worker`
2. Vérifier les erreurs PHP dans les logs
3. Vérifier la connexion DB

### Scénario 3 : Performance Dégradée

**Symptômes** :
- Status `warning`
- Le service tourne mais lentement
- `runs_today` très bas

**Diagnostic** :
```bash
# Vérifier la charge serveur
top
htop

# Vérifier l'espace disque
df -h

# Vérifier MySQL
mysql -u root -e "SHOW PROCESSLIST;"
```

---

## 🔍 Requêtes SQL Utiles

### État Actuel

```sql
SELECT
    service_name,
    last_run_at,
    runs_today,
    last_status,
    TIMESTAMPDIFF(MINUTE, last_run_at, NOW()) as minutes_ago,
    last_error
FROM system_health_checks;
```

### Services en Erreur

```sql
SELECT * FROM system_health_checks
WHERE last_status = 'error'
   OR TIMESTAMPDIFF(HOUR, last_run_at, NOW()) > 24
   OR last_run_at IS NULL;
```

### Réinitialiser un Service

```sql
UPDATE system_health_checks
SET last_run_at = NOW(),
    runs_today = 0,
    last_status = 'ok',
    last_error = NULL
WHERE service_name = 'cron_job';
```

### Réinitialisation Quotidienne (Automatique)

Le compteur `runs_today` se réinitialise automatiquement à chaque nouveau jour lors du prochain heartbeat.

---

## 📁 Fichiers Créés

| Fichier | Description |
|---------|-------------|
| [Core/Services/HealthCheckService.php](Core/Services/HealthCheckService.php) | Service principal de surveillance |
| [Core/Services/HeartbeatHelper.php](Core/Services/HeartbeatHelper.php) | Helper pour simplifier l'usage |
| [Modules/Settings/Controllers/HealthCheckController.php](Modules/Settings/Controllers/HealthCheckController.php) | Controller pour dashboard et API |
| [Modules/Settings/Views/health/index.php](Modules/Settings/Views/health/index.php) | Vue du dashboard |
| [cron/process_sms_queue.php](cron/process_sms_queue.php) | Exemple de cron avec heartbeat |
| [workers/queue_worker.php](workers/queue_worker.php) | Exemple de worker avec heartbeat |
| [scripts/test_heartbeat.php](scripts/test_heartbeat.php) | Script de test |

---

## ⚠️ Important

### À Faire Sur le Serveur de Production

1. ✅ **Configurer les routes** pour `/admin/health` et `/api/health`
2. ✅ **Ajouter les heartbeats** dans tous vos cron jobs
3. ✅ **Configurer Supervisord** pour les workers
4. ✅ **Mettre en place un monitoring externe** (UptimeRobot)
5. ✅ **Tester** le système en arrêtant volontairement un service

### Sécurité

- L'endpoint `/api/health` est **public** (pas d'authentification)
- Il ne révèle **aucune information sensible**
- Seulement : status, timestamp, et nom des services
- Idéal pour monitoring externe

---

## 🎯 Résumé

### Pour les Cron Jobs

```php
// Au début de chaque script cron
HeartbeatHelper::ping('cron_job');
```

### Pour les Queue Workers

```php
// Régulièrement dans la boucle (ex: toutes les 60 secondes)
HeartbeatHelper::ping('queue_worker');
```

### Pour le Monitoring

- 🖥️ **Dashboard** : `/admin/health`
- 🔌 **API** : `/api/health`
- 🔔 **Alertes** : Configurer UptimeRobot sur `/api/health`

---

✅ **Le système est prêt à déployer !** 🚀

Une fois configuré, vous serez alerté automatiquement si un cron ou worker ne tourne plus.
