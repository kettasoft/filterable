# Cache Monitoring & Debugging

::: tip Overview
Learn how to monitor cache performance, debug cache issues, and optimize your caching strategy.
:::

## Cache Tracking

### Enabling Cache Tracking

Enable detailed cache tracking in your configuration:

```php
// config/filterable.php
return [
    'cache' => [
        'tracking' => [
            'enabled' => env('FILTERABLE_CACHE_TRACKING', false),
            'log_channel' => env('FILTERABLE_CACHE_LOG_CHANNEL', 'daily'),
            'metrics' => [
                'hits' => true,
                'misses' => true,
                'timings' => true,
                'keys' => true,
            ],
        ],
    ],
];
```

### Environment Configuration

```env
# .env
FILTERABLE_CACHE_TRACKING=true
FILTERABLE_CACHE_LOG_CHANNEL=daily
```

### What Gets Tracked

When tracking is enabled, the following information is logged:

-   **Cache Hits**: When a result is served from cache
-   **Cache Misses**: When a query executes (cache miss)
-   **Query Timing**: Database query execution time vs cache retrieval time
-   **Cache Keys**: Generated cache keys for debugging
-   **TTL Values**: Time to live for each cache entry
-   **Tags & Scopes**: Applied tags and scopes

## Monitoring Cache Performance

### Basic Performance Metrics

```php
use Kettasoft\Filterable\Caching\FilterableCacheManager;
use Illuminate\Support\Facades\Log;

// Enable profiling for a query
$startTime = microtime(true);

$posts = Post::filter()
    ->cache(3600)
    ->get();

$executionTime = microtime(true) - $startTime;

Log::info('Query performance', [
    'execution_time' => $executionTime,
    'cached' => true,
]);
```

### Cache Hit Rate

Track cache effectiveness:

```php
class CacheMetrics
{
    private static int $hits = 0;
    private static int $misses = 0;

    public static function recordHit(): void
    {
        static::$hits++;
    }

    public static function recordMiss(): void
    {
        static::$misses++;
    }

    public static function getHitRate(): float
    {
        $total = static::$hits + static::$misses;
        return $total > 0 ? (static::$hits / $total) * 100 : 0;
    }

    public static function report(): array
    {
        return [
            'hits' => static::$hits,
            'misses' => static::$misses,
            'hit_rate' => static::getHitRate(),
        ];
    }
}
```

Usage in middleware:

```php
// app/Http/Middleware/CacheMetricsMiddleware.php
class CacheMetricsMiddleware
{
    public function handle($request, Closure $next)
    {
        $response = $next($request);

        if (app()->environment('local')) {
            Log::debug('Cache metrics', CacheMetrics::report());
        }

        return $response;
    }
}
```

### Query Timing Comparison

Compare cached vs non-cached query performance:

```php
use Illuminate\Support\Facades\DB;

// Measure database query time
DB::enableQueryLog();
$startDb = microtime(true);

$resultsFromDb = Post::filter()->apply(['status' => 'published'])->get();

$dbTime = microtime(true) - $startDb;
$queries = DB::getQueryLog();

// Measure cache query time
$startCache = microtime(true);

$resultsFromCache = Post::filter()
    ->cache(3600)
    ->apply(['status' => 'published'])
    ->get();

$cacheTime = microtime(true) - $startCache;

Log::info('Performance comparison', [
    'db_time' => $dbTime,
    'cache_time' => $cacheTime,
    'improvement' => round(($dbTime / $cacheTime), 2) . 'x faster',
    'queries' => count($queries),
]);
```

## Debugging Cache Issues

### Inspecting Cache Keys

View generated cache keys:

```php
use Kettasoft\Filterable\Caching\CacheKeyGenerator;

$generator = new CacheKeyGenerator();

$key = $generator->generate(
    PostFilter::class,
    ['status' => 'published'],
    [],
    ['user' => auth()->id()]
);

Log::debug('Generated cache key', [
    'key' => $key,
    'filter' => PostFilter::class,
]);
```

### Verifying Cache Contents

Check if a key exists and view its contents:

```php
use Illuminate\Support\Facades\Cache;

$filter = Post::filter()->cache(3600);
$key = $filter->getCacheKey();

if (Cache::has($key)) {
    $cached = Cache::get($key);
    Log::debug('Cache contents', [
        'key' => $key,
        'data' => $cached,
        'ttl' => Cache::store()->getStore()->ttl($key),
    ]);
} else {
    Log::debug('Cache miss', ['key' => $key]);
}
```

### Cache Key Conflicts

Debug key generation issues:

```php
// Check for key conflicts
$keys = [];

$filters = [
    ['status' => 'published'],
    ['status' => 'draft'],
    ['status' => 'published', 'author' => 1],
];

foreach ($filters as $filter) {
    $key = (new CacheKeyGenerator())->generate(
        PostFilter::class,
        $filter
    );

    if (in_array($key, $keys)) {
        Log::error('Cache key conflict detected!', [
            'key' => $key,
            'filter' => $filter,
        ]);
    }

    $keys[] = $key;
}
```

### Debug Mode

Enable verbose debugging:

```php
// config/filterable.php
return [
    'cache' => [
        'debug' => env('FILTERABLE_CACHE_DEBUG', false),
    ],
];
```

```php
// In your filter
if (config('filterable.cache.debug')) {
    Log::debug('Filterable cache debug', [
        'filter' => static::class,
        'enabled' => $this->isCachingEnabled(),
        'ttl' => $this->getCacheTtl(),
        'tags' => $this->getCacheTags(),
        'scopes' => $this->getCacheScopes(),
        'key' => $this->getCacheKey(),
    ]);
}
```

## Cache Profiling

### Using Laravel Debugbar

Install and configure Laravel Debugbar:

```bash
composer require barryvdh/laravel-debugbar --dev
```

Create a custom collector:

```php
// app/Http/Debugbar/FilterableCacheCollector.php
use DebugBar\DataCollector\DataCollector;
use DebugBar\DataCollector\Renderable;

class FilterableCacheCollector extends DataCollector implements Renderable
{
    protected array $queries = [];

    public function addQuery(array $query): void
    {
        $this->queries[] = $query;
    }

    public function collect(): array
    {
        return [
            'count' => count($this->queries),
            'queries' => $this->queries,
        ];
    }

    public function getName(): string
    {
        return 'filterable_cache';
    }

    public function getWidgets(): array
    {
        return [
            'filterable_cache' => [
                'icon' => 'database',
                'widget' => 'PhpDebugBar.Widgets.SQLQueriesWidget',
                'map' => 'filterable_cache',
                'default' => '[]',
            ],
            'filterable_cache:badge' => [
                'map' => 'filterable_cache.count',
                'default' => 0,
            ],
        ];
    }
}
```

Register the collector:

```php
// config/debugbar.php
'collectors' => [
    'filterable_cache' => [
        'class' => \App\Http\Debugbar\FilterableCacheCollector::class,
    ],
],
```

### Custom Profiler

Create a simple profiler:

```php
// app/Services/FilterableCacheProfiler.php
class FilterableCacheProfiler
{
    private array $profile = [];

    public function start(string $key): void
    {
        $this->profile[$key] = [
            'start' => microtime(true),
            'memory_start' => memory_get_usage(),
        ];
    }

    public function end(string $key, bool $cached = false): void
    {
        if (!isset($this->profile[$key])) {
            return;
        }

        $this->profile[$key]['end'] = microtime(true);
        $this->profile[$key]['memory_end'] = memory_get_usage();
        $this->profile[$key]['duration'] = $this->profile[$key]['end'] - $this->profile[$key]['start'];
        $this->profile[$key]['memory'] = $this->profile[$key]['memory_end'] - $this->profile[$key]['memory_start'];
        $this->profile[$key]['cached'] = $cached;
    }

    public function getProfile(): array
    {
        return $this->profile;
    }

    public function report(): void
    {
        $total = count($this->profile);
        $cached = collect($this->profile)->where('cached', true)->count();
        $avgDuration = collect($this->profile)->avg('duration');

        Log::info('Filterable Cache Profile', [
            'total_queries' => $total,
            'cached_queries' => $cached,
            'cache_hit_rate' => $total > 0 ? round(($cached / $total) * 100, 2) . '%' : '0%',
            'avg_duration' => round($avgDuration * 1000, 2) . 'ms',
            'queries' => $this->profile,
        ]);
    }
}
```

Usage:

```php
$profiler = new FilterableCacheProfiler();

$profiler->start('posts_query');
$posts = Post::filter()->cache(3600)->get();
$profiler->end('posts_query', true);

$profiler->start('users_query');
$users = User::filter()->get();  // Not cached
$profiler->end('users_query', false);

$profiler->report();
```

## Log Analysis

### Analyzing Cache Logs

View cache logs:

```bash
# View today's cache logs
tail -f storage/logs/laravel.log | grep "filterable"

# Search for cache misses
grep "cache miss" storage/logs/laravel.log

# Search for cache hits
grep "cache hit" storage/logs/laravel.log

# Calculate hit rate
echo "Hit rate: $(( $(grep -c "cache hit" storage/logs/laravel.log) * 100 / $(grep -c "filterable" storage/logs/laravel.log) ))%"
```

### Cache Statistics Command

Create an artisan command to analyze cache usage:

```php
// app/Console/Commands/CacheStatsCommand.php
namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;

class CacheStatsCommand extends Command
{
    protected $signature = 'filterable:cache-stats';
    protected $description = 'Display Filterable cache statistics';

    public function handle()
    {
        $this->info('Filterable Cache Statistics');
        $this->line('----------------------------');

        // Get all filterable cache keys
        $keys = Cache::get('filterable:all_keys', []);

        $this->table(
            ['Metric', 'Value'],
            [
                ['Total Keys', count($keys)],
                ['Cache Driver', config('cache.default')],
                ['Tracking Enabled', config('filterable.cache.tracking.enabled') ? 'Yes' : 'No'],
                ['Default TTL', config('filterable.cache.default_ttl') . 's'],
            ]
        );

        if ($this->option('verbose')) {
            $this->line('');
            $this->info('Cache Keys:');
            foreach ($keys as $key) {
                $this->line("  - {$key}");
            }
        }
    }
}
```

## Real-time Monitoring

### Event Listeners

Create listeners for cache events:

```php
// app/Listeners/CacheHitListener.php
namespace App\Listeners;

use Illuminate\Support\Facades\Log;

class CacheHitListener
{
    public function handle($event)
    {
        Log::info('Cache hit', [
            'key' => $event->key,
            'tags' => $event->tags ?? [],
        ]);
    }
}
```

```php
// app/Listeners/CacheMissListener.php
namespace App\Listeners;

use Illuminate\Support\Facades\Log;

class CacheMissListener
{
    public function handle($event)
    {
        Log::warning('Cache miss', [
            'key' => $event->key,
            'tags' => $event->tags ?? [],
        ]);
    }
}
```

Register listeners:

```php
// app/Providers/EventServiceProvider.php
protected $listen = [
    'Illuminate\Cache\Events\CacheHit' => [
        CacheHitListener::class,
    ],
    'Illuminate\Cache\Events\CacheMissed' => [
        CacheMissListener::class,
    ],
];
```

### Dashboard Integration

Create a simple dashboard route:

```php
// routes/web.php
Route::get('/cache-dashboard', function () {
    $stats = [
        'enabled' => config('filterable.cache.enabled'),
        'driver' => config('cache.default'),
        'tracking' => config('filterable.cache.tracking.enabled'),
        'profiles' => array_keys(config('filterable.cache.profiles', [])),
    ];

    return view('cache-dashboard', compact('stats'));
});
```

## Performance Testing

### Load Testing Cache

Test cache performance under load:

```php
// tests/Performance/CachePerformanceTest.php
namespace Tests\Performance;

use Tests\TestCase;
use App\Models\Post;
use Illuminate\Support\Facades\DB;

class CachePerformanceTest extends TestCase
{
    public function test_cache_performance()
    {
        // Create test data
        Post::factory()->count(1000)->create();

        // Test without cache
        DB::enableQueryLog();
        $startNoCacheTime = microtime(true);

        for ($i = 0; $i < 10; $i++) {
            Post::filter()->apply(['status' => 'published'])->get();
        }

        $noCacheTime = microtime(true) - $startNoCacheTime;
        $noCacheQueries = count(DB::getQueryLog());

        // Test with cache
        DB::flushQueryLog();
        $startCacheTime = microtime(true);

        for ($i = 0; $i < 10; $i++) {
            Post::filter()
                ->cache(3600)
                ->apply(['status' => 'published'])
                ->get();
        }

        $cacheTime = microtime(true) - $startCacheTime;
        $cacheQueries = count(DB::getQueryLog());

        $this->assertLessThan($noCacheTime, $cacheTime);
        $this->assertLessThan($noCacheQueries, $cacheQueries);

        dump([
            'no_cache_time' => $noCacheTime,
            'cache_time' => $cacheTime,
            'improvement' => round($noCacheTime / $cacheTime, 2) . 'x',
            'no_cache_queries' => $noCacheQueries,
            'cache_queries' => $cacheQueries,
        ]);
    }
}
```

### Memory Usage Testing

Monitor memory consumption:

```php
public function test_cache_memory_usage()
{
    $memoryStart = memory_get_usage();

    // Large dataset
    Post::factory()->count(10000)->create();

    // Without cache
    $posts1 = Post::filter()->get();
    $memoryAfterQuery = memory_get_usage();

    // With cache
    $posts2 = Post::filter()->cache(3600)->get();
    $memoryAfterCache = memory_get_usage();

    dump([
        'query_memory' => ($memoryAfterQuery - $memoryStart) / 1024 / 1024 . ' MB',
        'cache_memory' => ($memoryAfterCache - $memoryAfterQuery) / 1024 / 1024 . ' MB',
    ]);
}
```

## Troubleshooting Guide

### Cache Not Working

**Check global cache status:**

```php
// Verify cache is enabled
config('filterable.cache.enabled'); // Should be true

// Check cache driver
config('cache.default'); // Should not be 'array' in production
```

**Verify cache connection:**

```php
use Illuminate\Support\Facades\Cache;

try {
    Cache::put('test_key', 'test_value', 60);
    $result = Cache::get('test_key');

    if ($result === 'test_value') {
        echo "Cache is working!";
    }
} catch (\Exception $e) {
    Log::error('Cache connection failed', ['error' => $e->getMessage()]);
}
```

### Cache Not Invalidating

**Check auto-invalidation configuration:**

```php
// config/filterable.php
'auto_invalidate' => [
    'enabled' => true,  // Must be true
    'models' => [
        App\Models\Post::class => ['posts'],  // Ensure model is listed
    ],
],
```

**Verify observer is registered:**

```php
// app/Providers/AppServiceProvider.php
use Kettasoft\Filterable\Caching\CacheInvalidationObserver;

public function boot()
{
    Post::observe(CacheInvalidationObserver::class);
}
```

**Manual invalidation:**

```php
// Manually flush cache
Post::flushCacheByTagsStatic(['posts']);
```

### Tags Not Working

**Check cache driver:**

```php
// Tags require Redis or Memcached
config('cache.default'); // Should be 'redis' or 'memcached'
```

**Verify tag support:**

```php
use Illuminate\Support\Facades\Cache;

if (method_exists(Cache::getStore(), 'tags')) {
    echo "Tags are supported!";
} else {
    echo "Tags are NOT supported. Use Redis or Memcached.";
}
```

### High Memory Usage

**Check cache size:**

```bash
# Redis
redis-cli info memory

# Check specific keys
redis-cli --scan --pattern "filterable:*"
```

**Reduce cache payload:**

```php
// Only cache what you need
$posts = Post::filter()
    ->cache(3600)
    ->select(['id', 'title', 'status'])  // Don't cache everything
    ->get();
```

### Slow Cache Operations

**Profile cache operations:**

```php
$start = microtime(true);
$result = Cache::remember('key', 3600, fn() => expensiveOperation());
$duration = microtime(true) - $start;

if ($duration > 0.1) {  // Slower than 100ms
    Log::warning('Slow cache operation', [
        'key' => 'key',
        'duration' => $duration,
    ]);
}
```

## Best Practices

### 1. Enable Tracking in Development

```php
// .env.local
FILTERABLE_CACHE_TRACKING=true
FILTERABLE_CACHE_DEBUG=true
```

### 2. Monitor Cache Hit Rate

Aim for >80% hit rate in production:

```php
if ($hitRate < 80) {
    Log::warning('Low cache hit rate', [
        'hit_rate' => $hitRate,
        'recommendation' => 'Review TTL settings and cache warming strategy',
    ]);
}
```

### 3. Set Up Alerts

```php
// Alert on cache failures
if (!Cache::has($criticalKey)) {
    \Sentry::captureMessage('Critical cache miss');
}
```

### 4. Regular Cache Audits

```bash
# Weekly cache audit
php artisan filterable:cache-stats --verbose
```

### 5. Document Cache Strategy

```php
/**
 * PostFilter
 *
 * Cache Strategy:
 * - TTL: 30 minutes
 * - Tags: ['posts', 'content']
 * - Scopes: ['user']
 * - Invalidation: Auto on Post create/update/delete
 */
class PostFilter extends Filterable
{
    // ...
}
```

::: tip Next Steps

-   [Getting Started →](./getting-started.md)
-   [Caching Strategies →](./strategies.md)
-   [API Reference →](./api-reference.md)
    :::
