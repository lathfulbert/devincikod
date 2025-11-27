<?php

declare(strict_types=1);

namespace App\Core\Events;

use App\Core\Queue\QueueManager;
use App\Core\Contracts\ShouldQueue;

/**
 * Event Dispatcher
 * 
 * Singleton service managing event dispatching and listener execution.
 * PSR-14 inspired implementation.
 */
class EventDispatcher
{
    private static ?EventDispatcher $instance = null;

    /**
     * Registered event listeners
     * 
     * @var array<string, array>
     */
    protected array $listeners = [];

    /**
     * Listener provider for lazy loading
     */
    protected ?ListenerProvider $provider = null;

    /**
     * Get singleton instance
     */
    public static function getInstance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    /**
     * Private constructor (singleton)
     */
    private function __construct()
    {
        $this->provider = new ListenerProvider();
    }

    /**
     * Dispatch an event to all registered listeners
     * 
     * @param object $event The event to dispatch
     * @return object The event (possibly modified by listeners)
     */
    public function dispatch(object $event): object
    {
        $eventName = get_class($event);

        // Load listeners from provider if not already loaded
        if (!isset($this->listeners[$eventName])) {
            $this->listeners[$eventName] = $this->provider->getListenersForEvent($eventName);
        }

        // Execute each listener
        foreach ($this->listeners[$eventName] as $listener) {
            // Check if propagation stopped
            if (method_exists($event, 'isPropagationStopped') && $event->isPropagationStopped()) {
                break;
            }

            $this->callListener($listener, $event);
        }

        return $event;
    }

    /**
     * Register a listener for an event
     * 
     * @param string $event Event class name
     * @param string|callable $listener Listener class or callable
     */
    public function listen(string $event, string|array|callable $listener): void
    {
        if (!isset($this->listeners[$event])) {
            $this->listeners[$event] = [];
        }

        $this->listeners[$event][] = $listener;
    }

    /**
     * Check if event has listeners
     */
    public function hasListeners(string $event): bool
    {
        return !empty($this->listeners[$event] ?? []);
    }

    /**
     * Get all listeners for an event
     */
    public function getListeners(string $event): array
    {
        return $this->listeners[$event] ?? [];
    }

    /**
     * Clear all listeners
     */
    public function clearListeners(?string $event = null): void
    {
        if ($event === null) {
            $this->listeners = [];
        } else {
            unset($this->listeners[$event]);
        }
    }

    /**
     * Call a listener with the event
     * 
     * @param string|callable $listener
     * @param object $event
     */
    protected function callListener(string|array|callable $listener, object $event): mixed
    {
        // If listener is a callable, call it directly
        if (is_callable($listener)) {
            return $listener($event);
        }

        // Instantiate listener class
        $listenerInstance = new $listener();

        // Check if listener should be queued
        if ($listenerInstance instanceof ShouldQueue) {
            return $this->queueListener($listenerInstance, $event);
        }

        // Call handle method synchronously
        return $listenerInstance->handle($event);
    }

    /**
     * Queue a listener for async execution
     */
    protected function queueListener(ShouldQueue $listener, object $event): void
    {
        $queueName = method_exists($listener, 'queue') ? $listener->queue() : 'default';
        $delay = method_exists($listener, 'delay') ? $listener->delay() : 0;

        QueueManager::getInstance()->push(
            \App\Jobs\CallQueuedListener::class,
            [
                'listener' => get_class($listener),
                'event' => serialize($event),
            ],
            $queueName,
            $delay
        );
    }

    /**
     * Set listener provider
     */
    public function setProvider(ListenerProvider $provider): void
    {
        $this->provider = $provider;
    }
}
