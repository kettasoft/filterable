---
title: Field Operator Policies
description: Restrict each public filter field to a safe subset of the operators enabled for its engine.
tags: [operators, fields, security, strict mode]
---

# Field Operator Policies

An engine-wide operator allowlist answers which operators an endpoint supports. A field operator policy narrows that list for a particular public field.

For example, an endpoint may support `eq`, `in`, `like`, `gte`, and `lte`, while allowing only meaningful combinations:

```php
$posts = Filterable::for(Post::class, $request)
    ->using('expression')
    ->setAllowedFields(['status', 'title', 'views'])
    ->allowedOperators(['eq', 'in', 'like', 'gte', 'lte'])
    ->operatorPolicies([
        'status' => ['eq', 'in'],
        'title' => ['eq', 'like'],
        'views' => ['eq', 'gte', 'lte'],
    ])
    ->paginate();
```

A policy can only restrict the active engine's operator allowlist. It cannot register or enable an operator that is unavailable globally.

## Declare policies on a filter class

Use `$fieldOperatorPolicies` when the rules belong to the filter contract:

```php
final class PostFilter extends Filterable
{
    protected $fieldOperatorPolicies = [
        'status' => ['eq', 'in'],
        'title' => ['eq', 'like'],
        'views' => ['eq', 'gte', 'lte'],
    ];
}
```

Use `operatorPolicies()` for runtime or profile-driven configuration:

```php
$filterable->operatorPolicies([
    'status' => ['eq', 'in'],
    'title' => ['eq', 'like'],
    'views' => ['eq', 'gte', 'lte'],
]);
```

By default, this replaces the existing policy map. Pass `false` to merge new entries; a duplicate field replaces that field's operator list:

```php
$filterable->operatorPolicies([
    'published_at' => ['gte', 'lte'],
], override: false);
```

Use `allowOperatorsFor()` as a smaller convenience when one operator list belongs to one or several fields:

```php
$filterable->allowOperatorsFor(
    ['price', 'discount'],
    ['eq', 'gte', 'lte'],
);
```

## Add a wildcard fallback

The `*` policy applies to fields without an exact policy:

```php
protected $fieldOperatorPolicies = [
    '*' => ['eq'],
    'title' => ['eq', 'like'],
    'views' => ['eq', 'gte', 'lte'],
];
```

Exact policies take precedence. In this example, `title` accepts `eq` and `like`, not only the wildcard's `eq`.

Without an exact or wildcard policy, a field retains the existing engine-wide operator behavior. This makes policies backward-compatible and safe to adopt incrementally.

## Use aliases or resolved operators

Policy entries accept the same public aliases and resolved values as `allowedOperators()`:

```php
->allowOperatorsFor('views', ['gte', 'lte'])
->allowOperatorsFor('views', ['>=', '<='])
```

Both forms restrict the field to the same comparisons.

## Public names and relations

Policies use the public request field before field mapping:

```php
->setAllowedFields(['state'])
->setFieldsMap(['state' => 'status'])
->allowOperatorsFor('state', ['eq', 'in'])
```

Relational fields use their complete approved path:

```php
->allowRelations([
    'author.profile' => ['country'],
])
->allowOperatorsFor('author.profile.country', ['eq', 'in'])
```

## Strict and permissive behavior

In strict mode, a policy violation throws `OperatorNotAllowedForFieldException`:

```php
Filterable::for(Post::class, $request)
    ->using('ruleset')
    ->strict()
    ->setAllowedFields(['status'])
    ->allowOperatorsFor('status', ['eq', 'in'])
    ->get();
```

In permissive mode, the rejected condition is recorded by `skipped()` and is not replaced silently with the engine's default operator:

```php
$filterable = Filterable::for(Post::class, $request)
    ->using('ruleset')
    ->permissive()
    ->setAllowedFields(['status'])
    ->allowOperatorsFor('status', ['eq', 'in']);

$posts = $filterable->get();
$filterable->hasSkipped('status');
$filterable->skipped('status')[0]['reason'];
```

This behavior prevents a rejected comparison from changing meaning during fallback.

## Engine support

Policies are enforced centrally during Payload creation, so they work consistently with Invokable, Ruleset, Expression, and Tree filters, including mapped and relational fields.

## Next steps

- Configure the engine-wide aliases in [Operator Strategies](./operators).
- Review [strict and permissive exceptions](/exceptions).
- Inspect active policies with `php artisan filterable:inspect PostFilter`.
