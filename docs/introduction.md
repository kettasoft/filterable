---
title: Introduction
description:
    Filterable is a Laravel package for advanced Eloquent query filtering.
    It supports multiple filtering engines, deep relationship filtering,
    and a clean expressive syntax — ideal for APIs, dashboards, and data search systems.
tags: [introduction, overview, features, why use filterable]
---

[![Total Downloads](https://img.shields.io/packagist/dt/kettasoft/filterable?style=flat-square)](https://packagist.org/packages/kettasoft/filterable)&nbsp;
[![Tests](https://github.com/kettasoft/filterable/actions/workflows/php.yml/badge.svg)](https://github.com/kettasoft/filterable/actions/workflows/php.yml)

Filterable is an elegant, developer-friendly Laravel package designed to simplify
and streamline advanced filtering of Eloquent queries. Whether you're building APIs,
admin dashboards, or complex data search systems, Filterable gives you full control
over how data is filtered — without compromising on flexibility or performance.

## Why Filterable?

Filterable supports a **multi-engine architecture**, allowing you to choose or define
how filtering should work, tailored to your project's exact needs. You can filter based
on query parameters, nested relationships, or even dynamic methods — all with minimal boilerplate.

## Key Features

### Multiple filtering engines

Choose the engine that fits your use case:

- [Ruleset Engine](/engines/ruleset) — flat rule arrays with operator support
- [Invokable Engine](/engines/invokable) — method-based dynamic filtering
- [Tree Engine](/engines/tree) — nested relationship filtering
- [Expression Engine](/engines/expression) — advanced expression-based filtering

### Customizable filter sanitizers

Sanitize and validate user input at the filter level before it reaches your query builder.

### Decoupled & extendable architecture

Built with SOLID principles to support clean separation of concerns and easy engine extension.

### Deep relationship filtering

Filter through nested Eloquent relationships with full control over allowed fields and depth.

### Plug & play integration

Works seamlessly with any Laravel query builder — just install and start filtering.

### Readable, expressive syntax

Define filters in a natural, concise way for better maintainability and team collaboration.

---

Filterable empowers you to keep filtering logic organized, testable, and reusable —
making it a must-have tool for any Laravel developer working with structured data.
