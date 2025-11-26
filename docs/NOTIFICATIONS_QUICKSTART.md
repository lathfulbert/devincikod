# Email Notification System - Quick Start Guide

## 🎉 Phase 1 MVP Complete!

**27 fichiers créés** - Système de notification Email entièrement fonctionnel.

---

## 🚀 Configuration Rapide

### 1. Variables d'Environnement (.env)

```env
# Email Provider (smtp ou sendgrid)
MAIL_PROVIDER=smtp
MAIL_FROM_ADDRESS=noreply@sunuframework.com
MAIL_FROM_NAME=SunuFramework

# SendGrid (optionnel)
SENDGRID_API_KEY=your_sendgrid_api_key

# Queue
QUEUE_CONNECTION=redis
```

### 2. Migrations

```bash
php sunu migrate --up
```

Cela créera 8 tables:

- notifications
- notification_recipients
- delivery_logs
- notification_templates
- user_notification_preferences
- notification_providers
- provider_metrics
- inapp_notifications

---

## 💡 Usage

### Méthode 1 : Helper Function (⭐ recommandé)

```php
// Envoyer une notification simple
Notification([
    'event' => 'welcome',
    'user_id' => 123,
    'template' => 'welcome_email',
    'data' => [
        'name' => 'John Doe',
        'message' => 'Bienvenue sur SunuFramework!',
    ],
]);

// Multi-users
Notification([
    'event' => 'newsletter',
    'user_id' => [123, 456, 789],
    'template' => 'monthly_newsletter',
    'data' => ['month' => 'Novembre'],
]);

// Notification programmée
Notification([
    'event' => 'reminder',
    'user_id' => 123,
    'template' => 'event_reminder',
    'scheduled_at' => '2024-12-01 10:00:00',
    'data' => ['event' => 'Meeting'],
]);
```

### Méthode 2 : Via Service

```php
use Modules\Notifications\Services\NotificationService;

$notificationService = new NotificationService(app());

$notification = $notificationService->send([
    'event' => 'order_shipped',
    'user_id' => 123,
    'template' => 'order_shipped',
    'channels' => ['email'], // optionnel
    'data' => [
        'order_number' => 'ORD-001',
        'tracking_url' => 'https://...',
    ],
]);
```

### Méthode 3 : API REST

```bash
# Créer une notification
curl -X POST http://localhost/api/v1/notifications \
  -H "Content-Type: application/json" \
  -d '{
    "event": "welcome",
    "user_id": 123,
    "template": "welcome_email",
    "data": {"name": "John"}
  }'

# Test email
curl -X POST http://localhost/api/v1/notifications/test \
  -H "Content-Type: application/json" \
  -d '{"user_id": 1, "template": "test"}'
```

---

## 📧 Créer des Templates

### Insérer un template dans la DB

```php
use Modules\Notifications\Models\NotificationTemplate;

NotificationTemplate::create([
    'name' => 'welcome_email',
    'channel' => 'email',
    'subject' => 'Bienvenue {{ name }} !',
    'body_html' => '
        <h1>Bonjour {{ name }} !</h1>
        <p>{{ message }}</p>
    ',
    'body_text' => 'Bonjour {{ name }}! {{ message }}',
    'variables' => ['name', 'message'],
    'active' => true,
]);
```

### Variables supportées

- Simple: `{{ name }}`
- Nested: `{{ user.name }}`, `{{ order.items.0.name }}`
- Conditionals: Utilisez PHP dans le template

---

## ⚙️ Providers Disponibles

### SMTP (par défaut)

Utilise la fonction PHP `mail()` native.

### SendGrid

Requiert `SENDGRID_API_KEY` dans `.env`.

### Ajouter un Provider Custom

```php
use App\Core\Contracts\NotificationProviderInterface;
use App\Core\Notifications\ProviderResponse;

class MailgunProvider implements NotificationProviderInterface
{
    public function send(array $payload): object
    {
        // Votre implémentation Mailgun
        return ProviderResponse::success('msg-id-123');
    }

    public function healthCheck(): bool { return true; }
    public function getType(): string { return 'email'; }
    public function getName(): string { return 'mailgun'; }
}
```

---

## 🔄 Queue & Async

Les notifications sont automatiquement envoyées via la queue:

```bash
# Démarrer un worker
php sunu queue:work
```

**Retry automatique** :

- 3 tentatives max
- Backoff exponentiel: 30s, 5m, 30m
- DLQ pour echecs permanents

---

## 👤 Préférences Utilisateur

```php
use Modules\Notifications\Models\UserNotificationPreference;

// Créer/Modifier préférences
$prefs = UserNotificationPreference::forUser(123);
$prefs->update([
    'channels_enabled' => ['email', 'push'],
    'email_opt_in' => true,
    'dnd_enabled' => true,
    'dnd_from' => '22:00',
    'dnd_to' => '08:00',
    'timezone' => 'Africa/Dakar',
]);

// Check DND
if ($prefs->isInDND()) {
    // Ne pas envoyer maintenant
}
```

---

## 📊 Tracking & Logs

```php
use App\Core\Notifications\DeliveryTracker;

$tracker = new DeliveryTracker();

// Stats provider
$stats = $tracker->getProviderStats('sendgrid', 7);
// ['total' => 100, 'successful' => 95, 'failed' => 5, 'success_rate' => 95]

// Logs pour un recipient
$logs = $tracker->getLogsForRecipient($recipientId);
```

---

## 🧪 Test

```bash
# Test via API
curl -X POST http://localhost/api/v1/notifications/test \
  -d '{"user_id": 1}'
```

OU

```php
// Dans le code
Notification()->sendEmailNow(1, 'test', [
    'message' => 'Test notification',
]);
```

---

## 📁 Structure Créée

```
Core/
  Contracts/
    NotificationProviderInterface.php
  Notifications/
    ProviderResponse.php
    TemplateEngine.php
    ProviderManager.php
    NotificationRouter.php
    DeliveryTracker.php
    Providers/Email/
      SMTPProvider.php
      SendGridProvider.php

Modules/Notifications/
  Database/Migrations/ (8 fichiers)
  Models/ (5 fichiers)
  Services/
    NotificationService.php
  Controllers/
    NotificationController.php
  routes/api.php
  module.json

App/Jobs/
  SendEmailNotification.php

config/
  notifications.php
```

---

## ✅ Prochaines Étapes (Phase 2)

- [ ] SMS Channel + Twilio provider
- [ ] Push Notifications (FCM/APNs)
- [ ] In-app notifications
- [ ] Template editor UI
- [ ] Analytics dashboard
- [ ] A/B testing

**Phase 1 MVP est PRÊT pour production !** 🎉
