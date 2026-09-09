---
title: Tree Engine
description: Translate nested AND and OR condition trees into grouped Eloquent queries.
tags: [engines, tree, boolean logic, json]
---

# Tree Engine

The Tree engine turns nested JSON conditions into correctly grouped Eloquent constraints. Use it when the client must describe boolean logic, such as an advanced search builder with nested `AND` and `OR` groups.

## Choose Tree when

- Users build groups of conditions in a search interface.
- The client must control nested boolean logic.
- A flat list of independent conditions cannot express the query.
- Your API can validate and document a more complex JSON contract.

For ordinary operator filters, prefer the smaller [Ruleset](/engines/rule-set) or [Expression](/engines/expression) contract.

## Request shape

```json
{
  "filter": {
    "and": [
      {
        "field": "status",
        "operator": "eq",
        "value": "published"
      },
      {
        "or": [
          {
            "field": "views",
            "operator": "gte",
            "value": 100
          },
          {
            "field": "featured",
            "operator": "eq",
            "value": true
          }
        ]
      }
    ]
  }
}
```

Each group contains either `and` or `or`. Each leaf condition contains `field`, `operator`, and `value`.

## Minimal example

```php
use App\Models\Post;
use Kettasoft\Filterable\Filterable;

$posts = Filterable::for(Post::class, $request)
    ->using('tree')
    ->setAllowedFields(['status', 'views', 'featured'])
    ->allowedOperators(['eq', 'gte'])
    ->paginate();
```

The engine preserves group boundaries when it generates nested `where` and `orWhere` clauses.

## Provide a tree without an HTTP request

Use `setData()` for jobs, services, or tests:

```php
$posts = Filterable::for(Post::class)
    ->using('tree')
    ->setAllowedFields(['status', 'views'])
    ->allowedOperators(['eq', 'gte'])
    ->setData([
        'and' => [
            [
                'field' => 'status',
                'operator' => 'eq',
                'value' => 'published',
            ],
            [
                'field' => 'views',
                'operator' => 'gte',
                'value' => 100,
            ],
        ],
    ])
    ->get();
```

When the HTTP body uses a top-level `filter` key, Filterable automatically scopes engine data to that key.

## Relational fields

Tree leaves may target approved relation paths. Keep the relation policy explicit:

```php
->allowRelations([
    'author' => ['name'],
    'author.profile' => ['country'],
])
```

The leaf's `field` can then use a path such as `author.profile.country`.

## Strict and permissive modes

Strict mode is recommended for public Tree endpoints. It stops execution when a field, operator, or condition structure violates the contract.

```php
->using('tree')
->strict()
->setAllowedFields(['status', 'views'])
->allowedOperators(['eq', 'gte'])
```

Permissive mode can skip rejected leaves, but malformed tree structure may still make the request unusable. Validate the incoming JSON shape before filtering.

## Common mistakes

- Choosing Tree for a request that only needs independent comparisons.
- Allowing `*` fields or every operator on a public endpoint.
- Accepting unlimited nesting without application-level limits.
- Sending a leaf without all of `field`, `operator`, and `value`.
- Forgetting that boolean grouping is part of the public API and must be tested.

## Next steps

- Compare the four contracts in [Choose an Engine](/choosing-an-engine).
- Review [operator strategies](/features/operators).
- Add request-shape rules with [validation](/validation).
