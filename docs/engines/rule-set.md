---
title: Ruleset Engine
description: Apply compact field and operator rules through an explicit allowlist.
tags: [engines, ruleset, operators, filtering]
---

# Ruleset Engine

The Ruleset engine turns compact field/operator/value input into Eloquent constraints. The package applies each approved condition automatically, so you do not need to create one PHP method per field.

## Choose Ruleset when

- Most filters are direct comparisons.
- The client benefits from a compact query-string format.
- Conditions do not need nested boolean groups.
- Allowed fields and operators are enough to define the public contract.

## Request shape

Use the default operator for equality:

```http
GET /api/posts?filter[status]=published
```

Or prefix a value with an operator:

```http
GET /api/posts?filter[views]=gte:100&filter[title]=like:%laravel%
```

Ruleset also accepts a one-key operator array such as `filter[views][gte]=100`. Prefer one format consistently in your public API.

## Minimal example

```php
use App\Models\Post;
use Kettasoft\Filterable\Filterable;

$posts = Filterable::for(Post::class, $request)
    ->using('ruleset')
    ->setAllowedFields(['status', 'views', 'title'])
    ->allowedOperators(['eq', 'gte', 'like'])
    ->latest()
    ->paginate();
```

Calling `paginate()` applies the accepted rules before forwarding the terminal operation to Eloquent.

## Operator resolution

Without an operator, Ruleset uses the configured default—`eq` by default.

```text
filter[status]=published  → status = published
filter[views]=gte:100     → views >= 100
```

Common built-in operators include `eq`, `neq`, `gt`, `gte`, `lt`, `lte`, `like`, `in`, `between`, `null`, and their negative variants. See [Operator Strategies](/features/operators) for the complete list and custom operators.

## Relational fields

Authorize relation paths before accepting them:

```http
GET /api/posts?filter[tags][name]=featured
```

```php
$posts = Filterable::for(Post::class, $request)
    ->using('ruleset')
    ->allowRelations(['tags' => ['name']])
    ->paginate();
```

Dot notation and nested request keys normalize to the same relation path. Deep relations can be approved explicitly:

```php
->allowRelations([
    'tags.post' => ['status'],
])
```

## Strict and permissive modes

Strict mode throws when a request uses a field or operator outside the configured policy:

```php
Filterable::for(Post::class, $request)
    ->using('ruleset')
    ->strict()
    ->setAllowedFields(['status'])
    ->allowedOperators(['eq'])
    ->get();
```

Permissive mode skips rejected conditions and keeps their diagnostic payloads available through `skipped()`:

```php
$filterable = Filterable::for(Post::class, $request)
    ->using('ruleset')
    ->permissive()
    ->setAllowedFields(['status']);

$posts = $filterable->get();
$skipped = $filterable->skipped();
```

## Common mistakes

- Allowing every model column in a public endpoint.
- Mixing compact `operator:value` and nested operator formats without documenting both.
- Allowing relation names without restricting their fields.
- Choosing Ruleset when a condition needs custom joins or domain logic; use [Invokable](/engines/invokable/) for that case.

## Next steps

- Compare request contracts in [Choose an Engine](/choosing-an-engine).
- Configure [operator strategies](/features/operators).
- Add [sorting](/sorting) to the filtered query.
