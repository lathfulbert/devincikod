# ✅ Système de Surveillance Cron & Queue - Installation Terminée

## 🎯 Ce qui a été mis en place

### 1. ✅ Base de Données

**Table créée** : `system_health_checks`

```
📊 État actuel:
┌──────────────────┬─────────────────────────┬──────────┬────────┐
│ Service          │ Dernière Exécution      │ Exec/Jour│ Statut │
├──────────────────┼─────────────────────────┼──────────┼────────┤
│ 🕐 Cron Jobs     │ ✅ 09/12/2025 10:09:44  │    1     │ ✅ OK  │
│ ⚙️ Queue Worker  │ ✅ 09/12/2025 10:09:44  │    1     │ ✅ OK  │
└──────────────────┴─────────────────────────┴──────────┴────────┘
```

### 2. ✅ Services PHP

- **HealthCheckService** - Gestion de la surveillance
- **HeartbeatHelper** - Helper simple pour les cron/workers

### 3. ✅ Dashboard Web

**URL** : `/admin/health`

Affiche en temps réel :
- 🟢 Statut global du système
- 📊 État de chaque service
- ⏱️ Temps écoulé depuis dernière exécution
- 🔄 Auto-refresh toutes les 30s

### 4. ✅ API de Monitoring

**Endpoints** :
- `GET /api/health` - JSON avec l'état complet
- `GET /api/health/badge` - Badge SVG pour README

### 5. ✅ Exemples de Code

- `cron/process_sms_queue.php` - Cron avec heartbeat
- `workers/queue_worker.php` - Worker avec heartbeat
- `scripts/test_heartbeat.php` - Script de test

---

## 🚀 À Faire Sur le Serveur

### Étape 1 : Ajouter HeartBeat dans vos Cron Jobs

Dans **TOUS** vos scripts cron existants, ajoutez en début de fichier :

```php
<?php
use App\Core\Services\HeartbeatHelper;

// ✅ Enregistrer que le cron a tourné
HeartbeatHelper::ping('cron_job');

// ... votre code existant ...
```

**Exemple** :

```php
// cron/process_sms_queue.php
<?php
require_once __DIR__ . '/../vendor/autoload.php';

use App\Core\Application;
use App\Core\Services\HeartbeatHelper;

$app = new Application(dirname(__DIR__));
$app->boot();

HeartbeatHelper::ping('cron_job'); // ⬅️ Ajouter cette ligne

// Votre code existant
processSmsQueue();
```

### Étape 2 : Ajouter HeartBeat dans vos Queue Workers

Si vous avez un worker qui tourne en continu :

```php
while (true) {
    $job = getNextJob();

    if ($job) {
        HeartbeatHelper::ping('queue_worker'); // ⬅️ À chaque job
        processJob($job);
    }

    sleep(5);
}
```

### Étape 3 : Configurer les Routes

Ajouter dans votre fichier de routes :

```php
// Dashboard de monitoring
$router->get('/admin/health', 'Modules\Settings\Controllers\HealthCheckController@index');

// API publique
$router->get('/api/health', 'Modules\Settings\Controllers\HealthCheckController@apiCheck');
$router->get('/api/health/badge', 'Modules\Settings\Controllers\HealthCheckController@badge');
```

### Étape 4 : Mettre en Place un Monitoring Externe

**Option recommandée** : UptimeRobot (gratuit)

1. Créer un compte sur https://uptimerobot.com
2. Ajouter un monitor HTTP(s) :
   - **URL** : `https://votre-site.com/api/health`
   - **Intervalle** : 5 minutes
   - **Type** : HTTP(s)
3. Configurer les alertes :
   - Email (gratuit)
   - SMS (payant)
   - Webhook Slack/Discord

**Résultat** : Vous recevez un email/SMS automatiquement si un service ne tourne plus pendant > 24h.

---

## 📊 Comment Ça Marche

### Logique Simple

1. **Heartbeat** : Chaque fois qu'un cron/worker tourne, il enregistre un "ping" dans la DB
2. **Vérification** : Le dashboard vérifie le dernier ping
3. **Alertes** : Si dernier ping > 24h → Status ERROR

### Seuils d'Alerte

| Temps écoulé | Status | Signification |
|--------------|--------|---------------|
| < 2 heures | ✅ **OK** | Service actif |
| 2-24 heures | ⚠️ **WARNING** | Attention requise |
| > 24 heures | ❌ **ERROR** | Service arrêté |

---

## 🧪 Tester le Système

### Test 1 : Simuler une Exécution

```bash
# Simuler que le cron a tourné
mysql -u root sunuframework -e "
UPDATE system_health_checks
SET last_run_at = NOW(), runs_today = runs_today + 1, last_status = 'ok'
WHERE service_name = 'cron_job';
"

# Vérifier le dashboard
# Allez sur : https://votre-site.com/admin/health
```

### Test 2 : API JSON

```bash
curl https://votre-site.com/api/health | jq
```

**Résultat attendu** :

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
    }
  }
}
```

### Test 3 : Simuler un Service Arrêté

```bash
# Simuler un service qui n'a pas tourné depuis 25h
mysql -u root sunuframework -e "
UPDATE system_health_checks
SET last_run_at = DATE_SUB(NOW(), INTERVAL 25 HOUR)
WHERE service_name = 'cron_job';
"

# Vérifier le dashboard → devrait afficher ERROR
```

---

## 🔔 Scénarios d'Utilisation

### Scénario 1 : Cron Bloqué

**Alerte** : Dashboard montre ERROR pour cron_job

**Actions** :
```bash
# 1. Vérifier crontab
crontab -l

# 2. Vérifier les logs
tail -50 /var/log/cron.log

# 3. Tester le script manuellement
php /path/to/cron/process_sms_queue.php

# 4. Redémarrer cron si nécessaire
sudo systemctl restart cron
```

### Scénario 2 : Queue Worker Crashé

**Alerte** : Dashboard montre ERROR pour queue_worker

**Actions** :
```bash
# Avec supervisord
sudo supervisorctl status sms_queue_worker
sudo supervisorctl restart sms_queue_worker

# Voir les logs d'erreur
tail -50 /var/log/supervisor/queue_worker_error.log
```

---

## 📁 Fichiers à Consulter

### Documentation Complète
📖 [HEALTH_CHECK_MONITORING.md](HEALTH_CHECK_MONITORING.md) - Guide complet (50+ pages)

### Code Source
- `Core/Services/HealthCheckService.php` - Service principal
- `Core/Services/HeartbeatHelper.php` - Helper
- `Modules/Settings/Controllers/HealthCheckController.php` - Controller
- `Modules/Settings/Views/health/index.php` - Vue dashboard

### Exemples
- `cron/process_sms_queue.php` - Exemple de cron
- `workers/queue_worker.php` - Exemple de worker

---

## ✅ Checklist de Déploiement

### Avant Déploiement

- [ ] Vérifier que la table `system_health_checks` existe
- [ ] Tester le dashboard en local : `/admin/health`
- [ ] Tester l'API en local : `/api/health`
- [ ] Ajouter les heartbeats dans les cron jobs existants

### Sur le Serveur

- [ ] Déployer le code
- [ ] Configurer les routes
- [ ] Vérifier que les cron jobs tournent
- [ ] Configurer Supervisord pour les workers
- [ ] Tester le dashboard : `https://site.com/admin/health`
- [ ] Configurer UptimeRobot sur `/api/health`
- [ ] Recevoir la première alerte de test

### Maintenance

- [ ] Vérifier le dashboard une fois par semaine
- [ ] Vérifier les emails d'alerte UptimeRobot
- [ ] Adapter les seuils si nécessaire (2h, 24h)

---

## 🎯 Résumé en 3 Points

1. **Heartbeat** : Ajoutez `HeartbeatHelper::ping('cron_job')` dans vos cron
2. **Dashboard** : Surveillez `/admin/health` pour voir l'état
3. **Alertes** : Configurez UptimeRobot sur `/api/health` pour les alertes

---

## 💡 Conseil

**Ce système est léger par design** :
- Pas d'historique complet (seulement aujourd'hui)
- Pas de logs détaillés (seulement dernier état)
- Pas de surcharge (1 requête UPDATE par exécution)

Si vous avez besoin de plus de détails, vous pouvez consulter les logs système :
- Cron : `/var/log/cron.log`
- Supervisord : `/var/log/supervisor/`
- Application : Vos logs personnalisés

---

✅ **Le système est prêt ! Suivez la checklist ci-dessus pour le déploiement.** 🚀

Des questions ? Consultez [HEALTH_CHECK_MONITORING.md](HEALTH_CHECK_MONITORING.md) pour plus de détails.
