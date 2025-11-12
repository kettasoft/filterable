# Caching API Reference

::: tip Overview
Complete API reference for the Filterable caching system.
:::

## HasFilterableCache Trait

All methods available on any class using the `HasFilterableCache` trait.

### cache()

Enable caching with optional TTL.

```php
public function cache(DateTimeInterface|int|null $ttl = null): self
```

**Parameters:**
- `$ttl` - Time to live in seconds or DateTimeInterface instance

**Returns:** Self for method chaining

**Example:**
```php
Post::filter()->cache(3600)->get();
Post::filter()->cache(now()->addHour())->get();
```

---

### remember()

Alias for `cache()` method.

```php
public function remember(DateTimeInterface|int|null $ttl = null): self
```

**Example:**
```php
Post::filter()->remember(1800)->get();
```

---

### cacheForever()

Cache results permanently until manually flushed.

```php
public function cacheForever(): self
```

**Returns:** Self for method chaining

**Example:**
```php
Category::filter()->cacheForever()->get();
```

---

### cacheTags()

Set cache tags for organization and bulk invalidation.

```php
public function cacheTags(array $tags): self
```

**Parameters:**
- `$tags` - Array of tag strings

**Returns:** Self for method chaining

**Example:**
```php
Post::filter()
    ->cache(3600)
    ->cacheTags(['posts', 'content'])
    ->get();
```

::: warning
Cache tags require Redis or Memcached. Not supported by file/database drivers.
:::

---

### scopeByUser()

Scope cache by authenticated user ID.

```php
public function scopeByUser(int|string|null $userId = null): self
```

**Parameters:**
- `$userId` - User ID (defaults to `auth()->id()`)

**Returns:** Self for method chaining

**Example:**
```php
// Automatic user ID
Post::filter()
    ->cache(1800)
    ->scopeByUser()
    ->get();

// Explicit user ID
Post::filter()
    ->cache(1800)
    ->scopeByUser(123)
    ->get();
```

---

### scopeByTenant()

Scope cache by tenant ID for multi-tenant applications.

```php
public function scopeByTenant(int|string $tenantId): self
```

**Parameters:**
- `$tenantId` - Tenant identifier

**Returns:** Self for method chaining

**Example:**
```php
Post::filter()
    ->cache(3600)
    ->scopeByTenant(tenant()->id)
    ->get();
```

---

### scopeBy()

Add a custom cache scope.

```php
public function scopeBy(string $key, mixed $value): self
```

**Parameters:**
- `$key` - Scope key name
- `$value` - Scope value

**Returns:** Self for method chaining

**Example:**
```php
Post::filter()
    ->cache(3600)
    ->scopeBy('organization', $orgId)
    ->scopeBy('department', $deptId)
    ->get();
```

---

### withScopes()

Set multiple cache scopes at once.

```php
public function withScopes(array $scopes): self
```

**Parameters:**
- `$scopes` - Associative array of scope key-value pairs

**Returns:** Self for method chaining

**Example:**
```php
Post::filter()
    ->cache(3600)
    ->withScopes([
        'organization' => $orgId,
        'department' => $deptId,
        'region' => $region,
    ])
    ->get();
```

---

### cacheProfile()

Use a predefined cache profile from configuration.

```php
public function cacheProfile(string $profile): self
```

**Parameters:**
- `$profile` - Profile name from config

**Returns:** Self for method chaining

**Example:**
```php
// Uses settings from config/filterable.php profiles
Post::filter()
    ->cacheProfile('heavy_reports')
    ->get();
```

---

### cacheWhen()

Cache only when a condition is true.

```php
public function cacheWhen(bool|callable $condition, DateTimeInterface|int|null $ttl = null): self
```

**Parameters:**
- `$condition` - Boolean or callable returning boolean
- `$ttl` - Optional TTL override

**Returns:** Self for method chaining

**Example:**
```php
// Boolean condition
Post::filter()
    ->cacheWhen(!auth()->user()->isAdmin(), 3600)
    ->get();

// Callable condition
Post::filter()
    ->cacheWhen(fn() => !app()->isLocal(), 1800)
    ->get();
```

---

### cacheUnless()

Cache unless a condition is true (inverse of `cacheWhen`).

```php
public function cacheUnless(bool|callable $condition, DateTimeInterface|int|null $ttl = null): self
```

**Parameters:**
- `$condition` - Boolean or callable returning boolean
- `$ttl` - Optional TTL override

**Returns:** Self for method chaining

**Example:**
```php
Post::filter()
    ->cacheUnless(request()->has('refresh'), 3600)
    ->get();
```

---

### flushCache()

Flush all cached results for this filterable class.

```php
public function flushCache(): bool
```

**Returns:** True if cache was flushed

**Example:**
```php
$filter = new PostFilter();
$filter->flushCache(); // Flushes all PostFilter caches
```

---

### flushCacheByTags()

Flush cache by specific tags.

```php
public function flushCacheByTags(?array $tags = null): bool
```

**Parameters:**
- `$tags` - Array of tags (defaults to instance tags)

**Returns:** True if cache was flushed

**Example:**
```php
$filter = new PostFilter();
$filter->flushCacheByTags(['posts', 'content']);
```

---

### flushCacheByTagsStatic()

Static method to flush cache by tags.

```php
public static function flushCacheByTagsStatic(array $tags): bool
```

**Parameters:**
- `$tags` - Array of tags to flush

**Returns:** True if cache was flushed

**Example:**
```php
Post::flushCacheByTagsStatic(['posts']);
PostFilter::flushCacheByTagsStatic(['posts', 'content']);
```

---

### isCachingEnabled()

Check if caching is enabled for this instance.

```php
public function isCachingEnabled(): bool
```

**Returns:** True if caching is enabled

**Example:**
```php
$filter = Post::filter()->cache(3600);
if ($filter->isCachingEnabled()) {
    // Caching is active
}
```

---

### getCacheTtl()

Get the cache TTL setting.

```php
public function getCacheTtl(): DateTimeInterface|int|null
```

**Returns:** TTL value or null

---

### getCacheTags()

Get the cache tags for this instance.

```php
public function getCacheTags(): array
```

**Returns:** Array of cache tags (includes auto-generated class tag)

**Example:**
```php
$filter = Post::filter()
    ->cacheTags(['posts', 'content']);
    
$tags = $filter->getCacheTags();
// ['filterable:post_filter', 'posts', 'content']
```

---

### getCacheScopes()

Get the cache scopes for this instance.

```php
public function getCacheScopes(): array
```

**Returns:** Array of scope key-value pairs

---

### getCacheProfile()

Get the current cache profile name.

```php
public function getCacheProfile(): ?string
```

**Returns:** Profile name or null

---

## FilterableCacheManager

Singleton cache manager for advanced operations.

### getInstance()

Get the singleton instance.

```php
public static function getInstance(): FilterableCacheManager
```

**Example:**
```php
$manager = \Kettasoft\Filterable\Caching\FilterableCacheManager::getInstance();
```

---

### put()

Store a value in cache.

```php
public function put(string $key, mixed $value, DateTimeInterface|int|null $ttl = null): bool
```

**Parameters:**
- `$key` - Cache key
- `$value` - Value to cache
- `$ttl` - Time to live

**Returns:** True if successful

---

### remember()

Get from cache or execute callback and cache result.

```php
public function remember(string $key, DateTimeInterface|int|null $ttl, callable $callback): mixed
```

**Parameters:**
- `$key` - Cache key
- `$ttl` - Time to live
- `$callback` - Callback to execute if cache miss

**Returns:** Cached or computed value

---

### forever()

Cache a value permanently.

```php
public function forever(string $key, mixed $value): bool
```

---

### rememberForever()

Remember forever version of `remember()`.

```php
public function rememberForever(string $key, callable $callback): mixed
```

---

### get()

Retrieve a value from cache.

```php
public function get(string $key, $default = null): mixed
```

**Parameters:**
- `$key` - Cache key
- `$default` - Default value if not found

**Returns:** Cached value or default

---

### has()

Check if a key exists in cache.

```php
public function has(string $key): bool
```

---

### forget()

Remove a value from cache.

```php
public function forget(string $key): bool
```

---

### flushByTags()

Flush all cache entries with given tags.

```php
public function flushByTags(array $tags): bool
```

**Parameters:**
- `$tags` - Array of tags

**Returns:** True if successful

::: warning
Requires Redis or Memcached cache driver.
:::

---

### withTags()

Set tags for the next operation.

```php
public function withTags(array $tags): self
```

**Returns:** Self for method chaining

---

### addScope()

Add a scope to the cache manager.

```php
public function addScope(string $key, mixed $value): self
```

---

### generateKey()

Generate a cache key with current scopes.

```php
public function generateKey(string $baseKey): string
```

---

### enable() / disable()

Enable or disable caching globally.

```php
public function enable(): self
public function disable(): self
```

---

### isEnabled()

Check if caching is globally enabled.

```php
public function isEnabled(): bool
```

---

## CacheKeyGenerator

Generates deterministic cache keys.

### generate()

Generate a cache key for a filterable operation.

```php
public function generate(
    string $filterClass,
    array $filters = [],
    array $providedData = [],
    array $scopes = [],
    ?Builder $query = null
): string
```

**Parameters:**
- `$filterClass` - Filter class name
- `$filters` - Applied filters
- `$providedData` - Provided data
- `$scopes` - Cache scopes
- `$query` - Optional query builder

**Returns:** Generated cache key

---

### simple()

Generate a simple cache key from components.

```php
public function simple(string ...$components): string
```

**Returns:** Generated cache key

---

### forUser()

Generate a user-scoped cache key.

```php
public function forUser(string $filterClass, int|string $userId, array $filters = []): string
```

---

### forTenant()

Generate a tenant-scoped cache key.

```php
public function forTenant(string $filterClass, int|string $tenantId, array $filters = []): string
```

---

## Configuration Reference

```php
// config/filterable.php
return [
    'cache' => [
        // Global enable/disable
        'enabled' => env('FILTERABLE_CACHE_ENABLED', true),
        
        // Cache driver (null = use default)
        'driver' => env('FILTERABLE_CACHE_DRIVER', null),
        
        // Default TTL in seconds
        'default_ttl' => env('FILTERABLE_CACHE_TTL', 3600),
        
        // Cache key prefix
        'prefix' => env('FILTERABLE_CACHE_PREFIX', 'filterable'),
        
        // Cache version (increment to bust all caches)
        'version' => env('FILTERABLE_CACHE_VERSION', 'v1'),
        
        // Cache profiles
        'profiles' => [
            'profile_name' => [
                'ttl' => 7200,
                'tags' => ['tag1', 'tag2'],
            ],
        ],
        
        // Auto-invalidation
        'auto_invalidate' => [
            'enabled' => env('FILTERABLE_AUTO_INVALIDATE', false),
            'models' => [
                App\Models\Post::class => ['posts', 'content'],
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

## Environment Variables

```env
# Enable/disable caching
FILTERABLE_CACHE_ENABLED=true

# Cache driver
FILTERABLE_CACHE_DRIVER=redis

# Default TTL (seconds)
FILTERABLE_CACHE_TTL=3600

# Cache key prefix
FILTERABLE_CACHE_PREFIX=filterable

# Cache version
FILTERABLE_CACHE_VERSION=v1

# Auto-invalidation
FILTERABLE_AUTO_INVALIDATE=true

# Cache tracking
FILTERABLE_CACHE_TRACKING=true
FILTERABLE_CACHE_LOG_CHANNEL=daily
```

::: tip Next Steps
- [Getting started guide →](./getting-started.md)
- [Caching strategies →](./strategies.md)
- [Examples →](./examples.md)
:::
