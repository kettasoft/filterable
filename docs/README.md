---
home: true
title: Home
heroImage: /images/logo.png
tagline: Advanced, Extensible Filtering for Laravel Applications
actions:
    - text: Get Started
      link: /introduction
      type: primary
    - text: View on GitHub
      link: https://github.com/kettasoft/filterable
      type: secondary

features:
    - title: ⚙️ Four Filtering Engines
      details: Invokable, Ruleset, Expression, and Tree — each designed for a different filtering style. Pick the one that fits your use case.
    - title: 🧩 Clean, Decoupled Architecture
      details: Built with SOLID principles in mind. Easily swap or extend engines without touching core logic.
    - title: 🚀 Built-in Caching
      details: Tagged caching, user-scoped, tenant-scoped, auto-invalidation, and cache profiles — all built into the filter pipeline.
    - title: 🔗 Deep Relation Filtering
      details: Filter through nested Eloquent relationships using dot notation. Controlled depth and whitelisted fields keep it secure.
    - title: 🛡️ Authorization & Validation
      details: Protect filter classes by role or permission. Define validation rules and sanitizers co-located with your filter logic.
    - title: 🖥️ Powerful CLI
      details: Generate, discover, list, test, and inspect filter classes directly from the command line.

footer: MIT Licensed | Copyright © 2024-present Kettasoft
---

## Choose Your Engine

Each engine is designed for a different filtering style. Pick the one that matches how your frontend sends data.

| Engine         | Best For                   | Example                            |
| -------------- | -------------------------- | ---------------------------------- |
| **Invokable**  | Custom logic per field     | `?status=active&title=laravel`     |
| **Ruleset**    | Operator-based API queries | `?filter[title][like]=laravel`     |
| **Expression** | Ruleset + nested relations | `?filter[author.name][like]=ahmed` |
| **Tree**       | Complex AND/OR JSON logic  | `{ "and": [...] }`                 |

## Quick Example

```php
// 1. Define your filter class
class PostFilter extends Filterable
{
    protected $filters = ['status', 'title'];

    protected function title(Payload $payload)
    {
        return $this->builder->where('title', 'like', $payload->asLike('both'));
    }

    protected function status(Payload $payload)
    {
        return $this->builder->where('status', $payload->value);
    }
}

// 2. Apply it in your controller
Post::filter(PostFilter::class)->paginate();
```

## Use Cases

- REST APIs that need strict control over queryable fields
- Admin panels with role-based filter access
- Multi-tenant dashboards with isolated cached results
- Reporting tools with complex AND/OR filter logic
