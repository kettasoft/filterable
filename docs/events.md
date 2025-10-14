# Events

The Filterable Event System allows you to listen to lifecycle events during filtering operations. This provides powerful hooks for logging, monitoring, analytics, auditing, and implementing custom business logic that reacts to filtering activities.

## Table of Contents

-   [Introduction](#introduction)
-   [Configuration](#configuration)
-   [Available Events](#available-events)
-   [Registering Event Listeners](#registering-event-listeners)
    -   [Global Listeners](#global-listeners)
    -   [Filter-Specific Observers](#filter-specific-observers)
-   [Event Payloads](#event-payloads)
-   [Enabling/Disabling Events](#enabling-disabling-events)
-   [Use Cases](#use-cases)
-   [Exception Handling](#exception-handling)
-   [API Reference](#api-reference)

---

## Introduction

The event system is lightweight, framework-agnostic (though designed for Laravel), and doesn't depend on Laravel's Event facade. It uses a simple pub-sub pattern that integrates seamlessly with the filterable lifecycle.

**Key Features:**

-   🎯 Global and filter-specific event listeners
-   🛡️ Safe exception handling (listener failures won't crash your app)
-   ⚙️ Configurable (can be disabled globally or per instance)
-   📊 Perfect for logging, monitoring, and analytics
-   🧪 Easy to test with listener flushing

---

## Configuration

Enable or disable the event system in `config/filterable.php`:

```php
'events' => [
    /*
    |--------------------------------------------------------------------------
    | Enable or Disable Event System
    |--------------------------------------------------------------------------
    |
    | This option allows you to enable or disable the event system globally.
    | When disabled, no event listeners or observers will be triggered.
    |
    */
    'enabled' => env('FILTERABLE_EVENTS_ENABLED', true),
],
```

You can also set this in your `.env` file:

```env
FILTERABLE_EVENTS_ENABLED=true
```

---

## Available Events

The following events are dispatched during the filterable lifecycle:

| Event Name                | Description                                | When Fired                 | Payload                             |
| ------------------------- | ------------------------------------------ | -------------------------- | ----------------------------------- |
| `filterable.initializing` | A new Filterable instance is being created | Constructor start          | `$filterable`                       |
| `filterable.resolved`     | Engine and request data have been resolved | Constructor end            | `$filterable, $engine, $data`       |
| `filterable.applied`      | Filters have been executed successfully    | After successful `apply()` | `$filterable, $builder`             |
| `filterable.failed`       | An exception occurred during `apply()`     | Catch block in `apply()`   | `$filterable, $exception, $builder` |
| `filterable.finished`     | Filtering lifecycle has completed          | Finally block in `apply()` | `$filterable, $builder`             |

---

## Registering Event Listeners

### Global Listeners

Global listeners are triggered for **all** filterable instances, regardless of the filter class.

```php
use Kettasoft\Filterable\Filterable;

Filterable::on('filterable.applied', function (Filterable $filterable) {
    logger()->info("Filter applied", [
        'filter_class' => get_class($filterable),
        'sql' => $filterable->getBuilder()->toSql(),
        'bindings' => $filterable->getBuilder()->getBindings(),
    ]);
});
```

**Registering Multiple Listeners:**

```php
// Log when filters start initializing
Filterable::on('filterable.initializing', function (Filterable $filterable) {
    logger()->debug("Initializing filter: " . get_class($filterable));
});

// Track successful applications
Filterable::on('filterable.applied', function (Filterable $filterable) {
    metrics()->increment('filters.applied');
});

// Handle failures
Filterable::on('filterable.failed', function (Filterable $filterable, Throwable $exception) {
    logger()->error("Filter failed", [
        'filter' => get_class($filterable),
        'error' => $exception->getMessage(),
    ]);
});
```

### Filter-Specific Observers

Observers are called only for specific filter classes. This is ideal for filter-specific logging or side effects.

```php
use App\Http\Filters\PostFilter;
use Kettasoft\Filterable\Filterable;

Filterable::observe(PostFilter::class, function ($event, Filterable $filterable) {
    // $event is the event name without 'filterable.' prefix
    // $filterable is instance of Filterable

    if ($event === 'applied') {
        activity()
            ->causedBy(auth()->user())
            ->performedOn($filterable->getModel())
            ->log('PostFilter was applied');
    }
});
```

**Multiple Observers:**

```php
Filterable::observe(UserFilter::class, function ($event, Filterable $filterable) {
    match ($event) {
        'initializing' => logger()->info("UserFilter initializing"),
        'applied' => logger()->info("UserFilter applied successfully"),
        'failed' => logger()->error("UserFilter failed", ['error' => $filterable->getMessage()]),
        default => null,
    };
});
```

---

## Event Payloads

Each event receives different payload data:

### `filterable.initializing`

```php
function (Filterable $filterable) {
    // $filterable: The Filterable instance
}
```

### `filterable.resolved`

```php
function ($engine, $data) {
    // $engine: The resolved Engine instance
    // $data: Parsed request data array
}
```

### `filterable.applied`

```php
function (Filterable $filterable) {
    // $filterable: The Filterable instance
}
```

### `filterable.failed`

```php
function ($filterable, $exception) {
    // $filterable: The Filterable instance
    // $exception: The Throwable that was caught
}
```

### `filterable.finished`

```php
function (Filterable $filterable) {
    // $filterable: The Filterable instance
}
```

---

## Enabling/Disabling Events

### Global Configuration

Disable events globally in `config/filterable.php`:

```php
'events' => [
    'enabled' => false,
],
```

### Per-Instance Control

Override the global setting for specific instances:

```php
// Disable events for this instance
$filter = PostFilter::create()->disableEvents();

// Enable events for this instance (even if globally disabled)
$filter = PostFilter::create()->enableEvents();
```

### Conditional Event Control

```php
$filter = PostFilter::create()
    ->when(app()->environment('production'), fn($f) => $f->disableEvents())
    ->apply($builder);
```

---

## Use Cases

### 1. Audit Logging

Track who applied which filters and when:

```php
Filterable::on('filterable.applied', function (Filterable $filterable) {
    AuditLog::create([
        'user_id' => auth()->id(),
        'filter_class' => get_class($filterable),
        'filters_applied' => $filterable->getData(),
        'sql_query' => $filterable->getBuilder()->toSql(),
        'timestamp' => now(),
    ]);
});
```

### 2. Performance Monitoring

Track slow filters:

```php
Filterable::on('filterable.finished', function (Filterable $filterable) {
    $executionTime = microtime(true) - LARAVEL_START;

    if ($executionTime > 1.0) {
        logger()->warning("Slow filter detected", [
            'filter' => get_class($filterable),
            'execution_time' => $executionTime,
            'sql' => $filterable->getBuilder()->toSql(),
        ]);
    }
});
```

### 3. Analytics & Metrics

Collect usage statistics:

```php
Filterable::on('filterable.applied', function (Filterable $filterable) {
    Redis::hincrby('filter_stats', get_class($filterable), 1);

    $data = $filterable->getData();
    foreach (array_keys($data) as $field) {
        Redis::hincrby('filter_fields', $field, 1);
    }
});
```

### 4. Error Notifications

Send alerts when filters fail:

```php
Filterable::on('filterable.failed', function (Filterable $filterable, Throwable $exception) {
    Notification::route('slack', config('logging.slack_webhook'))
        ->notify(new FilterFailureNotification(
            get_class($filterable),
            $exception->getMessage(),
            $filterable->getData()
        ));
});
```

### 5. Cache Invalidation

Clear relevant caches when filters are applied:

```php
Filterable::observe(PostFilter::class, function ($event, Filterable $filterable) {
    if ($event === 'applied') {
        Cache::tags(['posts', 'filters'])->flush();
    }
});
```

### 6. Development Debugging

Log all filter activity in development:

```php
if (app()->environment('local')) {
    Filterable::on('filterable.resolved', function ($engine, $data) {
        logger()->debug("Filter Resolved", [
            'engine' => get_class($engine),
            'data' => $data,
        ]);
    });
}
```

---

## Exception Handling

The event system handles exceptions gracefully. If a listener throws an exception, it will be caught and logged without breaking the filtering process.

```php
Filterable::on('filterable.applied', function (Filterable $filterable) {
    // This will be caught and logged, but won't crash the app
    throw new \Exception("Listener failed!");
});

// The filter will still work correctly
$results = PostFilter::create()->apply($builder)->get();
```

**Exception Logging:**

Failed listeners are logged using Laravel's logger (if available) or `error_log()`:

```
[2025-10-14 10:23:45] production.ERROR: Filterable event listener failed for event 'filterable.applied': Listener failed! {"event":"filterable.applied","type":"listener","exception":{...},"filterable_class":"App\\Http\\Filters\\PostFilter"}
```

---

## API Reference

### Static Methods

#### `Filterable::on(string $event, callable $callback): void`

Register a global event listener for all filterable instances.

**Parameters:**

-   `$event`: The event name (e.g., `'filterable.applied'`)
-   `$callback`: The callback to execute when the event fires

**Example:**

```php
Filterable::on('filterable.applied', function (Filterable $filterable) {
    logger("Filter applied");
});
```

---

#### `Filterable::observe(string $filterClass, callable $callback): void`

Register an observer for a specific filter class.

**Parameters:**

-   `$filterClass`: The fully qualified filter class name
-   `$callback`: The observer callback receiving `($event, $filterable)`

**Example:**

```php
Filterable::observe(PostFilter::class, function ($event, Filterable $filterable) {
    if ($event === 'applied') {
        // Handle the event
    }
});
```

---

#### `Filterable::flushListeners(): void`

Remove all registered event listeners and observers.

**Example:**

```php
Filterable::flushListeners();
```

---

#### `Filterable::getListeners(string $event): array`

Get all registered listeners for a specific event.

**Parameters:**

-   `$event`: The event name

**Returns:** Array of callable listeners

**Example:**

```php
$listeners = Filterable::getListeners('filterable.applied');
```

---

#### `Filterable::getObservers(string $filterClass): array`

Get all registered observers for a specific filter class.

**Parameters:**

-   `$filterClass`: The filter class name

**Returns:** Array of callable observers

**Example:**

```php
$observers = Filterable::getObservers(PostFilter::class);
```

---

### Instance Methods

#### `enableEvents(): static`

Enable events for this specific filterable instance.

**Example:**

```php
$filter = PostFilter::create()->enableEvents();
```

---

#### `disableEvents(): static`

Disable events for this specific filterable instance.

**Example:**

```php
$filter = PostFilter::create()->disableEvents();
```

---

## Best Practices

1. **Keep listeners lightweight**: Avoid heavy processing in event listeners to prevent performance degradation.

2. **Use queued jobs for expensive operations**: If you need to perform heavy tasks, dispatch a job from the listener:

    ```php
    Filterable::on('filterable.applied', function ($filterable, $builder) {
        ProcessFilterAnalytics::dispatch($filterable, $builder->toSql());
    });
    ```

3. **Disable in production if not needed**: If you're only using events for debugging, disable them in production:

    ```php
    'events' => [
        'enabled' => env('FILTERABLE_EVENTS_ENABLED', !app()->environment('production')),
    ],
    ```

4. **Use observers for filter-specific logic**: Keep global listeners for cross-cutting concerns and use observers for filter-specific behavior.

5. **Always flush in tests**: Prevent test pollution by flushing listeners in `tearDown()`:
    ```php
    protected function tearDown(): void
    {
        Filterable::flushListeners();
        parent::tearDown();
    }
    ```
