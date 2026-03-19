---
title: Event Exception Handling
description: Learn how the Filterable Event System gracefully handles exceptions thrown inside event listeners without disrupting the filtering process.
tags:
    - events
    - exception-handling
    - filterable
---

# Exception Handling

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
