# Cache Profiles

::: tip What are Cache Profiles?
Cache profiles are reusable, predefined cache configurations that allow you to define complex caching strategies once and reuse them throughout your application.
:::

## Overview

Cache profiles eliminate the need to repeatedly configure the same caching settings across your application. Define your caching strategy once in configuration, then apply it with a single method call.

### Benefits

- **Consistency**: Ensure the same caching strategy is used across your application
- **Maintainability**: Update caching behavior in one place
- **Simplicity**: Clean, readable code without repetitive configuration
- **Environment-specific**: Different profiles for development, staging, and production

## Basic Usage

### Defining Profiles

Define cache profiles in `config/filterable.php`:

```php
// config/filterable.php
return [
    'cache' => [
        'profiles' => [
            'quick' => [
                'ttl' => 300, // 5 minutes
                'tags' => ['quick-cache'],
            ],
            
            'standard' => [
                'ttl' => 3600, // 1 hour
                'tags' => ['standard-cache'],
            ],
            
            'long_term' => [
                'ttl' => 86400, // 24 hours
                'tags' => ['long-term-cache'],
            ],
        ],
    ],
];
```

### Using Profiles

Apply a profile to your filterable query:

```php
use App\Filters\PostFilter;
use App\Models\Post;

// Use the 'standard' profile
$posts = Post::filter()
    ->cacheProfile('standard')
    ->get();

// Use the 'quick' profile for frequently changing data
$recentPosts = Post::filter()
    ->cacheProfile('quick')
    ->apply(['status' => 'published'])
    ->get();
```

## Profile Configuration

### Profile Structure

Each profile can contain the following settings:

```php
'profile_name' => [
    'ttl' => 3600,                    // Time to live in seconds
    'tags' => ['tag1', 'tag2'],       // Cache tags
    'scopes' => ['user', 'tenant'],   // Auto-apply scopes
    'enabled' => true,                 // Enable/disable this profile
],
```

### Configuration Options

| Option | Type | Description |
|--------|------|-------------|
| `ttl` | int\|null | Cache duration in seconds (null = forever) |
| `tags` | array | Cache tags for organization |
| `scopes` | array | Scopes to automatically apply |
| `enabled` | bool | Whether this profile is active |

## Common Profile Examples

### Short-lived Data

For data that changes frequently:

```php
'realtime' => [
    'ttl' => 60,  // 1 minute
    'tags' => ['realtime'],
    'enabled' => env('CACHE_REALTIME', true),
],
```

```php
// Live dashboard data
$stats = DashboardFilter::filter()
    ->cacheProfile('realtime')
    ->get();
```

### User-specific Data

For user-scoped queries:

```php
'user_personalized' => [
    'ttl' => 1800,  // 30 minutes
    'tags' => ['users', 'personalized'],
    'scopes' => ['user'],  // Auto-apply user scope
],
```

```php
// User's personalized feed
$feed = Post::filter()
    ->cacheProfile('user_personalized')
    ->apply(['feed_type' => 'personalized'])
    ->get();
```

### Heavy Reports

For expensive queries and reports:

```php
'heavy_reports' => [
    'ttl' => 21600,  // 6 hours
    'tags' => ['reports', 'analytics'],
    'enabled' => env('CACHE_REPORTS', true),
],
```

```php
// Sales analytics report
$report = SalesFilter::filter()
    ->cacheProfile('heavy_reports')
    ->apply(['year' => 2024])
    ->get();
```

### Static Content

For rarely changing data:

```php
'static_content' => [
    'ttl' => null,  // Forever
    'tags' => ['static', 'content'],
],
```

```php
// Categories, tags, settings
$categories = Category::filter()
    ->cacheProfile('static_content')
    ->get();
```

### Multi-tenant Data

For SaaS applications:

```php
'tenant_isolated' => [
    'ttl' => 7200,  // 2 hours
    'tags' => ['tenants'],
    'scopes' => ['tenant'],
],
```

```php
// Tenant-specific products
$products = Product::filter()
    ->cacheProfile('tenant_isolated')
    ->apply(['status' => 'active'])
    ->get();
```

## Environment-specific Profiles

### Development Profile

```php
'development' => [
    'ttl' => 10,  // Very short cache
    'tags' => ['dev'],
    'enabled' => app()->environment('local'),
],
```

### Staging Profile

```php
'staging' => [
    'ttl' => 300,  // 5 minutes
    'tags' => ['staging'],
    'enabled' => app()->environment('staging'),
],
```

### Production Profile

```php
'production' => [
    'ttl' => 3600,  // 1 hour
    'tags' => ['production'],
    'enabled' => app()->environment('production'),
],
```

### Usage

```php
// Automatically uses the right profile for the environment
$data = Model::filter()
    ->cacheProfile(config('app.env'))
    ->get();
```

## Advanced Patterns

### Profile Inheritance

Create base profiles and extend them:

```php
// config/filterable.php
'profiles' => [
    // Base profile
    'base' => [
        'ttl' => 3600,
        'tags' => ['base'],
    ],
    
    // Extended profiles
    'posts' => [
        'ttl' => 3600,
        'tags' => ['base', 'posts'],
    ],
    
    'products' => [
        'ttl' => 3600,
        'tags' => ['base', 'products'],
    ],
],
```

### Dynamic Profile Selection

```php
class PostFilter extends Filterable
{
    protected function getCacheProfile(): string
    {
        // Select profile based on filter criteria
        if ($this->filters['category'] === 'news') {
            return 'quick';  // News changes frequently
        }
        
        if ($this->filters['type'] === 'report') {
            return 'heavy_reports';
        }
        
        return 'standard';
    }
    
    public function apply(array $filters = []): Builder
    {
        return parent::apply($filters)
            ->cacheProfile($this->getCacheProfile());
    }
}
```

### Conditional Profiles

```php
$profile = auth()->user()->isPremium() 
    ? 'premium_cache'  // Better caching for premium users
    : 'standard';

$posts = Post::filter()
    ->cacheProfile($profile)
    ->get();
```

### Overriding Profile Settings

You can override specific profile settings:

```php
// Use profile but override TTL
$posts = Post::filter()
    ->cacheProfile('standard')
    ->cache(7200)  // Override TTL to 2 hours
    ->get();

// Use profile but add more tags
$posts = Post::filter()
    ->cacheProfile('standard')
    ->cacheTags(['custom-tag'])  // Adds to profile tags
    ->get();
```

## Real-world Examples

### Blog Platform

```php
// config/filterable.php
'profiles' => [
    'posts_list' => [
        'ttl' => 1800,  // 30 minutes
        'tags' => ['posts', 'listings'],
    ],
    
    'post_detail' => [
        'ttl' => 3600,  // 1 hour
        'tags' => ['posts', 'details'],
    ],
    
    'author_posts' => [
        'ttl' => 1800,
        'tags' => ['posts', 'authors'],
        'scopes' => ['user'],
    ],
];
```

```php
// In controllers
class PostController
{
    public function index()
    {
        return Post::filter()
            ->cacheProfile('posts_list')
            ->get();
    }
    
    public function show($id)
    {
        return Post::filter()
            ->cacheProfile('post_detail')
            ->apply(['id' => $id])
            ->first();
    }
    
    public function authorPosts($authorId)
    {
        return Post::filter()
            ->cacheProfile('author_posts')
            ->scopeByUser($authorId)
            ->get();
    }
}
```

### E-commerce Platform

```php
// config/filterable.php
'profiles' => [
    'product_catalog' => [
        'ttl' => 7200,  // 2 hours
        'tags' => ['products', 'catalog'],
    ],
    
    'product_search' => [
        'ttl' => 600,  // 10 minutes
        'tags' => ['products', 'search'],
    ],
    
    'inventory_check' => [
        'ttl' => 60,  // 1 minute
        'tags' => ['inventory'],
    ],
    
    'pricing' => [
        'ttl' => 300,  // 5 minutes
        'tags' => ['pricing', 'products'],
        'scopes' => ['user'],  // User-specific pricing
    ],
];
```

### SaaS Application

```php
// config/filterable.php
'profiles' => [
    'tenant_data' => [
        'ttl' => 3600,
        'tags' => ['tenants'],
        'scopes' => ['tenant'],
    ],
    
    'user_dashboard' => [
        'ttl' => 600,
        'tags' => ['dashboards', 'users'],
        'scopes' => ['user', 'tenant'],
    ],
    
    'tenant_reports' => [
        'ttl' => 10800,  // 3 hours
        'tags' => ['reports', 'tenants'],
        'scopes' => ['tenant'],
    ],
];
```

## Profile Management

### Disabling Profiles

Temporarily disable a profile:

```php
'profile_name' => [
    'enabled' => false,  // Profile is disabled
    'ttl' => 3600,
    'tags' => ['example'],
],
```

Or use environment variables:

```php
'profile_name' => [
    'enabled' => env('ENABLE_PROFILE_NAME', true),
    'ttl' => 3600,
    'tags' => ['example'],
],
```

### Profile Fallback

Handle missing profiles gracefully:

```php
class BaseFilter extends Filterable
{
    protected function applyCacheProfile(string $profile): self
    {
        try {
            return $this->cacheProfile($profile);
        } catch (\InvalidArgumentException $e) {
            // Fallback to default caching
            Log::warning("Cache profile not found: {$profile}");
            return $this->cache(3600);
        }
    }
}
```

## Best Practices

### 1. Use Descriptive Names

```php
// ❌ Bad
'p1', 'p2', 'fast', 'slow'

// ✅ Good
'user_dashboard', 'product_catalog', 'heavy_reports'
```

### 2. Document Your Profiles

```php
'profiles' => [
    // Quick cache for frequently updated data (dashboards, live feeds)
    'quick' => [
        'ttl' => 300,
        'tags' => ['quick'],
    ],
    
    // Standard cache for typical queries (listings, searches)
    'standard' => [
        'ttl' => 3600,
        'tags' => ['standard'],
    ],
],
```

### 3. Group Related Tags

```php
'product_listing' => [
    'ttl' => 1800,
    'tags' => ['products', 'listings', 'catalog'],  // Easy to invalidate
],
```

### 4. Use Environment Variables

```php
'heavy_reports' => [
    'ttl' => env('CACHE_REPORTS_TTL', 21600),
    'tags' => ['reports'],
    'enabled' => env('CACHE_REPORTS_ENABLED', true),
],
```

### 5. Consider User Experience

```php
// Shorter cache for user-facing features
'user_dashboard' => [
    'ttl' => 600,  // 10 minutes
],

// Longer cache for admin features
'admin_analytics' => [
    'ttl' => 3600,  // 1 hour
],
```

### 6. Test Profile Performance

```php
// Use cache tracking to monitor profile effectiveness
'profiles' => [
    'test_profile' => [
        'ttl' => 1800,
        'tags' => ['test'],
    ],
],

// In your code
Log::info('Testing profile performance', [
    'profile' => 'test_profile',
    'cached' => Cache::has($key),
]);
```

## Troubleshooting

### Profile Not Found

```php
// Error: Cache profile 'xyz' is not defined

// Solution: Check config/filterable.php
'profiles' => [
    'xyz' => [  // Add missing profile
        'ttl' => 3600,
    ],
],
```

### Profile Not Working

```php
// Check if caching is globally enabled
// config/filterable.php
'cache' => [
    'enabled' => true,  // Must be true
],

// Check if profile is enabled
'profiles' => [
    'profile_name' => [
        'enabled' => true,  // Must be true
    ],
],
```

### Tags Not Working

```php
// Ensure you're using a driver that supports tags
// config/cache.php
'default' => env('CACHE_DRIVER', 'redis'),  // Use redis or memcached
```

::: tip Next Steps
- [Getting Started Guide →](./getting-started.md)
- [Caching Strategies →](./strategies.md)
- [API Reference →](./api-reference.md)
:::
