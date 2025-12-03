# Plan d'Implémentation - Module Email Marketing

## 📊 Analyse du Module SMS Existant

### Structure Actuelle du Module SmsCore
```
Modules/SmsCore/
├── Controllers/
│   ├── DashboardController.php       ✅ Dashboard + Statistiques
│   ├── SmsApiController.php          ✅ API RESTful
│   ├── SmsCampaignController.php     ✅ Gestion campagnes
│   ├── SmsController.php             ✅ Envoi SMS
│   └── SmsPricingController.php      ✅ Tarification + Facturation
│
├── Services/
│   ├── SmsSenderService.php          ✅ Envoi + Billing
│   ├── SmsBillingService.php         ✅ Gestion facturation
│   ├── SmsPricingService.php         ✅ Calcul coûts
│   └── SmsGatewayFactory.php         ✅ Factory pour gateways
│
├── Gateways/
│   ├── InfobipGateway.php            ✅ Infobip
│   ├── OrangeSmsGateway.php          ✅ Orange
│   └── MockGateway.php               ✅ Tests
│
├── Models/
│   ├── SmsCampaign.php               ✅ Campagnes SMS
│   ├── SmsMessage.php                ✅ Messages SMS
│   ├── SmsBillingLog.php             ✅ Logs facturation
│   └── SmsQueue.php                  ✅ File d'attente
│
└── Database/
    └── Migrations/
        ├── 001_create_sms_messages_table.php
        ├── 002_create_sms_billing_logs_table.php
        ├── 003_create_sms_campaigns_table.php
        └── 004_create_sms_queue_table.php
```

### Table Contacts Existante ✅
```sql
contacts (
    id BIGINT PRIMARY KEY,
    phone VARCHAR(20) UNIQUE NOT NULL,
    first_name VARCHAR(100),
    last_name VARCHAR(100),
    email VARCHAR(255),              -- ✅ Email déjà présent !
    custom_fields JSON,
    tags JSON,                        -- ✅ Segmentation déjà possible
    is_active TINYINT(1),
    created_at TIMESTAMP,
    updated_at TIMESTAMP
)
```

---

## 🏗️ Architecture du Module Email Marketing

### 1. Structure Modulaire
```
Modules/EmailMarketing/
├── EmailMarketingModule.php          -- Déclaration module
│
├── Controllers/
│   ├── DashboardController.php       -- Dashboard email
│   ├── EmailCampaignController.php   -- CRUD campagnes
│   ├── EmailController.php           -- Envoi simple/bulk
│   ├── EmailTemplateController.php   -- Gestion templates
│   ├── EmailApiController.php        -- API RESTful
│   └── WorkflowController.php        -- Workflows multicanal
│
├── Services/
│   ├── EmailSenderService.php        -- Envoi email + billing
│   ├── EmailGatewayFactory.php       -- Factory gateways email
│   ├── TemplateEngine.php            -- Moteur de templates
│   ├── MultiChannelService.php       -- Orchestration SMS+Email
│   └── CampaignAnalyticsService.php  -- Analytics centralisé
│
├── Gateways/
│   ├── EmailGatewayInterface.php     -- Interface commune
│   ├── TwilioEmailGateway.php        -- Twilio SendGrid
│   ├── InfobipEmailGateway.php       -- Infobip Email
│   ├── ElasticMailGateway.php        -- ElasticEmail
│   ├── SmtpGateway.php               -- SMTP générique
│   └── MockEmailGateway.php          -- Tests
│
├── Models/
│   ├── EmailCampaign.php             -- Campagnes email
│   ├── EmailMessage.php              -- Messages email
│   ├── EmailTemplate.php             -- Templates HTML
│   ├── EmailLog.php                  -- Logs email
│   ├── CampaignLog.php               -- Logs multicanal ⭐
│   └── Workflow.php                  -- Workflows automatisés
│
├── Jobs/
│   ├── SendBulkEmailJob.php          -- Job async email
│   ├── SendWorkflowJob.php           -- Job workflow multicanal
│   └── ProcessEmailQueueJob.php      -- Traitement queue
│
├── Database/
│   └── Migrations/
│       ├── 001_create_email_campaigns_table.php
│       ├── 002_create_email_messages_table.php
│       ├── 003_create_email_templates_table.php
│       ├── 004_create_email_logs_table.php
│       ├── 005_create_campaign_logs_table.php      -- ⭐ Centralisé
│       └── 006_create_workflows_table.php
│
├── Views/
│   ├── dashboard/
│   ├── campaigns/
│   ├── templates/
│   ├── workflows/
│   └── analytics/
│
└── config/
    ├── permissions.php
    └── gateways.php
```

---

## 🗄️ Structure de Base de Données

### 1. Table email_campaigns
```sql
CREATE TABLE email_campaigns (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(150) NOT NULL,
    subject VARCHAR(255) NOT NULL,
    template_id BIGINT NULL,
    from_name VARCHAR(100),
    from_email VARCHAR(255),
    reply_to VARCHAR(255),

    status ENUM('draft','scheduled','sending','completed','paused','failed') DEFAULT 'draft',

    total_recipients INT DEFAULT 0,
    sent_count INT DEFAULT 0,
    delivered_count INT DEFAULT 0,
    opened_count INT DEFAULT 0,
    clicked_count INT DEFAULT 0,
    bounced_count INT DEFAULT 0,
    unsubscribed_count INT DEFAULT 0,
    failed_count INT DEFAULT 0,

    scheduled_at DATETIME NULL,
    started_at DATETIME NULL,
    completed_at DATETIME NULL,

    created_by BIGINT NULL,
    contact_ids JSON NULL,           -- IDs des contacts ciblés
    segments JSON NULL,               -- Critères de segmentation
    use_personalization TINYINT(1) DEFAULT 0,

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    INDEX idx_status (status),
    INDEX idx_created_by (created_by),
    INDEX idx_scheduled_at (scheduled_at),
    FOREIGN KEY (template_id) REFERENCES email_templates(id) ON DELETE SET NULL
);
```

### 2. Table email_messages
```sql
CREATE TABLE email_messages (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    campaign_id BIGINT NULL,
    user_id BIGINT NULL,

    to_email VARCHAR(255) NOT NULL,
    to_name VARCHAR(100),
    from_email VARCHAR(255),
    from_name VARCHAR(100),
    reply_to VARCHAR(255),

    subject VARCHAR(255) NOT NULL,
    body_html TEXT NOT NULL,
    body_text TEXT NULL,

    gateway VARCHAR(50) NOT NULL,
    status ENUM('pending','queued','sending','sent','delivered','opened','clicked','bounced','failed','unsubscribed') DEFAULT 'pending',

    message_id VARCHAR(100) UNIQUE,
    gateway_message_id VARCHAR(100),

    cost DECIMAL(10,4) DEFAULT 0,
    metadata JSON NULL,
    gateway_response JSON NULL,

    scheduled_at DATETIME NULL,
    sent_at DATETIME NULL,
    delivered_at DATETIME NULL,
    opened_at DATETIME NULL,
    clicked_at DATETIME NULL,

    open_count INT DEFAULT 0,
    click_count INT DEFAULT 0,

    error TEXT NULL,

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    INDEX idx_campaign_id (campaign_id),
    INDEX idx_user_id (user_id),
    INDEX idx_status (status),
    INDEX idx_to_email (to_email),
    INDEX idx_gateway (gateway),
    INDEX idx_message_id (message_id),
    FOREIGN KEY (campaign_id) REFERENCES email_campaigns(id) ON DELETE CASCADE
);
```

### 3. Table email_templates
```sql
CREATE TABLE email_templates (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(150) NOT NULL,
    description TEXT NULL,
    subject VARCHAR(255),

    type ENUM('html','drag_drop','plain_text') DEFAULT 'html',

    html_content TEXT NOT NULL,
    json_structure JSON NULL,         -- Pour drag & drop

    thumbnail VARCHAR(255) NULL,
    is_active TINYINT(1) DEFAULT 1,
    is_default TINYINT(1) DEFAULT 0,

    category VARCHAR(50) NULL,
    tags JSON NULL,

    created_by BIGINT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    INDEX idx_is_active (is_active),
    INDEX idx_category (category)
);
```

### 4. Table email_logs (Statistiques détaillées)
```sql
CREATE TABLE email_logs (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    message_id BIGINT NOT NULL,

    event_type ENUM('sent','delivered','opened','clicked','bounced','unsubscribed','complained','failed') NOT NULL,

    ip_address VARCHAR(45) NULL,
    user_agent TEXT NULL,
    location VARCHAR(100) NULL,

    link_clicked VARCHAR(500) NULL,   -- URL cliquée
    bounce_type VARCHAR(50) NULL,     -- hard/soft bounce
    bounce_reason TEXT NULL,

    event_data JSON NULL,

    occurred_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    INDEX idx_message_id (message_id),
    INDEX idx_event_type (event_type),
    INDEX idx_occurred_at (occurred_at),
    FOREIGN KEY (message_id) REFERENCES email_messages(id) ON DELETE CASCADE
);
```

### 5. Table campaign_logs ⭐ (Logs Multicanal Centralisés)
```sql
CREATE TABLE campaign_logs (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,

    campaign_id BIGINT NOT NULL,
    campaign_type ENUM('email','sms','multichannel','workflow') NOT NULL,

    channel ENUM('email','sms','push','whatsapp') NOT NULL,

    contact_id BIGINT NULL,
    recipient_identifier VARCHAR(255),  -- Email ou phone

    message_id VARCHAR(100),
    gateway VARCHAR(50),

    status ENUM('pending','sent','delivered','opened','clicked','failed','bounced','unsubscribed') NOT NULL,

    cost DECIMAL(10,4) DEFAULT 0,

    sent_at DATETIME NULL,
    delivered_at DATETIME NULL,
    opened_at DATETIME NULL,
    clicked_at DATETIME NULL,

    error TEXT NULL,
    metadata JSON NULL,

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    INDEX idx_campaign_id (campaign_id),
    INDEX idx_campaign_type (campaign_type),
    INDEX idx_channel (channel),
    INDEX idx_contact_id (contact_id),
    INDEX idx_status (status),
    INDEX idx_sent_at (sent_at)
);
```

### 6. Table workflows (Automatisation Multicanal)
```sql
CREATE TABLE workflows (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(150) NOT NULL,
    description TEXT NULL,

    trigger_type ENUM('manual','scheduled','event','api') DEFAULT 'manual',
    trigger_config JSON NULL,         -- Config du trigger

    steps JSON NOT NULL,              -- Séquence d'actions
    /* Exemple steps:
    [
        {"order": 1, "channel": "email", "template_id": 5, "delay": 0},
        {"order": 2, "channel": "sms", "message": "...", "delay": 3600},
        {"order": 3, "channel": "email", "template_id": 6, "delay": 86400}
    ]
    */

    status ENUM('active','paused','completed','archived') DEFAULT 'active',

    total_executions INT DEFAULT 0,
    successful_executions INT DEFAULT 0,
    failed_executions INT DEFAULT 0,

    created_by BIGINT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    INDEX idx_status (status),
    INDEX idx_trigger_type (trigger_type)
);
```

### 7. Table workflow_executions
```sql
CREATE TABLE workflow_executions (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    workflow_id BIGINT NOT NULL,
    contact_id BIGINT NOT NULL,

    status ENUM('pending','running','completed','failed','cancelled') DEFAULT 'pending',

    current_step INT DEFAULT 1,
    total_steps INT NOT NULL,

    started_at DATETIME NULL,
    completed_at DATETIME NULL,
    next_step_at DATETIME NULL,       -- Quand exécuter le prochain step

    execution_data JSON NULL,         -- Données de l'exécution
    error TEXT NULL,

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    INDEX idx_workflow_id (workflow_id),
    INDEX idx_contact_id (contact_id),
    INDEX idx_status (status),
    INDEX idx_next_step_at (next_step_at),
    FOREIGN KEY (workflow_id) REFERENCES workflows(id) ON DELETE CASCADE
);
```

---

## 🔧 Services Principaux

### 1. EmailSenderService.php
```php
<?php
namespace Modules\EmailMarketing\Services;

class EmailSenderService
{
    protected EmailGatewayInterface $gateway;
    protected EmailBillingService $billingService;

    public function send(
        string $to,
        string $subject,
        string $html,
        array $options = []
    ): array {
        // 1. Validation
        // 2. Calcul coût
        // 3. Vérification balance
        // 4. Envoi via gateway
        // 5. Log dans email_messages
        // 6. Log dans campaign_logs
        // 7. Facturation
    }

    public function sendBulk(array $recipients, EmailTemplate $template): array
    {
        // Envoi bulk avec queue
    }
}
```

### 2. MultiChannelService.php (⭐ Service Clé)
```php
<?php
namespace Modules\EmailMarketing\Services;

use Modules\SmsCore\Services\SmsSenderService;

class MultiChannelService
{
    protected EmailSenderService $emailService;
    protected SmsSenderService $smsService;

    /**
     * Envoyer une campagne multicanal
     */
    public function sendCampaign(
        array $contacts,
        array $channels,  // ['email', 'sms']
        array $config
    ): array {
        $results = [
            'email' => [],
            'sms' => [],
            'total_sent' => 0,
            'total_failed' => 0
        ];

        foreach ($contacts as $contact) {
            foreach ($channels as $channel) {
                if ($channel === 'email' && $contact->email) {
                    $result = $this->sendEmail($contact, $config['email']);
                    $results['email'][] = $result;
                }

                if ($channel === 'sms' && $contact->phone) {
                    $result = $this->sendSms($contact, $config['sms']);
                    $results['sms'][] = $result;
                }
            }

            // Log centralisé dans campaign_logs
            $this->logToUnifiedLogs($contact, $channels, $results);
        }

        return $results;
    }

    /**
     * Exécuter un workflow
     */
    public function executeWorkflow(Workflow $workflow, Contact $contact): void
    {
        $execution = WorkflowExecution::create([
            'workflow_id' => $workflow->id,
            'contact_id' => $contact->id,
            'total_steps' => count($workflow->steps),
            'status' => 'running'
        ]);

        foreach ($workflow->steps as $step) {
            // Délai avant exécution
            if ($step['delay'] > 0) {
                sleep($step['delay']); // ou queue job
            }

            // Exécution selon le canal
            match($step['channel']) {
                'email' => $this->sendEmail($contact, $step),
                'sms' => $this->sendSms($contact, $step),
                default => throw new \Exception("Unknown channel")
            };

            $execution->increment('current_step');
        }

        $execution->update(['status' => 'completed']);
    }
}
```

### 3. CampaignAnalyticsService.php
```php
<?php
namespace Modules\EmailMarketing\Services;

class CampaignAnalyticsService
{
    /**
     * Statistiques unifiées email + SMS
     */
    public function getCampaignStats(int $campaignId, string $type): array
    {
        // Récupère depuis campaign_logs
        $logs = CampaignLog::where('campaign_id', $campaignId)
                          ->where('campaign_type', $type)
                          ->get();

        return [
            'total_sent' => $logs->where('status', 'sent')->count(),
            'total_delivered' => $logs->where('status', 'delivered')->count(),
            'total_opened' => $logs->where('status', 'opened')->count(),
            'total_clicked' => $logs->where('status', 'clicked')->count(),
            'total_failed' => $logs->where('status', 'failed')->count(),

            'by_channel' => [
                'email' => $logs->where('channel', 'email')->count(),
                'sms' => $logs->where('channel', 'sms')->count()
            ],

            'conversion_rate' => $this->calculateConversionRate($logs),
            'engagement_rate' => $this->calculateEngagementRate($logs)
        ];
    }

    /**
     * Comparaison des canaux
     */
    public function compareChannels(int $campaignId): array
    {
        return [
            'email' => $this->getChannelStats($campaignId, 'email'),
            'sms' => $this->getChannelStats($campaignId, 'sms')
        ];
    }
}
```

---

## 🌐 Gateways Email

### Interface EmailGatewayInterface
```php
<?php
namespace Modules\EmailMarketing\Gateways;

interface EmailGatewayInterface
{
    public function send(
        string $to,
        string $subject,
        string $html,
        array $options = []
    ): array;

    public function sendBulk(array $recipients, string $subject, string $html): array;

    public function getDeliveryStatus(string $messageId): array;

    public function getCredits(): float;
}
```

### Implémentations
1. **TwilioEmailGateway** (SendGrid)
2. **InfobipEmailGateway**
3. **ElasticMailGateway**
4. **SmtpGateway** (Générique)
5. **MockEmailGateway** (Tests)

---

## 📝 Routes du Module

```php
// Dashboard
GET  /admin/email-marketing
GET  /admin/email-marketing/statistics

// Campaigns
GET  /admin/email-marketing/campaigns
GET  /admin/email-marketing/campaigns/create
POST /admin/email-marketing/campaigns/store
GET  /admin/email-marketing/campaigns/{id}
POST /admin/email-marketing/campaigns/{id}/send
POST /admin/email-marketing/campaigns/{id}/pause
POST /admin/email-marketing/campaigns/{id}/resume

// Templates
GET  /admin/email-marketing/templates
GET  /admin/email-marketing/templates/create
POST /admin/email-marketing/templates/store
GET  /admin/email-marketing/templates/{id}/edit

// Workflows
GET  /admin/email-marketing/workflows
GET  /admin/email-marketing/workflows/create
POST /admin/email-marketing/workflows/store
POST /admin/email-marketing/workflows/{id}/execute

// Analytics
GET  /admin/email-marketing/analytics/{campaignId}
GET  /admin/email-marketing/analytics/compare

// API
POST /api/v1/email/send
POST /api/v1/email/send-bulk
GET  /api/v1/email/stats
POST /api/v1/workflow/trigger
```

---

## 🔄 Workflow Multicanal - Exemple

### Scénario: Campagne de Bienvenue
```json
{
  "name": "Welcome Campaign",
  "trigger_type": "event",
  "trigger_config": {
    "event": "user.registered"
  },
  "steps": [
    {
      "order": 1,
      "channel": "email",
      "template_id": 10,
      "delay": 0,
      "subject": "Bienvenue sur notre plateforme!"
    },
    {
      "order": 2,
      "channel": "sms",
      "message": "Merci de vous être inscrit! Votre code promo: WELCOME20",
      "delay": 3600
    },
    {
      "order": 3,
      "channel": "email",
      "template_id": 11,
      "delay": 86400,
      "subject": "Découvrez nos fonctionnalités"
    }
  ]
}
```

### Exécution
1. **T+0** : Email de bienvenue envoyé
2. **T+1h** : SMS avec code promo
3. **T+24h** : Email de découverte

---

## 📊 Dashboard Unifié

### Métriques Globales
- Total campagnes (Email + SMS)
- Taux d'ouverture moyen
- Taux de clic moyen
- Coût total par canal
- ROI par canal

### Graphiques
- Évolution des envois par canal
- Comparaison Email vs SMS
- Heatmap des heures d'envoi
- Taux d'engagement par segment

---

## ✅ Checklist d'Implémentation

### Phase 1: Base de Données (Priorité: Haute)
- [ ] Créer migration `email_campaigns`
- [ ] Créer migration `email_messages`
- [ ] Créer migration `email_templates`
- [ ] Créer migration `email_logs`
- [ ] Créer migration `campaign_logs` (centralisé)
- [ ] Créer migration `workflows`
- [ ] Créer migration `workflow_executions`

### Phase 2: Models (Priorité: Haute)
- [ ] EmailCampaign.php
- [ ] EmailMessage.php
- [ ] EmailTemplate.php
- [ ] EmailLog.php
- [ ] CampaignLog.php
- [ ] Workflow.php
- [ ] WorkflowExecution.php

### Phase 3: Services (Priorité: Haute)
- [ ] EmailSenderService.php
- [ ] EmailGatewayFactory.php
- [ ] MultiChannelService.php ⭐
- [ ] CampaignAnalyticsService.php
- [ ] TemplateEngine.php

### Phase 4: Gateways (Priorité: Moyenne)
- [ ] EmailGatewayInterface.php
- [ ] TwilioEmailGateway.php
- [ ] InfobipEmailGateway.php
- [ ] ElasticMailGateway.php
- [ ] SmtpGateway.php
- [ ] MockEmailGateway.php

### Phase 5: Controllers (Priorité: Moyenne)
- [ ] DashboardController.php
- [ ] EmailCampaignController.php
- [ ] EmailTemplateController.php
- [ ] WorkflowController.php
- [ ] EmailApiController.php

### Phase 6: Views (Priorité: Moyenne)
- [ ] Dashboard
- [ ] Liste campagnes
- [ ] Créer/éditer campagne
- [ ] Éditeur de templates
- [ ] Builder de workflows
- [ ] Analytics

### Phase 7: Intégration SMS (Priorité: Haute)
- [ ] Connecter MultiChannelService au SmsSenderService existant
- [ ] Migrer logs SMS vers `campaign_logs`
- [ ] Adapter SmsCampaignController pour logs centralisés
- [ ] Dashboard unifié SMS + Email

### Phase 8: Jobs & Queue (Priorité: Moyenne)
- [ ] SendBulkEmailJob.php
- [ ] SendWorkflowJob.php
- [ ] ProcessEmailQueueJob.php

### Phase 9: Tests (Priorité: Basse)
- [ ] Tests unitaires services
- [ ] Tests intégration gateways
- [ ] Tests workflows
- [ ] Tests E2E campagnes

---

## 🚀 Ordre de Développement Recommandé

### Semaine 1: Fondations
1. Structure BDD + Migrations
2. Models de base
3. EmailSenderService (simple)
4. Gateway SMTP générique

### Semaine 2: Intégration SMS
1. CampaignLog (table centralisée)
2. MultiChannelService
3. Adapter module SMS existant
4. Dashboard unifié

### Semaine 3: Campagnes
1. EmailCampaignController
2. Vues campagnes
3. Système de templates
4. Envoi bulk avec queue

### Semaine 4: Workflows
1. WorkflowController
2. Exécution workflows
3. Builder UI (drag & drop)
4. Tests E2E

### Semaine 5: Analytics & Gateways
1. CampaignAnalyticsService
2. Dashboard reporting
3. Gateways premium (Twilio, Infobip, ElasticMail)
4. API RESTful

---

## 🔐 Permissions RBAC

```php
// config/permissions.php
return [
    'email_marketing.view' => 'Voir email marketing',
    'email_marketing.send' => 'Envoyer emails',
    'email_marketing.campaigns.manage' => 'Gérer campagnes',
    'email_marketing.templates.manage' => 'Gérer templates',
    'email_marketing.workflows.manage' => 'Gérer workflows',
    'email_marketing.analytics.view' => 'Voir analytics',
    'email_marketing.settings.manage' => 'Gérer paramètres',
];
```

---

## 📦 Dépendances & Librairies

### Composer
```json
{
    "require": {
        "php": "^8.0",
        "symfony/mailer": "^6.0",
        "sendgrid/sendgrid": "^8.0",
        "league/html-to-markdown": "^5.0",
        "mjml/mjml": "^4.0"
    }
}
```

### NPM (pour l'éditeur drag & drop)
```json
{
    "dependencies": {
        "unlayer": "^1.0",
        "grapesjs": "^0.20"
    }
}
```

---

## 💰 Facturation

### Modèle de Coûts
- **Email**: 0.001€ par email
- **SMS**: Variable selon pays (géré par SmsBillingService existant)
- **Workflow**: Somme des coûts de chaque canal

### Table `billing_transactions` (Réutiliser existante)
```sql
-- Ajouter colonne pour différencier email/sms
ALTER TABLE billing_transactions
ADD COLUMN channel ENUM('email','sms') DEFAULT 'sms';
```

---

## 🎯 Résumé des Avantages

✅ **Réutilisation maximale** du module SMS existant
✅ **Logs centralisés** dans `campaign_logs`
✅ **Workflow multicanal** automatisé
✅ **Gateways extensibles** (Twilio, Infobip, ElasticMail)
✅ **Table contacts unifiée** (email + phone)
✅ **Analytics consolidé** SMS + Email
✅ **Architecture modulaire** et scalable
✅ **RGPD compliant** (opt-in/opt-out)

---

**Date**: 2025-12-02
**Version**: 1.0
**Statut**: Plan d'architecture validé ✅
