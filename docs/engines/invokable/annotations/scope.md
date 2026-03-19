---
title: "#[Scope]"
description: Automatically applies an Eloquent local scope to the query builder using the #[Scope] attribute. Use it to reuse model scopes directly from a filter method, passing the payload value as the scope argument.
tags: [annotations, behavior, eloquent-scope]
sidebarDepth: 1
---

::: info Stage
`BEHAVIOR` — runs after all control, transform, and validate attributes, directly before the filter method executes.
:::

Automatically applies an Eloquent local scope on the query builder, passing the [payload](/api/payload) value to the scope. This allows you to reuse your model's scope methods directly from filter attributes.

---

## Parameters

| Parameter | Type     | Required | Description                                              |
| --------- | -------- | -------- | -------------------------------------------------------- |
| `$scope`  | `string` | ✅       | The scope name (without the `scope` prefix on the model) |

---

## Usage

### Model with Scope

```php
// App\Models\Post
class Post extends Model
{
    public function scopeActive(Builder $query, $value = null): Builder
    {
        return $query->where('status', $value ?? 'active');
    }

    public function scopePopular(Builder $query, $minViews = 100): Builder
    {
        return $query->where('views', '>=', $minViews);
    }
}
```

### Filter Class

```php
use Kettasoft\Filterable\Engines\Foundation\Attributes\Annotations\Scope;

#[Scope('active')]
protected function status(Payload $payload)
{
    // The scope is applied automatically before this method runs.
    // The scope receives $payload->value as its argument.
    // This method body also executes after the scope.
}
```

### Using Popular Scope

```php
#[Scope('popular')]
protected function minViews(Payload $payload)
{
    // Calls: $query->popular($payload->value)
    // e.g., $query->where('views', '>=', 500)
}
```

---

## How It Works

1. The attribute checks that the scope method exists on the model (`scope{Name}`).
2. It calls `$query->{scopeName}($payload->value)` on the query builder.
3. It sets `scope_applied = true` in the attribute context state.
4. The filter method still executes after the scope is applied.

---

## Behavior

| Scenario                  | Result                                                 |
| ------------------------- | ------------------------------------------------------ |
| Scope exists on the model | Scope is applied, then filter method executes          |
| Scope does not exist      | `InvalidArgumentException` (caught by engine pipeline) |

---

## Combining with Other Attributes

```php
#[SkipIf('empty')]
#[Trim]
#[Sanitize('lowercase')]
#[In('active', 'pending', 'archived')]
#[Scope('active')]
protected function status(Payload $payload)
{
    // 1. Skip if empty
    // 2. Trim whitespace
    // 3. Lowercase
    // 4. Validate against allowed values
    // 5. Apply the 'active' scope with the value
    // 6. Filter method body runs
}
```
