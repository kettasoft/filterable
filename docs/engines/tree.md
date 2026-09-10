---
title: Tree Engine
description: Translate nested AND and OR conditions into a portable operation tree.
tags: [engines, tree, boolean logic, json]
---

# Tree Engine

The Tree engine turns nested JSON conditions into a backend-independent operation tree. The selected [Driver](/features/drivers) then translates the complete tree for its query backend. Use it when the client must describe boolean logic, such as an advanced search builder with nested `AND` and `OR` groups.

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

Each group contains either `and` or `or`, and that boolean joins all of its direct children. Each leaf condition contains `field`, `operator`, and `value`. Nested groups apply their own boolean independently, so the example means `status = published AND (views >= 100 OR featured = true)`.

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

The engine compiles this request into nested `Group` and `Comparison` Operations and dispatches the root group to the selected Driver once. This preserves every group boundary without coupling Tree parsing to Eloquent. An empty group applies no query constraint.

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

Permissive mode can omit rejected leaves while preserving the remaining group. Applied payloads are committed only after the Driver successfully applies the complete tree. Malformed tree structure may still make the request unusable, so validate the incoming JSON shape before filtering.

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
