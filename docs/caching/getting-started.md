# Getting Started with Caching

::: tip Overview
The Filterable caching system intelligently caches filtered query results to dramatically improve performance for frequently-used filters. It supports TTL, tags, scopes, profiles, and automatic cache invalidation.
:::

## Basic Usage

Enable caching for any filterable query by calling the `cache()` method:

```php
use App\Models\Post;

// Cache results for 1 hour (3600 seconds)
$posts = Post::filter()->cache(3600)->get();

// Cache with default TTL from config
$posts = Post::filter()->cache()->get();

// Cache forever (until manually flushed)
$posts = Post::filter()->cacheForever()->get();
```

## How It Works

The caching system works at the **terminal method** level, meaning it only caches when you actually retrieve data:

```php
// ✅ These are cached (terminal methods)
$posts = Post::filter()->cache(3600)->get();
$count = Post::filter()->cache(3600)->count();
$first = Post::filter()->cache(3600)->first();
$paginated = Post::filter()->cache(3600)->paginate(10);

// ❌ These are NOT cached (builder methods)
$query = Post::filter()->cache(3600)->where('status', 'active'); // Returns query builder
```

## Cache Key Generation

The system automatically generates unique, deterministic cache keys based on:

-   Filter class name
-   Applied filters
-   Provided data
-   Cache scopes (user, tenant, custom)
-   Terminal method and arguments

Example cache key:

```
filterable:post_filter:a1b2c3d4:e5f6g7h8:v1:get:abc123
```

## Configuration

Enable caching globally in `config/filterable.php`:

```php
'cache' => [
    'enabled' => true,
    'driver' => null, // Uses default Laravel cache driver
    'default_ttl' => 3600, // 1 hour
    'prefix' => 'filterable',
    'version' => 'v1', // Increment to invalidate all caches
],
```

Control via environment variables:

```env
FILTERABLE_CACHE_ENABLED=true
FILTERABLE_CACHE_DRIVER=redis
FILTERABLE_CACHE_TTL=3600
FILTERABLE_CACHE_PREFIX=filterable
FILTERABLE_CACHE_VERSION=v1
```

## When to Use Caching

**✅ Good Use Cases:**

-   Frequently accessed data that changes infrequently
-   Heavy reports and analytics queries
-   Dashboard widgets with complex filters
-   Public-facing filtered lists
-   Search results with stable criteria

**❌ Avoid Caching For:**

-   Real-time data that must be always fresh
-   User-specific data without proper scoping
-   One-time queries
-   Simple queries that are already fast

## Performance Impact

Typical performance gains:

| Scenario                        | Without Cache | With Cache | Improvement     |
| ------------------------------- | ------------- | ---------- | --------------- |
| Complex filters (5+ conditions) | 250ms         | 2ms        | **125x faster** |
| Joins with aggregations         | 500ms         | 2ms        | **250x faster** |
| Paginated results               | 150ms         | 2ms        | **75x faster**  |
| Simple filters                  | 50ms          | 2ms        | **25x faster**  |

::: tip Next Steps

-   [Advanced caching strategies →](./strategies.md)
-   [Cache scoping and multi-tenancy →](./scoping.md)
-   [Auto-invalidation setup →](./auto-invalidation.md)
-   [Cache profiles →](./profiles.md)
    :::
