---
title: Pagination Policy
description: Apply consistent page-size defaults and limits to Laravel pagination calls made through Filterable.
tags: [pagination, api, limits, runtime configuration]
---

# Pagination Policy

Filterable keeps Laravel's familiar pagination API while enforcing a page-size
policy on calls made through `filter()`:

```php
$posts = Post::filter()->paginate();
```

The same policy applies to all three Laravel pagination methods:

```php
Post::filter()->paginate();
Post::filter()->simplePaginate();
Post::filter()->cursorPaginate();
```

Direct model queries are unchanged. `Post::query()->paginate(200)` continues to
use Laravel's native behavior without Filterable's policy.

## Configure the default policy

Publish or edit `config/filterable.php`:

```php
'pagination' => [
    'parameter' => 'per_page',
    'default' => 15,
    'max' => 100,
    'overflow' => 'clamp',
],
```

| Option | Purpose |
| --- | --- |
| `parameter` | Query-string parameter used to request a page size. |
| `default` | Page size used when neither code nor the request supplies one. |
| `max` | Hard maximum applied to explicit and request-provided sizes. |
| `overflow` | `clamp` caps oversized values; `reject` throws an exception. |

The matching environment variables are:

```dotenv
FILTERABLE_PAGINATION_PARAMETER=per_page
FILTERABLE_PAGINATION_DEFAULT=15
FILTERABLE_PAGINATION_MAX=100
FILTERABLE_PAGINATION_OVERFLOW=clamp
```

## Accept a client page size

With the default configuration, a request can select its page size normally:

```http
GET /api/posts?per_page=30
```

```php
$posts = Post::filter()->paginate();
```

The paginator uses 30 items per page. If the client requests 500 while `max`
is 100, `clamp` uses 100 instead.

Malformed, zero, and negative page sizes are rejected. They are not silently
converted into valid values.

## Explicit values and the maximum

An explicit Laravel argument takes priority over request input:

```php
// Uses 50 even when the request contains ?per_page=20.
$posts = Post::filter()->paginate(50);
```

The maximum remains authoritative. With `max` set to 100, this call uses 100:

```php
$posts = Post::filter()->paginate(500);
```

To allow a larger page for a specific endpoint, override the policy instead of
bypassing it.

## Configure one filter class

Define `$pagination` on a filter when every endpoint using it shares a policy:

```php
final class PostFilter extends Filterable
{
    protected $pagination = [
        'default' => 25,
        'max' => 200,
        'overflow' => 'reject',
    ];
}
```

Unspecified options inherit from `config/filterable.php`.

## Override the policy at runtime

Use `paginationPolicy()` for an endpoint-specific or authorization-aware limit:

```php
$maxPerPage = $request->user()->isAdmin() ? 500 : 100;

$posts = Post::filter()
    ->paginationPolicy(maxPerPage: $maxPerPage)
    ->paginate();
```

You can override every setting when necessary:

```php
$posts = Post::filter()
    ->paginationPolicy(
        defaultPerPage: 20,
        maxPerPage: 250,
        parameter: 'limit',
        overflow: 'reject',
    )
    ->simplePaginate();
```

Runtime values override filter-class values, which override package
configuration. Omitted runtime values continue to inherit.

## Reject oversized requests

Set `overflow` to `reject` when exceeding the limit should fail visibly:

```php
use Kettasoft\Filterable\Exceptions\InvalidPageSizeException;

try {
    $posts = Post::filter()
        ->paginationPolicy(maxPerPage: 100, overflow: 'reject')
        ->paginate();
} catch (InvalidPageSizeException $exception) {
    // Convert this into the API error response used by your application.
}
```

`InvalidPageSizeException` is also thrown for malformed and non-positive page
sizes.

## Laravel arguments remain available

Named and positional arguments are forwarded after the page size is resolved:

```php
$posts = Post::filter()->paginate(
    columns: ['id', 'title', 'status'],
    pageName: 'posts_page',
);
```

Closures accepted by Laravel's `paginate()` method are preserved and their
resolved value is still capped by the policy.

::: warning Raw builder mode
Calling pagination directly on the Filterable instance still applies the policy
when `shouldReturnQueryBuilder()` is enabled:

```php
Post::filter()->shouldReturnQueryBuilder()->paginate();
```

Calling `apply()` explicitly returns Eloquent and ends Filterable's execution
boundary. Pagination invoked later on that raw builder uses native Laravel
behavior and cannot be intercepted by the package.
:::
