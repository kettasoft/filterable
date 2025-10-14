<?php

namespace Kettasoft\Filterable\Traits;

/**
 * Trait HasFilterableEvents
 * 
 * Provides event listening and firing capabilities for filterable classes.
 * This trait acts as a thin wrapper that delegates all event management
 * to the FilterableEventManager singleton instance.
 * 
 * This design provides backward compatibility while centralizing all event
 * logic in a dedicated manager class, making the system more maintainable
 * and testable.
 * 
 * @package Kettasoft\Filterable\Traits
 * 
 * @link https://kettasoft.github.io/filterable/features/events
 * @property \Kettasoft\Filterable\Foundation\Events\Contracts\EventManager $eventManager
 */
trait HasFilterableEvents
{

    /**
     * Register a global event listener.
     * 
     * This method delegates to the FilterableEventManager singleton,
     * allowing you to listen to specific lifecycle events across
     * all filterable instances.
     * 
     * Available events:
     * - filterable.initializing: When a new Filterable instance is created
     * - filterable.resolved: After resolving engine and request data
     * - filterable.applied: After filters are executed successfully
     * - filterable.failed: If any exception occurs during apply
     * - filterable.finished: At the end of filtering lifecycle (finally block)
     * - filterable.fetched: After data retrieval operations (get, first, paginate, etc.)
     * 
     * @param string $event The event name to listen for (e.g., 'filterable.applied')
     * @param callable $callback The callback to execute when the event fires.
     * 
     * @return void
     */
    public static function on(string $event, callable $callback): void
    {
        self::$eventManager->on($event, $callback);
    }

    /**
     * Register an observer for a specific filter class.
     * 
     * This method delegates to the FilterableEventManager singleton.
     * Observers are called only when events are fired from instances of the
     * specified filter class.
     * 
     * @param string $filterClass The fully qualified filter class name to observe
     * @param callable $callback The observer callback. Receives ($event, $payload) where
     *                          $event is the event name (e.g., 'applied') and $payload
     *                          is an array containing the filterable instance and other data.
     * 
     * @return void
     */
    public static function observe(string $filterClass, callable $callback): void
    {
        self::$eventManager->observe($filterClass, $callback);
    }

    /**
     * Fire an event and notify all registered listeners and observers.
     * 
     * This method is called internally at various points in the filterable lifecycle.
     * It handles exceptions gracefully to prevent listener failures from breaking
     * the filtering process.
     * 
     * The event system can be disabled via configuration ('filterable.events.enabled' => false).
     * When disabled, this method becomes a no-op.
     * 
     * @param string $event The event name to fire (e.g., 'filterable.applied')
     * @param array $payload Additional data to pass to listeners. The filterable
     *                      instance ($this) is automatically prepended.
     * 
     * @return void
     * 
     * @internal
     */
    protected function fireEvent(string $event, array $payload = []): void
    {
        self::$eventManager->dispatch($event, $payload);
    }


    /**
     * Enable events for this specific filterable instance.
     * 
     * This overrides the global configuration setting for this instance only.
     * 
     * @return static
     */
    public function enableEvents(): static
    {
        self::$eventManager->enable();
        return $this;
    }

    /**
     * Disable events for this specific filterable instance.
     * This overrides the global configuration setting for this instance only.
     * 
     * @return static
     */
    public function disableEvents(): static
    {
        self::$eventManager->disable();
        return $this;
    }

    /**
     * Remove all registered event listeners and observers.
     * 
     * This is particularly useful in testing scenarios where you want to
     * ensure a clean state between tests.
     * 
     * @return void
     */
    public static function flushListeners(): void
    {
        self::$eventManager->clear();
    }

    /**
     * Reset the event manager instance.
     * 
     * This method is useful for testing purposes to ensure a fresh
     * event manager state before each test.
     * 
     * @return void
     */
    public static function resetEventManager(): void
    {
        self::$eventManager->resetInstance();
    }

    /**
     * Get all registered listeners for a specific event.
     * 
     * This method is primarily intended for testing and debugging purposes.
     * 
     * @param string $event The event name
     * 
     * @return array<callable>
     */
    public static function getListeners(string $event): array
    {
        return self::$eventManager->getListeners($event);
    }

    /**
     * Get all registered observers for a specific filter class.
     * 
     * This method is primarily intended for testing and debugging purposes.
     * 
     * @param string $filterClass The filter class name
     * 
     * @return array<callable>
     */
    public static function getObservers(string $filterClass): array
    {
        return self::$eventManager->getObservers($filterClass);
    }
}
