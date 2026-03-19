---
title: Event Use Cases
description: Practical examples of using Filterable events for audit logging, performance monitoring, analytics, error notifications, cache invalidation, and development debugging.
tags:
    - events
    - use-cases
    - filterable
---

# Use Cases

## 1. Audit Logging

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

## 2. Performance Monitoring

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

## 3. Analytics & Metrics

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

## 4. Error Notifications

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

## 5. Cache Invalidation

Clear relevant caches when filters are applied:

```php
Filterable::observe(PostFilter::class, function ($event, Filterable $filterable) {
    if ($event === 'applied') {
        Cache::tags(['posts', 'filters'])->flush();
    }
});
```

## 6. Development Debugging

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
