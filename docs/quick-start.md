---
title: Quick Start
description: Build and run your first Filterable query in a few minutes.
tags: [quick start, tutorial, invokable]
---

# Quick Start

This guide takes you from an installed package to a working filtered endpoint. It uses the default **Invokable** engine because it gives each public filter an explicit PHP method.

::: info Before you start
Complete the [installation guide](/installation) and make sure your application has a `Post` model and database table.
:::

## 1. Generate a filter

Create a filter with `status` and `search` methods:

```bash
php artisan filterable:make-filter PostFilter --filters=status,search
```

The command creates `app/Http/Filters/PostFilter.php`.

## 2. Define the filtering behavior

Replace the generated methods with focused query rules:

```php
<?php

namespace App\Http\Filters;

use Illuminate\Contracts\Database\Eloquent\Builder;
use Kettasoft\Filterable\Engines\Foundation\Attributes\Annotations\Trim;
use Kettasoft\Filterable\Filterable;
use Kettasoft\Filterable\Support\Payload;

final class PostFilter extends Filterable
{
    protected $filters = ['status', 'search'];

    protected function status(Payload $payload): Builder
    {
        return $this->builder->where('status', $payload->value);
    }

    #[Trim]
    protected function search(Payload $payload): Builder
    {
        return $this->builder->where(
            'title',
            'like',
            $payload->asLike('both'),
        );
    }
}
```

Each accepted request value becomes a [`Payload`](/api/payload). It keeps the field, resolved operator, transformed value, and original value together throughout the filtering lifecycle.

## 3. Bind the filter to the model

Add the package trait and default filter class to `Post`:

```php
<?php

namespace App\Models;

use App\Http\Filters\PostFilter;
use Illuminate\Database\Eloquent\Model;
use Kettasoft\Filterable\Traits\InteractsWithFilterable;

final class Post extends Model
{
    use InteractsWithFilterable;

    protected $filterable = PostFilter::class;
}
```

## 4. Use it in an endpoint

The filtered object continues to accept normal Eloquent builder methods:

```php
use App\Models\Post;

public function index()
{
    return Post::filter()
        ->latest()
        ->paginate();
}
```

Filterable applies the request filters automatically before `paginate()` runs.

## 5. Send a request

```http
GET /api/posts?status=published&search=laravel
```

The query now returns published posts whose titles contain `laravel`. Unregistered request keys do not become Invokable filter methods.

## What to learn next

- Read [Choose an Engine](/choosing-an-engine) before designing a public filtering contract.
- Explore the [Invokable engine](/engines/invokable/) for attributes, method mapping, and testing.
- Add [validation](/validation), [sanitization](/sanitization), or [authorization](/authorization) as the endpoint grows.
