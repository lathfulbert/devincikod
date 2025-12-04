# SMS Rate Limiting - Documentation Complète

## Vue d'ensemble

Le système de **Rate Limiting** permet de configurer des limites d'envoi SMS par gateway pour éviter de dépasser les quotas imposés par les fournisseurs (Orange CI, Infobip, etc.).

## Caractéristiques

✅ **Configuration par Gateway** - Chaque gateway peut avoir ses propres limites
✅ **Trois fenêtres temporelles** - Par minute, par heure, par jour
✅ **Activation/désactivation flexible** - Toggle pour activer/désactiver les limites
✅ **Protection automatique** - La queue vérifie les limites avant chaque envoi
✅ **Valeurs par défaut intelligentes** - 60/min, 1000/h, 10000/jour

## Configuration depuis le Backoffice

### 1. Créer un nouveau Gateway avec Rate Limiting

1. Accéder à **Settings > SMS Gateways > Ajouter**
2. Remplir les informations du gateway (nom, provider, API URL, etc.)
3. Dans la section **"Limites d'Envoi (Rate Limiting)"** :
   - ✅ Cocher "Activer les limites d'envoi"
   - Définir **SMS / Minute** (ex: 60)
   - Définir **SMS / Heure** (ex: 1000)
   - Définir **SMS / Jour** (ex: 10000)
4. Cliquer sur **"Créer le Gateway"**

### 2. Modifier les limites d'un Gateway existant

1. Accéder à **Settings > SMS Gateways**
2. Cliquer sur **"Éditer"** pour le gateway concerné
3. Ajuster les valeurs dans la section **"Limites d'Envoi"**
4. Cliquer sur **"Mettre à jour"**

### 3. Désactiver temporairement les limites

Si vous voulez temporairement désactiver le rate limiting (par exemple pour des tests) :
- Décocher "Activer les limites d'envoi"
- Sauvegarder

Les limites configurées seront conservées mais non appliquées.

## Structure de la Base de Données

### Table `sms_gateways` - Nouvelles colonnes

```sql
ALTER TABLE sms_gateways
ADD COLUMN rate_limit_per_minute INT DEFAULT 60,
ADD COLUMN rate_limit_per_hour INT DEFAULT 1000,
ADD COLUMN rate_limit_per_day INT DEFAULT 10000,
ADD COLUMN rate_limit_enabled TINYINT(1) DEFAULT 1;
```

| Colonne | Type | Description |
|---------|------|-------------|
| `rate_limit_per_minute` | INT | Nombre max de SMS par minute |
| `rate_limit_per_hour` | INT | Nombre max de SMS par heure |
| `rate_limit_per_day` | INT | Nombre max de SMS par jour |
| `rate_limit_enabled` | TINYINT(1) | Activer (1) ou désactiver (0) les limites |

## Fonctionnement Technique

### 1. Vérification avant envoi

Avant chaque envoi de SMS depuis la queue, le système vérifie :

```php
if ($gateway->rate_limit_enabled) {
    $canSend = self::checkRateLimits($gateway);

    if (!$canSend['allowed']) {
        // SMS remis en attente pour retry ultérieur
        $sms->update(['status' => 'pending']);
        // Arrêt du batch actuel
        break;
    }
}
```

### 2. Calcul des limites

Le système compte les SMS déjà envoyés dans la table `sms_billing_logs` :

```sql
-- Dernier minute
SELECT COUNT(*) FROM sms_billing_logs
WHERE gateway = 'orange_ci'
AND created_at >= DATE_SUB(NOW(), INTERVAL 1 MINUTE)

-- Dernière heure
WHERE created_at >= DATE_SUB(NOW(), INTERVAL 1 HOUR)

-- Dernier jour
WHERE created_at >= DATE_SUB(NOW(), INTERVAL 1 DAY)
```

### 3. Comportement en cas de dépassement

Quand une limite est atteinte :

1. ✅ Le SMS actuel est **remis en `pending`** (pas perdu)
2. ⚠️ Le batch actuel s'arrête
3. 📊 Un message est affiché : `"Rate limit reached: Per-minute limit reached (60/60)"`
4. ⏱️ Le prochain cron (1 minute plus tard) réessaiera automatiquement

### 4. Résultats de la Queue

Les résultats incluent maintenant un compteur `skipped` :

```php
[
    'processed' => 5,
    'success' => 3,
    'failed' => 0,
    'skipped' => 2,  // Nouveauté
    'details' => [...]
]
```

## Exemples de Configuration

### Exemple 1 : Provider Low-Cost (limites strictes)

```
SMS / Minute : 10
SMS / Heure : 300
SMS / Jour : 5000
```

**Cas d'usage** : Provider avec tarifs bas mais quotas stricts.

### Exemple 2 : Provider Premium (limites élevées)

```
SMS / Minute : 100
SMS / Heure : 5000
SMS / Jour : 50000
```

**Cas d'usage** : Provider professionnel avec hautes capacités.

### Exemple 3 : Mode Test/Développement

```
SMS / Minute : 5
SMS / Heure : 50
SMS / Jour : 200
```

**Cas d'usage** : Environnement de test pour éviter les coûts.

### Exemple 4 : Sans Limites (déconseillé)

```
✗ Désactiver "Activer les limites d'envoi"
```

**Cas d'usage** : Gateway sans restrictions (rare), ou tests ponctuels.

## Monitoring et Logs

### Logs du Cron

Lorsque la queue est processée, les logs affichent :

```
[2025-12-04 14:32:15] Processing SMS Queue...
  Processed: 5
  Success: 3
  Failed: 0
  Skipped (rate limit): 2
  Pending in queue: 47
  ⚠️  Rate limit reached: Per-minute limit reached (60/60)
✅ Queue processing completed
```

### Vérification manuelle

Exécuter le script de test :

```bash
php process_sms_queue_manual.php
```

### Statistiques en temps réel

Utiliser l'API interne :

```php
use Modules\SmsCore\Services\SmsQueueService;

// Statistiques globales
$stats = SmsQueueService::getStats();
// ['pending' => 47, 'processing' => 0, 'sent' => 153, 'failed' => 2]

// Nombre en attente
$pending = SmsQueueService::getPendingCount();
```

## Recommandations

### ✅ Bonnes Pratiques

1. **Toujours activer le rate limiting** - Protection essentielle contre les dépassements de quota
2. **Configurer selon le contrat** - Respecter les limites de votre fournisseur
3. **Marquer une marge de sécurité** - Si limite = 100/min, configurer 80-90/min
4. **Tester avant production** - Utiliser `process_sms_queue_manual.php` pour valider
5. **Monitorer régulièrement** - Vérifier les logs cron et les compteurs skipped

### ⚠️ À Éviter

1. ❌ Désactiver complètement les limites en production
2. ❌ Configurer des limites irréalistes (ex: 10000/minute)
3. ❌ Oublier d'adapter les limites après changement de plan provider
4. ❌ Ignorer les messages "Rate limit reached" dans les logs

## Dépannage

### Problème : Tous les SMS sont skipped

**Symptôme** : `Skipped (rate limit): 10`, aucun envoi

**Solutions** :
1. Vérifier les limites configurées (peut-être trop basses)
2. Vérifier la table `sms_billing_logs` (anciens records qui comptent)
3. Augmenter temporairement les limites
4. Vérifier que `rate_limit_enabled = 1`

### Problème : Les limites ne sont pas respectées

**Symptôme** : Plus de SMS envoyés que la limite configurée

**Solutions** :
1. Vérifier que le gateway a bien `rate_limit_enabled = 1`
2. Vérifier que le cron tourne (ne pas envoyer directement sans queue)
3. Vérifier que le champ `gateway` dans `sms_billing_logs` correspond à `provider_code`

### Problème : Performance dégradée

**Symptôme** : Le cron est lent

**Solutions** :
1. Ajouter un index sur `sms_billing_logs.created_at`
2. Nettoyer les anciens records avec `SmsQueueService::cleanup(30)`
3. Réduire `sms_queue_batch_size` dans les settings

## API Reference

### SmsQueueService::checkRateLimits()

```php
private static function checkRateLimits(SmsGateway $gateway): array
```

**Retour** :
```php
[
    'allowed' => true|false,
    'message' => 'Within rate limits' | 'Per-minute limit reached (60/60)'
]
```

**Logique** :
1. Vérifie limite par minute
2. Si OK, vérifie limite par heure
3. Si OK, vérifie limite par jour
4. Retourne le premier dépassement ou succès

### Modèle SmsGateway - Nouveaux champs

```php
$gateway->rate_limit_enabled;      // boolean
$gateway->rate_limit_per_minute;   // integer
$gateway->rate_limit_per_hour;     // integer
$gateway->rate_limit_per_day;      // integer
```

## Migration depuis version précédente

Si vous aviez déjà des gateways configurés AVANT cette fonctionnalité :

1. Exécuter la migration SQL (voir "Structure de la Base de Données")
2. Les valeurs par défaut seront appliquées :
   - `rate_limit_per_minute = 60`
   - `rate_limit_per_hour = 1000`
   - `rate_limit_per_day = 10000`
   - `rate_limit_enabled = 1`
3. Ajuster manuellement via le backoffice selon vos besoins

## Changelog

### Version 1.0 (2025-12-04)

✅ Ajout des 4 colonnes dans `sms_gateways`
✅ Formulaires create/edit avec toggle et inputs
✅ Intégration dans `SmsQueueService::processQueue()`
✅ Méthode `checkRateLimits()` pour vérification
✅ Logs cron avec compteur "skipped"
✅ Documentation complète

---

**Développé pour SunuFramework2**
**Date** : Décembre 2025
**Module** : SmsCore + Settings
