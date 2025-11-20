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
| URL & Routing Helpers
|--------------------------------------------------------------------------
*/

if (!function_exists('url')) {
    /**
     * Generate a url for the application.
     */
    function url(string $path = ''): string
    {
        $baseUrl = config('app.url', '/sunuframework2');
        $baseUrl = rtrim($baseUrl, '/');

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
        header('Location: ' . url($path));
        exit;
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
