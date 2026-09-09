---
title: Invokable Engine
description: Map approved request keys to focused filter methods with a rich Payload and PHP attributes.
tags: [engines, invokable, payload, attributes]
---

# Invokable Engine

The Invokable engine maps registered request keys to methods on a filter class. It is the default engine and the best starting point when each filter needs explicit, domain-specific Eloquent logic.

## Choose Invokable when

- Each public filter should have its own PHP method.
- A filter needs joins, scopes, subqueries, or other custom builder logic.
- You want to transform, validate, or authorize values with PHP attributes.
- The backend—not the client—should own the query implementation.

For operator-driven or nested request formats, compare the other options in [Choose an Engine](/choosing-an-engine).

## Request shape

```http
GET /api/posts?status=published&search=laravel
```

Only keys listed in `$filters` are considered. Each key maps to a method with the same camel-cased name.

```text
status      → status()
created_at  → createdAt()
```

## Minimal example

```php
<?php

namespace App\Http\Filters;

use Illuminate\Contracts\Database\Eloquent\Builder;
use Kettasoft\Filterable\Filterable;
use Kettasoft\Filterable\Support\Payload;

final class PostFilter extends Filterable
{
    protected $filters = ['status', 'search'];

    protected function status(Payload $payload): Builder
    {
        return $this->builder->where('status', $payload->value);
    }

    protected function search(Payload $payload): Builder
    {
        return $this->builder->where(
            'title',
            'like',
            $payload->asLike('both'),
        );
    }
}
```

Bind the class to a model with `InteractsWithFilterable`, then execute it like a normal Eloquent query:

```php
$posts = Post::filter()->latest()->paginate();
```

See the [Quick Start](/quick-start) for the complete model and endpoint setup.

## How execution works

For every registered field, the engine:

1. Reads the matching request value.
2. Resolves its operator and raw value.
3. Creates a [`Payload`](/api/payload).
4. Runs the method's attributes in lifecycle order.
5. Invokes the filter method when the pipeline succeeds.
6. Records a snapshot that is available through `applied()`.

The filter method may return a Builder. If it does, that Builder becomes the current query for the next filter.

## Work with Payload

Every method receives one object containing the condition's full context:

| Property | Contains |
| --- | --- |
| `field` | Public filter name |
| `operator` | Resolved operator |
| `value` | Current transformed value |
| `rawValue` | Original value before transformation |

```php
protected function minimumViews(Payload $payload): Builder
{
    return $this->builder->where('views', '>=', $payload->asInt());
}
```

Use the [Payload reference](/api/payload) when you need conversion, matching, or serialization helpers.

## Add PHP attributes

Attributes keep control and value preparation outside the query method:

```php
use Kettasoft\Filterable\Engines\Foundation\Attributes\Annotations\In;
use Kettasoft\Filterable\Engines\Foundation\Attributes\Annotations\Required;
use Kettasoft\Filterable\Engines\Foundation\Attributes\Annotations\Sanitize;
use Kettasoft\Filterable\Engines\Foundation\Attributes\Annotations\Trim;

#[Trim]
#[Sanitize('lowercase')]
#[Required]
#[In('published', 'draft', 'archived')]
protected function status(Payload $payload): Builder
{
    return $this->builder->where('status', $payload->value);
}
```

Attributes always run by stage, regardless of their visual order on the method:

| Stage | Purpose | Built-in attributes |
| --- | --- | --- |
| Control | Decide whether the method may run | `Authorize`, `SkipIf` |
| Transform | Prepare the value | `Trim`, `Sanitize`, `Cast`, `MapValue`, `DefaultValue`, `Explode` |
| Validate | Assert the prepared value | `Required`, `In`, `Between`, `Regex` |
| Behavior | Delegate query behavior | `Scope` |

Browse the [attributes guide](./annotations/) or learn how to create [custom attributes](./custom-annotations).

## Map public names to methods

Use `$mentors` when the public request name should differ from the PHP method:

```php
protected $filters = ['joined'];

protected $mentors = [
    'joined' => 'joinedAfter',
];

protected function joinedAfter(Payload $payload): Builder
{
    return $this->builder->whereDate('joined_at', '>', $payload->value);
}
```

::: warning Reserved method names
A public filter must not resolve to a method already defined by the base `Filterable` class, such as `apply`, `filter`, or `getBuilder`. Map that request key to a unique method with `$mentors`.
:::

## Query relationships

Invokable methods own their Eloquent logic, so relational filters can use familiar builder methods:

```php
protected function author(Payload $payload): Builder
{
    return $this->builder->whereHas(
        'author',
        fn (Builder $query) => $query->where(
            'name',
            'like',
            $payload->asLike('both'),
        ),
    );
}
```

Choose the [Expression engine](/engines/expression) instead when clients need to send approved relation paths directly.

## Common mistakes

- Forgetting to add the request key to `$filters`.
- Using a base `Filterable` method name as a filter method.
- Reading request data again instead of using the provided `Payload`.
- Mixing validation and normalization into the query method when an attribute already handles them.
- Exposing dynamic column names without an explicit allowlist or mapping.

## Next steps

- Browse all [built-in attributes](./annotations/).
- Learn how to [test Invokable filters](./testing).
- Review the full [Payload API](/api/payload).
