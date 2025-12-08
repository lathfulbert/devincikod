<?php

use App\Core\Application;

/*
|--------------------------------------------------------------------------
| Debug Helpers
|--------------------------------------------------------------------------
*/

if (!function_exists('dd')) {
    /**
     * Dump the passed variables and end the script.
     */
    function dd(...$vars)
    {
        foreach ($vars as $var) {
            echo '<pre>';
            var_dump($var);
            echo '</pre>';
        }
        die(1);
    }
}

if (!function_exists('dump')) {
    /**
     * Dump the passed variables without ending the script.
     */
    function dump(...$vars)
    {
        foreach ($vars as $var) {
            echo '<pre>';
            var_dump($var);
            echo '</pre>';
        }
    }
}

/*
|--------------------------------------------------------------------------
| Application & Config Helpers
|--------------------------------------------------------------------------
*/

if (!function_exists('app')) {
    /**
     * Get the available container instance.
     */
    function app()
    {
        return Application::getInstance();
    }
}

if (!function_exists('auth')) {
    /**
     * Get the Auth instance.
     * 
     * @return \App\Core\Auth\Auth
     */
    function auth(): \App\Core\Auth\Auth
    {
        static $auth = null;

        if ($auth === null) {
            $auth = new \App\Core\Auth\Auth();
        }

        return $auth;
    }
}


if (!function_exists('config')) {
    /**
     * Get / set the specified configuration value.
     */
    function config(string $key = null, mixed $default = null)
    {
        if (is_null($key)) {
            return app()->config;
        }
        return app()->config->get($key, $default);
    }
}

if (!function_exists('env')) {
    /**
     * Get the value of an environment variable.
     */
    function env(string $key, mixed $default = null)
    {
        $value = getenv($key);
        if ($value === false) {
            return $default;
        }

        switch (strtolower($value)) {
            case 'true':
            case '(true)':
                return true;
            case 'false':
            case '(false)':
                return false;
            case 'empty':
            case '(empty)':
                return '';
            case 'null':
            case '(null)':
                return null;
        }

        return $value;
    }
}

/*
|--------------------------------------------------------------------------
| Environment Helpers
|--------------------------------------------------------------------------
*/

if (!function_exists('environment')) {
    /**
     * Get the current application environment.
     *
     * @return string
     */
    function environment(): string
    {
        return env('APP_ENV', 'production');
    }
}

if (!function_exists('isDevelopment')) {
    /**
     * Check if the application is in development mode.
     *
     * @return bool
     */
    function isDevelopment(): bool
    {
        return environment() === 'development' || environment() === 'dev' || environment() === 'local';
    }
}

if (!function_exists('isProduction')) {
    /**
     * Check if the application is in production mode.
     *
     * @return bool
     */
    function isProduction(): bool
    {
        return environment() === 'production' || environment() === 'prod';
    }
}

if (!function_exists('isStaging')) {
    /**
     * Check if the application is in staging mode.
     *
     * @return bool
     */
    function isStaging(): bool
    {
        return environment() === 'staging' || environment() === 'stage';
    }
}

if (!function_exists('isTesting')) {
    /**
     * Check if the application is in testing mode.
     *
     * @return bool
     */
    function isTesting(): bool
    {
        return environment() === 'testing' || environment() === 'test';
    }
}

if (!function_exists('isDebugMode')) {
    /**
     * Check if debug mode is enabled.
     *
     * @return bool
     */
    function isDebugMode(): bool
    {
        return env('APP_DEBUG', false) === true;
    }
}

/*
|--------------------------------------------------------------------------
| Encryption Helpers
|--------------------------------------------------------------------------
*/

if (!function_exists('encrypt')) {
    /**
     * Encrypt the given value.
     *
     * @param mixed $value
     * @param bool $serialize
     * @return string
     * @throws \Exception
     */
    function encrypt($value, bool $serialize = true): string
    {
        static $encrypter = null;

        if ($encrypter === null) {
            $key = env('APP_KEY');

            if (empty($key)) {
                throw new \RuntimeException('APP_KEY is not set. Run: php sunu key:generate');
            }

            // Remove base64: prefix if present
            if (str_starts_with($key, 'base64:')) {
                $key = base64_decode(substr($key, 7));
            }

            $encrypter = new \App\Core\Encryption\Encrypter($key, 'AES-256-CBC');
        }

        return $encrypter->encrypt($value, $serialize);
    }
}

if (!function_exists('decrypt')) {
    /**
     * Decrypt the given value.
     *
     * @param string $payload
     * @param bool $unserialize
     * @return mixed
     * @throws \Exception
     */
    function decrypt(string $payload, bool $unserialize = true)
    {
        static $encrypter = null;

        if ($encrypter === null) {
            $key = env('APP_KEY');

            if (empty($key)) {
                throw new \RuntimeException('APP_KEY is not set. Run: php sunu key:generate');
            }

            // Remove base64: prefix if present
            if (str_starts_with($key, 'base64:')) {
                $key = base64_decode(substr($key, 7));
            }

            $encrypter = new \App\Core\Encryption\Encrypter($key, 'AES-256-CBC');
        }

        return $encrypter->decrypt($payload, $unserialize);
    }
}

if (!function_exists('encryptString')) {
    /**
     * Encrypt a string without serialization.
     *
     * @param string $value
     * @return string
     */
    function encryptString(string $value): string
    {
        return encrypt($value, false);
    }
}

if (!function_exists('decryptString')) {
    /**
     * Decrypt a string without unserialization.
     *
     * @param string $payload
     * @return string
     */
    function decryptString(string $payload): string
    {
        return decrypt($payload, false);
    }
}

/*
|--------------------------------------------------------------------------
| Secure Cookie Helpers
|--------------------------------------------------------------------------
*/

if (!function_exists('setSecureCookie')) {
    /**
     * Set an encrypted cookie.
     *
     * @param string $name Cookie name
     * @param mixed $value Cookie value (will be encrypted)
     * @param int $expire Expiration time in seconds from now (default: 1 hour)
     * @param string $path Cookie path
     * @param string $domain Cookie domain
     * @param bool $secure Send only over HTTPS
     * @param bool $httponly HTTP only (not accessible via JavaScript)
     * @return bool
     */
    function setSecureCookie(
        string $name,
        $value,
        int $expire = 3600,
        string $path = '/',
        string $domain = '',
        ?bool $secure = null,
        bool $httponly = true
    ): bool {
        // Auto-detect HTTPS
        if ($secure === null) {
            $secure = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ||
                      ($_SERVER['SERVER_PORT'] ?? 80) == 443 ||
                      isProduction();
        }

        // Encrypt the value
        try {
            $encrypted = encrypt($value);

            return setcookie(
                $name,
                $encrypted,
                time() + $expire,
                $path,
                $domain,
                $secure,
                $httponly
            );
        } catch (\Exception $e) {
            error_log("Failed to set secure cookie '$name': " . $e->getMessage());
            return false;
        }
    }
}

if (!function_exists('getSecureCookie')) {
    /**
     * Get and decrypt a cookie value.
     *
     * @param string $name Cookie name
     * @param mixed $default Default value if cookie doesn't exist or can't be decrypted
     * @return mixed
     */
    function getSecureCookie(string $name, $default = null)
    {
        if (!isset($_COOKIE[$name])) {
            return $default;
        }

        try {
            return decrypt($_COOKIE[$name]);
        } catch (\Exception $e) {
            error_log("Failed to decrypt cookie '$name': " . $e->getMessage());
            return $default;
        }
    }
}

if (!function_exists('deleteSecureCookie')) {
    /**
     * Delete a cookie.
     *
     * @param string $name Cookie name
     * @param string $path Cookie path
     * @param string $domain Cookie domain
     * @return bool
     */
    function deleteSecureCookie(string $name, string $path = '/', string $domain = ''): bool
    {
        if (isset($_COOKIE[$name])) {
            unset($_COOKIE[$name]);
        }

        return setcookie($name, '', time() - 3600, $path, $domain);
    }
}

/*
|--------------------------------------------------------------------------
| Secure Session Helpers
|--------------------------------------------------------------------------
*/

if (!function_exists('sessionPut')) {
    /**
     * Store an encrypted value in the session.
     *
     * @param string $key Session key
     * @param mixed $value Value to encrypt and store
     * @return void
     */
    function sessionPut(string $key, $value): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        try {
            $_SESSION['_encrypted'][$key] = encrypt($value);
        } catch (\Exception $e) {
            error_log("Failed to encrypt session value for key '$key': " . $e->getMessage());
        }
    }
}

if (!function_exists('sessionGet')) {
    /**
     * Retrieve and decrypt a value from the session.
     *
     * @param string $key Session key
     * @param mixed $default Default value if key doesn't exist
     * @return mixed
     */
    function sessionGet(string $key, $default = null)
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (!isset($_SESSION['_encrypted'][$key])) {
            return $default;
        }

        try {
            return decrypt($_SESSION['_encrypted'][$key]);
        } catch (\Exception $e) {
            error_log("Failed to decrypt session value for key '$key': " . $e->getMessage());
            return $default;
        }
    }
}

if (!function_exists('sessionForget')) {
    /**
     * Remove an encrypted value from the session.
     *
     * @param string $key Session key
     * @return void
     */
    function sessionForget(string $key): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (isset($_SESSION['_encrypted'][$key])) {
            unset($_SESSION['_encrypted'][$key]);
        }
    }
}

if (!function_exists('component')) {
    /**
     * Include an admin component from resources/views/backend/components/
     */
    function component(string $name, array $data = [])
    {
        // Protection contre la récursion infinie
        static $depth = 0;
        if ($depth > 10) {
            error_log("Component recursion detected for: $name");
            return;
        }

        $depth++;

        // Chercher uniquement dans resources/views/backend/components/
        $componentPath = dirname(__DIR__, 2) . '/resources/views/backend/components/' . $name . '.php';

        if (file_exists($componentPath)) {
            extract($data, EXTR_SKIP);
            include $componentPath;
        } else {
            error_log("Component not found: $name at $componentPath");
        }

        $depth--;
    }
}

/*
|--------------------------------------------------------------------------
| URL & Routing Helpers
|--------------------------------------------------------------------------
*/

if (!function_exists('url')) {
    /**
     * Generate a url for the application.
     */
    function url(string $path = ''): string
    {
        static $baseUrl = null;

        if ($baseUrl === null) {
            $configUrl = config('app.url');

            if ($configUrl !== null) {
                $baseUrl = rtrim($configUrl, '/');
            } else {
                // Auto-detect base path from SCRIPT_NAME
                $scriptName = $_SERVER['SCRIPT_NAME'] ?? '/index.php';

                // Get directory of the script (e.g., /sunuframework2/public or /public)
                $scriptDir = str_replace('\\', '/', dirname($scriptName));

                // Remove '/public' suffix if present
                if (substr($scriptDir, -7) === '/public') {
                    $scriptDir = substr($scriptDir, 0, -7);
                }

                // Clean up: remove trailing slash, but keep root '/'
                $baseUrl = ($scriptDir === '' || $scriptDir === '/') ? '' : rtrim($scriptDir, '/');
            }
        }

        if (!empty($path) && $path[0] !== '/') {
            $path = '/' . $path;
        }

        return $baseUrl . $path;
    }
}

if (!function_exists('asset')) {
    /**
     * Generate an asset path for the application.
     */
    function asset(string $path): string
    {
        return url('public/' . ltrim($path, '/'));
    }
}

if (!function_exists('redirect')) {
    /**
     * Get an instance of the redirector.
     */
    function redirect(string $path): void
    {
        if (preg_match('/^https?:\/\//', $path)) {
            header('Location: ' . $path);
        } else {
            header('Location: ' . url($path));
        }
        exit;
    }
}

/*
|--------------------------------------------------------------------------
| Route Helpers
|--------------------------------------------------------------------------
*/

if (!function_exists('route')) {
    /**
     * Generate a URL for a named route.
     * 
     * @param string $name Route name
     * @param array $params Route parameters
     * @return string Generated URL
     */
    function route(string $name, array $params = []): string
    {
        return app()->router->route($name, $params);
    }
}

if (!function_exists('current_route_name')) {
    /**
     * Get the current route name.
     * 
     * @return string|null Current route name or null
     */
    function current_route_name(): ?string
    {
        return app()->router->currentRouteName();
    }
}

if (!function_exists('is_active_route')) {
    /**
     * Check if the given route name matches the current route.
     * 
     * @param string|array $routeNames Route name(s) to check
     * @param string $activeClass CSS class to return if active
     * @return string Active class or empty string
     */
    function is_active_route(string|array $routeNames, string $activeClass = 'active'): string
    {
        $currentRoute = current_route_name();

        if (!$currentRoute) {
            return '';
        }

        $routeNames = (array) $routeNames;

        foreach ($routeNames as $routeName) {
            // Exact match
            if ($currentRoute === $routeName) {
                return $activeClass;
            }

            // Support wildcard matching (e.g., 'admin.*')
            if (str_ends_with($routeName, '.*')) {
                $prefix = substr($routeName, 0, -2);
                if (str_starts_with($currentRoute, $prefix . '.')) {
                    return $activeClass;
                }
            }
        }

        return '';
    }
}

if (!function_exists('current_user')) {
    /**
     * Get the currently authenticated user.
     *
     * @return \Modules\Users\Models\User|array|null
     */
    function current_user()
    {
        // Try from auth helper
        if (function_exists('auth')) {
            $user = auth()->user();
            if ($user) {
                return $user;
            }
        }

        // Fallback to session
        return $_SESSION['user'] ?? null;
    }
}

if (!function_exists('current_user_id')) {
    /**
     * Get the ID of the currently authenticated user.
     *
     * @return int|null
     */
    function current_user_id(): ?int
    {
        $user = current_user();

        if (is_object($user) && isset($user->id)) {
            return (int) $user->id;
        }

        if (is_array($user) && isset($user['id'])) {
            return (int) $user['id'];
        }

        return null;
    }
}

if (!function_exists('can')) {
    /**
     * Check if the current user has a given permission.
     *
     * @param string $permission Permission name
     * @param mixed $model Optional model instance
     * @return bool
     */
    function can(string $permission, $model = null): bool
    {
        // Check if auth system exists
        if (!function_exists('auth')) {
            return false;
        }

        $user = auth()->user();

        if (!$user) {
            return false;
        }

        // If user is an object with can() method, use it
        if (is_object($user) && method_exists($user, 'can')) {
            return $user->can($permission, $model);
        }

        // If user is an object with hasPermission() method, use it
        if (is_object($user) && method_exists($user, 'hasPermission')) {
            return $user->hasPermission($permission);
        }

        // If user is an object, check if admin
        if (is_object($user) && method_exists($user, 'isAdmin') && $user->isAdmin()) {
            return true;
        }

        // If user is an array, check role
        if (is_array($user)) {
            // Check if user is admin (array format)
            if (isset($user['role']) && $user['role'] === 'admin') {
                return true;
            }

            // Check if user has permission in permissions array
            if (isset($user['permissions']) && is_array($user['permissions'])) {
                return in_array($permission, $user['permissions']);
            }
        }

        return false;
    }
}

if (!function_exists('current_url')) {
    /**
     * Get the current URL.
     */
    function current_url(): string
    {
        $protocol = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ? "https" : "http";
        return $protocol . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
    }
}


/*
|--------------------------------------------------------------------------
| View Helpers
|--------------------------------------------------------------------------
*/

if (!function_exists('view')) {
    /**
     * Get the evaluated view contents for the given view.
     * Can be used in two ways:
     * 1. echo view('module/subfolder/view', $data) - Returns rendered content
     * 2. view('module/subfolder/view', $data)->render() - Chainable
     */
    function view(string $view = null, array $data = [], array $mergeData = [])
    {
        $viewInstance = app()->view;

        if (func_num_args() === 0) {
            return $viewInstance;
        }

        return $viewInstance->render($view, array_merge($data, $mergeData));
    }
}

if (!function_exists('e')) {
    /**
     * Escape HTML entities in a string.
     */
    function e(string $value): string
    {
        return htmlspecialchars($value, ENT_QUOTES, 'UTF-8', false);
    }
}

/*
|--------------------------------------------------------------------------
| String Helpers
|--------------------------------------------------------------------------
*/

if (!function_exists('str_limit')) {
    /**
     * Limit the number of characters in a string.
     */
    function str_limit(string $value, int $limit = 100, string $end = '...'): string
    {
        if (mb_strwidth($value, 'UTF-8') <= $limit) {
            return $value;
        }
        return rtrim(mb_strimwidth($value, 0, $limit, '', 'UTF-8')) . $end;
    }
}

if (!function_exists('str_contains')) {
    /**
     * Determine if a given string contains a given substring.
     */
    function str_contains(string $haystack, string|array $needles): bool
    {
        foreach ((array) $needles as $needle) {
            if ($needle !== '' && mb_strpos($haystack, $needle) !== false) {
                return true;
            }
        }
        return false;
    }
}

if (!function_exists('str_starts_with')) {
    /**
     * Determine if a given string starts with a given substring.
     */
    function str_starts_with(string $haystack, string|array $needles): bool
    {
        foreach ((array) $needles as $needle) {
            if ((string) $needle !== '' && str_starts_with_impl($haystack, $needle)) {
                return true;
            }
        }
        return false;
    }
}

if (!function_exists('str_starts_with_impl')) {
    function str_starts_with_impl($haystack, $needle)
    {
        return strncmp($haystack, $needle, strlen($needle)) === 0;
    }
}

if (!function_exists('str_ends_with')) {
    /**
     * Determine if a given string ends with a given substring.
     */
    function str_ends_with(string $haystack, string|array $needles): bool
    {
        foreach ((array) $needles as $needle) {
            if ((string) $needle !== '' && substr($haystack, -strlen($needle)) === (string) $needle) {
                return true;
            }
        }
        return false;
    }
}

if (!function_exists('str_slug')) {
    /**
     * Generate a URL friendly "slug" from a given string.
     */
    function str_slug(string $title, string $separator = '-'): string
    {
        // Convert all dashes/underscores into separator
        $flip = $separator === '-' ? '_' : '-';
        $title = preg_replace('![' . preg_quote($flip) . ']+!u', $separator, $title);
        // Remove all characters that are not the separator, letters, numbers, or whitespace
        $title = preg_replace('![^' . preg_quote($separator) . '\pL\pN\s]+!u', '', mb_strtolower($title));
        // Replace all separator characters and whitespace by a single separator
        $title = preg_replace('![' . preg_quote($separator) . '\s]+!u', $separator, $title);
        return trim($title, $separator);
    }
}

/*
|--------------------------------------------------------------------------
| Array Helpers
|--------------------------------------------------------------------------
*/

if (!function_exists('array_get')) {
    /**
     * Get an item from an array using "dot" notation.
     */
    function array_get(array $array, string $key, mixed $default = null): mixed
    {
        if (is_null($key)) {
            return $array;
        }

        if (array_key_exists($key, $array)) {
            return $array[$key];
        }

        foreach (explode('.', $key) as $segment) {
            if (is_array($array) && array_key_exists($segment, $array)) {
                $array = $array[$segment];
            } else {
                return $default;
            }
        }

        return $array;
    }
}

if (!function_exists('array_first')) {
    /**
     * Return the first element in an array passing a given truth test.
     */
    function array_first(array $array, callable $callback = null, mixed $default = null): mixed
    {
        if (is_null($callback)) {
            if (empty($array)) {
                return $default;
            }
            foreach ($array as $item) {
                return $item;
            }
        }

        foreach ($array as $key => $value) {
            if ($callback($value, $key)) {
                return $value;
            }
        }

        return $default;
    }
}

if (!function_exists('array_last')) {
    /**
     * Return the last element in an array passing a given truth test.
     */
    function array_last(array $array, callable $callback = null, mixed $default = null): mixed
    {
        if (is_null($callback)) {
            return empty($array) ? $default : end($array);
        }

        return array_first(array_reverse($array, true), $callback, $default);
    }
}

if (!function_exists('array_only')) {
    /**
     * Get a subset of the items from the given array.
     */
    function array_only(array $array, array|string $keys): array
    {
        return array_intersect_key($array, array_flip((array) $keys));
    }
}

if (!function_exists('array_except')) {
    /**
     * Get all of the given array except for a specified array of keys.
     */
    function array_except(array $array, array|string $keys): array
    {
        return array_diff_key($array, array_flip((array) $keys));
    }
}

/*
|--------------------------------------------------------------------------
| Miscellaneous Helpers
|--------------------------------------------------------------------------
*/

if (!function_exists('value')) {
    /**
     * Return the default value of the given value.
     */
    function value(mixed $value): mixed
    {
        return $value instanceof Closure ? $value() : $value;
    }
}

if (!function_exists('with')) {
    /**
     * Return the given value, optionally passed through the given callback.
     */
    function with(mixed $value, callable $callback = null): mixed
    {
        if (is_null($callback)) {
            return $value;
        }
        return $callback($value);
    }
}

/*
|--------------------------------------------------------------------------
| Security Helpers
|--------------------------------------------------------------------------
*/

if (!function_exists('csrf_token')) {
    /**
     * Get the CSRF token value.
     */
    function csrf_token(): string
    {
        return \App\Core\Security\CSRF::getInstance()->getToken();
    }
}

if (!function_exists('csrf_field')) {
    /**
     * Generate a CSRF token form field.
     */
    function csrf_field(): string
    {
        return \App\Core\Security\CSRF::getInstance()->getTokenField();
    }
}

if (!function_exists('method_field')) {
    /**
     * Generate a form field to spoof the HTTP verb.
     */
    function method_field(string $method): string
    {
        return '<input type="hidden" name="_method" value="' . $method . '">';
    }
}

if (!function_exists('selected')) {
    /**
     * Generate a selected HTML attribute.
     *
     * @param  mixed  $value
     * @param  mixed  $current
     * @return string
     */
    function selected($value, $current): string
    {
        return (string) $value === (string) $current ? 'selected="selected"' : '';
    }
}

if (!function_exists('checked')) {
    /**
     * Generate a checked HTML attribute.
     *
     * @param  mixed  $value
     * @param  mixed  $current
     * @return string
     */
    function checked($value, $current): string
    {
        return (string) $value === (string) $current ? 'checked="checked"' : '';
    }
}

/*
|--------------------------------------------------------------------------
| Event Helpers
|--------------------------------------------------------------------------
*/

if (!function_exists('event')) {
    /**
     * Dispatch an event or get the event dispatcher
     * 
     * @param object|string|null $event Event instance or class name
     * @param mixed ...$args Event arguments
     * @return object|\App\Core\Events\EventDispatcher
     */
    function event(object|string $event = null, ...$args)
    {
        $dispatcher = \App\Core\Events\EventDispatcher::getInstance();

        if ($event === null) {
            return $dispatcher;
        }

        // If string, create event instance
        if (is_string($event)) {
            $event = new $event(...$args);
        }

        return $dispatcher->dispatch($event);
    }
}

if (!function_exists('listen')) {
    /**
     * Register an event listener
     * 
     * @param string $event Event class name
     * @param string|callable $listener Listener class or callable
     */
    function listen(string $event, string|callable $listener): void
    {
        \App\Core\Events\EventDispatcher::getInstance()->listen($event, $listener);
    }
}

/*
|--------------------------------------------------------------------------
| HTTP Client Helpers
|--------------------------------------------------------------------------
*/

if (!function_exists('Http')) {
    /**
     * Get a new HTTP client instance
     * 
     * @return \App\Core\Http\PendingRequest
     */
    function Http(): \App\Core\Http\PendingRequest
    {
        return new \App\Core\Http\PendingRequest();
    }
}

if (!function_exists('Notification')) {
    /**
     * Get notification service instance or send notification
     * 
     * @param array|null $payload If provided, sends notification
     * @return \Modules\Notifications\Services\NotificationService|mixed
     */
    function Notification(?array $payload = null)
    {
        $app = app();
        $service = new \Modules\Notifications\Services\NotificationService($app);

        if ($payload !== null) {
            return $service->send($payload);
        }

        return $service;
    }
}

if (!function_exists('sanitize')) {
    /**
     * Sanitize input data
     */
    function sanitize($input, string $type = 'string')
    {
        return \App\Core\Security\Sanitizer::clean($input, $type);
    }
}

if (!function_exists('escape')) {
    /**
     * Escape output for HTML display (XSS protection)
     */
    function escape(?string $string): string
    {
        return \App\Core\Security\Sanitizer::escapeOutput($string);
    }
}

if (!function_exists('old')) {
    /**
     * Retrieve old input from session
     */
    function old(string $key, $default = null)
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $oldInput = $_SESSION['_old_input'] ?? [];
        return $oldInput[$key] ?? $default;
    }
}


if (!function_exists('flash')) {
    /**
     * Flash data to session
     */
    function flash(string $key, $value = null)
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if ($value === null) {
            $flash = $_SESSION['_flash'] ?? [];
            unset($_SESSION['_flash']);
            return $flash[$key] ?? null;
        }

        $_SESSION['_flash'][$key] = $value;
    }
}

if (!function_exists('validator')) {
    /**
     * Créer une instance de validation
     */
    function validator(array $data, array $rules, array $messages = []): \App\Core\Validation\Validator
    {
        return \App\Core\Validation\Validator::make($data, $rules, $messages);
    }
}

if (!function_exists('errors')) {
    /**
     * Récupérer le ErrorBag de la session
     */
    function errors(): ?\App\Core\Validation\ErrorBag
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $errors = $_SESSION['_errors'] ?? null;

        if ($errors && $errors instanceof \App\Core\Validation\ErrorBag) {
            return $errors;
        }

        // Retourner un ErrorBag vide si pas d'erreurs
        return new \App\Core\Validation\ErrorBag();
    }
}

if (!function_exists('has_error')) {
    /**
     * Vérifier si un champ a une erreur
     */
    function has_error(string $field): bool
    {
        return errors()->has($field);
    }
}

if (!function_exists('error')) {
    /**
     * Récupérer la première erreur d'un champ
     */
    function error(string $field): ?string
    {
        return errors()->first($field);
    }
}

if (!function_exists('redirect_back_with_errors')) {
    /**
     * Redirection avec erreurs et old input
     */
    function redirect_back_with_errors(\App\Core\Validation\ErrorBag $errors): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        // Stocker les erreurs
        $_SESSION['_errors'] = $errors;

        // Stocker l'ancien input
        $_SESSION['_old_input'] = $_POST;

        // Rediriger vers la page précédente
        $referer = $_SERVER['HTTP_REFERER'] ?? '/';
        header('Location: ' . $referer);
        exit;
    }
}

if (!function_exists('with_errors')) {
    /**
     * Rediriger avec des erreurs vers une URL spécifique
     */
    function redirect_with_errors(string $url, \App\Core\Validation\ErrorBag $errors): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        // Stocker les erreurs
        $_SESSION['_errors'] = $errors;

        // Stocker l'ancien input
        $_SESSION['_old_input'] = $_POST;

        redirect($url);
    }
}


/*
|--------------------------------------------------------------------------
| Cache Helpers
|--------------------------------------------------------------------------
*/

if (!function_exists('cache')) {
    /**
     * Cache helper - Get/Set/Manage cache
     * 
     * @param string|null $key Cache key
     * @param mixed $value Value to cache (if provided, acts as SET)
     * @param int|null $ttl Time to live in seconds
     * @return mixed|App\Core\Cache\CacheManager
     */
    function cache(?string $key = null, mixed $value = null, ?int $ttl = null): mixed
    {
        $cacheManager = \App\Core\Cache\CacheManager::getInstance();

        // No arguments: return the cache manager instance
        if ($key === null) {
            return $cacheManager;
        }

        // Two or three arguments: SET operation
        if (func_num_args() >= 2) {
            return $cacheManager->set($key, $value, $ttl);
        }

        // One argument: GET operation
        return $cacheManager->get($key);
    }
}

if (!function_exists('cache_remember')) {
    /**
     * Get an item from cache, or execute the callback and store the result
     * 
     * @param string $key Cache key
     * @param callable $callback Callback to execute if key doesn't exist
     * @param int|null $ttl Time to live in seconds
     * @return mixed
     */
    function cache_remember(string $key, callable $callback, ?int $ttl = null): mixed
    {
        return \App\Core\Cache\CacheManager::getInstance()->remember($key, $callback, $ttl);
    }
}

if (!function_exists('cache_forget')) {
    /**
     * Remove an item from cache
     * 
     * @param string $key Cache key to remove
     * @return bool
     */
    function cache_forget(string $key): bool
    {
        return \App\Core\Cache\CacheManager::getInstance()->delete($key);
    }
}

if (!function_exists('cache_flush')) {
    /**
     * Clear all items from cache
     * 
     * @return bool
     */
    function cache_flush(): bool
    {
        return \App\Core\Cache\CacheManager::getInstance()->clear();
    }
}

if (!function_exists('cache_has')) {
    /**
     * Check if an item exists in cache
     * 
     * @param string $key Cache key to check
     * @return bool
     */
    function cache_has(string $key): bool
    {
        return \App\Core\Cache\CacheManager::getInstance()->has($key);
    }
}

/*
|--------------------------------------------------------------------------
| Vite Helpers
|--------------------------------------------------------------------------
*/

if (!function_exists('vite')) {
    /**
     * Vite Asset Helper
     */
    function vite(string $path)
    {
        $devServer = 'http://localhost:5173';
        $manifestPath = __DIR__ . '/../../public/build/.vite/manifest.json';

        // Check if dev server is running
        $isDev = false;
        $handle = @fsockopen('localhost', 5173, $errno, $errstr, 0.1);
        if ($handle) {
            $isDev = true;
            fclose($handle);
        }

        if ($isDev) {
            return '<script type="module" src="' . $devServer . '/@vite/client"></script>' .
                '<script type="module" src="' . $devServer . '/' . $path . '"></script>';
        }

        if (!file_exists($manifestPath)) {
            return '<!-- Vite Manifest not found. Run npm run build -->';
        }

        $manifest = json_decode(file_get_contents($manifestPath), true);
        $file = $manifest[$path]['file'] ?? null;
        $css = $manifest[$path]['css'] ?? [];

        if (!$file) {
            return "<!-- Asset $path not found in manifest -->";
        }

        $html = '';
        $ext = pathinfo($file, PATHINFO_EXTENSION);

        if ($ext === 'css') {
            $html .= '<link rel="stylesheet" href="' . url('build/' . $file) . '">';
        } else {
            $html .= '<script type="module" src="' . url('build/' . $file) . '"></script>';
        }

        foreach ($css as $cssFile) {
            $html .= '<link rel="stylesheet" href="' . url('build/' . $cssFile) . '">';
        }

        return $html;
    }
}

/*
|--------------------------------------------------------------------------
| I18n Translation Helpers
|--------------------------------------------------------------------------
*/

if (!function_exists('__t')) {
    /**
     * Translate the given message.
     * Shorthand alias for trans().
     *
     * @param string $key Translation key (dot notation)
     * @param array $replace Replacement parameters
     * @param string|null $locale Specific locale
     * @return string Translated string
     */
    function __t(string $key, array $replace = [], ?string $locale = null): string
    {
        return App\Core\I18n\LanguageManager::getInstance()->trans($key, $replace, $locale);
    }
}

if (!function_exists('__')) {
    /**
     * Translate the given message (Laravel-style shorthand).
     *
     * @param string $key Translation key (dot notation)
     * @param array $replace Replacement parameters
     * @param string|null $locale Specific locale
     * @return string Translated string
     */
    function __(string $key, array $replace = [], ?string $locale = null): string
    {
        return App\Core\I18n\LanguageManager::getInstance()->trans($key, $replace, $locale);
    }
}

if (!function_exists('trans')) {
    /**
     * Translate the given message.
     *
     * @param string $key Translation key (dot notation) 
     * @param array $replace Replacement parameters
     * @param string|null $locale Specific locale
     * @return string Translated string
     */
    function trans(string $key, array $replace = [], ?string $locale = null): string
    {
        return App\Core\I18n\LanguageManager::getInstance()->trans($key, $replace, $locale);
    }
}

if (!function_exists('trans_choice')) {
    /**
     * Translate the given message with pluralization.
     *
     * @param string $key Translation key
     * @param int $count Count for pluralization
     * @param array $replace Replacement parameters
     * @param string|null $locale Specific locale
     * @return string Translated string
     */
    function trans_choice(string $key, int $count, array $replace = [], ?string $locale = null): string
    {
        return App\Core\I18n\LanguageManager::getInstance()->transChoice($key, $count, $replace, $locale);
    }
}

if (!function_exists('app_locale')) {
    /**
     * Get the current application locale.
     *
     * @return string Current locale code
     */
    function app_locale(): string
    {
        return App\Core\I18n\LanguageManager::getInstance()->getLocale();
    }
}

if (!function_exists('set_locale')) {
    /**
     * Set the application locale.
     *
     * @param string $locale Locale code
     * @return void
     */
    function set_locale(string $locale): void
    {
        App\Core\I18n\LanguageManager::getInstance()->setLocale($locale);
    }
}

if (!function_exists('supported_locales')) {
    /**
     * Get all supported locales.
     *
     * @return array List of supported locale codes
     */
    function supported_locales(): array
    {
        return App\Core\I18n\LanguageManager::getInstance()->getSupportedLocales();
    }
}

if (!function_exists('is_locale_supported')) {
    /**
     * Check if a locale is supported.
     *
     * @param string $locale Locale code
     * @return bool True if supported
     */
    function is_locale_supported(string $locale): bool
    {
        return App\Core\I18n\LanguageManager::getInstance()->isLocaleSupported($locale);
    }
}

/*
|--------------------------------------------------------------------------
| Logging Helpers
|--------------------------------------------------------------------------
*/

if (!function_exists('logger')) {
    /**
     * Get a logger instance.
     *
     * @param string|null $channel
     * @return \App\Core\Logging\LoggerInterface|\App\Core\Logging\LogManager
     */
    function logger(?string $channel = null)
    {
        $logManager = \App\Core\Application::getInstance()->make('log');

        return $channel ? $logManager->channel($channel) : $logManager;
    }
}

if (!function_exists('logs')) {
    /**
     * Alias for logger().
     *
     * @param string|null $channel
     * @return \App\Core\Logging\LoggerInterface|\App\Core\Logging\LogManager
     */
    function logs(?string $channel = null)
    {
        return logger($channel);
    }
}

/*
|--------------------------------------------------------------------------
| Path Helpers
|--------------------------------------------------------------------------
*/

if (!function_exists('base_path')) {
    /**
     * Get the path to the base of the install.
     *
     * @param string $path
     * @return string
     */
    function base_path(string $path = ''): string
    {
        $basePath = dirname(dirname(__DIR__));
        return $path ? $basePath . '/' . ltrim($path, '/') : $basePath;
    }
}

if (!function_exists('config_path')) {
    /**
     * Get the path to the config folder.
     *
     * @param string $path
     * @return string
     */
    function config_path(string $path = ''): string
    {
        return base_path('config' . ($path ? '/' . ltrim($path, '/') : ''));
    }
}

if (!function_exists('storage_path')) {
    /**
     * Get the path to the storage folder.
     *
     * @param string $path
     * @return string
     */
    function storage_path(string $path = ''): string
    {
        $basePath = dirname(dirname(__DIR__)) . '/storage';

        return $path ? $basePath . '/' . ltrim($path, '/') : $basePath;
    }
}

if (!function_exists('logs_path')) {
    /**
     * Get the path to the logs folder.
     *
     * @param string $path
     * @return string
     */
    function logs_path(string $path = ''): string
    {
        return storage_path('logs' . ($path ? '/' . ltrim($path, '/') : ''));
    }
}

if (!function_exists('database_path')) {
    /**
     * Get the path to the database folder.
     *
     * @param string $path
     * @return string
     */
    function database_path(string $path = ''): string
    {
        $basePath = dirname(dirname(__DIR__)) . '/database';

        return $path ? $basePath . '/' . ltrim($path, '/') : $basePath;
    }
}

/*
|--------------------------------------------------------------------------
| Error Handling Helpers
|--------------------------------------------------------------------------
*/

if (!function_exists('abort')) {
    /**
     * Throw an HttpException with the given data.
     *
     * @param int $code HTTP status code
     * @param string|null $message Optional error message
     * @return void
     */
    function abort(int $code, ?string $message = null): void
    {
        \App\Core\Routing\Router::handleError($code, match($code) {
            401 => 'Non Authentifié',
            403 => 'Accès Interdit',
            404 => 'Page Introuvable',
            500 => 'Erreur Serveur',
            default => 'Erreur',
        }, $message);
    }
}

if (!function_exists('abort_if')) {
    /**
     * Throw an HttpException if the given condition is true.
     *
     * @param bool $condition
     * @param int $code
     * @param string|null $message
     * @return void
     */
    function abort_if(bool $condition, int $code, ?string $message = null): void
    {
        if ($condition) {
            abort($code, $message);
        }
    }
}

if (!function_exists('abort_unless')) {
    /**
     * Throw an HttpException unless the given condition is true.
     *
     * @param bool $condition
     * @param int $code
     * @param string|null $message
     * @return void
     */
    function abort_unless(bool $condition, int $code, ?string $message = null): void
    {
        if (!$condition) {
            abort($code, $message);
        }
    }
}
