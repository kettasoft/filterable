---
title: Introduction
description: Understand what Filterable solves and where it fits in a Laravel application.
tags: [introduction, overview, use cases]
---

# Introduction

Filterable keeps Eloquent filtering rules out of controllers and gives each endpoint a clear, reusable filtering policy. It is designed for APIs, dashboards, admin tables, and search screens where clients need to filter, sort, or paginate model queries.

Instead of forcing every project into one request format, Filterable provides four engines:

- [Invokable](/engines/invokable/) for custom behavior per filter field.
- [Ruleset](/engines/rule-set) for field/operator/value requests.
- [Expression](/engines/expression) for expressive filters and relational fields.
- [Tree](/engines/tree) for nested `AND`/`OR` groups sent as JSON.

## Why use Filterable?

- Keep controllers focused on handling requests and responses.
- Reuse the same filtering rules across endpoints.
- Allow only approved fields, operators, and relationships.
- Validate and sanitize request data before applying filters.
- Combine filtering with sorting, pagination, authorization, and caching.
- Test filtering behavior without duplicating query-building logic.

## Typical use cases

- A public API that accepts a controlled set of query parameters.
- An admin table with search, status filters, sorting, and pagination.
- A report builder that accepts nested logical conditions.
- A multi-tenant dashboard with authorized and cache-scoped filters.
- A product search endpoint that filters through categories and tags.

## Where to go next

Install the package, then follow the [Quick Start](/quick-start) to build a working endpoint. Before defining a public request contract, compare the options in [Choose an Engine](/choosing-an-engine).
