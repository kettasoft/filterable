# Caching Examples

::: tip
Real-world examples and common patterns for using Filterable caching.
:::

## Basic Examples

### Simple List Caching

```php
// Cache blog post listings for 30 minutes
$posts = Post::filter()
    ->cache(1800)
    ->apply(['status' => 'published'])
    ->orderBy('created_at', 'desc')
    ->get();
```

### Permanent Static Content

```php
// Cache categories until manually flushed
$categories = Category::filter()
    ->cacheForever()
    ->apply(['active' => true])
    ->get();
```

### Conditional Caching

```php
// Only cache for non-admin users
$data = Model::filter()
    ->cacheWhen(!auth()->user()->isAdmin(), 3600)
    ->get();

// Cache unless refresh requested
$data = Model::filter()
    ->cacheUnless(request()->has('refresh'), 3600)
    ->get();
```

## Blog Platform Examples

### Post Listing Page

```php
class PostController extends Controller
{
    public function index(Request $request)
    {
        $posts = Post::filter()
            ->cache(1800)  // 30 minutes
            ->cacheTags(['posts', 'listings'])
            ->apply($request->all())
            ->paginate(15);
            
        return view('posts.index', compact('posts'));
    }
}
```

### Single Post Page

```php
public function show($slug)
{
    $post = Post::filter()
        ->cache(3600)  // 1 hour
        ->cacheTags(['posts', 'details'])
        ->apply(['slug' => $slug])
        ->firstOrFail();
        
    return view('posts.show', compact('post'));
}
```

### Author Page

```php
public function author($username)
{
    $author = User::where('username', $username)->firstOrFail();
    
    $posts = Post::filter()
        ->cache(1800)
        ->cacheTags(['posts', 'authors'])
        ->scopeBy('author', $author->id)
        ->apply(['status' => 'published'])
        ->orderBy('published_at', 'desc')
        ->get();
        
    return view('authors.show', compact('author', 'posts'));
}
```

### Category Page

```php
public function category($slug)
{
    $category = Category::where('slug', $slug)->firstOrFail();
    
    $posts = Post::filter()
        ->cache(1800)
        ->cacheTags(['posts', 'categories', "category:{$category->id}"])
        ->apply([
            'category' => $category->id,
            'status' => 'published',
        ])
        ->paginate(15);
        
    return view('categories.show', compact('category', 'posts'));
}
```

### Search Results

```php
public function search(Request $request)
{
    $query = $request->input('q');
    
    $results = Post::filter()
        ->cache(600)  // 10 minutes
        ->cacheTags(['posts', 'search'])
        ->scopeBy('search_term', md5($query))
        ->apply([
            'search' => $query,
            'status' => 'published',
        ])
        ->paginate(20);
        
    return view('search', compact('results', 'query'));
}
```

### Auto-invalidation Setup

```php
// config/filterable.php
'auto_invalidate' => [
    'enabled' => true,
    'models' => [
        \App\Models\Post::class => ['posts', 'content'],
        \App\Models\Category::class => ['categories', 'navigation'],
        \App\Models\Tag::class => ['tags'],
    ],
],
```

## E-commerce Examples

### Product Catalog

```php
class ProductController extends Controller
{
    public function catalog(Request $request)
    {
        $products = Product::filter()
            ->cache(3600)  // 1 hour
            ->cacheTags(['products', 'catalog'])
            ->apply($request->only([
                'category',
                'price_min',
                'price_max',
                'brand',
                'in_stock',
            ]))
            ->paginate(24);
            
        return view('products.catalog', compact('products'));
    }
}
```

### Product Search

```php
public function search(Request $request)
{
    $query = $request->input('q');
    
    $products = Product::filter()
        ->cache(600)  // 10 minutes
        ->cacheTags(['products', 'search'])
        ->apply([
            'search' => $query,
            'available' => true,
        ])
        ->paginate(24);
        
    return view('products.search', compact('products', 'query'));
}
```

### Product Details

```php
public function show($slug)
{
    $product = Product::filter()
        ->cache(3600)
        ->cacheTags(['products', 'details'])
        ->apply(['slug' => $slug, 'available' => true])
        ->firstOrFail();
        
    // Related products
    $related = Product::filter()
        ->cache(7200)  // 2 hours
        ->cacheTags(['products', 'related'])
        ->scopeBy('related_to', $product->id)
        ->apply([
            'category' => $product->category_id,
            'available' => true,
        ])
        ->limit(8)
        ->get();
        
    return view('products.show', compact('product', 'related'));
}
```

### Cart Recommendations

```php
public function cartRecommendations()
{
    $cartItems = auth()->user()->cart->items;
    
    $recommendations = Product::filter()
        ->cache(1800)
        ->cacheTags(['products', 'recommendations'])
        ->scopeByUser()
        ->apply([
            'frequently_bought_together' => $cartItems->pluck('product_id'),
            'available' => true,
        ])
        ->limit(10)
        ->get();
        
    return view('cart.recommendations', compact('recommendations'));
}
```

### User-specific Pricing

```php
public function catalog(Request $request)
{
    $user = auth()->user();
    
    $products = Product::filter()
        ->cache(1800)
        ->cacheTags(['products', 'catalog'])
        ->scopeByUser()  // User-specific pricing
        ->scopeBy('customer_group', $user->customer_group_id)
        ->apply($request->all())
        ->paginate(24);
        
    return view('products.catalog', compact('products'));
}
```

## SaaS Application Examples

### Tenant Dashboard

```php
class DashboardController extends Controller
{
    public function index()
    {
        $metrics = Metric::filter()
            ->cache(600)  // 10 minutes
            ->cacheTags(['metrics', 'dashboard'])
            ->scopeByTenant(tenant()->id)
            ->apply([
                'period' => 'last_30_days',
            ])
            ->get();
            
        $recentActivity = Activity::filter()
            ->cache(300)  // 5 minutes
            ->cacheTags(['activity'])
            ->scopeByTenant(tenant()->id)
            ->scopeByUser()
            ->latest()
            ->limit(20)
            ->get();
            
        return view('dashboard', compact('metrics', 'recentActivity'));
    }
}
```

### User Management

```php
public function users(Request $request)
{
    $users = User::filter()
        ->cache(1800)
        ->cacheTags(['users'])
        ->scopeByTenant(tenant()->id)
        ->apply($request->only(['role', 'status', 'search']))
        ->paginate(50);
        
    return view('users.index', compact('users'));
}
```

### Reports

```php
public function salesReport(Request $request)
{
    $report = SalesReport::filter()
        ->cacheProfile('heavy_reports')  // Using profile
        ->scopeByTenant(tenant()->id)
        ->apply($request->only([
            'start_date',
            'end_date',
            'group_by',
        ]))
        ->get();
        
    return view('reports.sales', compact('report'));
}
```

### Multi-level Scoping

```php
public function organizationDepartmentData($orgId, $deptId)
{
    $data = Data::filter()
        ->cache(3600)
        ->cacheTags(['data', 'organizations', 'departments'])
        ->scopeByTenant(tenant()->id)
        ->scopeBy('organization', $orgId)
        ->scopeBy('department', $deptId)
        ->apply(request()->all())
        ->get();
        
    return view('data.show', compact('data'));
}
```

## API Examples

### Public API Endpoints

```php
class ApiController extends Controller
{
    public function posts(Request $request)
    {
        $posts = Post::filter()
            ->cache(3600)
            ->cacheTags(['api', 'posts'])
            ->scopeBy('api_version', 'v1')
            ->apply($request->only(['category', 'tag', 'author']))
            ->paginate(20);
            
        return response()->json($posts);
    }
}
```

### Authenticated API

```php
public function userPosts(Request $request)
{
    $posts = Post::filter()
        ->cache(1800)
        ->cacheTags(['api', 'posts'])
        ->scopeByUser()
        ->apply($request->all())
        ->paginate(20);
        
    return response()->json($posts);
}
```

### Rate-limited Endpoints

```php
public function heavyQuery(Request $request)
{
    $cacheKey = "api:heavy:{$request->user()->id}:" . md5(json_encode($request->all()));
    
    $result = Cache::remember($cacheKey, 300, function () use ($request) {
        return ComplexQuery::filter()
            ->apply($request->all())
            ->get();
    });
    
    return response()->json($result);
}
```

## Real-time & Live Data

### Live Dashboard

```php
public function liveDashboard()
{
    // Very short cache for live data
    $liveMetrics = Metric::filter()
        ->cache(30)  // 30 seconds
        ->cacheTags(['live', 'dashboard'])
        ->scopeByUser()
        ->apply(['period' => 'realtime'])
        ->get();
        
    return view('dashboard.live', compact('liveMetrics'));
}
```

### Notification Feed

```php
public function notifications()
{
    $notifications = Notification::filter()
        ->cache(60)  // 1 minute
        ->cacheTags(['notifications'])
        ->scopeByUser()
        ->apply(['status' => 'unread'])
        ->latest()
        ->limit(50)
        ->get();
        
    return response()->json($notifications);
}
```

## Multi-tenant Examples

### Tenant Isolation

```php
class TenantDataController extends Controller
{
    public function index(Request $request)
    {
        $data = Model::filter()
            ->cache(3600)
            ->cacheTags(['data'])
            ->scopeByTenant(tenant()->id)  // Isolate by tenant
            ->apply($request->all())
            ->get();
            
        return view('data.index', compact('data'));
    }
}
```

### Cross-tenant Aggregation (Admin)

```php
public function adminDashboard()
{
    // Don't scope by tenant for admin view
    $aggregatedData = Metric::filter()
        ->cache(600)
        ->cacheTags(['admin', 'metrics'])
        ->apply([
            'period' => 'today',
            'aggregate' => true,
        ])
        ->get();
        
    return view('admin.dashboard', compact('aggregatedData'));
}
```

## Advanced Patterns

### Layered Caching

```php
public function complexQuery(Request $request)
{
    // Layer 1: Cache base query
    $baseData = Model::filter()
        ->cache(7200)
        ->cacheTags(['base'])
        ->apply($request->only(['category', 'type']))
        ->get();
        
    // Layer 2: Cache aggregated results
    $cacheKey = 'aggregated:' . md5($baseData->pluck('id')->join(','));
    
    $result = Cache::remember($cacheKey, 3600, function () use ($baseData) {
        return $baseData->groupBy('type')
            ->map(fn($items) => $items->count());
    });
    
    return response()->json($result);
}
```

### Cache Warming

```php
// Warm cache during off-peak hours
class CacheWarmerCommand extends Command
{
    protected $signature = 'cache:warm';
    
    public function handle()
    {
        $this->info('Warming popular queries...');
        
        // Warm product catalog
        Product::filter()
            ->cache(3600)
            ->cacheTags(['products', 'catalog'])
            ->apply(['featured' => true])
            ->get();
            
        // Warm blog posts
        Post::filter()
            ->cache(3600)
            ->cacheTags(['posts'])
            ->apply(['status' => 'published'])
            ->limit(100)
            ->get();
            
        $this->info('Cache warmed successfully!');
    }
}
```

Schedule it:

```php
// app/Console/Kernel.php
protected function schedule(Schedule $schedule)
{
    $schedule->command('cache:warm')
        ->dailyAt('03:00');  // 3 AM
}
```

### Progressive Cache

```php
public function progressiveCatalog(Request $request)
{
    // Quick cache for first page
    if ($request->input('page', 1) == 1) {
        return Product::filter()
            ->cache(1800)  // 30 minutes
            ->cacheTags(['products', 'first-page'])
            ->apply($request->all())
            ->paginate(24);
    }
    
    // Longer cache for subsequent pages
    return Product::filter()
        ->cache(7200)  // 2 hours
        ->cacheTags(['products', 'pages'])
        ->apply($request->all())
        ->paginate(24);
}
```

### Fallback Caching

```php
public function reliableQuery(Request $request)
{
    try {
        return Model::filter()
            ->cache(3600)
            ->apply($request->all())
            ->get();
    } catch (\Exception $e) {
        Log::error('Cache failed, falling back to direct query', [
            'error' => $e->getMessage(),
        ]);
        
        // Fallback to non-cached query
        return Model::filter()
            ->apply($request->all())
            ->get();
    }
}
```

## Testing Examples

### Testing Cached Queries

```php
// tests/Feature/CachedQueryTest.php
class CachedQueryTest extends TestCase
{
    public function test_query_is_cached()
    {
        Post::factory()->count(10)->create();
        
        // First call - cache miss
        DB::enableQueryLog();
        $posts1 = Post::filter()->cache(3600)->get();
        $queries1 = count(DB::getQueryLog());
        
        // Second call - cache hit
        DB::flushQueryLog();
        $posts2 = Post::filter()->cache(3600)->get();
        $queries2 = count(DB::getQueryLog());
        
        $this->assertEquals($posts1->count(), $posts2->count());
        $this->assertGreaterThan($queries2, $queries1);
        $this->assertEquals(0, $queries2);  // No queries on cache hit
    }
}
```

### Testing Cache Invalidation

```php
public function test_cache_invalidates_on_model_update()
{
    $post = Post::factory()->create();
    
    // Cache the query
    $cached = Post::filter()
        ->cache(3600)
        ->cacheTags(['posts'])
        ->first();
        
    // Update the model
    $post->update(['title' => 'Updated Title']);
    
    // Query again
    $refreshed = Post::filter()
        ->cache(3600)
        ->cacheTags(['posts'])
        ->first();
        
    $this->assertEquals('Updated Title', $refreshed->title);
}
```

## Performance Optimization

### Selective Caching

```php
public function optimizedQuery(Request $request)
{
    // Only cache simple queries
    $isCacheable = !$request->has('complex_filter');
    
    $query = Model::filter();
    
    if ($isCacheable) {
        $query->cache(3600)->cacheTags(['simple']);
    }
    
    return $query->apply($request->all())->get();
}
```

### Batch Loading with Cache

```php
public function batchData(array $ids)
{
    return collect($ids)->map(function ($id) {
        return Model::filter()
            ->cache(3600)
            ->cacheTags(['batch'])
            ->scopeBy('batch_id', $id)
            ->first();
    });
}
```

::: tip Next Steps
- [Getting Started →](./getting-started.md)
- [Caching Strategies →](./strategies.md)
- [API Reference →](./api-reference.md)
:::
