<?php

/**
 * Authorization Helpers
 * 
 * Helpers pour le système d'autorisation
 */

if (!function_exists('cannot')) {
    /**
     * Check if the current user does NOT have a given permission.
     * 
     * @param string $permission Permission name
     * @param mixed $model Optional model instance
     * @return bool
     */
    function cannot(string $permission, $model = null): bool
    {
        return !can($permission, $model);
    }
}

if (!function_exists('authorize')) {
    /**
     * Authorize an action or throw an exception.
     * 
     * @param string $permission Permission name
     * @param mixed $model Optional model instance
     * @throws \App\Core\Exceptions\AuthorizationException
     */
    function authorize(string $permission, $model = null): void
    {
        if (cannot($permission, $model)) {
            throw new \App\Core\Exceptions\AuthorizationException(
                "This action is unauthorized."
            );
        }
    }
}

if (!function_exists('gate')) {
    /**
     * Get the Gate instance.
     * 
     * @return \App\Core\Authorization\Gate
     */
    function gate(): \App\Core\Authorization\Gate
    {
        static $gate = null;

        if ($gate === null) {
            $gate = new \App\Core\Authorization\Gate();

            // Set current user if authenticated
            if (function_exists('auth') && auth()->check()) {
                $gate->forUser(auth()->user());
            }
        }

        return $gate;
    }
}

if (!function_exists('has_role')) {
    /**
     * Check if the current user has a given role.
     * 
     * @param string $role Role name
     * @return bool
     */
    function has_role(string $role): bool
    {
        if (!function_exists('auth') || !auth()->check()) {
            return false;
        }

        $user = auth()->user();

        if (!$user) {
            return false;
        }

        // Object
        if (is_object($user) && method_exists($user, 'hasRole')) {
            return $user->hasRole($role);
        }

        // Array
        if (is_array($user) && isset($user['role'])) {
            return $user['role'] === $role;
        }

        return false;
    }
}
