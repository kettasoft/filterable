---
title: Lifecycle Hooks
description: Use the initially() and finally() hooks in Filterable to run logic
    before and after filters are applied — ideal for global constraints,
    default sorting, and query cleanup.
tags: [lifecycle, hooks, invokable-engine, query-builder]
---

The `Filterable` base class exposes two optional lifecycle hooks that let you
run logic **before** and **after** the filter pipeline executes.

## Available Hooks

| Hook          | Runs                       | Common use                |
| ------------- | -------------------------- | ------------------------- |
| `initially()` | Before any filter executes | Global constraints, joins |
| `finally()`   | After all filters finish   | Default ordering, cleanup |

---

## `initially()`

Invoked before any filter method runs. Use it to apply conditions that should
always be present regardless of the request.

```php
use Kettasoft\Filterable\Filterable;
use Illuminate\Database\Eloquent\Builder;

class ProductFilter extends Filterable
{
    protected function initially(Builder $builder): void
    {
        $builder->where('is_active', true);
    }
}
```

---

## `finally()`

Invoked after all filter methods have executed. Use it to finalize query
behavior that depends on the full filter state.

```php
protected function finally(Builder $builder): void
{
    if (! $builder->getQuery()->orders) {
        $builder->orderBy('created_at', 'desc');
    }
}
```

---

## How It Works

Both hooks receive the **same `$builder` instance** used throughout the
pipeline — any modification persists across the entire filtering process.

```text
Request
  │
  ▼
initially()        ← your setup logic
  │
  ▼
Filter methods     ← annotations + method execution
  │
  ▼
finally()          ← your cleanup/finalization logic
  │
  ▼
Modified Query
```

If a hook is not defined in your filter class, it is skipped automatically.

---

## Practical Examples

### Multi-tenant scoping

```php
protected function initially(Builder $builder): void
{
    $builder->where('tenant_id', auth()->user()->tenant_id);
}
```

### Default sort with override support

```php
protected function finally(Builder $builder): void
{
    if (! $builder->getQuery()->orders) {
        $builder->orderBy('created_at', 'desc');
    }
}
```

### Eager loading before filters run

```php
protected function initially(Builder $builder): void
{
    $builder->with(['category', 'tags']);
}
```

---

## Artisan Stub

Both hooks are included automatically when generating a filter class:

```bash
php artisan filterable:make-filter ProductFilter
```

The generated stub includes empty `initially()` and `finally()` methods
ready for customization.
