<?php

namespace App\Core\Events;

/**
 * Base Event Class
 * 
 * All events should extend this class.
 * Provides event dispatching and propagation control.
 */
abstract class Event
{
    /**
     * Event data storage
     */
    protected array $data = [];

    /**
     * Whether event propagation has been stopped
     */
    protected bool $propagationStopped = false;

    /**
     * Constructor
     */
    public function __construct(array $data = [])
    {
        $this->data = $data;
    }

    /**
     * Stop event propagation to further listeners
     */
    public function stopPropagation(): void
    {
        $this->propagationStopped = true;
    }

    /**
     * Check if propagation is stopped
     */
    public function isPropagationStopped(): bool
    {
        return $this->propagationStopped;
    }

    /**
     * Get event data
     */
    public function getData(string $key = null, mixed $default = null): mixed
    {
        if ($key === null) {
            return $this->data;
        }

        return $this->data[$key] ?? $default;
    }

    /**
     * Set event data
     */
    public function setData(string $key, mixed $value): void
    {
        $this->data[$key] = $value;
    }

    /**
     * Static dispatch helper
     * 
     * Example: UserRegistered::dispatch(['user' => $user])
     */
    public static function dispatch(...$args): object
    {
        // Create event instance
        $event = count($args) === 1 && is_array($args[0])
            ? new static($args[0])
            : new static(['data' => $args]);

        // Dispatch via EventDispatcher
        return EventDispatcher::getInstance()->dispatch($event);
    }

    /**
     * Get event name (used for registration)
     */
    public static function getName(): string
    {
        return static::class;
    }
}
