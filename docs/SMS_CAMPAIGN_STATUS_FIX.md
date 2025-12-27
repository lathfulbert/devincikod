# Fix: Statut de Campagne SMS & Tables Cron/Jobs

## 🐛 Problèmes Identifiés

### 1. Statut de Campagne Ne Change Pas

**Symptôme** : Les campagnes SMS restent en statut `scheduled` même après l'envoi des messages.

**Exemple** :
```sql
SELECT id, name, status, sent_count, total_recipients FROM sms_campaigns WHERE id = 7;
-- Résultat: status = 'scheduled', sent_count = 2, total_recipients = 2
-- Attendu: status = 'completed' puisque tous les SMS sont envoyés
```

### 2. Tâches Cron Non Affichées

**Symptôme** : La table `cron_tasks` existe mais `last_run_at` reste NULL.

```sql
SELECT id, name, last_run_at FROM cron_tasks WHERE id = 1;
-- Résultat: last_run_at = NULL
-- Attendu: last_run_at = timestamp de la dernière exécution
```

### 3. Confusion Entre Les Systèmes

Il existe **TROIS** systèmes différents qui coexistent :

#### A. Système `php sunu cron:run`
- Utilise `CronRunner` + `ProcessPendingSmsTask`
- Dispatche vers la table `jobs` via `QueueManager`
- Log dans `cron_logs` et `cron_tasks`
- Nécessite `php sunu queue:work` pour traiter les jobs

#### B. Système `php cron/process_sms_queue.php`
- Traite directement `sms_queue` via `SmsQueueService`
- Pas de dispatch vers `jobs`
- Traitement immédiat des SMS

#### C. Système `php workers/queue_worker.php`
- Worker long-running qui traite `sms_queue`
- Surveille en continu et traite les messages

## 📊 Comprendre les Tables

### Table: `sms_queue`
**Rôle** : File d'attente des SMS à envoyer

| Colonne | Description |
|---------|-------------|
| `id` | ID unique du message |
| `campaign_id` | Lien vers `sms_campaigns` |
| `recipient` | Numéro destinataire |
| `message` | Contenu du SMS |
| `sender_id` | Nom de l'expéditeur |
| `gateway` | Gateway à utiliser (auto/orange_ci/etc) |
| `status` | pending/processing/sent/failed |
| `scheduled_at` | Date programmée (NULL = immédiat) |
| `sent_at` | Date d'envoi réel |

**Usage** : C'est la **source de vérité** pour l'état des SMS.

### Table: `jobs`
**Rôle** : File d'attente pour le système de jobs asynchrones

| Colonne | Description |
|---------|-------------|
| `id` | ID unique du job |
| `queue` | Nom de la queue (default/mail/etc) |
| `payload` | Données sérialisées du job |
| `attempts` | Nombre de tentatives |
| `reserved_at` | Timestamp de réservation |
| `available_at` | Timestamp de disponibilité |

**Usage** : Utilisée UNIQUEMENT si vous lancez `php sunu cron:run` + `php sunu queue:work`.

**Important** : Cette table est **vide** car nous n'utilisons pas ce système pour les SMS.

### Table: `cron_tasks`
**Rôle** : Registre des tâches cron disponibles

| Colonne | Description |
|---------|-------------|
| `id` | ID unique de la tâche |
| `name` | Nom de la tâche |
| `class` | Classe PHP complète |
| `expression` | Expression cron (* * * * *) |
| `enabled` | Tâche activée ? |
| `last_run_at` | Dernière exécution |

**Usage** : Enregistre les tâches et leur historique d'exécution.

### Table: `cron_logs`
**Rôle** : Logs détaillés de chaque exécution de tâche cron

| Colonne | Description |
|---------|-------------|
| `id` | ID unique du log |
| `task_id` | Lien vers `cron_tasks` |
| `started_at` | Début d'exécution |
| `finished_at` | Fin d'exécution |
| `status` | success/failed |
| `output` | Sortie de la tâche |
| `duration_ms` | Durée en millisecondes |

**Usage** : Historique complet des exécutions.

## ✅ Solutions Appliquées

### 1. Mise à Jour Automatique du Statut de Campagne

**Fichier** : [Modules/SmsCore/Services/SmsQueueService.php](Modules/SmsCore/Services/SmsQueueService.php)

Ajout de la méthode `updateCampaignStats()` qui :
1. Compte les SMS par statut dans `sms_queue`
2. Met à jour `sent_count` et `failed_count`
3. Change le statut selon l'état :
   - `scheduled` → `sending` : Dès qu'un SMS est traité
   - `sending` → `completed` : Quand tous les SMS sont traités

```php
public static function updateCampaignStats(int $campaignId): void
{
    $db = \App\Core\Database\Database::getInstance()->getPdo();

    // Compter les SMS par statut
    $stmt = $db->prepare("
        SELECT
            COUNT(*) as total,
            SUM(CASE WHEN status = 'sent' THEN 1 ELSE 0 END) as sent,
            SUM(CASE WHEN status = 'failed' THEN 1 ELSE 0 END) as failed,
            SUM(CASE WHEN status IN ('pending', 'processing') THEN 1 ELSE 0 END) as pending
        FROM sms_queue
        WHERE campaign_id = ?
    ");
    $stmt->execute([$campaignId]);
    $stats = $stmt->fetch(\PDO::FETCH_ASSOC);

    $campaign = \Modules\SmsCore\Models\SmsCampaign::find($campaignId);
    if (!$campaign) return;

    $campaign->sent_count = (int) $stats['sent'];
    $campaign->failed_count = (int) $stats['failed'];

    // Déterminer le statut
    if ($stats['pending'] == 0) {
        // Tous les messages traités
        $campaign->markAsCompleted();
    } elseif ($stats['sent'] > 0 || $stats['failed'] > 0) {
        // Envoi en cours
        if ($campaign->status === 'scheduled' || $campaign->status === 'draft') {
            $campaign->markAsStarted();
        } else {
            $campaign->save();
        }
    }
}
```

**Appel automatique** : Dans `processQueue()` après chaque SMS traité.

### 2. Mise à Jour de `last_run_at` dans `cron_tasks`

**Fichier** : [Core/Cron/CronRunner.php](Core/Cron/CronRunner.php)

Ajout de la méthode `updateTaskRunTime()` :

```php
private function updateTaskRunTime(int $taskId): void
{
    try {
        $this->db->query("
            UPDATE cron_tasks
            SET last_run_at = NOW(),
                updated_at = NOW()
            WHERE id = ?
        ", [$taskId]);
    } catch (\Throwable $e) {
        error_log("Failed to update task run time: " . $e->getMessage());
    }
}
```

**Appel** : Dans `logSuccess()` après chaque exécution réussie.

### 3. Correction de `ProcessPendingSmsTask`

**Fichier** : [Modules/SmsCore/Cron/ProcessPendingSmsTask.php](Modules/SmsCore/Cron/ProcessPendingSmsTask.php)

**Avant** (❌ Utilise QueryBuilder avec closure) :
```php
$pendingSms = SmsQueue::where('status', 'pending')
    ->where('scheduled_at', '<=', date('Y-m-d H:i:s'))
    ->get();
```

**Après** (✅ Utilise SQL brut) :
```php
$db = \App\Core\Database\Database::getInstance()->getPdo();
$now = date('Y-m-d H:i:s');

$stmt = $db->prepare("
    SELECT * FROM sms_queue
    WHERE status = 'pending'
    AND (scheduled_at IS NULL OR scheduled_at <= ?)
    ORDER BY scheduled_at ASC, created_at ASC
    LIMIT 50
");
$stmt->execute([$now]);
$pendingData = $stmt->fetchAll(\PDO::FETCH_ASSOC);
```

### 4. Amélioration de `SendBulkSmsJob`

**Fichier** : [Modules/SmsCore/Jobs/SendBulkSmsJob.php](Modules/SmsCore/Jobs/SendBulkSmsJob.php)

**Avant** (❌ Met à jour manuellement) :
```php
if ($result['success']) {
    $queueItem->update(['status' => 'sent', 'sent_at' => NOW()]);
    if ($queueItem->campaign_id) {
        $campaign = SmsCampaign::find($queueItem->campaign_id);
        $campaign->update(['sent_count' => $campaign->sent_count + 1]);
    }
}
```

**Après** (✅ Utilise la méthode centralisée) :
```php
if ($result['success']) {
    $queueItem->markAsSent();

    if ($queueItem->campaign_id) {
        SmsQueueService::updateCampaignStats($queueItem->campaign_id);
    }
}
```

## 🧪 Tests et Validation

### Test 1 : Mise à Jour Manuelle des Campagnes Existantes

```bash
php -r "
require_once 'bootstrap.php';
use Modules\SmsCore\Services\SmsQueueService;

\$campaigns = [7, 9, 10, 11, 12];
foreach (\$campaigns as \$id) {
    echo \"Updating campaign \$id...\\n\";
    SmsQueueService::updateCampaignStats(\$id);
}
"
```

**Résultat** :
```sql
SELECT id, name, status, sent_count FROM sms_campaigns WHERE id IN (7, 9, 10, 11);
-- Avant: status = 'scheduled'
-- Après: status = 'completed' ✅
```

### Test 2 : Exécution de `php sunu cron:run`

```bash
php sunu cron:run
# Output:
# Found 1 due task(s).
# Running Modules\SmsCore\Cron\ProcessPendingSmsTask...
# ✅ Modules\SmsCore\Cron\ProcessPendingSmsTask completed in 0s
```

**Vérification** :
```sql
SELECT id, name, last_run_at FROM cron_tasks WHERE id = 1;
-- Résultat: last_run_at = '2025-12-09 15:39:07' ✅
```

### Test 3 : Exécution de `php cron/process_sms_queue.php`

```bash
php cron/process_sms_queue.php
# Output:
# [2025-12-09 15:40:00] Starting SMS queue processing...
# [2025-12-09 15:40:15] Terminé. Traités: 10, Réussis: 10, Échoués: 0, Ignorés: 0
```

## 📖 Recommandations d'Utilisation

### Scénario 1 : Déploiement Simple (Recommandé)

**Utiliser** : `php cron/process_sms_queue.php`

**Avantages** :
- Simple et direct
- Pas besoin de queue worker séparé
- Traite immédiatement les SMS

**Configuration crontab** :
```bash
* * * * * php /var/www/html/cron/process_sms_queue.php >> /var/log/sms_queue.log 2>&1
```

**Statut des campagnes** : ✅ Mis à jour automatiquement

### Scénario 2 : Worker Long-Running

**Utiliser** : `php workers/queue_worker.php`

**Avantages** :
- Traitement en continu
- Réessaie automatiquement en cas d'échec
- Surveille la queue en permanence

**Configuration Supervisord** :
```ini
[program:sms_queue_worker]
command=php /var/www/html/workers/queue_worker.php
directory=/var/www/html
user=www-data
autostart=true
autorestart=true
stdout_logfile=/var/log/supervisor/queue_worker.log
```

**Statut des campagnes** : ✅ Mis à jour automatiquement

### Scénario 3 : Système de Jobs Asynchrones (Avancé)

**Utiliser** : `php sunu cron:run` + `php sunu queue:work`

**Avantages** :
- Système de jobs complet
- Peut gérer plusieurs types de jobs (email, SMS, etc.)
- Logs détaillés dans `cron_tasks` et `cron_logs`

**Configuration** :
```bash
# Dans crontab
* * * * * php /var/www/html/sunu cron:run >> /var/log/cron.log 2>&1

# Worker de jobs (Supervisord)
[program:queue_worker]
command=php /var/www/html/sunu queue:work
directory=/var/www/html
user=www-data
autostart=true
autorestart=true
```

**Statut des campagnes** : ✅ Mis à jour automatiquement via `SendBulkSmsJob`

## 🎯 Résumé des Modifications

### Fichiers Modifiés

| Fichier | Changement |
|---------|------------|
| `Modules/SmsCore/Services/SmsQueueService.php` | Ajout de `updateCampaignStats()` (public) |
| `Modules/SmsCore/Cron/ProcessPendingSmsTask.php` | Remplacement QueryBuilder par SQL brut |
| `Modules/SmsCore/Jobs/SendBulkSmsJob.php` | Utilisation de `updateCampaignStats()` |
| `Core/Cron/CronRunner.php` | Ajout de `updateTaskRunTime()` |

### Comportement Avant/Après

| Aspect | Avant ❌ | Après ✅ |
|--------|----------|----------|
| Statut de campagne | Reste `scheduled` | Change en `sending` puis `completed` |
| `sent_count` | Pas mis à jour | Mis à jour automatiquement |
| `last_run_at` dans `cron_tasks` | NULL | Timestamp de la dernière exécution |
| SMS programmés | Pas traités (closure error) | Traités correctement |
| Logs cron | Présents dans `cron_logs` uniquement | Présents dans `cron_logs` ET `cron_tasks` |

## 🔄 Cycle de Vie d'une Campagne Programmée

1. **Création** : Statut = `scheduled`, SMS ajoutés à `sms_queue` avec `scheduled_at`
2. **Attente** : Le cron/worker vérifie toutes les minutes
3. **Premier SMS envoyé** : Statut → `sending` via `markAsStarted()`
4. **Envoi en cours** : `sent_count` et `failed_count` augmentent progressivement
5. **Tous SMS traités** : Statut → `completed` via `markAsCompleted()`

## 💡 À Retenir

### Tables et Leur Utilité

✅ **Utilisées activement** :
- `sms_queue` : File d'attente des SMS
- `sms_campaigns` : Campagnes SMS
- `cron_tasks` : Registre des tâches cron
- `cron_logs` : Historique des exécutions

❌ **Pas utilisées pour SMS** :
- `jobs` : Vide si vous n'utilisez pas `php sunu queue:work`
- `failed_jobs` : Vide si aucun job n'échoue

### Commandes et Leur Usage

| Commande | Quand l'utiliser |
|----------|------------------|
| `php cron/process_sms_queue.php` | ✅ Via crontab (recommandé) |
| `php workers/queue_worker.php` | ✅ Via Supervisord (worker permanent) |
| `php sunu cron:run` | ⚠️ Si vous voulez utiliser le système de jobs complet |
| `php sunu queue:work` | ⚠️ Requis UNIQUEMENT avec `cron:run` |

### Monitoring

Pour surveiller l'état des campagnes :

```sql
-- Voir les campagnes actives
SELECT id, name, status, sent_count, total_recipients,
       CONCAT(ROUND(sent_count / total_recipients * 100, 2), '%') as progress
FROM sms_campaigns
WHERE status IN ('scheduled', 'sending')
ORDER BY scheduled_at DESC;

-- Voir l'historique des tâches cron
SELECT ct.name, cl.started_at, cl.finished_at, cl.status, cl.duration_ms
FROM cron_logs cl
JOIN cron_tasks ct ON ct.id = cl.task_id
ORDER BY cl.started_at DESC
LIMIT 10;
```

---

✅ **Le système de statut de campagne et de monitoring cron fonctionne maintenant parfaitement !**
