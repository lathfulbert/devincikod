# HTTP Client - Usage Guide

## 🚀 Quick Start

### Simple GET Request

```php
$response = Http()->get('https://api.github.com/users/github');
$data = $response->json();
```

### POST with JSON

```php
$response = Http()->post('https://api.example.com/users', [
    'name' => 'John Doe',
    'email' => 'john@example.com',
]);

if ($response->successful()) {
    $user = $response->json();
}
```

### With Authentication

```php
// Bearer Token
$response = Http()->withToken('your-api-token')
    ->get('https://api.example.com/user');

// Basic Auth
$response = Http()->withBasicAuth('username', 'password')
    ->get('https://api.example.com/data');
```

### Custom Headers

```php
$response = Http()->withHeaders([
    'X-Custom-Header' => 'value',
    'Accept' => 'application/json',
])->post('https://api.example.com/data', $payload);
```

### Timeout & Retry

```php
$response = Http()->timeout(60)
    ->retry(3, 100) // 3 retries, 100ms between
    ->get('https://slow-api.com/data');
```

### File Upload

```php
$fileContent = file_get_contents('/path/to/file.pdf');

$response = Http()->attach('document', $fileContent, 'report.pdf')
    ->post('https://api.example.com/upload', [
        'title' => 'Monthly Report',
    ]);
```

### Disable SSL Verification (not recommended)

```php
$response = Http()->withoutVerifying()
    ->get('https://self-signed-cert.com/api');
```

## 📖 Response Methods

```php
$response->body();           // Get raw body
$response->json();           // Parse as JSON
$response->status();         // Get status code
$response->successful();     // Check if 2xx
$response->failed();         // Check if not 2xx
$response->ok();            // Check if 200
$response->created();       // Check if 201
$response->serverError();   // Check if 5xx
$response->header('Content-Type'); // Get header
$response->headers();       // Get all headers
```

## 🔧 Advanced Usage

### All HTTP Methods

```php
Http()->get($url, $query);
Http()->post($url, $data);
Http()->put($url, $data);
Http()->patch($url, $data);
Http()->delete($url);
Http()->head($url);
```

### Content Types

```php
// JSON (default)
Http()->asJson()->post($url, $data);

// Form Data
Http()->asForm()->post($url, $data);

// Multipart (for files)
Http()->asMultipart()->post($url, $data);
```

### Error Handling

```php
use App\Core\Http\Exceptions\RequestException;
use App\Core\Http\Exceptions\ConnectionException;

try {
    $response = Http()->get('https://api.example.com/data');

    if ($response->failed()) {
        // Handle HTTP error
    }

    $data = $response->json();

} catch (ConnectionException $e) {
    // Network error (timeout, DNS, etc)
    echo "Connection failed: " . $e->getMessage();

} catch (RequestException $e) {
    // Request error
    echo "Request failed: " . $e->getMessage();
}
```

## 📝 Examples

### GitHub API

```php
$user = Http()->get('https://api.github.com/users/torvalds')->json();
echo $user['name']; // Linus Torvalds
```

### Create Resource

```php
$response = Http()->withToken($apiToken)
    ->post('https://api.stripe.com/v1/charges', [
        'amount' => 2000,
        'currency' => 'usd',
        'source' => 'tok_visa',
    ]);

if ($response->created()) {
    $charge = $response->json();
}
```

### Download File

```php
$response = Http()->get('https://example.com/file.pdf');
file_put_contents('downloaded.pdf', $response->body());
```

## ⚙️ Configuration

Edit `config/http.php`:

```php
return [
    'timeout' => 30,        // Default timeout
    'verify' => true,       // SSL verification
    'headers' => [          // Default headers
        'User-Agent' => 'MyApp/1.0',
    ],
];
```

## 🎯 Best Practices

1. **Always check response status**

   ```php
   if ($response->successful()) {
       // Process data
   }
   ```

2. **Use try-catch for external APIs**

   ```php
   try {
       $data = Http()->get($url)->json();
   } catch (Exception $e) {
       // Handle error
   }
   ```

3. **Set appropriate timeouts**

   ```php
   Http()->timeout(10)->get($url); // 10 seconds
   ```

4. **Use retry for unstable APIs**
   ```php
   Http()->retry(3)->get($url);
   ```

## 🚀 HTTP Client Ready for Production!
