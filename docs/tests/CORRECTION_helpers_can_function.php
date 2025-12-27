<?php

/**
 * CORRECTION POUR helpers.php
 * 
 * Remplacer les lignes 263-280 par ce code :
 */

if (!function_exists('can')) {
    /**
     * Check if the current user has a given permission.
     * Supports both array and object user types.
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
