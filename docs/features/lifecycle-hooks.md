---
title: Lifecycle Hooks
description: Use the initially() and finally() hooks in Filterable to run logic
  before and after filters are applied — ideal for global constraints,
  default sorting, and query cleanup. The Invokable engine also supports
  per-field hooks (beforeX / afterX / onSkipX / onEmptyX) for fine-grained
  control over individual filter methods.
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

---


---

## Per-Field Hooks (Invokable Engine)

The **Invokable engine** supports an additional layer of lifecycle hooks that
fire around each individual filter method invocation. These hooks let you add
pre/post logic to a specific field without touching the method itself.

::: info Scope
Per-field hooks are available **only** in the Invokable engine, because it is
the only engine that maps request keys directly to individual filter methods.
Global `initially()` / `finally()` hooks already exist on the `Filterable`
base class and apply across **all** engines — they are intentionally excluded
from this system to avoid duplication.
:::

---

### Hook Types

| Hook method               | When it fires                             | Return `false` effect            |
| ------------------------- | ----------------------------------------- | -------------------------------- |
| `before{Field}(Payload)`  | Before a specific field's method          | Skip the method + `after{Field}` |
| `after{Field}(Payload)`   | After a specific field's method           | — (ignored)                      |
| `onSkip{Field}(Payload)`  | When the filter method is **not defined** | —                                |
| `onEmpty{Field}(Payload)` | When the filter value is `null` or `''`   | —                                |

---

### Execution Order

The full per-request flow, combining global and field-level hooks:

```text
initially(builder)               ← Filterable base class (all engines)
  │
  └─ for each field in $filters:
       │
       ▼
     onEmpty{Field}(payload)     ← only when value === null or ''
       │
       ▼
     before{Field}(payload)      ← false → skip method & after{Field}
       │
       ▼
     {field}(payload)  OR  onSkip{Field}(payload)
       │
       ▼
     after{Field}(payload)
  │
  ▼
finally(builder)                 ← Filterable base class (all engines)
```

---

### Basic Example

```php
use Kettasoft\Filterable\Filterable;
use Kettasoft\Filterable\Support\Payload;
use Illuminate\Contracts\Database\Eloquent\Builder;

class PostFilter extends Filterable
{
    protected $filters = ['status', 'title'];

    // ── Global hooks (base class, all engines) ────────────────────────────

    protected function initially(Builder $builder): Builder
    {
        // runs before any field is processed
        return $builder->where('tenant_id', auth()->id());
    }

    protected function finally(Builder $builder): Builder
    {
        // runs after all fields are processed
        return $builder->orderBy('created_at', 'desc');
    }

    // ── Field-level before hook ───────────────────────────────────────────

    /** Return false to skip the status() method. */
    public function beforeStatus(Payload $payload): bool
    {
        return auth()->user()->can('filter-by-status');
    }

    public function status(Payload $payload): void
    {
        $this->builder->where('status', $payload->value);
    }

    public function afterStatus(Payload $payload): void
    {
        // runs right after status() — e.g. metrics, cache invalidation
    }

    // ── Skip hook ─────────────────────────────────────────────────────────

    /** Fires when 'title' is in $filters but title() is not defined. */
    public function onSkipTitle(Payload $payload): void
    {
        $this->builder->where('title', 'like', "%{$payload->value}%");
    }

    // ── Empty hook ────────────────────────────────────────────────────────

    /** Fires when 'status' value is null or empty string. */
    public function onEmptyStatus(Payload $payload): void
    {
        $this->builder->where('status', 'active');
    }
}
```

---

### Halting Execution

A **before hook** prevents the filter method from running by returning exactly
`(bool) false`. Any other return value (including `void` / `null`) continues
as normal.

```php
public function beforeStatus(Payload $payload): bool
{
    return auth()->user()->isAdmin();
}
```

::: tip
`after{Field}` does **not** fire when `before{Field}` returns false, keeping
the pair symmetric.
:::

---

### Configuration

All hook behaviour is controlled under `config/filterable.php` inside
`engines.invokable.hooks`:

```php
'hooks' => [
    'enabled'       => true,    // master switch
    'field_hooks'   => true,    // before{Field} / after{Field}
    'skip_hooks'    => true,    // onSkip{Field}
    'empty_hooks'   => true,    // onEmpty{Field}
    'prefix' => [
        'before' => 'before',   // -> beforeStatus
        'after'  => 'after',    // -> afterStatus
        'skip'   => 'onSkip',   // -> onSkipStatus
        'empty'  => 'onEmpty',  // -> onEmptyStatus
    ],
    'naming'        => 'camel', // 'camel' | 'studly' | 'snake'
    'halt_on_false' => true,
],
```

---

### Method Name Resolution

Hook method names follow `{prefix}{TransformedField}`:

| `naming` | Field        | Result              |
| -------- | ------------ | ------------------- |
| `camel`  | `created_at` | `beforeCreatedAt`   |
| `studly` | `created_at` | `beforeCreatedAt`   |
| `snake`  | `created_at` | `before_created_at` |

Custom prefixes are respected:

```php
'prefix' => ['before' => 'hookBefore'],
// -> hookBeforeStatus, hookBeforeCreatedAt ...
```

---

### Practical Recipes

#### Authorization gate per field

```php
public function beforeStatus(Payload $payload): bool
{
    return Gate::allows('filter-status');
}
```

#### Fallback query when method is missing

```php
public function onSkipTitle(Payload $payload): void
{
    $this->builder->where('title', 'like', "%{$payload->value}%");
}
```

#### Apply a default when value is empty

```php
public function onEmptyStatus(Payload $payload): void
{
    $this->builder->where('status', 'published');
}
```

#### Post-filter metrics

```php
public function afterStatus(Payload $payload): void
{
    FilterMetrics::record($payload->field, $payload->value);
}
```
