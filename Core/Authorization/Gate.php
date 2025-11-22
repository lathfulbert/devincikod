<?php

namespace App\Core\Authorization;

/**
 * Gate - Authorization System
 * 
 * Gère les vérifications de permissions et les policies
 */
class Gate
{
    protected array $abilities = [];
    protected array $policies = [];
    protected $user = null;

    /**
     * Set the current user
     */
    public function forUser($user): self
    {
        $this->user = $user;
        return $this;
    }

    /**
     * Define an ability
     * 
     * @param string $ability
     * @param callable $callback
     */
    public function define(string $ability, callable $callback): void
    {
        $this->abilities[$ability] = $callback;
    }

    /**
     * Register a policy for a model
     * 
     * @param string $modelClass
     * @param string $policyClass
     */
    public function policy(string $modelClass, string $policyClass): void
    {
        $this->policies[$modelClass] = $policyClass;
    }

    /**
     * Check if user can perform an ability
     * 
     * @param string $ability
     * @param mixed $arguments
     * @return bool
     */
    public function allows(string $ability, $arguments = null): bool
    {
        // If no user, deny
        if (!$this->user) {
            return false;
        }

        // Check if user has the permission directly
        if (method_exists($this->user, 'can')) {
            $result = $this->user->can($ability, $arguments);
            if ($result !== null) {
                return $result;
            }
        }

        // Check custom defined abilities
        if (isset($this->abilities[$ability])) {
            return call_user_func($this->abilities[$ability], $this->user, $arguments);
        }

        // Check policies if arguments is a model
        if (is_object($arguments)) {
            $modelClass = get_class($arguments);
            if (isset($this->policies[$modelClass])) {
                $policy = new $this->policies[$modelClass]();
                if (method_exists($policy, $ability)) {
                    return $policy->$ability($this->user, $arguments);
                }
            }
        }

        return false;
    }

    /**
     * Check if user cannot perform an ability
     * 
     * @param string $ability
     * @param mixed $arguments
     * @return bool
     */
    public function denies(string $ability, $arguments = null): bool
    {
        return !$this->allows($ability, $arguments);
    }

    /**
     * Authorize an ability or throw exception
     * 
     * @param string $ability
     * @param mixed $arguments
     * @throws \App\Core\Exceptions\AuthorizationException
     */
    public function authorize(string $ability, $arguments = null): void
    {
        if ($this->denies($ability, $arguments)) {
            throw new \App\Core\Exceptions\AuthorizationException(
                "This action is unauthorized."
            );
        }
    }

    /**
     * Check any of the abilities
     * 
     * @param array $abilities
     * @param mixed $arguments
     * @return bool
     */
    public function any(array $abilities, $arguments = null): bool
    {
        foreach ($abilities as $ability) {
            if ($this->allows($ability, $arguments)) {
                return true;
            }
        }
        return false;
    }

    /**
     * Check all abilities
     * 
     * @param array $abilities
     * @param mixed $arguments
     * @return bool
     */
    public function all(array $abilities, $arguments = null): bool
    {
        foreach ($abilities as $ability) {
            if ($this->denies($ability, $arguments)) {
                return false;
            }
        }
        return true;
    }

    /**
     * Get the current user
     */
    public function user()
    {
        return $this->user;
    }
}
