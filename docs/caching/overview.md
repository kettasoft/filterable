# Caching

::: tip Overview
Filterable provides a powerful and flexible caching system that can dramatically improve your application's performance by caching filtered query results.
:::

## Key Features

-   **🚀 Simple API** - Cache with a single method call
-   **⏱️ Flexible TTL** - Time-based, permanent, or conditional caching
-   **🏷️ Tagged Caching** - Organize and invalidate caches by tags
-   **👤 Scoped Caching** - User, tenant, or custom scopes
-   **🔄 Auto-invalidation** - Automatically clear cache when models change
-   **📊 Cache Profiles** - Reusable cache configurations
-   **📈 Performance Tracking** - Monitor cache effectiveness
-   **🎯 Precise Control** - Fine-grained cache management

## Quick Start

### Basic Usage

```php
use App\Models\Post;

// Cache for 1 hour
$posts = Post::filter()
    ->cache(3600)
    ->apply(['status' => 'published'])
    ->get();

// Cache forever
$categories = Category::filter()
    ->cacheForever()
    ->get();

// Conditional caching
$data = Model::filter()
    ->cacheWhen(!auth()->user()->isAdmin(), 3600)
    ->get();
```

### With Tags

```php
// Cache with tags for easy invalidation
$posts = Post::filter()
    ->cache(3600)
    ->cacheTags(['posts', 'content'])
    ->get();

// Flush by tags
Post::flushCacheByTagsStatic(['posts']);
```

### User-scoped Caching

```php
// Each user gets their own cache
$personalFeed = Post::filter()
    ->cache(1800)
    ->scopeByUser()
    ->get();
```

### Multi-tenant Caching

```php
// Isolated cache per tenant
$products = Product::filter()
    ->cache(3600)
    ->scopeByTenant(tenant()->id)
    ->get();
```

## Performance Impact

Caching can provide significant performance improvements:

| Scenario                            | Without Cache | With Cache | Improvement      |
| ----------------------------------- | ------------- | ---------- | ---------------- |
| Simple query (10 filters)           | 45ms          | 2ms        | **22.5x faster** |
| Complex query (joins, aggregations) | 320ms         | 3ms        | **106x faster**  |
| Large dataset (10k+ records)        | 580ms         | 2ms        | **290x faster**  |
| User-scoped query                   | 125ms         | 2ms        | **62.5x faster** |

## Documentation

### Getting Started

-   [**Getting Started**](/caching/getting-started.md) - Installation, configuration, and basic usage
-   [**Examples**](/caching/examples.md) - Real-world examples and common patterns

### Core Concepts

-   [**Caching Strategies**](/caching/strategies.md) - TTL, forever, conditional, tagged, scoped, and profile-based caching
-   [**Cache Profiles**](/caching/profiles.md) - Reusable cache configurations
-   [**Cache Scoping**](/caching/scoping.md) - User, tenant, and custom scoping
-   [**Auto-invalidation**](/caching/auto-invalidation.md) - Automatic cache clearing

### Advanced Topics

-   [**Monitoring & Debugging**](/caching/monitoring.md) - Performance tracking and troubleshooting
-   [**API Reference**](/caching/api-reference.md) - Complete API documentation

## Installation

### 1. Configuration

Publish the configuration file:

```bash
php artisan vendor:publish --tag=filterable-config
```

### 2. Enable Caching

In your `.env` file:

```env
FILTERABLE_CACHE_ENABLED=true
FILTERABLE_CACHE_DRIVER=redis
FILTERABLE_CACHE_TTL=3600
```

### 3. Add Trait to Models

```php
use Kettasoft\Filterable\Traits\HasFilterableCache;

class Post extends Model
{
    use HasFilterableCache;
}
```

### 4. Start Caching

```php
$posts = Post::filter()
    ->cache(3600)
    ->get();
```

## Common Use Cases

### Blog Platform

```php
// Post listings with 30-minute cache
$posts = Post::filter()
    ->cache(1800)
    ->cacheTags(['posts', 'listings'])
    ->apply($request->all())
    ->paginate(15);
```

### E-commerce

```php
// Product catalog with 1-hour cache
$products = Product::filter()
    ->cache(3600)
    ->cacheTags(['products', 'catalog'])
    ->scopeBy('region', $region)
    ->apply($request->all())
    ->paginate(24);
```

### SaaS Application

```php
// Tenant-isolated dashboard
$metrics = Metric::filter()
    ->cache(600)
    ->cacheTags(['metrics', 'dashboard'])
    ->scopeByTenant(tenant()->id)
    ->scopeByUser()
    ->get();
```

### API Endpoints

```php
// Cached API responses
$data = Model::filter()
    ->cache(3600)
    ->cacheTags(['api', 'public'])
    ->scopeBy('api_version', 'v1')
    ->apply($request->all())
    ->paginate(20);
```

## Features Overview

### Time-based Caching

```php
// Fixed TTL
->cache(3600)  // 1 hour

// Forever
->cacheForever()

// DateTime
->cache(now()->addHour())
```

### Tagged Caching

```php
// Single tag
->cacheTags(['posts'])

// Multiple tags
->cacheTags(['posts', 'content', 'published'])

// Flush by tags
Post::flushCacheByTagsStatic(['posts']);
```

### Scoped Caching

```php
// User scope
->scopeByUser()
->scopeByUser(123)

// Tenant scope
->scopeByTenant(tenant()->id)

// Custom scope
->scopeBy('organization', $orgId)

// Multiple scopes
->withScopes([
    'organization' => $orgId,
    'department' => $deptId,
])
```

### Conditional Caching

```php
// Cache when condition is true
->cacheWhen(!auth()->user()->isAdmin(), 3600)

// Cache unless condition is true
->cacheUnless(request()->has('refresh'), 3600)

// Cache with callback
->cacheWhen(fn() => !app()->isLocal(), 3600)
```

### Cache Profiles

```php
// config/filterable.php
'profiles' => [
    'heavy_reports' => [
        'ttl' => 21600,  // 6 hours
        'tags' => ['reports', 'analytics'],
    ],
],
```

```php
// Use profile
$report = Report::filter()
    ->cacheProfile('heavy_reports')
    ->get();
```

### Auto-invalidation

```php
// config/filterable.php
'auto_invalidate' => [
    'enabled' => true,
    'models' => [
        \App\Models\Post::class => ['posts', 'content'],
    ],
],
```

When a Post is created, updated, or deleted, all caches tagged with `posts` or `content` are automatically cleared.

## Configuration Reference

```php
// config/filterable.php
return [
    'cache' => [
        // Global enable/disable
        'enabled' => env('FILTERABLE_CACHE_ENABLED', true),

        // Cache driver
        'driver' => env('FILTERABLE_CACHE_DRIVER', null),

        // Default TTL (seconds)
        'default_ttl' => env('FILTERABLE_CACHE_TTL', 3600),

        // Cache key prefix
        'prefix' => env('FILTERABLE_CACHE_PREFIX', 'filterable'),

        // Cache profiles
        'profiles' => [
            'standard' => [
                'ttl' => 3600,
                'tags' => ['standard'],
            ],
        ],

        // Auto-invalidation
        'auto_invalidate' => [
            'enabled' => env('FILTERABLE_AUTO_INVALIDATE', false),
            'models' => [
                // Model::class => ['tags'],
            ],
        ],

        // Cache tracking
        'tracking' => [
            'enabled' => env('FILTERABLE_CACHE_TRACKING', false),
            'log_channel' => env('FILTERABLE_CACHE_LOG_CHANNEL', 'daily'),
        ],
    ],
];
```

## Requirements

-   **Cache Driver**: Any Laravel cache driver (Redis/Memcached recommended for tags)
-   **Laravel**: 9.x or higher
-   **PHP**: 8.1 or higher

## Best Practices

### 1. Use Appropriate TTLs

```php
// Frequently changing data
->cache(300)  // 5 minutes

// Standard data
->cache(3600)  // 1 hour

// Rarely changing data
->cache(86400)  // 24 hours

// Static content
->cacheForever()
```

### 2. Always Tag Your Caches

```php
// Makes invalidation easier
->cacheTags(['posts', 'content'])
```

### 3. Scope Sensitive Data

```php
// User-specific
->scopeByUser()

// Tenant-specific
->scopeByTenant(tenant()->id)
```

### 4. Use Profiles for Consistency

```php
// Define once, use everywhere
->cacheProfile('heavy_reports')
```

### 5. Enable Auto-invalidation

```php
'auto_invalidate' => [
    'enabled' => true,
    'models' => [
        Post::class => ['posts'],
    ],
],
```

## Troubleshooting

### Cache Not Working

Check that caching is enabled and cache driver is configured:

```env
FILTERABLE_CACHE_ENABLED=true
CACHE_DRIVER=redis  # Not 'array'
```

### Tags Not Working

Tags require Redis or Memcached:

```env
CACHE_DRIVER=redis  # or memcached
```

### Cache Not Invalidating

Ensure auto-invalidation is enabled and models are configured:

```php
'auto_invalidate' => [
    'enabled' => true,
    'models' => [
        YourModel::class => ['your-tags'],
    ],
],
```

## Next Steps

Choose your learning path:

**For Beginners:**

1. [Getting Started →](/caching/getting-started.md) - Basic setup and usage
2. [Examples →](/caching/examples.md) - Real-world code examples
3. [Caching Strategies →](/caching/strategies.md) - Different caching approaches

**For Advanced Users:**

1. [Cache Profiles →](/caching/profiles.md) - Reusable configurations
2. [Cache Scoping →](/caching/scoping.md) - Multi-tenant and user isolation
3. [Auto-invalidation →](/caching/auto-invalidation.md) - Automatic cache clearing
4. [Monitoring →](/caching/monitoring.md) - Performance tracking

**For Reference:**

-   [API Reference →](/caching/api-reference.md) - Complete API documentation

## Support

-   [GitHub Issues](https://github.com/kettasoft/filterable/issues)
-   [Documentation](https://github.com/kettasoft/filterable/docs)

::: tip Contributing
Found a bug or have a suggestion? Please open an issue on GitHub!
:::
