<?php

namespace Kettasoft\Filterable\Foundation\Events\Contracts;

interface EventManager
{
    /**
     * Register a listener for a specific event.
     *
     * @param  string   $event
     * @param  callable $listener
     * @return void
     */
    public function on(string $event, callable $listener): void;

    /**
     * Register an observer for a specific filter class.
     *
     * @param  string   $class
     * @param  callable $listener
     * @return void
     */
    public function observe(string $class, callable $listener): void;

    /**
     * Dispatch an event with an optional payload.
     *
     * @param  string $event
     * @param  mixed  $payload
     * @return void
     */
    public function dispatch(string $event, mixed ...$payload): void;

    /**
     * Enable a specific event behavior (e.g. logging, async dispatch, retry, etc.)
     *
     * @return self
     */
    public function enable(): self;

    /**
     * Disable a specific event behavior.
     *
     * @return self
     */
    public function disable(): self;

    /**
     * Determine if a specific option is currently enabled.
     *
     * @return bool
     */
    public function isEnabled(): bool;

    /**
     * Get all listeners registered for a specific event.
     *
     * @param  string $event
     * @return array<int, callable>
     */
    public function getListeners(string $event): array;

    /**
     * Get all observers registered for a specific class.
     *
     * @param  string $class
     * @return array<int, callable>
     */
    public function getObservers(string $class): array;

    /**
     * Clear all registered listeners and observers.
     *
     * @return void
     */
    public function clear(): void;

    /**
     * Reset the event manager instance to its initial state.
     *
     * This method clears all listeners and observers, and resets
     * any internal state to ensure a fresh start.
     *
     * @return void
     */
    public static function resetInstance(): void;
}
