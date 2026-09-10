---
title: How Filterable Works
description: Follow a filter request from incoming data to the final Eloquent query and choose the right engine.
tags: [concepts, engines, filtering]
---

# How Filterable Works

Filterable sits between incoming filter data and an Eloquent query. It prepares the data, checks your filter policy, asks the selected engine to apply each condition, and then forwards terminal calls such as `get()`, `first()`, `count()`, and `paginate()` to the filtered query.

```text
Request or provided data
        ↓
Authorization → Request validation
        ↓
Selected engine → Payload sanitization
        ↓
Operation → Selected driver
        ↓
Backend query → Sorting → Result
```

## Two ways to start a filter

Bind a filter class to a model when it is the model's default filtering policy:

```php
use App\Http\Filters\PostFilter;
use Kettasoft\Filterable\Traits\InteractsWithFilterable;

class Post extends Model
{
    use InteractsWithFilterable;

    protected $filterable = PostFilter::class;
}

$posts = Post::filter()->paginate();
```

Or create a model-aware filter directly when choosing the engine at the call site:

```php
$posts = Filterable::for(Post::class, $request)
    ->using('ruleset')
    ->setAllowedFields(['status', 'title'])
    ->paginate();
```

## Choose an engine by request shape

| Engine | Choose it when | Typical input |
| --- | --- | --- |
| [Invokable](/engines/invokable/) | Each field needs custom PHP behavior | `?status=active&search=laravel` |
| [Ruleset](/engines/rule-set) | The client chooses from approved operators | `?filter[views][gte]=100` |
| [Expression](/engines/expression) | Requests include nested relational fields | `?filter[author][name][like]=%john%` |
| [Tree](/engines/tree) | The client sends grouped `AND`/`OR` conditions | A JSON condition tree |

## Payloads

Every accepted condition becomes a `Payload` containing the field, resolved operator, sanitized value, and original value. Invokable filters receive that payload directly:

```php
protected function status(Payload $payload): Builder
{
    return $this->builder->where('status', $payload->value);
}
```

Ruleset and Expression turn each resolved payload into a comparison Operation. Tree compiles all of its comparisons and logical groups into one operation tree. They dispatch these Operations through the selected [Driver](/features/drivers), while Invokable methods keep direct control of their domain-specific Eloquent logic.

## Safe filtering

Use allowed fields, operators, and relations to define what clients may query. Strict mode throws when input violates that policy; permissive mode skips the rejected condition and keeps a diagnostic record.

Request validation runs before the engine. The engine then parses each condition and sanitizes its payload before applying it to the query.

## Query execution

Filterable applies filters automatically before common Builder operations:

```php
$filterable->get();
$filterable->first();
$filterable->count();
$filterable->paginate();
```

You can also continue building the Eloquent query fluently. Use [Choose an Engine](/choosing-an-engine) to compare request formats, then open the guide for the engine that matches your API.
