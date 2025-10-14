<?php

namespace Kettasoft\Filterable\Foundation\Events;

use Throwable;
use Kettasoft\Filterable\Foundation\Events\Contracts\EventManager;

/**
 * FilterableEventManager
 * 
 * Centralized event management system for the Filterable package.
 * This class follows the Singleton pattern and manages all event listeners,
 * observers, and event dispatching throughout the application lifecycle.
 * 
 * The manager provides a clean separation of concerns by extracting event
 * handling logic from the main Filterable class while maintaining the
 * same developer-friendly API.
 * 
 * @package Kettasoft\Filterable\Foundation\Events
 * 
 * @link https://kettasoft.github.io/filterable/features/events
 */
class FilterableEventManager implements EventManager
{
    /**
     * The singleton instance of the event manager.
     * 
     * @var self|null
     */
    protected static ?self $instance = null;

    /**
     * Global event listeners registered across all filterable instances.
     * 
     * @var array<string, array<callable>>
     */
    protected array $listeners = [];

    /**
     * Filter-specific observers registered for particular filter classes.
     * 
     * @var array<string, array<callable>>
     */
    protected array $observers = [];

    /**
     * Advanced event system configuration options.
     * 
     * @var array<string, bool>
     */
    protected array $config = [
        'logging' => false,
        'async_queue_dispatch' => false,
        'retry_mechanism' => false,
        'listener_priority_sorting' => false,
        'silent_failure_handling' => false,
    ];

    /**
     * Indicates whether the event system is globally enabled.
     * 
     * @var bool
     */
    protected bool $enabled = true;

    /**
     * Private constructor to prevent direct instantiation.
     * Use getInstance() to get the singleton instance.
     */
    protected function __construct(protected array $options = [])
    {
        // Load configuration from Laravel config if available
        $this->loadConfiguration();
    }

    /**
     * Prevent cloning of the singleton instance.
     * 
     * @return void
     */
    protected function __clone(): void
    {
        // Prevent cloning
    }

    /**
     * Prevent unserialization of the singleton instance.
     * 
     * @return void
     */
    public function __wakeup(): void
    {
        throw new \Exception("Cannot unserialize singleton");
    }

    /**
     * Get the singleton instance of the event manager.
     * 
     * This method ensures only one instance of the FilterableEventManager
     * exists throughout the application lifecycle.
     * 
     * @return self
     */
    public static function getInstance(array $options = []): self
    {
        if (self::$instance === null) {
            self::$instance = new self($options);
        }

        return self::$instance;
    }

    /**
     * Reset the singleton instance (primarily for testing).
     * 
     * @return void
     * @internal
     */
    public static function resetInstance(): void
    {
        self::$instance = null;
    }

    /**
     * Load configuration from Laravel's config system.
     * 
     * @return void
     */
    protected function loadConfiguration(): void
    {
        $this->enabled = config('filterable.events.enabled', true);

        // Load advanced configuration options
        $advancedConfig = config('filterable.events.advanced', []);

        foreach ($this->config as $key => $default) {
            $this->config[$key] = $advancedConfig[$key] ?? $default;
        }
    }

    /**
     * Register a global event listener.
     * 
     * This method allows you to listen to specific lifecycle events across
     * all filterable instances. The callback receives the filterable instance
     * and additional event-specific payload data.
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
     *                          Receives ($filterable, ...$payload) as arguments.
     * 
     * @return void
     */
    public function on(string $event, callable $callback): void
    {
        if (!isset($this->listeners[$event])) {
            $this->listeners[$event] = [];
        }

        $this->listeners[$event][] = $callback;
    }

    /**
     * Register an observer for a specific filter class.
     * 
     * Observers are called only when events are fired from instances of the
     * specified filter class. This is useful for filter-specific logging,
     * monitoring, or side effects.
     * 
     * The observer callback receives the event name (without the 'filterable.' prefix)
     * and the full payload array.
     * 
     * @param string $filterClass The fully qualified filter class name to observe
     * @param callable $callback The observer callback. Receives ($event, $payload) where
     *                          $event is the event name (e.g., 'applied') and $payload
     *                          is an array containing the filterable instance and other data.
     * 
     * @return void
     */
    public function observe(string $filterClass, callable $callback): void
    {
        if (!isset($this->observers[$filterClass])) {
            $this->observers[$filterClass] = [];
        }

        $this->observers[$filterClass][] = $callback;
    }

    /**
     * Dispatch an event and notify all registered listeners and observers.
     * 
     * This method is called internally at various points in the filterable lifecycle.
     * It handles exceptions gracefully to prevent listener failures from breaking
     * the filtering process.
     * 
     * @param string $event The event name to fire (e.g., 'filterable.applied')
     * @param mixed ...$payload Additional data to pass to listeners.
     * 
     * @return void
     */
    public function dispatch(string $event, mixed ...$payload): void
    {
        // Check if events are enabled globally
        if (!$this->isEnabled()) {
            return;
        }

        // Log event if logging is enabled
        if ($this->config['logging']) {
            $this->logEvent($event, $payload);
        }

        // Fire global listeners
        $this->fireGlobalListeners($event, $payload);

        // Fire filter-specific observers if first argument is a filterable instance
        if (!empty($payload) && is_object($payload[0]['filterable'] ?? null)) {
            $this->fireObservers($event, $payload);
        }
    }

    /**
     * Fire all global listeners for the given event.
     * 
     * @param string $event The event name
     * @param array $payload The event payload
     * 
     * @return void
     */
    protected function fireGlobalListeners(string $event, array $payload): void
    {
        if (!isset($this->listeners[$event])) {
            return;
        }

        $listeners = $this->listeners[$event];

        // Sort by priority if enabled (placeholder for future implementation)
        if ($this->config['listener_priority_sorting']) {
            // TODO: Implement priority sorting in future release
        }

        foreach ($listeners as $listener) {
            $this->executeListener($listener, $event, $payload);
        }
    }

    /**
     * Fire all observers for the current filter class.
     * 
     * @param string $event The event name
     * @param array $payload The event payload
     * 
     * @return void
     */
    protected function fireObservers(string $event, array $payload): void
    {
        $filterClass = get_class($payload[0]['filterable'] ?? null);

        if (!isset($this->observers[$filterClass])) {
            return;
        }

        // Extract the event name without the 'filterable.' prefix for observers
        $eventName = str_replace('filterable.', '', $event);

        foreach ($this->observers[$filterClass] as $observer) {
            $this->executeObserver($observer, $eventName, $payload[0]);
        }
    }

    /**
     * Execute a listener callback and handle exceptions gracefully.
     * 
     * @param callable $listener The listener callback
     * @param string $event The event name
     * @param array $payload The event payload
     * 
     * @return void
     */
    protected function executeListener(callable $listener, string $event, array $payload): void
    {
        try {
            // Dispatch to queue if async is enabled (placeholder for future implementation)
            call_user_func_array($listener, ...$payload);
            if ($this->config['async_queue_dispatch']) {
                // TODO: Implement queue dispatching in future release
                // For now, execute synchronously
            }

        } catch (Throwable $e) {
            $this->handleListenerException($e, $event, 'listener');
        }
    }

    /**
     * Execute an observer callback and handle exceptions gracefully.
     * 
     * @param callable $observer The observer callback
     * @param string $eventName The event name (without prefix)
     * @param array $payload The event payload
     * 
     * @return void
     */
    protected function executeObserver(callable $observer, string $event, array ...$payload): void
    {
        try {
            call_user_func($observer, $event, $payload);
        } catch (Throwable $e) {
            $this->handleListenerException($e, $event, 'observer');
        }
    }

    /**
     * Handle exceptions thrown by event listeners or observers.
     * 
     * This method logs the exception details without propagating it,
     * ensuring that a failing listener doesn't break the filtering process.
     * 
     * @param Throwable $exception The caught exception
     * @param string $event The event name
     * @param string $type The type of callback ('listener' or 'observer')
     * 
     * @return void
     */
    protected function handleListenerException(Throwable $exception, string $event, string $type): void
    {
        // If silent failure handling is enabled, just log quietly
        if ($this->config['silent_failure_handling']) {
            // Log silently without triggering additional errors
            $this->logError($exception, $event, $type);
            return;
        }

        // Use Laravel's logger if available, otherwise use error_log
        if (function_exists('logger')) {
            logger()->error("Filterable event {$type} failed for event '{$event}': {$exception->getMessage()}", [
                'event' => $event,
                'type' => $type,
                'exception' => $exception,
            ]);
        } else {
            error_log("Filterable event {$type} failed for event '{$event}': {$exception->getMessage()}");
        }

        // TODO: Implement retry mechanism in future release
        if ($this->config['retry_mechanism']) {
            // Retry logic would go here
        }
    }

    /**
     * Log an event dispatch (when logging is enabled).
     * 
     * @param string $event The event name
     * @param array $payload The event payload
     * 
     * @return void
     */
    protected function logEvent(string $event, array $payload): void
    {
        if (!function_exists('logger')) {
            return;
        }

        $context = [
            'event' => $event,
            'payload_count' => count($payload),
        ];

        if (!empty($payload) && is_object($payload[0])) {
            $context['filterable_class'] = get_class($payload[0]);
        }

        logger()->debug("Filterable event dispatched: {$event}", $context);
    }

    /**
     * Log an error silently (used when silent_failure_handling is enabled).
     * 
     * @param Throwable $exception The exception
     * @param string $event The event name
     * @param string $type The type of callback
     * 
     * @return void
     */
    protected function logError(Throwable $exception, string $event, string $type): void
    {
        if (function_exists('logger')) {
            logger()->debug("Filterable event {$type} failed (silent): {$event}", [
                'exception_message' => $exception->getMessage(),
            ]);
        }
    }

    /**
     * Remove all registered event listeners and observers.
     * 
     * This is particularly useful in testing scenarios where you want to
     * ensure a clean state between tests.
     * 
     * @return void
     */
    public function clear(): void
    {
        $this->listeners = [];
        $this->observers = [];
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
    public function getListeners(string $event): array
    {
        return $this->listeners[$event] ?? [];
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
    public function getObservers(string $filterClass): array
    {
        return $this->observers[$filterClass] ?? [];
    }

    /**
     * Enable the event system globally.
     * 
     * @return self
     */
    public function enable(): self
    {
        $this->enabled = true;
        return $this;
    }

    /**
     * Disable the event system globally.
     * 
     * @return self
     */
    public function disable(): self
    {
        $this->enabled = false;
        return $this;
    }

    /**
     * Check if the event system is enabled.
     * 
     * @return bool
     */
    public function isEnabled(): bool
    {
        return $this->enabled;
    }

    /**
     * Enable a specific advanced configuration option.
     * 
     * Available options:
     * - logging: Log all event dispatches
     * - async_queue_dispatch: Dispatch listeners to queue (future implementation)
     * - retry_mechanism: Retry failed listeners (future implementation)
     * - listener_priority_sorting: Sort listeners by priority (future implementation)
     * - silent_failure_handling: Suppress error logs for failed listeners
     * 
     * @param string $option The configuration option to enable
     * 
     * @return self
     * 
     * @throws \InvalidArgumentException If the option doesn't exist
     */
    public function enableOption(string $option): self
    {
        if (!array_key_exists($option, $this->config)) {
            throw new \InvalidArgumentException("Unknown event option: {$option}");
        }

        $this->config[$option] = true;
        return $this;
    }

    /**
     * Disable a specific advanced configuration option.
     * 
     * @param string $option The configuration option to disable
     * 
     * @return self
     * 
     * @throws \InvalidArgumentException If the option doesn't exist
     */
    public function disableOption(string $option): self
    {
        if (!array_key_exists($option, $this->config)) {
            throw new \InvalidArgumentException("Unknown event option: {$option}");
        }

        $this->config[$option] = false;
        return $this;
    }

    /**
     * Check if a specific configuration option is enabled.
     * 
     * @param string $option The configuration option to check
     * 
     * @return bool
     */
    public function isOptionEnabled(string $option): bool
    {
        return $this->config[$option] ?? false;
    }

    /**
     * Get all configuration options.
     * 
     * @return array<string, bool>
     */
    public function getConfig(): array
    {
        return $this->config;
    }

    /**
     * Set multiple configuration options at once.
     * 
     * @param array<string, bool> $config Configuration options to set
     * 
     * @return self
     */
    public function setConfig(array $config): self
    {
        foreach ($config as $key => $value) {
            if (array_key_exists($key, $this->config)) {
                $this->config[$key] = (bool) $value;
            }
        }

        return $this;
    }
}
