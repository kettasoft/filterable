---
title: Event System
description: Learn how to use the Filterable Event System to listen to lifecycle events during filtering operations.
tags:
    - events
    - filterable
    - lifecycle
---

The Filterable Event System allows you to listen to lifecycle events during filtering operations. This provides powerful hooks for logging, monitoring, analytics, auditing, and implementing custom business logic that reacts to filtering activities.

---

## Introduction

The event system is lightweight, framework-agnostic (though designed for Laravel), and doesn't depend on Laravel's Event facade. It uses a simple pub-sub pattern that integrates seamlessly with the filterable lifecycle.

**Key Features:**

- 🎯 Global and filter-specific event listeners
- 🛡️ Safe exception handling (listener failures won't crash your app)
- ⚙️ Configurable (can be disabled globally or per instance)
- 📊 Perfect for logging, monitoring, and analytics
- 🧪 Easy to test with listener flushing

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

## In This Section

| Page                                                   | Description                                                        |
| ------------------------------------------------------ | ------------------------------------------------------------------ |
| [Registering Listeners](./registering-listeners.md)    | How to register global listeners and filter-specific observers     |
| [Event Payloads](./event-payloads.md)                  | Full payload signatures for each event                             |
| [Enabling & Disabling Events](./enabling-disabling.md) | Global config and per-instance control                             |
| [Use Cases](./use-cases.md)                            | Practical examples: audit logging, monitoring, analytics, and more |
| [Exception Handling](./exception-handling.md)          | How listener failures are caught and logged                        |
| [API Reference](./api-reference.md)                    | Full static and instance method signatures                         |
| [Best Practices](./best-practices.md)                  | Recommended patterns for using the event system                    |
