# Caching Strategies

::: tip Overview
Learn different caching strategies to optimize your application's performance based on your specific needs.
:::

## Time-Based Caching (TTL)

Cache results for a specific duration:

```php
use App\Models\Post;

// Cache for 1 hour
$posts = Post::filter()->cache(3600)->get();

// Cache for 30 minutes
$posts = Post::filter()->cache(1800)->get();

// Cache for 1 day
$posts = Post::filter()->cache(86400)->get();

// Use DateTimeInterface
$posts = Post::filter()
    ->cache(now()->addHours(2))
    ->get();
```

## Forever Caching

Cache until manually invalidated:

```php
// Cache permanently
$categories = Category::filter()
    ->cacheForever()
    ->get();

// Manually flush when needed
Category::filter()->flushCache();
```

**Best for:**

-   Static or rarely changing data (categories, settings)
-   Reference data
-   Configuration lists

## Conditional Caching

Cache only when certain conditions are met:

```php
// Cache when user is not admin
$posts = Post::filter()
    ->cacheWhen(!auth()->user()->isAdmin(), 3600)
    ->get();

// Cache based on dynamic condition
$posts = Post::filter()
    ->cacheWhen(function () {
        return !app()->isDownForMaintenance();
    }, 1800)
    ->get();

// Cache unless condition is true
$posts = Post::filter()
    ->cacheUnless(request()->has('fresh'), 3600)
    ->get();
```

**Best for:**

-   Different caching behavior for different user types
-   Development vs production environments
-   Dynamic cache decisions based on request context

## Tagged Caching

Organize caches with tags for efficient bulk invalidation:

```php
// Cache with tags
$posts = Post::filter()
    ->cache(3600)
    ->cacheTags(['posts', 'content'])
    ->get();

// Flush all caches with 'posts' tag
Post::flushCacheByTagsStatic(['posts']);

// Or from instance
$filter = new PostFilter();
$filter->flushCacheByTags(['posts']);
```

::: warning Tag Support Required
Cache tags require Redis or Memcached. File and database cache drivers don't support tags.
:::

**Best for:**

-   Grouping related caches
-   Bulk cache invalidation
-   Auto-invalidation setup

## Scoped Caching

Create isolated caches for different contexts:

### User Scoping

Cache per-user to avoid data leakage:

```php
// Automatic user scoping
$posts = Post::filter()
    ->cache(3600)
    ->scopeByUser() // Uses auth()->id()
    ->get();

// Explicit user ID
$posts = Post::filter()
    ->cache(3600)
    ->scopeByUser($userId)
    ->get();
```

### Tenant Scoping

Perfect for multi-tenant applications:

```php
// Scope by tenant
$posts = Post::filter()
    ->cache(3600)
    ->scopeByTenant($tenantId)
    ->get();
```

### Custom Scoping

Create your own cache scopes:

```php
// Single scope
$posts = Post::filter()
    ->cache(3600)
    ->scopeBy('organization', $orgId)
    ->get();

// Multiple scopes
$posts = Post::filter()
    ->cache(3600)
    ->withScopes([
        'organization' => $orgId,
        'department' => $deptId,
        'region' => $region,
    ])
    ->get();
```

**Best for:**

-   Multi-tenant applications
-   User-specific data
-   Organization/department isolation
-   Regional data separation

## Profile-Based Caching

Define reusable cache configurations:

### Configuration

```php
// config/filterable.php
'cache' => [
    'profiles' => [
        'heavy_reports' => [
            'ttl' => 7200, // 2 hours
            'tags' => ['reports', 'analytics'],
        ],
        'quick_filters' => [
            'ttl' => 300, // 5 minutes
            'tags' => ['filters'],
        ],
        'dashboards' => [
            'ttl' => 600, // 10 minutes
            'tags' => ['dashboard', 'widgets'],
        ],
    ],
],
```

### Usage

```php
// Use a profile
$report = Report::filter()
    ->cacheProfile('heavy_reports')
    ->get();

// Profile settings are automatically applied:
// - TTL: 7200 seconds
// - Tags: ['reports', 'analytics']
```

**Best for:**

-   Consistent caching across similar features
-   DRY principle (Don't Repeat Yourself)
-   Team collaboration with standard patterns

## Method-Specific Caching

Different terminal methods are cached separately:

```php
$filter = Post::filter()->cache(3600);

// Each method creates its own cache entry
$all = $filter->get();           // Cache key: ...filterable:post_filter:...:get:...
$first = $filter->first();       // Cache key: ...filterable:post_filter:...:first:...
$count = $filter->count();       // Cache key: ...filterable:post_filter:...:count:...
$paginated = $filter->paginate(10); // Cache key: ...filterable:post_filter:...:paginate:...
```

This ensures each query type maintains its own cache.

## Combining Strategies

Mix and match strategies for optimal caching:

```php
// Heavy report: long TTL + tags + tenant scoping
$report = Report::filter()
    ->cache(7200)
    ->cacheTags(['reports', 'analytics'])
    ->scopeByTenant($tenantId)
    ->get();

// User dashboard: conditional + user scoping + profile
$dashboard = Dashboard::filter()
    ->cacheWhen(!request()->has('refresh'))
    ->scopeByUser()
    ->cacheProfile('dashboards')
    ->get();

// Public data: forever + tags
$categories = Category::filter()
    ->cacheForever()
    ->cacheTags(['categories', 'public'])
    ->get();
```

## Performance Optimization Tips

### 1. Cache Warming

Pre-populate caches during off-peak hours:

```php
// In a scheduled job
class WarmFilterCachesJob
{
    public function handle()
    {
        // Warm popular filters
        Post::filter(['status' => 'published'])
            ->cache(3600)
            ->get();

        Category::filter()
            ->cacheForever()
            ->get();
    }
}
```

### 2. Appropriate TTL Selection

Choose TTL based on data volatility:

```php
// High volatility (real-time data) - short TTL
$liveData = Metric::filter()->cache(60)->get(); // 1 minute

// Medium volatility (frequently updated) - medium TTL
$posts = Post::filter()->cache(1800)->get(); // 30 minutes

// Low volatility (rarely changes) - long TTL
$settings = Setting::filter()->cache(86400)->get(); // 24 hours

// Static data - forever
$categories = Category::filter()->cacheForever()->get();
```

### 3. Tag Hierarchy

Organize tags hierarchically for flexible invalidation:

```php
// Specific tags for granular control
$posts = Post::filter()
    ->cacheTags(['posts', 'posts:published', 'content'])
    ->get();

// Flush specific subset
Post::flushCacheByTagsStatic(['posts:published']);

// Or flush all post-related caches
Post::flushCacheByTagsStatic(['posts']);
```

### 4. Scope Optimization

Use scopes to prevent cache pollution:

```php
// ❌ Bad: All users share same cache (potential data leakage)
$userPosts = Post::where('user_id', auth()->id())
    ->filter()
    ->cache(3600)
    ->get();

// ✅ Good: Each user has their own cache
$userPosts = Post::where('user_id', auth()->id())
    ->filter()
    ->cache(3600)
    ->scopeByUser()
    ->get();
```

## Monitoring and Debugging

### Enable Cache Tracking

```php
// config/filterable.php
'cache' => [
    'tracking' => [
        'enabled' => true,
        'log_channel' => 'daily',
    ],
],
```

Cache hits, misses, and invalidations will be logged for analysis.

### Debug Cache Keys

```php
$filter = Post::filter()->cache(3600);

// See what cache key will be used
$cacheKey = $filter->getCacheKey(); // Not yet implemented, but useful for debugging

// For now, enable tracking to see keys in logs
```

## Common Patterns

### Pattern 1: Paginated Results with Caching

```php
class PostController
{
    public function index(Request $request)
    {
        $posts = Post::filter()
            ->cache(1800)
            ->scopeByUser()
            ->cacheTags(['posts'])
            ->paginate($request->get('per_page', 15));

        return view('posts.index', compact('posts'));
    }
}
```

### Pattern 2: Dashboard Widgets

```php
class DashboardController
{
    public function index()
    {
        // Each widget uses caching
        $stats = [
            'posts' => Post::filter()->cache(600)->count(),
            'users' => User::filter()->cache(600)->count(),
            'revenue' => Order::filter()
                ->cache(300)
                ->sum('total'),
        ];

        return view('dashboard', compact('stats'));
    }
}
```

### Pattern 3: API Endpoints

```php
class ApiPostController
{
    public function index(Request $request)
    {
        // Cache API responses
        $posts = Post::filter()
            ->cache($request->get('cache', 1800))
            ->cacheUnless($request->has('no_cache'))
            ->cacheTags(['api', 'posts'])
            ->paginate();

        return response()->json($posts);
    }
}
```

::: tip Next Steps

-   [Cache scoping details →](./scoping.md)
-   [Auto-invalidation setup →](./auto-invalidation.md)
-   [API reference →](../api/caching.md)
    :::
