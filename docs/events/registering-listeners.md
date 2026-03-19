---
title: Registering Event Listeners
description: Learn how to register global event listeners and filter-specific observers in the Filterable Event System.
tags:
    - events
    - listeners
    - observers
    - filterable
---

# Registering Event Listeners

## Global Listeners

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

---

## Filter-Specific Observers

Observers are called only for specific filter classes. This is ideal for filter-specific logging or side effects.

```php
use App\Http\Filters\PostFilter;
use Kettasoft\Filterable\Filterable;
use Kettasoft\Filterable\Foundation\Events\FilterableState;

Filterable::observe(PostFilter::class, function (FilterableState $event, Filterable $filterable) {
    // $event is the event name without 'filterable.' prefix
    // $filterable is instance of Filterable

    if ($event->is('applied')) {
        activity()
            ->causedBy(auth()->user())
            ->performedOn($filterable->getModel())
            ->log('PostFilter was applied');
    }
});
```

**Multiple Observers:**

```php
Filterable::observe(UserFilter::class, function (string $event, Filterable $filterable) {
    match ($event) {
        'initializing' => logger()->info("UserFilter initializing"),
        'applied' => logger()->info("UserFilter applied successfully"),
        'failed' => logger()->error("UserFilter failed", ['error' => $filterable->getMessage()]),
        default => null,
    };
});
```
