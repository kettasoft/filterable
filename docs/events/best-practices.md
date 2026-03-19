---
title: Event System Best Practices
description: Recommended patterns and best practices for using the Filterable Event System effectively in production Laravel applications.
tags:
    - events
    - best-practices
    - filterable
---

# Best Practices

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
