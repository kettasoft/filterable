---
title: Event System API Reference
description: Full API reference for the Filterable Event System, including all static and instance methods with parameters and usage examples.
tags:
    - events
    - api-reference
    - filterable
---

# API Reference

## Static Methods

### `Filterable::on(string $event, callable $callback): void`

Register a global event listener for all filterable instances.

**Parameters:**

- `$event`: The event name (e.g., `'filterable.applied'`)
- `$callback`: The callback to execute when the event fires

**Example:**

```php
Filterable::on('filterable.applied', function (Filterable $filterable) {
    logger("Filter applied");
});
```

---

### `Filterable::observe(string $filterClass, callable $callback): void`

Register an observer for a specific filter class.

**Parameters:**

- `$filterClass`: The fully qualified filter class name
- `$callback`: The observer callback receiving `($event, $filterable)`

**Example:**

```php
Filterable::observe(PostFilter::class, function ($event, Filterable $filterable) {
    if ($event === 'applied') {
        // Handle the event
    }
});
```

---

### `Filterable::flushListeners(): void`

Remove all registered event listeners and observers.

**Example:**

```php
Filterable::flushListeners();
```

---

### `Filterable::getListeners(string $event): array`

Get all registered listeners for a specific event.

**Parameters:**

- `$event`: The event name

**Returns:** Array of callable listeners

**Example:**

```php
$listeners = Filterable::getListeners('filterable.applied');
```

---

### `Filterable::getObservers(string $filterClass): array`

Get all registered observers for a specific filter class.

**Parameters:**

- `$filterClass`: The filter class name

**Returns:** Array of callable observers

**Example:**

```php
$observers = Filterable::getObservers(PostFilter::class);
```

---

## Instance Methods

### `enableEvents(): static`

Enable events for this specific filterable instance.

**Example:**

```php
$filter = PostFilter::create()->enableEvents();
```

---

### `disableEvents(): static`

Disable events for this specific filterable instance.

**Example:**

```php
$filter = PostFilter::create()->disableEvents();
```
