---
title: Choose an Engine
description: Select the Filterable engine that matches your request contract and query complexity.
tags: [engines, architecture, request format]
---

# Choose an Engine

Filterable provides four engines because applications do not all expose the same request contract. Choose the engine from the shape your client sends—not from the number of filters you expect to add.

## Quick recommendation

| Choose | When your client sends | Best fit |
| --- | --- | --- |
| [Invokable](/engines/invokable/) | Named query parameters | Custom behavior for each filter field |
| [Ruleset](/engines/rule-set) | Compact field and operator values | Straightforward field/operator filtering |
| [Expression](/engines/expression) | Explicit nested operator objects | Structured APIs and relational fields |
| [Tree](/engines/tree) | Nested JSON with `AND` and `OR` groups | Query builders and advanced search UIs |

## Compare the request shapes

### Invokable

```http
GET /api/posts?status=published&search=laravel
```

Each registered key maps to a method in your filter class. Choose it when a field needs custom Eloquent logic, PHP attributes, or domain-specific behavior.

### Ruleset

```http
GET /api/posts?filter[status]=published&filter[views]=gte:100
```

The package applies allowed fields and resolved operators automatically. Choose it for compact APIs where conditions are independent.

### Expression

```http
GET /api/posts?filter[status][eq]=published&filter[author.name][like]=ahmed
```

Fields and operators are explicit in the request structure. Choose it when the API contract needs readable operator nesting or direct and deeply nested relations.

### Tree

```json
{
  "filter": {
    "and": [
      { "field": "status", "operator": "eq", "value": "published" },
      {
        "or": [
          { "field": "views", "operator": "gte", "value": 100 },
          { "field": "featured", "operator": "eq", "value": true }
        ]
      }
    ]
  }
}
```

Choose Tree only when the client must control grouped boolean logic. Its request contract is more powerful, but also more complex to validate and expose safely.

## Decision guide

Choose **Invokable** if most filters need their own PHP implementation.

Choose **Ruleset** if your filters are mostly direct comparisons and you want the smallest request format.

Choose **Expression** if the frontend naturally sends nested operator structures or relation paths.

Choose **Tree** if users build nested groups in an advanced search interface.

::: tip Start with the smallest contract
If Ruleset or Expression covers the public API, do not introduce a Tree request. You can change engines at a specific call site later without changing the rest of the package integration.
:::

## Shared capabilities

All four engines integrate with the same surrounding features:

- Allowed fields and operators
- Strict and permissive error handling
- Sanitization and request validation
- Sorting and pagination
- Runtime payload tracking
- Events, profiling, and caching

## Select an engine at the call site

```php
$posts = Filterable::for(Post::class, $request)
    ->using('expression')
    ->setAllowedFields(['status', 'title'])
    ->allowedOperators(['eq', 'like'])
    ->paginate();
```

Continue with the guide for the engine you selected, or follow the [Quick Start](/quick-start) to build an Invokable filter first.
