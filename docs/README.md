---
home: true
title: Home
heroImage: /logo.png
actions:
  - text: Introduction
    link: /introduction
    type: primary
  - text: Installation
    link: installation
    type: secondary

features:
  - title: ⚙️ Multiple Filtering Engines
    details: Support different filtering strategies like RuleSet, Tree-Based, Dynamic Methods, and SQL Expressions — all pluggable and extensible.
  - title: 🧩 Clean, Decoupled Architecture
    details: Built with SOLID principles in mind. Easily swap or extend engines without touching core logic.
  - title: 🧼 Customizable Filter Sanitizers
    details: Apply custom sanitization and validation logic for every input field or filter operator.
  - title: 🔗 Relation & Nested Filters Support
    details: Filter through deep nested relationships with controlled access and relation depth to preserve performance and security.
  - title: 🧠 Intelligent Field Management
    details: Define allowed fields, nested relations, and control exactly what’s queryable in each context.
  - title: 🚀 Plug & Play Integration
    details: Works seamlessly with any Laravel query builder. Minimal setup required to get started.

footer: MIT Licensed | Copyright © 2024-present Kettasoft
---

This is the content of home page. Check [Home Page Docs][intro] for more details.

[intro]: /introduction

## 📚 Use Cases

- Build complex dashboards with advanced filtering capabilities.
- Create public APIs with strict control over what fields can be queried.
- Support admin panels that need custom filtering rules per user role.
- Handle dynamic filtering for search pages or reports.

## 🔧 Example Use

```php
Filterable::withRequest($request)
    ->useEngine('ruleset')
    ->setAllowedFields(['status', 'title', 'author.name'])
    ->setRelations(['author'])
    ->run(Post::query());
```

## 🧪 Tested & Production-Ready

Filterable is heavily tested and battle-proven in real-world applications, ensuring stability and reliability even with large datasets and complex filters.

## 💡 Extending the Package

Need a custom engine? Simply extends the `Engine` abstract class and register it — the system is built for extension and customization.
