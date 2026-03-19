---
title: Enabling & Disabling Events
description: Learn how to enable or disable the Filterable Event System globally via configuration or per-instance using fluent methods.
tags:
    - events
    - configuration
    - filterable
---

# Enabling & Disabling Events

## Global Configuration

Disable events globally in `config/filterable.php`:

```php
'events' => [
    'enabled' => false,
],
```

## Per-Instance Control

Override the global setting for specific instances:

```php
// Disable events for this instance
$filter = PostFilter::create()->disableEvents();

// Enable events for this instance (even if globally disabled)
$filter = PostFilter::create()->enableEvents();
```

## Conditional Event Control

```php
$filter = PostFilter::create()
    ->when(app()->environment('production'), fn($f) => $f->disableEvents())
    ->apply($builder);
```
