---
title: Expression Engine
description: Build structured field and operator filters with explicit relational allowlists.
tags: [engines, expression, relations, operators]
---

# Expression Engine

The Expression engine accepts an explicit field/operator/value structure and applies approved conditions automatically. It is a strong fit for APIs whose clients send nested operator objects and relational field paths.

## Choose Expression when

- Operators should be visible in the request structure.
- The frontend sends filter objects rather than compact strings.
- Direct and deeply nested relations are part of the API contract.
- You do not need client-defined `AND` and `OR` groups.

## Request shape

```http
GET /api/posts?filter[status][eq]=published&filter[views][gte]=100
```

The request clearly identifies each field, operator, and value. Equality may use the configured default operator:

```http
GET /api/posts?filter[status]=published
```

## Minimal example

```php
use App\Models\Post;
use Kettasoft\Filterable\Filterable;

$posts = Filterable::for(Post::class, $request)
    ->using('expression')
    ->setAllowedFields(['status', 'views', 'created_at'])
    ->allowedOperators(['eq', 'gte', 'between'])
    ->latest()
    ->paginate();
```

Filterable normalizes each condition, creates a Payload, validates the policy, and applies the matching operator strategy.

## Relational fields

Expression accepts dot-notated relations inside the filter structure:

```http
GET /api/posts?filter[author.profile.name][like]=ahmed
```

Authorize both the relation path and its public fields:

```php
$posts = Filterable::for(Post::class, $request)
    ->using('expression')
    ->setAllowedFields(['status'])
    ->allowRelations([
        'author.profile' => ['name'],
        'tags' => ['name'],
    ])
    ->paginate();
```

Use `['tags']` or `['tags' => ['*']]` only when every field on that relation is intentionally public.

## Accepted condition forms

Expression can normalize common structured inputs:

```php
['status' => 'published']
['views' => ['gte' => 100]]
['price' => ['operator' => 'between', 'value' => [10, 50]]]
```

Choose one representation for an endpoint and document it consistently for client developers.

## Strict and permissive modes

Use strict mode when an invalid field or operator should fail the request:

```php
->using('expression')
->strict()
->setAllowedFields(['status', 'views'])
->allowedOperators(['eq', 'gte'])
```

Use permissive mode when unsupported conditions may be skipped. Inspect `skipped()` when you need diagnostics or client feedback.

## Column validation

The Expression engine can validate direct columns before generating conditions. Configure this behavior under `engines.expression.validate_columns` in `config/filterable.php`.

Relation fields are governed by `allowRelations()` rather than direct table-column validation.

## Common mistakes

- Treating a relation path as allowed because its root relation is allowed.
- Exposing all operators when the endpoint only needs equality and range filters.
- Mixing several condition representations within the same public API.
- Choosing Expression when the client needs grouped boolean logic; use the [Tree engine](/engines/tree) instead.

## Next steps

- Review [relational request examples](/choosing-an-engine#expression).
- Configure [operator strategies](/features/operators).
- Learn about [strictness and exceptions](/exceptions).
