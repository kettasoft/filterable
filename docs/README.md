---
home: true
title: Filterable - Powerful Eloquent Filtering for Laravel
description: Kettasoft Filterable dynamically maps HTTP request parameters to Eloquent query builder filter methods with zero boilerplate.
config:
  # 1. Hero Section
  - type: hero
    full: true
    effect: tint-plate
    hero:
      name: Filterable
      tagline: Powerful Eloquent Filtering
      text: Dynamically map HTTP request parameters to Eloquent query builder methods with zero boilerplate.
      actions:
        - theme: brand
          text: Get Started →
          link: /introduction
        - theme: alt
          text: GitHub
          link: https://github.com/kettasoft/filterable

  # 2. Features Section
  - type: features
    features:
      - title: Invokable Engine
        icon: ⚡
        details: Map request parameters directly to filter methods using PHP attributes and annotations.
      - title: Tree Engine
        icon: 🌲
        details: Build nested, hierarchical filter structures for complex query requirements.
      - title: Rule-Set Engine
        icon: 📋
        details: Define declarative filter rules without writing custom filter classes.
      - title: Expression Engine
        icon: 🔤
        details: Use expressive DSL-like syntax to define filters inline.
      - title: Lifecycle Hooks
        icon: 🔄
        details: Hook into the filtering pipeline with before, after, and conditional callbacks.
      - title: Caching
        icon: 🚀
        details: Built-in caching support with multiple strategies and auto-invalidation.
      - title: Authorization
        icon: 🔒
        details: Control which filters a user is allowed to apply with built-in authorization support.
      - title: Validation & Sanitization
        icon: ✅
        details: Validate and sanitize incoming filter values before they hit your query.
      - title: CLI Tools
        icon: 🛠️
        details: Generate, discover, inspect, and test your filters directly from the command line.

  # 3. image-text Section
  - type: image-text
    title: Engines
    description: Choose the engine that fits your use case — or combine them.
    image: /images/logo.png
    list:
      - title: Invokable Engine
        description: Write filter methods as plain PHP methods, driven by annotations for full control.
      - title: Tree Engine
        description: Ideal for nested conditions and grouped query logic.
      - title: Rule-Set Engine
        description: Declarative rule-based filtering with no extra classes needed.
      - title: Expression Engine
        description: Compact, readable syntax for simple to moderate filter logic.

  # 4. Custom Section - Installation
  - type: custom
---

## Installation

::: code-tabs
@tab composer

```bash
composer require kettasoft/filterable
```

@tab with setup

```bash
composer require kettasoft/filterable
php artisan filterable:setup
```

:::
