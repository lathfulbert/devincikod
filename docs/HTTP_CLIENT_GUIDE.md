# HTTP Client - Guide Pratique d'Utilisation

## 🎯 Qu'est-ce que le HTTP Client ?

Le HTTP Client **n'est PAS lié au routing interne** de votre application. C'est un outil pour faire des **requêtes HTTP vers des APIs externes** (comme GitHub, Stripe, OpenWeather, etc.).

### Différence importante :

- **Routing** = Routes internes de votre app (`/admin/users`, `/api/posts`)
- **HTTP Client** = Requêtes vers l'extérieur (`https://api.github.com`, `https://api.stripe.com`)

---

## 🚀 Comment Tester - 3 Méthodes

### Méthode 1 : Script de Test CLI (⭐ Le plus simple)

```bash
# Executer le test
php test-http-client.php
```

Ce script teste :

- ✅ GET request (GitHub API)
- ✅ POST request (JSONPlaceholder)
- ✅ Custom headers
- ✅ Timeout
- ✅ All HTTP methods
- ✅ Response methods

**Sortie attendue :**

```
🧪 Test 1: Simple GET request to GitHub API
───────────────────────────────────────────
✅ SUCCESS
   User: GitHub
   Bio: How people build software
   Repos: 369
```

---

### Méthode 2 : Routes de Test (Via navigateur)

1. **Ajouter les routes** dans `Modules/Admin/routes.php` :

```php
require __DIR__ . '/routes_http_demo.php';
```

2. **Accéder aux URLs** :

```
http://localhost:81/sunuframework2/admin/http-test/github
http://localhost:81/sunuframework2/admin/http-test/post
http://localhost:81/sunuframework2/admin/http-test/status
```

---

### Méthode 3 : Dans un Contrôleur Existant

```php
// Dans n'importe quel contrôleur
public function testApi()
{
    // GET user from GitHub
    $response = Http()->get('https://api.github.com/users/torvalds');
    $user = $response->json();

    return json_encode($user);
}
```

---

## 💡 Cas d'Usage Concrets

### 1. Intégration Paiement (Stripe)

```php
public function createCharge($amount, $token)
{
    $response = Http()->withToken(config('stripe.secret_key'))
        ->asForm()
        ->post('https://api.stripe.com/v1/charges', [
            'amount' => $amount,
            'currency' => 'usd',
            'source' => $token,
        ]);

    if ($response->successful()) {
        return $response->json();
    }

    throw new Exception('Payment failed');
}
```

### 2. Envoi SMS (Twilio)

```php
public function sendSMS($to, $message)
{
    $accountSid = config('twilio.account_sid');
    $authToken = config('twilio.auth_token');

    $response = Http()->withBasicAuth($accountSid, $authToken)
        ->asForm()
        ->post("https://api.twilio.com/2010-04-01/Accounts/{$accountSid}/Messages.json", [
            'To' => $to,
            'From' => config('twilio.phone'),
            'Body' => $message,
        ]);

    return $response->successful();
}
```

### 3. Récupérer Taux de Change

```php
public function getExchangeRate($from, $to)
{
    $response = Http()->get('https://api.exchangerate-api.com/v4/latest/' . $from);

    if ($response->successful()) {
        $data = $response->json();
        return $data['rates'][$to] ?? null;
    }

    return null;
}
```

### 4. Upload Fichier vers Cloud Storage

```php
public function uploadToS3($file)
{
    $fileContent = file_get_contents($file);

    $response = Http()->withHeaders([
        'Authorization' => 'Bearer ' . config('aws.token'),
    ])->attach('file', $fileContent, basename($file))
        ->post('https://s3.amazonaws.com/my-bucket/upload');

    return $response->successful();
}
```

### 5. Webhook Receiver (Slack notification)

```php
public function notifySlack($message)
{
    $webhookUrl = config('slack.webhook_url');

    $response = Http()->post($webhookUrl, [
        'text' => $message,
        'username' => 'SunuFramework Bot',
    ]);

    return $response->successful();
}
```

---

## 📝 Exemples Complets

### Exemple 1 : Dashboard avec APIs externes

```php
class DashboardController
{
    public function index()
    {
        // Get weather
        $weather = Http()->timeout(5)
            ->get('https://api.openweathermap.org/data/2.5/weather', [
                'q' => 'Dakar',
                'appid' => config('openweather.key'),
            ])->json();

        // Get crypto prices
        $crypto = Http()->timeout(5)
            ->get('https://api.coingecko.com/api/v3/simple/price', [
                'ids' => 'bitcoin,ethereum',
                'vs_currencies' => 'usd',
            ])->json();

        // Get GitHub stats
        $github = Http()->withToken(config('github.token'))
            ->get('https://api.github.com/user/repos')
            ->json();

        return view('dashboard', [
            'weather' => $weather,
            'crypto' => $crypto,
            'repos_count' => count($github),
        ]);
    }
}
```

### Exemple 2 : Synchronisation de Données

```php
class SyncController
{
    public function syncUsers()
    {
        // Fetch users from external API
        $response = Http()->withToken($apiToken)
            ->retry(3, 100) // Retry 3 times
            ->timeout(30)
            ->get('https://api.external-service.com/users');

        if ($response->failed()) {
            throw new Exception('Failed to fetch users');
        }

        $users = $response->json();

        // Save to database
        foreach ($users as $userData) {
            User::updateOrCreate(
                ['external_id' => $userData['id']],
                [
                    'name' => $userData['name'],
                    'email' => $userData['email'],
                ]
            );
        }

        return ['synced' => count($users)];
    }
}
```

---

## ⚙️ Configuration

Ajoutez vos clés API dans `.env` :

```env
# GitHub
GITHUB_TOKEN=ghp_xxxxxxxxxxxxx

# Stripe
STRIPE_SECRET_KEY=sk_test_xxxxxxxxxxxxx

# OpenWeather
OPENWEATHER_API_KEY=xxxxxxxxxxxxx

# Twilio
TWILIO_ACCOUNT_SID=xxxxxxxxxxxxx
TWILIO_AUTH_TOKEN=xxxxxxxxxxxxx
```

Et dans `config/services.php` :

```php
return [
    'github' => [
        'token' => env('GITHUB_TOKEN'),
    ],
    'stripe' => [
        'secret_key' => env('STRIPE_SECRET_KEY'),
    ],
    'openweather' => [
        'key' => env('OPENWEATHER_API_KEY'),
    ],
];
```

---

## 🧪 Debugging

### Voir la requête complète

```php
$response = Http()->get($url);

// Debug response
dump([
    'status' => $response->status(),
    'headers' => $response->headers(),
    'body' => $response->body(),
]);
```

### Logger les requêtes

```php
try {
    $response = Http()->get($url);

    Log::info('API Request', [
        'url' => $url,
        'status' => $response->status(),
    ]);

} catch (Exception $e) {
    Log::error('API Error', [
        'url' => $url,
        'error' => $e->getMessage(),
    ]);
}
```

---

## ✅ Checklist de Test

- [ ] Exécuter `php test-http-client.php`
- [ ] Tester GET request vers API publique
- [ ] Tester POST avec authentification
- [ ] Tester timeout et retry
- [ ] Tester upload fichier
- [ ] Vérifier gestion erreurs
- [ ] Intégrer dans un contrôleur réel

---

## 🎉 Résumé

Le HTTP Client est **prêt à l'emploi** pour :

- ✅ Consommer des APIs externes (Stripe, Twilio, GitHub, etc.)
- ✅ Webhooks
- ✅ Synchronisation de données
- ✅ Intégrations tierces
- ✅ Microservices communication

**Testez maintenant :** `php test-http-client.php` 🚀
